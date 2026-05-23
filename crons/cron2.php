<?php
// Define the base directory constant
define('PROJECT_ROOT', realpath(dirname(__FILE__)) . '/../');

// Securely include files using the PROJECT_ROOT constant
include PROJECT_ROOT . 'pages/dbFunctions.php';
include PROJECT_ROOT . 'auth/config.php';
include PROJECT_ROOT . 'auth/function.php';

date_default_timezone_set("Asia/Kolkata");

// Set maximum execution time to 5 minutes (300 seconds)
ini_set('max_execution_time', 300);
ini_set('memory_limit', '256M'); // Increase memory limit for better performance

// Initialize cURL multi handle for concurrent requests
$multi_handle = curl_multi_init();
$curl_handles = [];

function performPostRequestAsync($url, $postData, &$multi_handle, &$curl_handles, $order_id) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30); // 30 second timeout
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10); // 10 second connect timeout
    
    curl_multi_add_handle($multi_handle, $ch);
    $curl_handles[$order_id] = $ch;
}

function statusCheckBatch($orders, &$multi_handle, &$curl_handles) {
    $server_name = $_SERVER["SERVER_NAME"];
    
    foreach ($orders as $order) {
        $order_id = $order['order_id'];
        $merchant = $order['method'];
        $byteTransactionId = $order['byteTransactionId'];
        $user_token = $order['user_token'];
        
        switch($merchant) {
            case 'SBI':
                $url = "https://$server_name/order7/payment-status";
                $postData = ['order_id' => $order_id];
                performPostRequestAsync($url, $postData, $multi_handle, $curl_handles, $order_id);
                break;
                
            case 'Hdfc':
                $url = "https://$server_name/order/payment-status";
                $postData = ['order_id' => $order_id, 'user_token' => $user_token];
                performPostRequestAsync($url, $postData, $multi_handle, $curl_handles, $order_id);
                break;
                
            case 'Phonepe':
                $url = "https://$server_name/order2/payment-status";
                $postData = ['byte_order_status' => $byteTransactionId];
                performPostRequestAsync($url, $postData, $multi_handle, $curl_handles, $order_id);
                break;
                
            case 'MobiKwik':
                echo "Checking status for MobiKwik. Processing details...<br>";
                break;
                
            case 'Paytm':
                $url = "https://$server_name/order3/payment-status";
                $postData = ['byte_order_status' => $byteTransactionId,'order_id' => $order_id];
                performPostRequestAsync($url, $postData, $multi_handle, $curl_handles, $order_id);
                break;
                
            case 'Bharatpe':
                if (!empty($user_token)) {
                    $url = "https://$server_name/order4/payment-statusAuto";
                    $postData = ['order_id' => $order_id, 'user_token' => $user_token];
                    performPostRequestAsync($url, $postData, $multi_handle, $curl_handles, $order_id);
                }
                break;
            case 'Googlepay':
                if (!empty($user_token)) {
                    $url = "https://$server_name/order5/payment-statusAuto";
                    $postData = ['order_id' => $order_id, 'user_token' => $user_token];
                    performPostRequestAsync($url, $postData, $multi_handle, $curl_handles, $order_id);
                }
                break;
                
            case 'Freecharge':
                $url = "https://$server_name/order6/payment-status";
                $postData = ['order_id' => $order_id, 'user_token' => $user_token];
                performPostRequestAsync($url, $postData, $multi_handle, $curl_handles, $order_id);
                break;
                
            case 'MANUAL':
                echo "No need status check for MANUAL. Processing details...<br>";
                break;
                
            case 'Amazon':
                $url = "https://$server_name/order92/payment-status";
                $postData = ['byte_order_status' => $order_id, 'user_token' => $user_token];
                performPostRequestAsync($url, $postData, $multi_handle, $curl_handles, $order_id);
                break;
            case 'Quintuspay':
                $url = "https://$server_name/order95/payment-status";
                $postData = [ 'order_id' => $order_id, 'user_token' => $user_token];
                performPostRequestAsync($url, $postData, $multi_handle, $curl_handles, $order_id);
                break;
            default:
                echo "No need status Defined for $merchant<br>";
                break;
        }
    }
    
    // Execute all requests concurrently
    $running = null;
    do {
        curl_multi_exec($multi_handle, $running);
        curl_multi_select($multi_handle);
    } while ($running > 0);
    
    // Process responses
    foreach ($curl_handles as $order_id => $ch) {
        $response = curl_multi_getcontent($ch);
        if ($response !== false) {
            echo "response: $response For Order $order_id <br>";
        }
        curl_multi_remove_handle($multi_handle, $ch);
        curl_close($ch);
    }
}

