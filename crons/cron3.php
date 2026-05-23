<?php
// Optimized Webhook Cron Job - Runs every 1 minute
// Enhanced for performance, reliability, and large dataset handling

define('PROJECT_ROOT', realpath(dirname(__FILE__)) . '/../');
require_once PROJECT_ROOT . 'pages/dbFunctions.php';
require_once PROJECT_ROOT . 'auth/config.php';

date_default_timezone_set("Asia/Kolkata");

// Performance Configuration
$config = [
    'max_retries' => 2,
    'retry_delays' => [1, 3], // Reduced retry delays
    'max_execution_time' => 600, // 10 minutes
    'memory_limit' => '1024M',
    'chunk_size' => 100, // Increased chunk size
    'curl_timeout' => 30, // Reduced timeout
    'curl_connect_timeout' => 10,
    'process_delay' => 25000, // 0.025 seconds between requests
    'batch_delay' => 0.5 // 0.5 seconds between batches
];

// Set execution limits
set_time_limit($config['max_execution_time']);
ini_set('memory_limit', $config['memory_limit']);

// Initialize counters
$stats = [
    'start_time' => microtime(true),
    'webhooks_processed' => 0,
    'webhooks_success' => 0,
    'webhooks_failed' => 0,
    'notifications_sent' => 0,
    'payment_links_deleted' => 0,
    'memory_peak' => 0
];

// Enhanced logging with rotation
function logWebhookData($data, $logFile = 'callback.log') {
    // Rotate log if it gets too large (>10MB)
    if (file_exists($logFile) && filesize($logFile) > 10 * 1024 * 1024) {
        rename($logFile, $logFile . '.' . date('Y-m-d-H-i-s'));
    }
    
    $logMessage = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . PHP_EOL . "---" . PHP_EOL;
    file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
}

// Optimized webhook sender with connection pooling
function sendWebhookWithRetry($callback_url, $postData, $orderId, $userId, $config) {
    global $conn;
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $callback_url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($postData),
        CURLOPT_TIMEOUT => $config['curl_timeout'],
        CURLOPT_CONNECTTIMEOUT => $config['curl_connect_timeout'],
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 2,
        CURLOPT_USERAGENT => 'FastWebhook/3.0',
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/x-www-form-urlencoded',
            'Connection: close'
        ],
        CURLOPT_FRESH_CONNECT => false, // Allow connection reuse
        CURLOPT_TCP_KEEPALIVE => 1
    ]);
    
    $success = false;
    $lastError = '';
    
    for ($attempt = 1; $attempt <= $config['max_retries']; $attempt++) {
        $startTime = microtime(true);
        $response = curl_exec($ch);
        $duration = round((microtime(true) - $startTime) * 1000, 2);
        
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        
        if ($response !== false && empty($curlError) && $httpCode >= 200 && $httpCode < 300) {
            $success = true;
            
            // Update database - use prepared statement for better performance
            $stmt = mysqli_prepare($conn, "UPDATE orders SET webhook_sent = 'yes' WHERE order_id = ? AND user_id = ?");
            mysqli_stmt_bind_param($stmt, 'si', $orderId, $userId);
            $updateResult = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            
            // Log only on first attempt success or final attempt
            if ($attempt === 1 || $attempt === $config['max_retries']) {
                logWebhookData([
                    'timestamp' => date('Y-m-d H:i:s'),
                    'order_id' => $orderId,
                    'user_id' => $userId,
                    'attempt' => $attempt,
                    'success' => true,
                    'http_code' => $httpCode,
                    'duration_ms' => $duration,
                    'db_updated' => $updateResult
                ]);
            }
            break;
        }
        
        $lastError = $curlError ?: "HTTP $httpCode";
        
        // Retry delay only if not last attempt
        if ($attempt < $config['max_retries']) {
            usleep($config['retry_delays'][$attempt - 1] * 1000000);
        }
    }
    
    curl_close($ch);
    
    if (!$success) {
        // Mark as failed after all retries
        $stmt = mysqli_prepare($conn, "UPDATE orders SET webhook_sent = 'failed' WHERE order_id = ? AND user_id = ?");
        mysqli_stmt_bind_param($stmt, 'si', $orderId, $userId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        logWebhookData([
            'timestamp' => date('Y-m-d H:i:s'),
            'order_id' => $orderId,
            'user_id' => $userId,
            'success' => false,
            'error' => $lastError,
            'attempts' => $config['max_retries']
        ]);
    }
    
    return $success;
}

