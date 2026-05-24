<?php
use WHMCS\Database\Capsule;

require_once __DIR__ . '/../../../init.php';
require_once __DIR__ . '/../../../includes/gatewayfunctions.php';
require_once __DIR__ . '/../../../includes/invoicefunctions.php';

$gatewayModuleName = "upigateway";
$gatewayParams = getGatewayVariables($gatewayModuleName);

if (!$gatewayParams["type"]) {
    die("Module Not Activated");
}

// Ensure webhook payload is captured whether it's JSON or Form Data
$data = $_POST;
if (empty($data)) {
    $json = file_get_contents('php://input');
    if (!empty($json)) {
        $data = json_decode($json, true);
    }
}

file_put_contents(__DIR__ . '/upigateway_callback_log.txt', date('Y-m-d H:i:s') . " => " . print_r($data, true) . PHP_EOL, FILE_APPEND);

$orderId = $data['order_id'] ?? '';
$amount = $data['amount'] ?? 0;
$status = $data['status'] ?? '';
$txn_id = !empty($data['utr']) ? $data['utr'] : $orderId;

if (!preg_match('/WHMCS_(\d+)_/', $orderId, $matches)) {
    logTransaction($gatewayParams["name"], $data, "Invalid Order ID Format");
    die("Invalid Order");
}

$invoiceId = $matches[1];

// Validate invoice ID exists
$invoiceId = checkCbInvoiceID($invoiceId, $gatewayParams['name']);

// Prevent duplicate transactions
checkCbTransID($txn_id);

// Apply payment if status is success
if (strtolower($status) === 'success' || strtoupper($status) === 'SUCCESS') {
    addInvoicePayment($invoiceId, $txn_id, $amount, 0, $gatewayModuleName);
    logTransaction($gatewayParams["name"], $data, "Successful");
    echo "OK";
} else {
    logTransaction($gatewayParams["name"], $data, "Failed");
    echo "FAILED";
}
