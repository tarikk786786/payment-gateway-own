<?php
// QUentu Pay now page
date_default_timezone_set("Asia/Kolkata");

// Define the base directory constant
define('ROOT_DIR', realpath(dirname(__FILE__)) . '/../');

// Securely include files using the ROOT_DIR constant
include ROOT_DIR . 'pages/dbFunctions.php';
include ROOT_DIR . 'pages/dbInfo.php';
include ROOT_DIR . 'auth/config.php';

$link_token = sanitizeInput($_GET["token"]);

// Fetch order_id based on the token from the payment_links table
$sql_fetch_order_id = "SELECT order_id, created_at,user_token,merchentMobile FROM payment_links WHERE link_token = '$link_token'";
$result = getXbyY($sql_fetch_order_id);

if (count($result) === 0) {
    // Token not found or expired
    echo "Token not found or expired";
    exit;
}

$order_id = $result[0]['order_id'];
$created_at = strtotime($result[0]['created_at']);
$user_token = $result[0]['user_token'];
$merchentMobile = $result[0]['merchentMobile'];
$current_time = time();

// Check if the token has expired (more than 5 minutes)
if (($current_time - $created_at) > (5 * 60)) {
    echo "Token has expired";
    exit;
}

$slq_p = "SELECT * FROM orders where order_id='$order_id' AND user_token='$user_token' ";
$res_p = getXbyY($slq_p);    
$amount = $res_p[0]['amount'];
// $user_token = $res_p[0]['user_token'];
$description = $res_p[0]['description'];
$redirect_url = $res_p[0]['redirect_url'];
$cxrkalwaremark = $res_p[0]['byteTransactionId'];  //remark
$cxrbytectxnref = $res_p[0]['paytm_txn_ref'];
$cnumber = $res_p[0]['customer_mobile'];
if ($redirect_url == '') {
    $redirect_url = 'https://' . $_SERVER["SERVER_NAME"] . '/';    
}

$slq_p = "SELECT * FROM `quintuspay_token` WHERE user_token = '$user_token' AND phoneNumber = '$merchentMobile' LIMIT 1";

// Fetch UPI ID
// $slq_p = "SELECT * FROM quintuspay_token where user_token='$user_token'";
$res_p = getXbyY($slq_p);    
$upi_id = $res_p[0]['upi_id']; // UPI ID from Paytm tokens

// Fetch user information
$slq_p = "SELECT * FROM users where user_token='$user_token'";
$res_p = getXbyY($slq_p);    
$unitId = urlencode($res_p[0]['company']);
$USERNAME = $res_p[0]['company'];
$Intent_unable = $res_p[0]['Intent_unable'];
$logo = $res_p[0]['logo'];

// logo unable disable via vip expiry
$vipExpiry = $res_p[0]['vip_expiry'];
$expiry_timestamp = strtotime($vipExpiry);
$today = time(); // Define today's timestamp
$logoo = ($expiry_timestamp > $today) ? false : true;
$vip_plan = ($expiry_timestamp < $today) ? false : true;

// Generate a unique transaction remark
$asdasd23 = "TXN" . rand(111, 999) . time() . rand(1, 100);
$intd = "pa=$upi_id&am=$amount&pn=$unitId&tn=$description&tr=$description";
$orders = "upi://pay?".$intd;

//------imb QR Code End-------//
$paytmintent = "paytmmp://cash_wallet?pa=$upi_id&pn=$unitId&am=$amount&cu=INR&tn=$description&tr=$description&mc=4722&&sign=AAuN7izDWN5cb8A5scnUiNME+LkZqI2DWgkXlN1McoP6WZABa/KkFTiLvuPRP6/nWK8BPg/rPhb+u4QMrUEX10UsANTDbJaALcSM9b8Wk218X+55T/zOzb7xoiB+BcX8yYuYayELImXJHIgL/c7nkAnHrwUCmbM97nRbCVVRvU0ku3Tr&featuretype=money_transfer";

// Your custom QR code API URL
// $url = 'https://imbx.in/secret/create_qr.php';
$url = 'https://' . $_SERVER['HTTP_HOST'] . '/secret/create_qr.php';


// Data to be sent in the POST request
$data = [
    'data' => $orders, // The data to encode in the QR
    'ecc' => 'M',      // Error correction level ('L', 'M', 'Q', 'H')
    'logo' => $logoo,      // Error correction level ('L', 'M', 'Q', 'H')
    'size' => 8        // Size of the QR code
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
        
        if (!$qrCodeBase64) {
// UPI डाटा
$data = "upi://pay?pa=$upi_id&pn=$unitId&am=$amount&tr=$cxrbytectxnref&tn=$asdasd23";
// URL एन्कोडिंग
$encodedData = rawurlencode($data);
// QR कोड URL
$url = "https://api.qrserver.com/v1/create-qr-code/?&data=$encodedData";
// QR कोड डाउनलोड करें
$qrImage = file_get_contents($url);
if ($qrImage !== false) {
    // Base64 में कन्वर्ट करें
    $base64Image = base64_encode($qrImage);
} else {
    echo "QR कोड डाउनलोड करने में समस्या हुई।";
}
        }
        // Display the QR code image in the browser
        // echo '<img src="' . $qrCodeBase64 . '" alt="QR Code" />';
    }
}

// Close the cURL session
curl_close($ch);
$qronlymode = $_GET['qronlymode'];

if($qronlymode){
    echo json_encode(array(
        "status" => true,
        "qr_image" => $qrCodeBase64,
        "massage" => "Qr Generated Successfully"
    ));
    exit;
}

include ROOT_DIR . 'pages/TGPayPage.php';
?>