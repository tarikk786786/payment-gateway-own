<?php
error_reporting(1);

// Define constants
define('ROOT_DIR', realpath(dirname(__FILE__)) . '/../');

// Include required files
include ROOT_DIR . 'pages/dbFunctions.php';
include ROOT_DIR . 'auth/config.php';
include ROOT_DIR . 'pages/dbInfo.php';

// Set response header
header('Content-Type: application/json; charset=utf-8');

/**
 * Send JSON response and exit
 */
function sendResponse($status, $message, $additionalData = []) {
    $response = array_merge([
        'status' => $status,
        'message' => $message
    ], $additionalData);
    echo json_encode($response);
    exit;
}

/**
 * Validate required parameters
 */
function validateInputs($order_id, $user_token) {
    if (empty($order_id)) {
        sendResponse('FAILURE', 'Order ID is required', ['error_code' => 'MISSING_ORDER_ID']);
    }
    if (empty($user_token)) {
        sendResponse('FAILURE', 'User token is required', ['error_code' => 'MISSING_USER_TOKEN']);
    }
}

/**
 * Get order data from database
 */
function getOrderData($conn, $order_id, $user_token) {
    $sql = "SELECT description AS db_description, amount, create_date AS otime, 
                   customer_mobile AS customerMobile, merchentMobile,user_id, user_token
            FROM orders 
            WHERE order_id = ? AND user_token = ? 
            LIMIT 1";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        sendResponse('FAILURE', 'Database preparation failed', ['error_code' => 'DB_PREPARE_ERROR']);
    }
    
    $stmt->bind_param("ss", $order_id, $user_token);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $stmt->close();
    
    if (empty($data)) {
        sendResponse('FAILURE', 'Order not found or invalid credentials', [
            'error_code' => 'ORDER_NOT_FOUND',
            'order_id' => $order_id
        ]);
    }
    
    return $data;
}

/**
 * Get Google Pay tokens from database
 */
function getGooglePayData($conn, $user_token, $merchantMobile) {
    $sql = "SELECT phoneNumber, AT, FREQ_RAW, COOKIE 
            FROM googlepay_tokens 
            WHERE user_token = ? AND phoneNumber = ? 
            LIMIT 1";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        sendResponse('FAILURE', 'Database preparation failed for Google Pay data', ['error_code' => 'GOOGLEPAY_DB_ERROR']);
    }
    
    $stmt->bind_param("ss", $user_token, $merchantMobile);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $stmt->close();
    
    return $data;
}

/**
 * Get user data from database
 */
function getUserData($conn, $user_token) {
    $sql = "SELECT mobile, id AS megabyteuserid 
            FROM users 
            WHERE user_token = ? 
            LIMIT 1";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        sendResponse('FAILURE', 'Database preparation failed for user data', ['error_code' => 'USER_DB_ERROR']);
    }
    
    $stmt->bind_param("s", $user_token);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $stmt->close();
    
    return $data;
}

/**
 * Extract cookie value by name
 */
function getCookieValue($cookieString, $name) {
    foreach (explode(';', $cookieString) as $part) {
        $keyValue = explode('=', trim($part), 2);
        if (count($keyValue) == 2 && $keyValue[0] === $name) {
            return $keyValue[1];
        }
    }
    return null;
}

/**
 * Generate SAPISIDHASH for authentication
 */
function generateSapisidHash($cookie, $origin) {
    $sapisid = getCookieValue($cookie, 'SAPISID') ?: getCookieValue($cookie, '__Secure-3PAPISID');
    if (!$sapisid) return null;
    
    $timestamp = (string) time();
    return 'SAPISIDHASH ' . $timestamp . '_' . sha1($timestamp . ' ' . $sapisid . ' ' . $origin);
}

/**
 * Make API request to Google Pay
 */