// Process webhooks in optimized chunks
function processWebhooks($config, &$stats) {
    global $conn;
    
    do {
        // Optimized query with better indexing hints
        $query = "SELECT o.user_id, o.order_id, o.remark1, o.remark2, o.amount, o.utr, o.status, o.customer_mobile, o.merchentMobile, o.method, o.create_date, u.callback_url FROM orders o INNER JOIN users u ON o.user_id = u.id WHERE ( o.status = 'SUCCESS' OR (o.status = 'FAILURE' AND o.create_date < NOW() - INTERVAL 3 HOUR) ) AND o.webhook_sent = 'no' AND u.callback_url IS NOT NULL AND u.callback_url != '' ORDER BY o.id ASC LIMIT {$config['chunk_size']}";
        
        $result = mysqli_query($conn, $query);
        
        if (!$result) {
            error_log("Webhook query error: " . mysqli_error($conn));
            break;
        }
        
        $rowCount = mysqli_num_rows($result);
        if ($rowCount === 0) break;
        
        while ($row = mysqli_fetch_assoc($result)) {
            if ($row['status'] === 'SUCCESS') {
                $successCount++;
            } elseif ($row['status'] === 'FAILURE') {
                $failureCount++;
            }
            
            
            
            $currentTime = date("Y-m-d H:i:s");
            
            // Skip FAILURE orders that are less than 3 hours old
            if ($row['status'] === 'FAILURE') {
                $timeThreshold = date("Y-m-d H:i:s", strtotime('-3 hours'));
                if ($row['create_date'] > $timeThreshold) {
                    continue;
                }
            }
            
            // Validate callback URL
            if (!filter_var($row['callback_url'], FILTER_VALIDATE_URL)) {
                $stmt = mysqli_prepare($conn, "UPDATE orders SET webhook_sent = 'invalid_url' WHERE order_id = ? AND user_id = ?");
                mysqli_stmt_bind_param($stmt, 'si', $row['order_id'], $row['user_id']);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
                $stats['webhooks_failed']++;
                continue;
            }
            
            // Prepare webhook data
            $postData = [
                'status' => $row['status'],
                'utr' => $row['status'] === 'SUCCESS' ? $row['utr'] : '3HTimeout_Failure',
                'order_id' => $row['order_id'],
                'amount' => $row['amount'],
                'customer_mobile' => $row['customer_mobile'],
                'method' => $row['method'],
                'merchentMobile' => $row['merchentMobile'],
                'remark1' => $row['remark1'],
                'remark2' => $row['remark2'],
                'user_id' => $row['user_id'],
                'timestamp' => $currentTime,
                'webhook_version' => '3.0'
            ];
            
            // Send webhook
            if (sendWebhookWithRetry($row['callback_url'], $postData, $row['order_id'], $row['user_id'], $config)) {
                $stats['webhooks_success']++;
            } else {
                $stats['webhooks_failed']++;
            }
            
            $stats['webhooks_processed']++;
            
            // Performance optimizations
            usleep($config['process_delay']);
            
            // Memory management
            if (memory_get_usage(true) > 800 * 1024 * 1024) { // 800MB threshold
                gc_collect_cycles();
            }
        }
        echo "Total Records: {$rowCount} | SUCCESS: {$successCount} | FAILURE: {$failureCount}<br>";
        mysqli_free_result($result);
        
        // Break between batches
        if ($rowCount === $config['chunk_size']) {
            usleep($config['batch_delay'] * 1000000);
        }
        
    } while ($rowCount === $config['chunk_size']);
}

