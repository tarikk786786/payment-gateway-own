<?php
// Mobiquik Status Page

date_default_timezone_set("Asia/Kolkata");

define('ROOT_DIR', realpath(dirname(__FILE__)) . '/../');
include ROOT_DIR . 'pages/dbFunctions.php';
include ROOT_DIR . 'pages/dbInfo.php';
include ROOT_DIR . 'auth/config.php';

$order_id = ($_POST["order_id"]);
$user_token = ($_POST["user_token"]);


// Fetch order details
$sql_order = "SELECT * FROM orders WHERE order_id='$order_id' AND user_token = '$user_token'";
$order_result = getXbyY($sql_order);

if (empty($order_result)) {
    echo json_encode(['status' => 'error', 'message' => 'Order not found']);
    exit;
}

// $user_token = $order_result[0]['user_token'];
$redirect_url = $order_result[0]['redirect_url'] ?: 'https://'.$_SERVER["SERVER_NAME"].'/';  // If redirect URL is empty, set a default
$cxruser_id = $order_result[0]['user_id'];
$orderstatus = $order_result[0]['status'];
$merchentMobile = $order_result[0]['merchentMobile'];
$amount = $order_result[0]['amount'];

$order_time = $order_result[0]['create_date'];
// Convert order time to timestamp (in seconds)
$timestamp = strtotime($order_time);
// Convert to milliseconds (optional, only if needed for other parts of your code)
$order_timestamp = $timestamp * 1000;
// Add 2 minutes (120 seconds = 120000 milliseconds) to get expiry timestamp
$expiry_timestamp = $order_timestamp + (2 * 60 * 1000);

// Get current timestamp in milliseconds (to match $expiry_timestamp)
$current_timestamp = time() * 1000;

// Calculate difference in milliseconds
$checktill_ms = $expiry_timestamp - $current_timestamp;
// Convert difference to seconds
$checktill = $checktill_ms / 1000;

if ($checktill<0) {
    // echo json_encode(['status' => 'AutoCheckTimeout', 'error' => 'Sumit UTR MANUALLY.']);
    echo 'AutoCheckTimeout';
    exit;
}

// If order is already successful, stop the process
if ($orderstatus == "SUCCESS") {
    // echo json_encode(['status' => 'info', 'message' => 'Order is already successful']);
    echo 'success';
    exit;
}

// Fetch UPI and Authorization details for the user
$sql_mobikwik = "SELECT * FROM mobikwik_token WHERE user_token='$user_token' AND phoneNumber='$merchentMobile'";
$mobikwik_result = getXbyY($sql_mobikwik);
$upi_id = $mobikwik_result[0]['merchant_upi'];
$Authorization = $mobikwik_result[0]['Authorization'];  // Removed double quotes

// echo($Authorization);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['order_id'])) { 

        // Initialize cURL session
        $ch = curl_init();

        // Set URL and other appropriate options
        // curl_setopt($ch, CURLOPT_URL, "https://webapi.mobikwik.com/p/wallet/history/v2");
        curl_setopt($ch, CURLOPT_URL, "https://webapi.mobikwik.com/p/wallet/history/v3?count=20&page=1&category=&theme=v2");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");

        // Set headers
        $headers = [
            "accept: application/json, text/plain, */*",
            "accept-encoding: gzip, deflate, br, zstd",
            "accept-language: en-US,en;q=0.9",
            "authorization: $Authorization",  // Use the dynamic value of $Authorization
            "connection: keep-alive",
            "host: webapi.mobikwik.com",
            "origin: https://www.mobikwik.com",
            "referer: https://www.mobikwik.com/",
            'sec-ch-ua: "Chromium";v="128", "Not;A=Brand";v="24", "Google Chrome";v="128"',
            "sec-ch-ua-mobile: ?0",
            'sec-ch-ua-platform: "Windows"',
            "sec-fetch-dest: empty",
            "sec-fetch-mode: cors",
            "sec-fetch-site: same-site",
            "user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36",
            "x-mclient: 0"
        ];
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // Tell cURL to automatically handle decoding of compressed responses
        curl_setopt($ch, CURLOPT_ENCODING, ""); // This allows cURL to handle gzip, deflate, br, and zstd

        // Execute the request
        $response = curl_exec($ch);
        
//   
        $err = curl_error($ch);
        curl_close($ch); // Close cURL session
        
        

        if ($err) {
            echo json_encode(['status' => 'error', 'message' => "cURL Error: $err"]);
        } else {
            // Decode the JSON response
            $data = json_decode($response, true);

             if ($data && isset($data['data']['historyData'])) {
                $transactions = $data['data']['historyData'];
                // print_r($transactions);
                    //  var_dump($transactions);
                // Loop through each transaction to match UTR, and other conditions
                $matchFound = false;
                foreach ($transactions as $transaction) {
                    // if ( isset($transaction['rrn']) && $transaction['status'] == "success" && $transaction['mode'] == "credit") {
                    if (isset($transaction['rrn']) && $transaction['amount'] == $amount &&
                    $transaction['date'] > $order_timestamp &&
                    $transaction['date'] < $expiry_timestamp
                ) {
                        
                        // echo($transaction['description']);
                        $utr_number = $transaction['rrn'];
                        $payer = $transaction['description'];
                                // Check if UTR has already been used
                        $checkutr= db_exists($conn, "orders", "utr=$utr_number");
                        if ($checkutr) {
                            echo("utr alrady available");
                        } else {
                        $matchFound = true;
                        break; // Exit the loop once a match is found
                        }
                    }
                }

                // If a match is found, update the order status
                if ($matchFound) {
                    // Update order status to SUCCESS and store the UTR number
                    $update_query = "UPDATE orders SET status='SUCCESS', utr='$utr_number', payerUpi='$payer' WHERE order_id='$order_id' AND user_id='$cxruser_id'";
                    $update_result = mysqli_query($conn, $update_query);

                    if ($update_result) {
                    $update_q = "UPDATE mobikwik_token SET failCount='0' WHERE phoneNumber='$merchentMobile' AND user_id='$cxruser_id'";
                    $update_r = mysqli_query($conn, $update_q);
                        sendCallback("SUCCESS", $utr_number, $amount, $order_id, $cxruser_id);
                        // echo("SUCCESS".". UTR:".$utr_number.". AM:".$amount.". OD:".$order_id.". UID:".$cxruser_id);
                        // echo json_encode(['status' => 'success', 'message' => 'Transaction successful', 'redirect_url' => $redirect_url]);
                        echo 'success';
                        exit;
                    } else {
                        echo json_encode(['status' => 'error', 'message' => 'Failed to update the order status']);
                    }
                } else {
                    // echo json_encode(['status' => 'error', 'message' => 'Transaction not found or conditions did not match']);
                    echo 'pending';
                }
            } else {
                echo json_encode(['status' => 'error', 'message' => 'No transaction data found']);
            }
        }
}

?>
