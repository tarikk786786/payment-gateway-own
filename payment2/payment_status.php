<?php
// Ultra-Fast PhonePe Status Page
error_reporting(0);
define('ROOT_DIR', realpath(dirname(__FILE__)) . '/../');

include ROOT_DIR . 'pages/dbFunctions.php';
include ROOT_DIR . 'auth/config.php';
include ROOT_DIR . 'pages/dbInfo.php';

// Fast JSON response
function sendResponse($status, $message, $data = null) {
    $response = ['status' => $status, 'message' => $message];
    if ($data) $response['data'] = $data;
    echo json_encode($response);
    exit;
}

// Get transaction ID (optimized)
$byteTransactionId = $_POST['byte_order_status'] ?? $_GET['byte_order_status'] ?? null;
if (!$byteTransactionId) sendResponse('error', 'Transaction ID is required');

try {
    // Single optimized query with all needed data
    $stmt = $conn->prepare("SELECT * FROM orders WHERE byteTransactionId = ? LIMIT 1");
    $stmt->bind_param("s", $byteTransactionId);
    $stmt->execute();
    $orderData = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$orderData) sendResponse('error', 'Order not found :'.$byteTransactionId);

    // Extract all variables at once
    extract($orderData, EXTR_PREFIX_ALL, 'o');
    $megabyteuserid = $o_user_id;

    // Quick exit if already successful
    if ($o_status === 'SUCCESS') {
        sendResponse('success', 'Order already processed', ['order_id' => $o_order_id]);
    }

    // Cleanup + check report in single operation
    $stmt = $conn->prepare("DELETE FROM reports WHERE status = '' AND order_id = ? AND user_id = ?");
    $stmt->bind_param("si", $o_order_id, $o_user_id);
    $stmt->execute();
    $stmt->close();

    // Check for existing successful report
    $stmt = $conn->prepare("SELECT status, amount, UTR, user_name FROM reports WHERE order_id = ? AND user_id = ? AND status = 'SUCCESS' LIMIT 1");
    $stmt->bind_param("si", $o_order_id, $o_user_id);
    $stmt->execute();
    $existingReport = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // Process existing successful report immediately
    if ($existingReport) {
        $conn->begin_transaction();
        try {
            // Batch update orders and reset fail count
            $stmt = $conn->prepare("UPDATE orders SET status='SUCCESS', utr=?, payerUpi=? WHERE order_id=? AND user_id=?");
            $stmt->bind_param("sssi", $existingReport['UTR'], $existingReport['user_name'], $o_order_id, $o_user_id);
            $stmt->execute();
            $stmt->close();

            $stmt = $conn->prepare("UPDATE phonepe_tokens SET failCount='0' WHERE phoneNumber=? AND user_id=?");
            $stmt->bind_param("si", $o_merchentMobile, $o_user_id);
            $stmt->execute();
            $stmt->close();

            $conn->commit();
            sendCallback("SUCCESS", $existingReport['UTR'], $existingReport['amount'], $o_order_id, $megabyteuserid);
            sendResponse('success', 'Transaction successful', ['utr' => $existingReport['UTR'], 'amount' => $existingReport['amount']]);
        } catch (Exception $e) {
            $conn->rollback();
            sendResponse('error', 'Database update failed');
        }
    }

    // Fetch from API with minimal settings
    $api_url = 'https://' . $_SERVER["SERVER_NAME"] . '/phnpe/user_txn.php?no=' . $o_user_token;
    
    $ch = curl_init($api_url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 8,
        CURLOPT_CONNECTTIMEOUT => 3,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_FOLLOWLOCATION => true
    ]);
    
    $txn_data = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($txn_data === false || $http_code !== 200) {
        sendResponse('error', 'Failed to fetch transaction data from API');
    }

    $jsonData = json_decode($txn_data, true);
    if (!$jsonData || !isset($jsonData['data']['results'])) {
        sendResponse('error', 'Invalid API response format');
    }

    // Process only first 10 results for speed
    $results = array_slice($jsonData['data']['results'], 0, 10);

    foreach ($results as $result) {
        if ($result['merchantTransactionId'] !== $byteTransactionId) continue;
        
        // Fast variable extraction
        $user_name = $result["customerDetails"]["userName"] ?? 'Unknown';
        $paymentApp = $result["paymentApp"]["paymentApp"] ?? 'Unknown';
        $amount = ($result["amount"] ?? 0) / 100;
        $transactionId = $result["transactionId"] ?? '';
        $paymentState = $result["payResponseCode"] ?? 'PENDING';
        $transactionNote = $result["transactionNote"] ?? '';
        $UTR = $result["utr"] ?? '';
        $vpa = $result["vpa"] ?? 'test@paytm';

        // Fast upsert
        $stmt = $conn->prepare("INSERT INTO reports (transactionId, status, order_id, vpa, user_name, paymentApp, amount, user_token, transactionNote, merchantTransactionId, user_id, UTR) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE status = VALUES(status), transactionId = VALUES(transactionId), UTR = VALUES(UTR)");
        $stmt->bind_param("ssssssdsssss", $transactionId, $paymentState, $o_order_id, $vpa, $user_name, $paymentApp, $amount, $o_user_token, $transactionNote, $byteTransactionId, $megabyteuserid, $UTR);
        $stmt->execute();
        $stmt->close();

        // Process payment states
        if ($paymentState === 'SUCCESS' && $amount == $o_amount) {
            $conn->begin_transaction();
            try {
                $stmt = $conn->prepare("UPDATE orders SET status='SUCCESS', utr=?, payerUpi=? WHERE order_id=? AND user_id=?");
                $stmt->bind_param("sssi", $UTR, $user_name, $o_order_id, $o_user_id);
                $stmt->execute();
                $stmt->close();

                $stmt = $conn->prepare("UPDATE phonepe_tokens SET failCount='0' WHERE phoneNumber=? AND user_id=?");
                $stmt->bind_param("si", $o_merchentMobile, $o_user_id);
                $stmt->execute();
                $stmt->close();

                $conn->commit();
                sendCallback("SUCCESS", $UTR, $amount, $o_order_id, $megabyteuserid);
                sendResponse('success', 'Transaction successful', ['utr' => $UTR, 'amount' => $amount]);
            } catch (Exception $e) {
                $conn->rollback();
                sendResponse('error', 'Database update failed');
            }
        } elseif (in_array($paymentState, ['FAILURE', 'FAILED', 'UPI_BACKBONE_ERROR'])) {
            sendCallback("FAILURE", "NA", $amount, $o_order_id, $megabyteuserid);
            sendResponse('failure', 'Transaction failed', ['reason' => $paymentState]);
        } else {
            sendResponse('pending', 'Transaction is pending');
        }
        break;
    }

    sendResponse('pending', 'Transaction not found in reports');

} catch (Exception $e) {
    error_log("PhonePe Status Error: " . $e->getMessage());
    sendResponse('error', 'Internal server error');
} finally {
    if (isset($conn)) $conn->close();
}
?>