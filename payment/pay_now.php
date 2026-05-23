<?php
function RandomNumber($length)
{    $str = "";
    for ($i = 0; $i < $length; $i++) {
        $str .= mt_rand(0, 9);
    }
    return $str;
}

// Define the base directory constant
define('ROOT_DIR', realpath(dirname(__FILE__)) . '/../');

// Securely include files using the ROOT_DIR constant
include ROOT_DIR . 'pages/dbFunctions.php';
include ROOT_DIR . 'auth/config.php';
include ROOT_DIR . 'pages/dbInfo.php';


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
$user_token = $result[0]['user_token'];
$merchentMobile = $result[0]['merchentMobile'];
$created_at = strtotime($result[0]['created_at']);
$current_time = time();

// Check if the token has expired (more than 5 minutes)
if (($current_time - $created_at) > (5 * 60)) {

echo json_encode(array(
        "status" => false,
        "massage" => "Token has expired"
    ));
    exit;
}

$sql_p = "SELECT * FROM orders WHERE order_id='$order_id' AND user_token = '$user_token'";
$res_p = getXbyY($sql_p);
$amount = $res_p[0]['amount'];
// $user_token = $res_p[0]['user_token'];
$redirect_url = $res_p[0]['redirect_url'];
$cnumber = $res_p[0]['customer_mobile'];
$description = $res_p[0]['description'];
$apptxnidd = "2560".RandomNumber(7).time();
// Update the HDFC_TXNID and description in the orders table
if (!$description) {
$description = RandomNumber(20);
$update_sql = "UPDATE orders SET HDFC_TXNID='$apptxnidd',description='$description' WHERE order_id='$order_id' AND user_token = '$user_token'";
}else{
$update_sql = "UPDATE orders SET HDFC_TXNID='$apptxnidd' WHERE order_id='$order_id' AND user_token = '$user_token'";
}

if ($conn->query($update_sql) === TRUE) {
    //echo "Record updated successfully";
}
$sql_p = "SELECT * FROM users WHERE user_token='$user_token'";
$res_p = getXbyY($sql_p);
$USERNAME = $res_p[0]['company'];
$mobile = $res_p[0]['mobile'];
$email = $res_p[0]['email'];
$Intent_unable = $res_p[0]['Intent_unable'];
$logo = $res_p[0]['logo'];

// logo unable disable via vip expiry
$vipExpiry = $res_p[0]['vip_expiry'];
$expiry_timestamp = strtotime($vipExpiry);
$today = time(); // Define today's timestamp
$logoo = ($expiry_timestamp > $today) ? false : true;
$vip_plan = ($expiry_timestamp < $today) ? false : true;


$sql_p = "SELECT * FROM hdfc WHERE user_token='$user_token' AND status = 'Active' AND number ='$merchentMobile'  LIMIT 1";


$res_p = getXbyY($sql_p);
$hdfc_seassion = $res_p[0]['seassion'];  // Column name is 'seassion' despite the spelling mistake
$tidList = $res_p[0]['tidlist'];
$upi_id = $res_p[0]['upi_hdfc'];
// $merchentMobile = $res_p[0]['number'];
$request_upi = $res_p[0]['Request_Upi'];

$intd = "pa=$upi_id&pn=$USERNAME&am=$amount&tr=$description&tn=$description";


$paytmintent = "paytmmp://cash_wallet?pa=$upi_id&pn=$USERNAME&am=$amount&cu=INR&tn=$description&tr=$description&mc=4722&&sign=AAuN7izDWN5cb8A5scnUiNME+LkZqI2DWgkXlN1McoP6WZABa/KkFTiLvuPRP6/nWK8BPg/rPhb+u4QMrUEX10UsANTDbJaALcSM9b8Wk218X+55T/zOzb7xoiB+BcX8yYuYayELImXJHIgL/c7nkAnHrwUCmbM97nRbCVVRvU0ku3Tr&featuretype=money_transfer";


// Define the API endpoint
$url = 'https://miniapi.in/api/hdfc/upiqr';
// $url = 'https://imbx.in/secret/hdfc_qr';
// $url = "https://$server/HDFCSoft/QRpay";

// Create the payload array
$payloadArray = array(
    "seassion" => $hdfc_seassion,
    "tidlist" => $tidList,
    "amount" => $amount,
    "description" => $description,
    "cnumber" => $cnumber,
    "apptxnidd" => $apptxnidd
);

// Initialize cURL
$ch = curl_init($url);

// Set cURL options
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payloadArray));

// Execute the request
$response = curl_exec($ch);

// Check for cURL errors
if (curl_errno($ch)) {
    // echo 'cURL Error: ' . curl_error($ch);
} else {
    // Print the response from the API
}

// Close cURL session
curl_close($ch);

session_start();


// Decode the JSON response
$jsonResponse = json_decode($response, true);

if ($jsonResponse['status']==true){
    $base64Image=$jsonResponse['base64'];
}elseif(!$base64Image){

    
//Merchantapi  Call
// $url = 'https://smartupi.in.net/ai/smarthub/hdfc_qr.php';
$url = "https://$server/HDFCSoft/QRpay";

$params = array(
    "terminalId" => $tidList,
    "amount" => $amount,
    "description" => $description,
    "customerMobileNumber" => $cnumber,
    "appTxnid" => $apptxnidd,
    "sessionid" => $hdfc_seassion
);

$queryString = http_build_query($params);
$urlWithParams = $url . '?' . $queryString;
$response = file_get_contents($urlWithParams);

//Output the response
// echo $response;

if (curl_errno($ch)) {
    echo 'cURL Error: ' . curl_error($ch);
} else {
   
}

curl_close($ch);

$jsonResponse = json_decode($response, true);

if (isset($jsonResponse['qr_code'])) {
    $qrCodeBase64 = $jsonResponse['qr_code'];
    $qrdata = $jsonResponse['qr_data'];
}else{
// UPI डाटा
$orders = "upi://pay?".$intd;
// Your custom QR code API URL
$url = 'https://chickenpox.in/secret/create_qr.php';

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
}
    
}


// setcookie("description_cookie", $description, time() + 300, "/"); // Expires in 5 Minuts (adjust the expiration time as needed)
include ROOT_DIR . 'pages/TGPayPage.php';
?>

</body>
</html>
