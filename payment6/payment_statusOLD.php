<?php
// freecharge Status Page
error_reporting(0);

// Define the base directory constant
define('ROOT_DIR', realpath(dirname(__FILE__)) . '/../');

// Securely include files using the ROOT_DIR constant
include ROOT_DIR . 'pages/dbFunctions.php';
include ROOT_DIR . 'auth/config.php';
include ROOT_DIR . 'pages/dbInfo.php';

// Sanitize input
$link_token = filter_input(INPUT_POST, 'LINKID', FILTER_SANITIZE_STRING);
echo($link_token);
// Fetch order_id based on the token from the payment_links table
if(isset($link_token)){
$sql_fetch_order_id = "SELECT order_id, user_token, created_at FROM payment_links WHERE link_token = ?";
$stmt = $conn->prepare($sql_fetch_order_id);
$stmt->bind_param('s', $link_token);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if (!$row || !isset($row['order_id'])) {
    die("Order ID not found");
}

$order_id = $row['order_id'];
$user_token = $row['user_token'];

// Validate $order_id to prevent SQL injection
if (!ctype_alnum($order_id)) {
    die("Invalid order_id");
}
} else{
    $order_id = filter_input(INPUT_POST, 'order_id', FILTER_SANITIZE_STRING);
    // $user_token = filter_input(INPUT_POST, 'user_token', FILTER_SANITIZE_STRING);
}

// Fetch order details
$slq_p = "SELECT * FROM orders WHERE order_id = ? AND user_token=?";
$stmt = $conn->prepare($slq_p);
$stmt->bind_param('ss', $order_id, $user_token);
$stmt->execute();
$res_p = $stmt->get_result();
$order_details = $res_p->fetch_assoc();

if (!$order_details) {
    die("Order details not found");
}

// $user_token = $order_details['user_token'];
$db_amount = $order_details['amount'];
$cxrbytectxnref = $order_details['paytm_txn_ref'];
$userid = $order_details['user_id'];
$cxrremark1 = $order_details['remark1'];
$merchentMobile = $order_details['merchentMobile'];
$sql_fetch_user = $conn->query("SELECT callback_url FROM users WHERE id = '$userid'")->fetch_assoc();

// Fetch app_fc from freecharge_token table
$sql_fetch_fc = "SELECT app_fc FROM freecharge_token WHERE user_token = ? AND phoneNumber = ?";
$stmt = $conn->prepare($sql_fetch_fc);
$stmt->bind_param('si', $user_token, $merchentMobile);
$stmt->execute();
$res_fc = $stmt->get_result();
$fc_row = $res_fc->fetch_assoc();


if (!$fc_row || !isset($fc_row['app_fc'])) {
    die("Freecharge token not found");
}

$app_fc = $fc_row['app_fc'];

// New Freecharge API implementation
$url = 'https://www.freecharge.in/thv/listv3?fcAppType=MSITE';

// Set the necessary headers
$headers = [
    'Content-Type: application/json',
    'Accept: application/json, text/plain, */*',
    'Accept-Encoding: gzip, deflate',
    'Accept-Language: en-GB,en-US;q=0.9,en;q=0.8',
    'Origin: https://www.freecharge.in',
    'Referer: https://www.freecharge.in/',
    'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36',
    'Sec-Fetch-Dest: empty',
    'Sec-Fetch-Mode: cors',
    'Sec-Fetch-Site: same-origin',
    'Sec-CH-UA: "Google Chrome";v="131", "Chromium";v="131", "Not_A Brand";v="24"',
    'Sec-CH-UA-Mobile: ?0',
    'Sec-CH-UA-Platform: "Windows"',
    'CSRFRequestIdentifier: YOUR_CSRF_TOKEN', // Replace with actual CSRF token if needed
    'Cookie: _ga_Q9NVXVJCL0=GS1.1.1734723375.4.1.1734723840.50.0.1170835981; _ga=GA1.1.697946827.1734678023; moe_uuid=6cad2ca1-4f5f-4942-a815-d7f0b940d3cf; app_fc=' . $app_fc . '; '
];

// Payload for the POST request
$data = json_encode([
    "fcAppType" => "MSITE"
]);

// cURL initialization
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate'); // Removed 'br' encoding

// Execute the request
$response = curl_exec($ch);



if (curl_errno($ch)) {
    // If there's an encoding error, retry without encoding
    if (strpos(curl_error($ch), 'Unrecognized content encoding type') !== false) {
        curl_setopt($ch, CURLOPT_ENCODING, '');
        $response = curl_exec($ch);
    } else {
        die("Error: " . curl_error($ch));
    }
}

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode == 200) {
    $responseData = json_decode($response, true);
    
    if (json_last_error() === JSON_ERROR_NONE && isset($responseData['data']['globalTransactions'])) {
        $transactionFound = false;
        $utr = '';
        
        // print_r($responseData['data']['globalTransactions']);
        
        foreach ($responseData['data']['globalTransactions'] as $transaction) {
            $txnDetails = $transaction['txnDetails'];
            
    
            if ($txnDetails['globalTxnId'] == $cxrbytectxnref) {
                $transactionFound = true;
                
                // Get UTR from transaction details if available
                if (isset($transaction['billerInfo']['billerMetaData'][1]['sectionDetails'][1]['value'])) {
                    $utr = $transaction['billerInfo']['billerMetaData'][1]['sectionDetails'][1]['value'];
                } else {
                    $utr = '43'.mt_rand(1000000000,9999999999);
                }
                
                // Update order status in the database
                $update_query = "UPDATE orders SET status = 'SUCCESS', utr = ? WHERE order_id = ? AND user_id = ?";
                $stmt = $conn->prepare($update_query);
                $stmt->bind_param('sss', $utr, $order_id, $userid);
                $stmt->execute();
                
                $update_query = "UPDATE freecharge_token SET failCount = '0' WHERE phoneNumber = ? AND user_id = ?";
                $stmt = $conn->prepare($update_query);
                $stmt->bind_param('ss', $merchentMobile, $userid);
                $stmt->execute();
                // Send callback
                $postData = array(
                    'order_id' => htmlspecialchars_decode($order_id),
                    'status' => 'SUCCESS',
                    'remark1' => urlencode($cxrremark1)
                );
                
                $callback_ch = curl_init($sql_fetch_user["callback_url"]);
                curl_setopt($callback_ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($callback_ch, CURLOPT_POST, true);
                curl_setopt($callback_ch, CURLOPT_POSTFIELDS, http_build_query($postData));
                curl_exec($callback_ch);
                curl_close($callback_ch);
                
                echo 'success';
                break;
            }
        }
        
        if (!$transactionFound) {
            echo 'PENDING';
        }
    } else {
        echo 'error';
    }
} else {
    echo 'error';
}
?>

