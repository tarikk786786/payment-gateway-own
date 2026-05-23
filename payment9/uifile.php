<?php
// Mobikwik Status API Page with Expiry Check
date_default_timezone_set("Asia/Kolkata");

// Show errors (only for development)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set response header to JSON
header('Content-Type: application/json');

// Define constants & includes
define('ROOT_DIR', realpath(dirname(__FILE__)) . '/../');
include ROOT_DIR . 'pages/dbFunctions.php';
include ROOT_DIR . 'pages/dbInfo.php';
include ROOT_DIR . 'auth/config.php';
include ROOT_DIR . 'auth/function.php';

// Token Input
$token = $_GET['token'] ?? '';
if (empty($token)) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "Missing token parameter"
    ]);
    exit;
}

// Escape token
$token_safe = mysqli_real_escape_string($conn, $token);

if (isset($_GET['order_id']) && isset($_GET['status']) && isset($_GET['user_id'])) {
    $order_id = $_GET['order_id'];
    $status = $_GET['status'];
    $user_id = $_GET['user_id'];
    // Example: Update only if status is FAILURE
    if (isset($status)) {
        $update = db_update($conn, "orders", array("status" => "$status"), "order_id='$order_id' AND user_id='$user_id'");
        if ($update) {
         echo json_encode([
             "status" => "success",
             "message" => "Order Updated",
         ]);
         exit;
        }
    }
} else {
    
// Query user
$result = db_select($conn, "users", "*", "user_token = '$token_safe'");
if (!$result) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Database query failed",
        "error" => mysqli_error($conn)
    ]);
    exit;
}

// Fetch user data
$user = mysqli_fetch_assoc($result);

if (!$user) {
    echo json_encode([
        "status" => "success",
        "message" => "No user found with this token",
        "data" => []
    ]);
    exit;
}

// Expiry Check
$expiry_date = $user['expiry'];
$current_date = date("Y-m-d");

if (strtotime($expiry_date) < strtotime($current_date)) {
    echo json_encode([
        "status" => "expired",
        "account_expired" => true,
        "message" => "User account has expired on $expiry_date"
    ]);
    exit;
}

$user_id = $user['id']; // या जैसे भी यूज़र ID मिल रही हो

// सभी PENDING और MANUAL orders लाओ
$result = db_select($conn, "orders", "order_id,amount,utr,create_date,user_id", "user_id = '$user_id' AND status = 'PENDING' AND method = 'MANUAL' ORDER BY id DESC");

if (!$result) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Database error",
        "error" => mysqli_error($conn)
    ]);
    exit;
}

$orders = [];
$orders_with_utr = [];
$orders_without_utr = [];

while ($row = mysqli_fetch_assoc($result)) {
    $orders[] = $row;
    if (!empty($row['utr'])) {
        $orders_with_utr[] = $row;
    } else {
        $orders_without_utr[] = $row;
    }
}

if (empty($orders)) {
    echo json_encode([
        "status" => "success",
        "message" => "No pending MANUAL orders found",
        "count" => 0,
        "data" => []
    ]);
    exit;
}

// Response based on UTR
if (!empty($orders_with_utr)) {
    echo json_encode([
        "status" => "success",
        "message" => "Manual pending order(s) found with UTR",
        "count" => count($orders_with_utr),
        "data" => $orders_with_utr
    ]);
} else {
    echo json_encode([
        "status" => "success",
        "message" => "Manual pending order(s) received but UTR not submitted yet",
        "count" => count($orders_without_utr),
        "data" => $orders_without_utr
    ]);
}
   
}








?>