// Assume $conn is your MySQLi database connection object,
// already established in this file/context.
// Example:
// $servername = "localhost"; $username = "user"; $password = "pass"; $dbname = "db";
// $conn = new mysqli($servername, $username, $password, $dbname);
// if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

$merchantTables = [];
$columnMapping = [];

$sql = "SELECT merchantname, tablename, user_update_column FROM merchants WHERE status = 1"; // Fetch only necessary columns
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $merchantName = strtolower($row['merchantname']);
        $tableName = $row['tablename'];
        $userUpdateColumn = $row['user_update_column'];

        // --- Populate $merchantTables array ---
        $numberCol = ($merchantName === 'hdfc') ? 'number' : 'phoneNumber';
        $merchantTables[$merchantName] = [
            "table" => $tableName,
            "numberCol" => $numberCol
        ];

        // --- Populate $columnMapping array ---
        $columnMapping[$tableName] = $userUpdateColumn;
    }
    $result->free();
} else {
    // Optional: Log or handle the case where no merchants are found
    // error_log("No merchants found for $merchantTables or $columnMapping arrays.");
}

// Now $merchantTables and $columnMapping arrays are ready to be used in this page/file.
// print_r($merchantTables);
// print_r($columnMapping);

$current_time = date("Y-m-d H:i:s");
$time_threshold = date("Y-m-d H:i:s", strtotime('-9 minutes'));

// Define log file path
$log_file = __DIR__ . '/count.log';

function log_message($message, $log_file) {
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($log_file, "[$timestamp] $message\n", FILE_APPEND | LOCK_EX);
}

// Single optimized query to fetch all pending orders
$sql = "SELECT order_id, create_date, method, merchentMobile, user_token, amount, user_id, description, byteTransactionId, paytm_txn_ref, HDFC_TXNID 
        FROM orders 
        WHERE status = 'PENDING' 
        ORDER BY create_date ASC";

$result = $conn->query($sql);

if ($result === false) {
    $error = "Query failed: " . $conn->error;
    log_message($error, $log_file);
    die($error);
}