// Process Telegram notifications
function processNotifications(&$stats) {
    global $conn;
    
    $query = "SELECT o.order_id, o.amount, o.byteTransactionId, o.method, o.create_date, o.user_id, u.telegram_chat_id
              FROM orders o 
              INNER JOIN users u ON o.user_id = u.id 
              WHERE o.notifi_send = 'no' 
                AND o.status = 'SUCCESS' 
                AND u.telegram_subscribed = 'on' 
                AND u.telegram_chat_id IS NOT NULL 
                AND u.telegram_chat_id != ''
              LIMIT 50";
    
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $message = "🎉 Transaction Successful!<br><br>" .
                      "📦 Order: {$row['order_id']}<br>" .
                      "💵 Amount: ₹{$row['amount']}<br>" .
                      "🔖 TXN ID: {$row['byteTransactionId']}<br>" .
                      "🏦 Method: {$row['method']}<br>" .
                      "📅 Date: {$row['create_date']}";
            
            if (function_exists('boltx_telegram_noti_bot')) {
                boltx_telegram_noti_bot($message, $row['telegram_chat_id']);
            }
            
            // Update notification status
            $stmt = mysqli_prepare($conn, "UPDATE orders SET notifi_send = 'yes' WHERE order_id = ?");
            mysqli_stmt_bind_param($stmt, 's', $row['order_id']);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            
            $stats['notifications_sent']++;
            usleep(100000); // 0.1 second delay between notifications
        }
        mysqli_free_result($result);
    }
}

// Cleanup old payment links
function cleanupPaymentLinks(&$stats) {
    global $conn;
    
    $timeThreshold = date("Y-m-d H:i:s", strtotime('-30 minutes'));
    $result = mysqli_query($conn, "DELETE FROM payment_links WHERE created_at <= '$timeThreshold'");
    
    if ($result) {
        $stats['payment_links_deleted'] = mysqli_affected_rows($conn);
    }
}

// === MAIN EXECUTION ===

try {
    // Process webhooks
    processWebhooks($config, $stats);
    
    // Process notifications
    // processNotifications($stats);
    
    // Cleanup old data
    cleanupPaymentLinks($stats);
    
    // Update memory peak
    $stats['memory_peak'] = memory_get_peak_usage(true);
    
} catch (Exception $e) {
    error_log("Cron execution error: " . $e->getMessage());
}

// === EXECUTION SUMMARY ===
$executionTime = round(microtime(true) - $stats['start_time'], 2);
$memoryPeak = round($stats['memory_peak'] / 1024 / 1024, 2);
$successRate = $stats['webhooks_processed'] > 0 ? round(($stats['webhooks_success'] / $stats['webhooks_processed']) * 100, 1) : 0;

echo "=== CRON EXECUTION SUMMARY ===<br>";
echo "⏱️  Execution Time: {$executionTime}s<br>";
echo "🔗 Webhooks: {$stats['webhooks_processed']} processed, {$stats['webhooks_success']} success, {$stats['webhooks_failed']} failed ({$successRate}%)<br>";
echo "📱 Notifications: {$stats['notifications_sent']} sent<br>";
echo "🗑️  Cleanup: {$stats['payment_links_deleted']} old payment links removed<br>";
echo "💾 Memory Peak: {$memoryPeak}MB<br>";
echo "✅ Status: " . ($stats['webhooks_failed'] === 0 ? "ALL GOOD" : "SOME FAILURES") . "<br>";
echo "===============================<br>";

// Log summary for monitoring
// logWebhookData([
//     'summary' => true,
//     'timestamp' => date('Y-m-d H:i:s'),
//     'execution_time' => $executionTime,
//     'stats' => $stats
// ]);

// Close database connection
mysqli_close($conn);
?>