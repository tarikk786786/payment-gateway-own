<?php
/**
 * Working Fast Paytm Status Check - Maintains Original Logic
 */

define('ROOT_DIR', realpath(dirname(__FILE__)) . '/../');
include ROOT_DIR . 'pages/dbFunctions.php';
include ROOT_DIR . 'auth/config.php';
include ROOT_DIR . 'pages/dbInfo.php';

// Request validation (exact same as original)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $byteTransactionId = $_POST['byte_order_status'];
    $order_id = $_POST['order_id'];
} else {
    header('Content-Type: application/json'); // Set header for JSON response
    echo json_encode(['status' => 'error', 'massage' => 'Forbidden: Invalid request method.']);
    exit;
}

// Get order by byteTransactionId (same as original)
$sqlSelectOrderscxr = "SELECT * FROM orders WHERE byteTransactionId=? AND order_id=?";
$stmtSelectOrderscxr = $conn->prepare($sqlSelectOrderscxr);
$stmtSelectOrderscxr->bind_param("ss", $byteTransactionId, $order_id);
$stmtSelectOrderscxr->execute();
$resultSelectOrders = $stmtSelectOrderscxr->get_result();
$cxrrrowOrders = $resultSelectOrders->fetch_assoc();
$stmtSelectOrderscxr->close();

if (!$cxrrrowOrders) {
    header('Content-Type: application/json'); // Set header for JSON response
    echo json_encode(['status' => 'error', 'massage' => 'Byter error: Transaction ID not found.']);
    exit;
}

// Extract order data (same variables as original)
$order_id = $cxrrrowOrders['order_id'];
$bytehackamount = $cxrrrowOrders['amount'];
$bytepaytmtxnref = $cxrrrowOrders['paytm_txn_ref'];
$db_merchantTransactionId = $bytepaytmtxnref;
$merchentMobile = $cxrrrowOrders['merchentMobile'];
$user_token = $cxrrrowOrders['user_token'];
// Check if already processed (same logic)
$sqlCheckStatus = "SELECT status FROM orders WHERE order_id=? AND user_token = ?";
$stmtCheckStatus = $conn->prepare($sqlCheckStatus);
$stmtCheckStatus->bind_param("ss", $order_id, $user_token);
$stmtCheckStatus->execute();
$resultCheckStatus = $stmtCheckStatus->get_result();
if ($resultCheckStatus->num_rows > 0) {
    $rowCheckStatus = $resultCheckStatus->fetch_assoc();
    if ($rowCheckStatus['status'] === 'SUCCESS') {
        header('Content-Type: application/json'); // Set header for JSON response
        echo json_encode(['status' => 'success', 'massage' => 'Transaction Successfully Processed.']);
        $stmtCheckStatus->close();
        $conn->close();
        exit;
    }
}
$stmtCheckStatus->close();

// Delete incomplete reports (same as original)
$sqlDelete = "DELETE FROM reports WHERE status='' AND order_id=? AND user_token = ?";
$stmtDelete = $conn->prepare($sqlDelete);
$stmtDelete->bind_param("ss", $order_id, $user_token);
$stmtDelete->execute();
$stmtDelete->close();

// Get order details again (original does this)
$sqlSelectOrders = "SELECT * FROM orders WHERE order_id=? AND user_token = ?";
$stmtSelectOrders = $conn->prepare($sqlSelectOrders);
$stmtSelectOrders->bind_param("ss", $order_id, $user_token);
$stmtSelectOrders->execute();
$resultSelectOrders = $stmtSelectOrders->get_result();
$rowOrders = $resultSelectOrders->fetch_assoc();
$stmtSelectOrders->close();

if (!$rowOrders) {
    header('Content-Type: application/json'); // Set header for JSON response
    echo json_encode(['status' => 'error', 'massage' => 'Order not found.']);
    exit;
}

$gateway_txn = $rowOrders['gateway_txn'];
$cxrremark1 = $rowOrders['remark1'];
$db_amount = $rowOrders['amount'];

// Get user data (same as original)
$sqlSelectUser = "SELECT * FROM users WHERE user_token=?";
$stmtSelectUser = $conn->prepare($sqlSelectUser);
$stmtSelectUser->bind_param("s", $user_token);
$stmtSelectUser->execute();
$resultSelectUser = $stmtSelectUser->get_result();
$rowUser = $resultSelectUser->fetch_assoc();
$stmtSelectUser->close();

$callback_url = $rowUser['callback_url'];
$megabyteuserid = $rowUser['id'];

// Get MID (same as original)
$sqlSelectMid = "SELECT MID FROM paytm_tokens WHERE user_token=? AND phoneNumber=?";
$stmtSelectMid = $conn->prepare($sqlSelectMid);
$stmtSelectMid->bind_param("si", $user_token, $merchentMobile);
$stmtSelectMid->execute();
$resultSelectMid = $stmtSelectMid->get_result();
$rowMid = $resultSelectMid->fetch_assoc();
$stmtSelectMid->close();