if ($result->num_rows > 0) {
    $orders_to_expire = [];
    $orders_to_check = [];
    $merchant_updates = []; // Batch merchant updates
    
    // Separate orders into two categories for batch processing
    while ($row = $result->fetch_assoc()) {
        $order_create_date = $row["create_date"];
        $method = strtolower(trim($row["method"]));
        
        if ($order_create_date <= $time_threshold) {
            $orders_to_expire[] = $row;
        } else {
            $orders_to_check[] = $row;
        }
    }
    
    // Batch process expired orders
    if (!empty($orders_to_expire)) {
        $order_ids = array_column($orders_to_expire, 'order_id');
        $placeholders = str_repeat('?,', count($order_ids) - 1) . '?';
        
        // Batch update all expired orders to FAILURE
        $update_sql = "UPDATE orders SET status = 'FAILURE', remark= 'System timeout' WHERE order_id IN ($placeholders) AND status = 'PENDING'";
        $stmt = $conn->prepare($update_sql);
        
        if ($stmt) {
            $stmt->bind_param(str_repeat('s', count($order_ids)), ...$order_ids);
            if ($stmt->execute()) {
                log_message("Batch updated " . count($order_ids) . " orders to FAILURE", $log_file);
            }
            $stmt->close();
        }
        
        // Process merchant fail counts
        foreach ($orders_to_expire as $order) {
            $method = strtolower(trim($order["method"]));
            $merchantMobile = $order["merchentMobile"] ?? '';
            $user_token = $order["user_token"] ?? '';
            
            if (empty($merchantMobile) || empty($user_token)) {
                continue;
            }
            
            if (array_key_exists($method, $merchantTables)) {
                $tableInfo = $merchantTables[$method];
                $tableName = $tableInfo["table"];
                $numberRow = $tableInfo["numberCol"];
                
                // Group updates by table and merchant for batch processing
                $key = "$tableName|$merchantMobile|$user_token";
                if (!isset($merchant_updates[$key])) {
                    $merchant_updates[$key] = [
                        'table' => $tableName,
                        'mobile' => $merchantMobile,
                        'token' => $user_token,
                        'numberCol' => $numberRow,
                        'count' => 0
                    ];
                }
                $merchant_updates[$key]['count']++;
            }
        }
        
        // Process merchant updates in batch
        foreach ($merchant_updates as $update_info) {
            $tableName = $update_info['table'];
            $merchantMobile = $update_info['mobile'];
            $user_token = $update_info['token'];
            $numberRow = $update_info['numberCol'];
            $failIncrement = $update_info['count'];
            
            // Get current failCount
            $sql = "SELECT failCount FROM `$tableName` WHERE `$numberRow` = ? AND `user_token` = ?";
            $stmt = $conn->prepare($sql);
            
            if ($stmt) {
                $stmt->bind_param("ss", $merchantMobile, $user_token);
                if ($stmt->execute()) {
                    $result1 = $stmt->get_result();
                    if ($result1->num_rows > 0) {
                        $row = $result1->fetch_assoc();
                        $failCount = (int)($row['failCount'] ?? 0) + $failIncrement;
                        
                        if ($failCount < 15) {
                            $updateSql = "UPDATE `$tableName` SET `failCount` = ? WHERE `$numberRow` = ? AND `user_token` = ?";
                            $updateStmt = $conn->prepare($updateSql);
                            if ($updateStmt) {
                                $updateStmt->bind_param("iss", $failCount, $merchantMobile, $user_token);
                                $updateStmt->execute();
                                $updateStmt->close();
                                echo "Updated failCount to $failCount for $merchantMobile, table: $tableName<br>";
                            }
                        } else {
                            // Deactivate merchant
                            $updateSql = "UPDATE `$tableName` SET `status` = 'Deactive', failCount = '12' WHERE `$numberRow` = ? AND `user_token` = ?";
                            $updateStmt = $conn->prepare($updateSql);
                            if ($updateStmt) {
                                $updateStmt->bind_param("ss", $merchantMobile, $user_token);
                                if ($updateStmt->execute()) {
                                    // Send WhatsApp message
                                    $message = "Hi 👋\nYour $method merchant account (📱 $merchantMobile) has been deactivated 🚫 due to 15 continuous failed transactions.\n\n📌 Please:\n1️⃣ Check your merchant setup.\n2️⃣ Reconnect if needed.\n4️⃣ Guide Users To work with UpiGateway.\n3️⃣ Still not working? Contact UpiGateway Support.\n\n🛠 Let's fix this quickly and get you back on track! ✅";
                                    $msgresult = sendwa($merchantMobile, $message);
                                    
                                    // Check if need to update user connection status
                                    $mcount = db_count($conn, "$tableName", "status = 'Active' AND user_token = '$user_token'");
                                    
                                    if ($mcount == 0 && isset($columnMapping[$tableName])) {
                                        $columname = $columnMapping[$tableName];
                                        db_update($conn, "users", [$columname => "No"], "user_token = '$user_token'");
                                    }
                                    
                                    echo "$merchantMobile Merchant From $tableName Deactivated Due to 15 Continuous FAIL Message Result = $msgresult<br>";
                                }
                                $updateStmt->close();
                            }
                        }
                    }
                    $result1->free();
                }
                $stmt->close();
            }
        }
    }
    
    // Batch process status checks for recent orders
    if (!empty($orders_to_check)) {
        statusCheckBatch($orders_to_check, $multi_handle, $curl_handles);
    }
    
    $result->free();
} else {
    log_message("No orders found to process", $log_file);
}

// Cleanup
curl_multi_close($multi_handle);
$conn->close();
?>