function makeGooglePayRequest($googlepayData) {
    // Configuration
    $config = [
        'FSID' => '8586358160238679812',
        'REQID' => '971843',
        'BL' => 'boq_payments-merchant-console-ui_20250803.08_p0',
        'HL' => 'en-GB',
        'SOURCE_PATH' => '/g4b/transactions/BCR2DN4T5HYOZ4AP',
        'ORIGIN' => 'https://pay.google.com'
    ];
    
    // Build URL
    $baseUrl = 'https://pay.google.com/g4b/_/SMBConsoleUI/data/batchexecute';
    $queryParams = http_build_query([
        'rpcids' => 'RPtkab',
        'source-path' => $config['SOURCE_PATH'],
        'f.sid' => $config['FSID'],
        'bl' => $config['BL'],
        'hl' => $config['HL'],
        'soc-app' => '1',
        'soc-platform' => '1',
        'soc-device' => '2',
        '_reqid' => $config['REQID'],
        'rt' => 'c'
    ]);
    $url = $baseUrl . '?' . $queryParams;
    
    // Prepare headers
    $headers = [
        'accept: */*',
        'content-type: application/x-www-form-urlencoded;charset=UTF-8',
        'origin: ' . $config['ORIGIN'],
        'referer: ' . $config['ORIGIN'] . '/',
        'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/139.0.0.0 Safari/537.36',
        'cookie: ' . $googlepayData['COOKIE']
    ];
    
    // Add authorization if available
    if ($auth = generateSapisidHash($googlepayData['COOKIE'], $config['ORIGIN'])) {
        $headers[] = 'authorization: ' . $auth;
        $headers[] = 'x-origin: ' . $config['ORIGIN'];
    }
    
    // Prepare POST body
    $postBody = http_build_query([
        'f.req' => $googlepayData['FREQ_RAW'],
        'at' => $googlepayData['AT']
    ]);
    
    // Make cURL request
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_POSTFIELDS => $postBody,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => true
    ]);
    
    $response = curl_exec($ch);
    $error = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($error || $httpCode !== 200 || !$response) {
        sendResponse('pending', 'Failed to connect to payment gateway', [
            'error_code' => 'API_REQUEST_FAILED',
            'http_code' => $httpCode,
            'curl_error' => $error ?: 'No response received'
        ]);
    }
    
    return $response;
}

/**
 * Parse API response chunks
 */
function parseResponseChunks($response) {
    if (strpos($response, ")]}'") === 0) {
        $response = substr($response, 4);
    }
    
    $response = ltrim($response, "\r\n");
    $lines = preg_split("/\r\n|\n|\r/", $response);
    $chunks = [];
    
    for ($i = 0; $i < count($lines);) {
        // Skip empty lines
        while ($i < count($lines) && trim($lines[$i]) === '') $i++;
        if ($i >= count($lines)) break;
        
        $length = trim($lines[$i++]);
        
        // Skip empty lines
        while ($i < count($lines) && trim($lines[$i]) === '') $i++;
        if ($i >= count($lines)) break;
        
        $jsonData = $lines[$i++] ?? '';
        $array = json_decode($jsonData, true);
        if (!is_array($array)) continue;
        
        $rpc = $array[0][1] ?? null;
        $payload = $array[0][2] ?? null;
        
        // Decode nested JSON if needed
        for ($k = 0; $k < 3; $k++) {
            if (is_string($payload)) {
                $temp = json_decode($payload, true);
                if ($temp !== null) {
                    $payload = $temp;
                } else {
                    break;
                }
            }
        }
        
        $chunks[] = ['rpc' => $rpc, 'payload' => $payload];
    }
    
    return $chunks;
}

/**
 * Map transaction data from payload
 */
function mapTransactions($payload) {
    if (!isset($payload[0]) || !is_array($payload[0])) {
        return [];
    }
    
    $transactions = $payload[0];
    $mappedTransactions = [];
    // print_r($transactions);
    foreach ($transactions as $transaction) {
        if (!is_array($transaction)) continue;
        
        $mappedTransactions[] = [
            'txn_id' => $transaction[0] ?? '',
            // 'order_id' => $transaction[1] ?? '',
            'remarks' => $transaction[9] ?? '',
            'amount' => $transaction[3][1] ?? '',
            'payerupi' => $transaction[8][1] ?? '',
            'payername' => $transaction[8][0] ?? '',
            'utr' => $transaction[1] ?? '',
            'remarks' => $transaction[9] ?? '',
            'status' => (isset($transaction[10]) && (int)$transaction[10] === 5) ? 'SUCCESS' : 'FAILED',
        ];
    }
    
    return $mappedTransactions;
}

/**
 * Find transaction status by matching remarks with transaction ID
 */
function findTransactionStatus($transactions, $txnId) {
    // print_r($transactions);
    foreach ($transactions as $transaction) {
        if (strcasecmp(trim($transaction['remarks']), trim($txnId)) === 0) {
            return [
                'status' => ($transaction['status'] === 'SUCCESS') ? 'success' : 'failed',
                'transaction' => $transaction
            ];
        }
    }
    return null;
}

