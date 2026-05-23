<?php
$server = $_SERVER["SERVER_NAME"];
// Dynamically get the host and protocol
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST']; // Gets the current domain or IP

// Construct the base URL dynamically
$url = $protocol . $host . '/api/create-order';
$orderid = "DEMO1".rand(123456789, 999999999);
$token = '3b5a65c28184fb285ab2751307c8908c'; //ADMIN
// $token = '253658775914f93c7dfe36802e541d58'; //257
// $token = 'ce1840bcc7ef6b2f982e2ef4006e4145'; //305
// $token = 'c460e07f627284373b7f282e997478a2'; //339
// $token = '8c984c6874550dbe81bf87b9e1dc6ba3'; //266
// $token = 'bdcfe0f787ed04d06b19cbea03e15419'; //304
// $token = '19cc4e9050cf2a15328873803ab2863a'; //337
// $token = '59551d774c9461e002d8776e68e3d1d9'; //303
// $token = 'c2885051575b6209bdb7f1a365f8500b'; //414
// $token = '6b92908091b0e615b0e49465d29f0c4c'; //436
// $token = '8bd24ab6b7b55d98488b37084e47b155'; //442
// $token = 'f2d91f31c7962c5960fbd6afa0aec37a'; //442
// $token = '22a4b088187fb300c992c2805e129785'; //524
// $token = 'cf6aa8188dd1a142d97389dad665e0ba'; //478
// $token = '7e60675093841782a4661c355465d8d1'; //533
// $token = 'b3f6b0c1c56e7d755fb93f6db8de08f9'; //510
// $token = 'b84090cb955ae70d167e81adaccf4dbe'; //374
// $token = 'fbe9c5c8f9b937af926501750b97cfb5'; //397
// $token = '0c8fab967647839f92130bb89106f2f8'; //397
// $token = '9d5a5c18f62b0215722c2f3e97b59677'; //299
// $token = 'a6a64a7fea081f8ffbb0c2e58627e4e7'; //579


// Data to be sent in the POST request
$data = array(
    'customer_mobile' => '1234567891',
    'user_token' => $token,
    'amount' => '1',
    'order_id' => $orderid,
    'redirect_url' => 'https://'.$server.'/success.php',
    'remark1' => 'hello',
    'remark2' => 'hello1',
);

// 'redirect_url' => $protocol . $host . '/Receipt?orderid=' . $orderid . '&token=' . $token,

// Initialize cURL session
$ch = curl_init();

// Set cURL options
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Execute cURL session and store the response
$response = curl_exec($ch);

// Check for cURL errors
if (curl_errno($ch)) {
    echo 'cURL Error: ' . curl_error($ch);
}

// Close cURL session
curl_close($ch);

// Decode the JSON response
$jsonResponse = json_decode($response, true);

// Check if decoding was successful
if ($jsonResponse !== null) {
    // Redirect the user to the payment URL
    if ($jsonResponse['status']=="true") {
    $paymentUrl = $jsonResponse['result']['payment_url'];
    header('Location: ' . $paymentUrl);
    exit;
    } else {
        echo $jsonResponse['message'];
    }
    

} else {
    echo 'Failed to decode JSON response.';
}
?>
