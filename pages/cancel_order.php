<?php
header("Content-Type: application/json");
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: no-referrer-when-downgrade");

// Define root directory securely
define('ROOT_DIR', realpath(dirname(__FILE__)) . '/../');

// Securely include required files
require_once ROOT_DIR . 'pages/dbFunctions.php';
require_once ROOT_DIR . 'auth/config.php';
require_once ROOT_DIR . 'pages/dbInfo.php';

// Start session for CSRF protection
session_start();

// Validate and sanitize order_id
if (!isset($_POST['order_id']) || empty($_POST['order_id'])) {
    echo json_encode(["status" => "error", "message" => "Invalid order_id"]);
    exit;
}

$order_id = htmlspecialchars(strip_tags($_POST['order_id']));

// Database connection check
// Database connection check
if (!$conn) {
    error_log("Database connection failed");
    echo json_encode(["status" => "error", "message" => "Internal Server Error"]);
    exit;
}

// Check if order is pending
$sql = "SELECT amount, user_id, status FROM orders WHERE status ='PENDING' AND order_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $order_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $row = $result->fetch_assoc()) {
    $amount = $row['amount'];
    $user_id = $row['user_id'];
    $status = $row['status'];

    if ($status === 'PENDING') {
        // Update order status securely
        $update_sql = "UPDATE orders SET status = ?, utr = ? WHERE status ='PENDING' AND order_id = ?";
        $stmt = $conn->prepare($update_sql);
        $new_status = 'FAILURE';
        $utr = 'UserCancled';
        $stmt->bind_param("sss", $new_status, $utr, $order_id);

        if ($stmt->execute()) {
            // Callback function call
            // sendCallback("FAILURE", "User Cancelled", $amount, $order_id, $user_id);
            echo json_encode(["status" => "success", "message" => "Transaction cancelled successfully"]);
        } else {
            error_log("Order cancellation failed for Order ID: $order_id");
            echo json_encode(["status" => "error", "message" => "Failed to cancel transaction"]);
        }
    } else {
        echo json_encode(["status" => "success", "message" => "Order is not pending, cannot cancel"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Order not found"]);
}

// Close connections
$stmt->close();
$conn->close();
