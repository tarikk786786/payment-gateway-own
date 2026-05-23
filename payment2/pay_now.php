<?php
// PhonePay payment Page
date_default_timezone_set("Asia/Kolkata");

// Define the base directory constant
define('ROOT_DIR', realpath(dirname(__FILE__)) . '/../');

// Securely include files using the ROOT_DIR constant
include ROOT_DIR . 'pages/dbFunctions.php';
include ROOT_DIR . 'pages/dbInfo.php';
include ROOT_DIR . 'auth/config.php';


$link_token = sanitizeInput($_GET["token"]);

// Fetch order_id based on the token from the payment_links table
$sql_fetch_order_id = "SELECT order_id,user_token,merchentMobile, created_at FROM payment_links WHERE link_token = '$link_token'";
$result = getXbyY($sql_fetch_order_id);

if (count($result) === 0) {
    // Token not found or expired
    echo "Token not found or expired";
    exit;
}

$order_id = $result[0]['order_id'];
$user_token = $result[0]['user_token'];
$merchentMobile = $result[0]['merchentMobile'];
$created_at = strtotime($result[0]['created_at']);
$current_time = time();

// Check if the token has expired (more than 5 minutes)
if (($current_time - $created_at) > (5 * 60)) {
    echo "Token has expired";
    exit;
}

$slq_p = "SELECT * FROM orders where order_id='$order_id' AND user_token='$user_token'";
$res_p = getXbyY($slq_p);    
$amount = $res_p[0]['amount'];
// $user_token = $res_p[0]['user_token'];
$redirect_url = $res_p[0]['redirect_url'];
$cxrkalwaremark = $res_p[0]['byteTransactionId'];  //remark
$method = $res_p[0]['method'];  //remark
$cnumber = $res_p[0]['customer_mobile'];
if($redirect_url==''){
$redirect_url= 'https://'.$_SERVER["SERVER_NAME"].'/';    
}

$result = db_custom_query($conn, "SELECT merchant_upi FROM phonepe_tokens WHERE user_token = '$user_token' AND phoneNumber = '$merchentMobile'");

// $merchentMobile = $result[0]['phoneNumber'];
$upi_id = $result[0]['merchant_upi'];
    
    
$obj = json_decode($txn_data);
$data=$obj->data;

$json0=json_decode($txn_data,1);
$data=$json0["data"];
$results=$data["results"];
$customerDetails=$results[$i]["customerDetails"];

    
$slq_p = "SELECT * FROM store_id where user_token='$user_token'";
        $res_p = getXbyY($slq_p);    
 $unitId = $res_p[0]['unitId'];
// Check if $upi_id is either not set or empty before querying

    $slq_p = "SELECT * FROM users WHERE user_token = '$user_token'";
    $res_p = getXbyY($slq_p);
    // Confirm that a valid result was returned
if (empty($upi_id)) {
    if (!empty($res_p) && isset($res_p[0]['upi_id'])) {
        $upi_id = $res_p[0]['upi_id'];
        // echo("HI".$upi_id);
    }
}

 $USERNAME = $res_p[0]['company'];
 $Intent_unable = $res_p[0]['Intent_unable'];
 $cxrmerchantTransactionId=$cxrkalwaremark;
 $description = $cxrkalwaremark;
 $logo = $res_p[0]['logo'];
// logo unable disable via vip expiry
$vipExpiry = $res_p[0]['vip_expiry'];
$expiry_timestamp = strtotime($vipExpiry);
$today = time(); // Define today's timestamp
$logoo = ($expiry_timestamp > $today) ? false : true;
$vip_plan = ($expiry_timestamp < $today) ? false : true;
 
 
 $paytmintent = "paytmmp://cash_wallet?pa=$upi_id&pn=$unitId&am=$amount&cu=INR&tn=$description&tr=$cxrmerchantTransactionId&mc=4722&&sign=AAuN7izDWN5cb8A5scnUiNME+LkZqI2DWgkXlN1McoP6WZABa/KkFTiLvuPRP6/nWK8BPg/rPhb+u4QMrUEX10UsANTDbJaALcSM9b8Wk218X+55T/zOzb7xoiB+BcX8yYuYayELImXJHIgL/c7nkAnHrwUCmbM97nRbCVVRvU0ku3Tr&featuretype=money_transfer";
 
$intd = "pa=$upi_id&am=$amount&pn=$unitId&tn=$description&tr=$cxrmerchantTransactionId";
$orders = "upi://pay?".$intd;
// Your custom QR code API URL
// $url = 'https://imbx.in/secret/create_qr.php';
$url = 'https://' . $_SERVER['HTTP_HOST'] . '/secret/create_qr.php';

// Data to be sent in the POST request
$data = [
    'data' => $orders, // The data to encode
    'ecc' => 'M', // Error correction level ('L', 'M', 'Q', 'H')
    'size' => 8  // Size of the QR code
];

// Convert the data array into a JSON string
$jsonData = json_encode($data);

// Initialize cURL session
$ch = curl_init($url);

// Set cURL options
curl_setopt($ch, CURLOPT_POST, true);  // Set method to POST
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  // Return the response as a string
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);  // Set content type to JSON
curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);  // Send data as JSON

// Execute the cURL request
$response = curl_exec($ch);
// echo($response);
// Check for errors
if (curl_errno($ch)) {
    echo 'Error: ' . curl_error($ch);
} else {
    // Decode the JSON response
    $result = json_decode($response, true);
    // Check if there is an error in the response
    if (isset($result['error'])) {
        echo 'Error: ' . $result['error'];
    } else {
        // Success! The QR code is in base64 format.
        $qrCodeBase64 = $result['qr_code'];

        // Display the QR code image in the browser
        // echo '<img src="' . $qrCodeBase64 . '" alt="QR Code" />';
    }
}

// Close the cURL session
// curl_close($ch);
include ROOT_DIR . 'pages/TGPayPage.php';
 ?>
    <!--<script disable-devtool-auto="" src="https://pay.imb.org.in/Qrcode/disable-devtool.js" data-url="https://www.google.com/"></script> -->