// ===== MAIN EXECUTION =====

try {
    // Get and validate input parameters
    $order_id = $_POST['order_id'] ?? '';
    $user_token = $_POST['user_token'] ?? '';
    
    validateInputs($order_id, $user_token);
    
    // Get order data
    $orderData = getOrderData($conn, $order_id, $user_token);
    $txnId = $orderData['db_description'] ?? null;
    $amount = $orderData['amount'] ?? null;
    $merchentMobile = $orderData['merchentMobile'] ?? null;
    $user_id = $orderData['user_id'] ?? null;
    if (!$txnId) {
        sendResponse('pending', 'Transaction ID not found in order', [
            'error_code' => 'MISSING_TXN_ID',
            'order_id' => $order_id
        ]);
    }
    
    // Get Google Pay and user data
    $googlepayData = getGooglePayData($conn, $orderData['user_token'], $orderData['merchentMobile']);
    $userData = getUserData($conn, $orderData['user_token']);
    
    if (!$googlepayData) {
        sendResponse('pending', 'Google Pay configuration not found', [
            'error_code' => 'GOOGLEPAY_CONFIG_MISSING',
            'merchant_mobile' => $orderData['merchentMobile']
        ]);
    }
    
    if (!$googlepayData['AT'] || !$googlepayData['FREQ_RAW'] || !$googlepayData['COOKIE']) {
        sendResponse('pending', 'Incomplete Google Pay configuration', [
            'error_code' => 'INCOMPLETE_GOOGLEPAY_CONFIG',
            'missing_fields' => [
                'AT' => empty($googlepayData['AT']),
                'FREQ_RAW' => empty($googlepayData['FREQ_RAW']),
                'COOKIE' => empty($googlepayData['COOKIE'])
            ]
        ]);
    }
    
    // Make API request to Google Pay
    $apiResponse = makeGooglePayRequest($googlepayData);
    
    // Parse response
    $chunks = parseResponseChunks($apiResponse);
    $payload = null;
    
    foreach ($chunks as $chunk) {
        if (($chunk['rpc'] ?? null) === 'RPtkab') {
            $payload = $chunk['payload'];
            break;
        }
    }
    
    if (!$payload) {
        sendResponse('pending', 'Invalid response from payment gateway', [
            'error_code' => 'INVALID_API_RESPONSE',
            'chunks_found' => count($chunks)
        ]);
    }
    
    // Map transactions and find status
    $transactions = mapTransactions($payload);
    
    if (empty($transactions)) {
        sendResponse('pending', 'No transactions found in response', [
            'error_code' => 'NO_TRANSACTIONS_FOUND',
            'txn_id' => $txnId
        ]);
    }
    
    $result = findTransactionStatus($transactions, $txnId);
 
    if ($result) {
        // print_r($result);
        $statusMessage = $result['status'] === 'success' ? 'Transaction completed successfully' 
            : 'Transaction failed';
    
        $gStatus = $result['status'];
        $gAmount = $result['transaction']['amount'];
        $payer = $result['transaction']['payerupi'];
        $utr = $result['transaction']['utr'];
        
        if ($gAmount==$amount && $statusMessage = $result['status'] === 'success') {
            
        $update_queries[] = "UPDATE `orders` SET status='SUCCESS',utr='$utr',payerUpi='$payer' WHERE order_id='$order_id' AND user_token='$user_token'";
        $update_queries[] = "UPDATE `googlepay_tokens` SET failCount='0' WHERE number='$merchentMobile' AND user_token='$user_token'";
        
        // Execute update queries
        foreach ($update_queries as $query) {
            setXbyY($query);
        }
            
        sendResponse($result['status'], $statusMessage);
        sendCallback("SUCCESS", $utr, $amount, $order_id, $user_id);
        } else {
            sendResponse("pending", "$gAmount-$amount : $statusMessage");
        }
        
    } else {
        sendResponse('pending', 'Transaction status pending or not found', [
            'error_code' => 'TXN_NOT_MATCHED',
            'txn_id' => $txnId,
            'order_id' => $order_id,
            'total_transactions_found' => count($transactions)
        ]);
    }
    
} catch (Exception $e) {
    sendResponse('FAILURE', 'An unexpected error occurred', [
        'error_code' => 'SYSTEM_ERROR',
        'error_message' => $e->getMessage()
    ]);
} finally {
    // Close database connection
    if (isset($conn)) {
        $conn->close();
    }
}
?>