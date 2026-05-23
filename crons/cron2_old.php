<?php
//every 1day 0 0,12 * * *
// Define the base directory constant
define('PROJECT_ROOT', realpath(dirname(__FILE__)) . '/../');

// Securely include files using the PROJECT_ROOT constant
include PROJECT_ROOT . 'pages/dbFunctions.php';
include PROJECT_ROOT . 'auth/config.php';

date_default_timezone_set("Asia/Kolkata");

// Set maximum execution time to 5 minutes (300 seconds)
ini_set('max_execution_time', 300);

$cxr4_current_time = date("Y-m-d H:i:s");

// Calculate time threshold (1 hour ago)
$cxr4_time_threshold = date("Y-m-d H:i:s", strtotime('-1 hour'));

// SQL query to fetch orders with create_date more than 1 hour older than current time and status is PENDING
$cxr4_sql = "SELECT order_id, user_token, create_date FROM orders WHERE create_date <= '$cxr4_time_threshold' AND status = 'PENDING'";

$cxr4_result = $conn->query($cxr4_sql);

if ($cxr4_result->num_rows > 0) {
    // Update orders status to FAILURE
    while ($cxr4_row = $cxr4_result->fetch_assoc()) {
        $cxr4_order_id = $cxr4_row["order_id"];
        $cxr4_user_token = $cxr4_row["user_token"];
        // Update status to FAILURE
        $cxr4_update_sql = "UPDATE orders SET status = 'FAILURE' WHERE order_id = '$cxr4_order_id'";
        if ($conn->query($cxr4_update_sql) === TRUE) {
            echo "Order ID: $cxr4_order_id marked as FAILURE.\n";
        } else {
            echo "Error updating record: " . $conn->error . "\n";
        }
    }
} else {
    echo "No orders found with create_date more than 1 hour older than current time.\n";
}