if ($rowMid) {
    $bytemerchantid = $rowMid['MID'];
} else {
    header('Content-Type: application/json'); // Set header for JSON response
    echo json_encode(['status' => 'error', 'massage' => "MID not found for user_token: $byteTransactionId merchentMobile:$order_id"]);
    exit;
}

// Paytm API call (same as original)
$mid = $bytemerchantid;
$txn_ref_id = $bytepaytmtxnref;

$JsonData = json_encode(array("MID" => $mid, "ORDERID" => $txn_ref_id));

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://securegw.paytm.in/order/status?JsonData=" . urlencode($JsonData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

$responseArray = json_decode($response, true);

// print_r($responseArray);

$apiRespcode = $responseArray['RESPCODE'];
if ($apiRespcode != 400){
// Process Paytm response (same logic as original)
if ($responseArray !== null) {
    if ($responseArray['STATUS'] == "TXN_SUCCESS" && $responseArray['MID'] == $mid && $responseArray['ORDERID'] == $txn_ref_id) {
        
        $transactionId = $responseArray['TXNID'];
        $paymentState = $responseArray['STATUS'];
        $vpa = "test@paytm";
        $user_name = "NULL";
        $paymentApp = $responseArray['GATEWAYNAME'];
        $amount = $responseArray['TXNAMOUNT'];
        $transactionNote = $responseArray['MERC_UNQ_REF'];
        $cxrmerchantTransactionId = $responseArray['ORDERID'];
        $bytehackamount = $amount;
        $UTR = $responseArray['BANKTXNID'];
        $mode = $responseArray['PAYMENTMODE'];
        // Insert report (same as original)
        $sqlInsertReport = "INSERT INTO reports (transactionId, status, order_id, vpa, user_name, paymentApp, amount, user_token, transactionNote, merchantTransactionId, user_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmtInsertReport = $conn->prepare($sqlInsertReport);
        $stmtInsertReport->bind_param("sssssssssss", $transactionId, $paymentState, $order_id, $vpa, $user_name, $paymentApp, $amount, $user_token, $transactionNote, $cxrmerchantTransactionId, $megabyteuserid);
        
        if ($stmtInsertReport->execute() === TRUE) {
            $stmtInsertReport->close();
        }else{
            echo json_encode(['status' => 'error', 'massage' => 'Report Data Insert Failed']);
            exit;
        }
    } elseif ($responseArray['RESPMSG']=="Invalid Order Id.") {
        echo json_encode(['status' => 'pending', 'massage' => 'Transaction Still not recived in payTm']);
        exit;
    }
}

// Final status check (same logic as original)
$sqlSelectReports = "SELECT * FROM reports WHERE order_id=? AND user_token=?";
$stmtSelectReports = $conn->prepare($sqlSelectReports);
$stmtSelectReports->bind_param("ss", $order_id, $user_token);
$stmtSelectReports->execute();
$resultSelectReports = $stmtSelectReports->get_result();
$rowReports = $resultSelectReports->fetch_assoc();
$stmtSelectReports->close();

header('Content-Type: application/json'); // Set header for JSON response before any echo

if ($rowReports) {
    $db_status = $rowReports['status'];
    $db_user_token = $rowReports['user_token'];
    $db_transactionId = $rowReports['transactionId'];
    $db_transactionNote = $rowReports['transactionNote'];

    // Success condition (same as original)
    if ($db_status == 'TXN_SUCCESS' && isset($cxrmerchantTransactionId) && $cxrmerchantTransactionId == $db_merchantTransactionId && $bytehackamount == $db_amount) {
        
        // Update orders status
        $sql = "UPDATE orders SET status='SUCCESS',utr=?,payerUpi=? WHERE order_id=? AND user_token=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $UTR, $mode, $order_id, $user_token);
        $stmt->execute();
        $stmt->close();

        // Update reports status
        $sql = "UPDATE reports SET status='TXN_SUCCESS' WHERE order_id=? AND user_token=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $order_id ,$user_token);
        $stmt->execute();
        $stmt->close();
        
        // Reset fail count
        $sql = "UPDATE paytm_tokens SET failCount='0' WHERE phoneNumber=? AND user_token=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $merchentMobile, $user_token);
        $stmt->execute();
        $stmt->close();
        
        // Send callback
        if (function_exists('sendCallback')) {
            sendCallback("SUCCESS", $UTR, $amount, $order_id, $megabyteuserid);
        }
        
        echo json_encode(['status' => 'success', 'massage' => 'Transaction Successfully Processed.']);
    } else {
        echo json_encode(['status' => 'pending', 'massage' => 'Transaction is still pending.']);
    }

    // Handle failure statuses (same as original)
    if ($db_status == 'FAILURE' || $db_status == 'FAILED' || $db_status == 'UPI_BACKBONE_ERROR') {
        echo json_encode(['status' => 'FAILURE', 'massage' => 'Transaction failed.']);
    }
} else {
    echo json_encode(['status' => 'pending', 'massage' => 'Transaction status is pending or not found in reports.']);
}
}else {
    echo json_encode(['status' => 'error', 'massage' => $responseArray['RESPMSG']]);
}
$conn->close();
?>
