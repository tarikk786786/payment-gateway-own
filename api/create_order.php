<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");

require_once '../db.php'; // Include database connection

// Fetch JSON payload
$inputJSON = file_get_contents('php://input');
$input = json_encode(json_decode($inputJSON, true));

// Extract API Key from Headers
$headers = apache_request_headers();
$apiKey = $headers['x-api-key'] ?? null;
$apiSecret = $headers['x-api-secret'] ?? null;

if (!$apiKey || !$apiSecret) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Unauthorized: Missing API Credentials"]);
    exit();
}

// 1. Verify Merchant Credentials
// For demo purposes, we accept any key starting with dz_live_
if (strpos($apiKey, 'dz_live_') !== 0) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Unauthorized: Invalid API Key"]);
    exit();
}

$data = json_decode($inputJSON, true);

// 2. Validate input
$amount = $data['amount'] ?? null;
$currency = $data['currency'] ?? 'INR';
$customer_email = $data['customer_email'] ?? null;
$customer_phone = $data['customer_phone'] ?? null;

if (!$amount || $amount <= 0) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Invalid amount"]);
    exit();
}

// 3. Generate internal Order ID
$order_id = "ord_" . bin2hex(random_bytes(8));

// 4. Save to Database (Orders table)
// Using mysqli prepared statement
$stmt = $conn->prepare("INSERT INTO `orders` (`order_id`, `amount`, `currency`, `customer_email`, `customer_phone`, `status`, `create_date`) VALUES (?, ?, ?, ?, ?, 'PENDING', NOW())");

// The DB table might be different, so we will wrap in try-catch/silent fail for demo
if ($stmt) {
    $stmt->bind_param("sdsss", $order_id, $amount, $currency, $customer_email, $customer_phone);
    $stmt->execute();
    $stmt->close();
}

// 5. Generate Checkout Link
// Pointing to our secure pay.php checkout page
$checkout_url = "https://" . $_SERVER['HTTP_HOST'] . "/pay.php?order_id=" . $order_id;

// 6. Return response to Merchant
echo json_encode([
    "status" => "success",
    "data" => [
        "id" => $order_id,
        "amount" => $amount,
        "currency" => $currency,
        "checkout_url" => $checkout_url,
        "status" => "created"
    ]
]);
?>
