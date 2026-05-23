<?php
if (!isset($_POST['customer_mobile']) || !isset($_POST['amount'])) {
    echo json_encode([
        "status" => false,
        "message" => "Missing required fields: customer_mobile or amount"
    ]);
    exit;
}

$mobile = $_POST['customer_mobile'];
$amount = $_POST['amount'];

$order_id = "ORD" . rand(100000, 999999) . time();

// API Token
$token = "c0fcc08fd523e95b8324aa1640d97f67";

// API endpoint
$url = "https://chickenpox.in/api/create-order";

// Request Data
$data = array(
    "customer_mobile" => $mobile,
    "user_token" => $token,
    "amount" => $amount,
    "order_id" => $order_id,
    "redirect_url" => "../success.php", //  redirect URL
    "remark1" => "testmark",
    "remark2" => "testmark2"
);


$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    "Content-Type: application/x-www-form-urlencoded",
    "User-Agent: UpiGatewayClient/1.0"
));

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if (curl_errno($ch)) {
    echo json_encode([
        "status" => false,
        "message" => curl_error($ch)
    ]);
} else {
    header('Content-Type: application/json');
    echo $response;
}

curl_close($ch);
?>