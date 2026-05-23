<?php
// Mobiquik Status Page


date_default_timezone_set("Asia/Kolkata");

define('ROOT_DIR', realpath(dirname(__FILE__)) . '/../');
include ROOT_DIR . 'pages/dbFunctions.php';
include ROOT_DIR . 'pages/dbInfo.php';
include ROOT_DIR . 'auth/config.php';

$link_token = ($_POST["token"]);

// Fetch order_id based on the token from the payment_links table
$sql_fetch_order_id = "SELECT order_id, created_at FROM payment_links WHERE link_token = '$link_token'";
$result = getXbyY($sql_fetch_order_id);

if (count($result) === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Token not found or expired']);
    exit;
}

$order_id = $result[0]['order_id'];
$created_at = strtotime($result[0]['created_at']);
$current_time = time();

// Check if token is expired (5 minutes)
if (($current_time - $created_at) > (5 * 60)) {
    echo json_encode(['status' => 'error', 'message' => 'Token has expired']);
    exit;
}

// Fetch order details
$sql_order = "SELECT * FROM orders WHERE order_id='$order_id'";
$order_result = getXbyY($sql_order);

if (empty($order_result)) {
    echo json_encode(['status' => 'error', 'message' => 'Order not found']);
    exit;
}


$user_token = $order_result[0]['user_token'];
$redirect_url = $order_result[0]['redirect_url'] ?: 'https://'.$_SERVER["SERVER_NAME"].'/';  // If redirect URL is empty, set a default
$cxruser_id = $order_result[0]['user_id'];
$orderstatus = $order_result[0]['status'];

// If order is already successful, stop the process
if ($orderstatus == "SUCCESS") {
    echo json_encode(['status' => 'info', 'message' => 'Order is already successful']);
    exit;
}

// Fetch UPI and Authorization details for the user
$sql_mobikwik = "SELECT * FROM manual_token WHERE user_token='$user_token'";
$mobikwik_result = getXbyY($sql_mobikwik);
$upi_id = $mobikwik_result[0]['merchant_upi'];
$Authorization = $mobikwik_result[0]['Authorization'];  // Removed double quotes

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['utrverify'])) { 
    $utr_number = ($_POST['utr_number']);
    $rrn = $utr_number;

    if (!empty($utr_number)) {
        // Check if UTR has already been used
        $check = db_exists($conn, "orders", "utr=$utr_number");
        if (!$check) {
            $update= db_custom_query($conn, "UPDATE `orders` SET `utr`='$utr_number' WHERE order_id='$order_id' AND user_id='$cxruser_id'");
            
                    if ($update) {
                        // sendCallback("SUCCESS", $utr_number, $amount, $order_id, $userid);
                        echo json_encode(['status' => 'success', 'message' => 'Utr Recorded', 'redirect_url' => $redirect_url]);
                        exit;
                    } else {
                        echo json_encode(['status' => 'error', 'message' => 'Failed to update the order status']);
                    }
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'This UTR is Alrady used']);
                }
            } else {
                echo json_encode(['status' => 'error', 'message' => 'No transaction data found']);
            }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'UTR number is missing']);
    }

?>
