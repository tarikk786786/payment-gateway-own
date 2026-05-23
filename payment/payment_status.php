<?php
error_reporting(0);

// Define the base directory constant
define('ROOT_DIR', realpath(dirname(__FILE__)) . '/../');

// Securely include files using the ROOT_DIR constant
include ROOT_DIR . 'pages/dbFunctions.php';
include ROOT_DIR . 'auth/config.php';
include ROOT_DIR . 'pages/dbInfo.php';

// Sanitizing the $order_id parameter retrieved using $_POST
$order_id = $_POST['order_id'] ?? '';
$user_token = $_POST['user_token'] ?? '';

if (empty($order_id) || empty($user_token)) {
    echo json_encode([
        'status' => 'FAILURE',
        'message' => 'Missing order_id or user_token'
    ]);
    exit;
}
// Use prepared statements to prevent SQL injection
$sql_combined = "SELECT
    o.user_token,
    o.description AS db_description,
    o.HDFC_TXNID AS hdfc_txn,
    o.remark1 AS bbbyteremark1,
    o.amount,
    o.create_date AS otime,
    o.customer_mobile AS customerMobile,
    h.seassion AS seassion_id_hdfc,
    h.number AS hdfc_number,
    u.callback_url,
    u.mobile,
    u.name,
    u.id AS megabyteuserid
FROM orders o
INNER JOIN hdfc h ON o.user_token = h.user_token
INNER JOIN users u ON o.user_token = u.user_token
WHERE o.order_id = '$order_id' AND o.user_token = '$user_token'
LIMIT 1";

$combined_result = getXbyY($sql_combined);

if (empty($combined_result)) {
    echo json_encode(['status' => 'FAILURE', 'massage' => 'Order not found']);
    exit;
}

// Extract all variables from single query result
$user_token = $combined_result[0]['user_token'];
$db_description = $combined_result[0]['db_description'];
$hdfc_txn = $combined_result[0]['hdfc_txn'];
$bbbyteremark1 = $combined_result[0]['bbbyteremark1'];
$amount = $combined_result[0]['amount'];
$otime = $combined_result[0]['otime'];
$customerMobile = $combined_result[0]['customerMobile'];
$seassion_id_hdfc = $combined_result[0]['seassion_id_hdfc'];
$hdfc_number = $combined_result[0]['hdfc_number'];
$callback_url = $combined_result[0]['callback_url'];
$mobile = $combined_result[0]['mobile'];
$name = $combined_result[0]['name'];
$megabyteuserid = $combined_result[0]['megabyteuserid'];

// Optimized cURL with timeout settings for faster response
$url = 'https://' . $server . '/payment/mstatement.php?no=' . $hdfc_number . '&session=' . $seassion_id_hdfc;
$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_TIMEOUT => 10,        // 10 second timeout
    CURLOPT_CONNECTTIMEOUT => 5,  // 5 second connection timeout
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_MAXREDIRS => 3
]);
$txn_data = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code !== 200 || !$txn_data) {
    echo json_encode(['status' => 'pending', 'massage' => 'Could not fetch transaction data']);
    exit;
}

$json0 = json_decode($txn_data, true);
if (!isset($json0["transactionParams"]) || !is_array($json0["transactionParams"])) {
    echo json_encode(['status' => 'pending', 'massage' => 'Invalid transaction data format']);
    exit;
}

$results = $json0["transactionParams"];
$rows = count($results);

// Pre-calculate time values once
$order_time = date("H:i:s", strtotime($otime));
$order_timestamp = strtotime($order_time);
$expiry_timestamp = strtotime($order_time . " +2 minutes");

// Batch process transactions with optimized logic
$batch_inserts = [];
$update_queries = [];
$success_found = false;
$failure_found = false;

for ($i = 0; $i < $rows; $i++) {
    $txnid = $results[$i]["txnid"];
    $Status = $results[$i]["status"];
    $utr = $results[$i]["utr"];
    $txnmessage = $results[$i]["txnmessage"];
    $hdescription = $results[$i]["description"];
    $payerVpa = $results[$i]["payerVpa"];
    $issuerRefNo = $results[$i]["issuerRefNo"];
    $paymentApp = $results[$i]["paymentApp"];
    $hamount = $results[$i]["amount"];
    $htime = $results[$i]["requestTime"];
    $hcustomerMobile = $results[$i]["customerMobile"];

    $hdfc_timestamp = strtotime(date("H:i:s", strtotime($htime)));

    // Optimized time comparison
    $time_valid = ($order_timestamp < $hdfc_timestamp && $hdfc_timestamp < $expiry_timestamp);

    if ($time_valid && $hdescription == "PSP Transaction" && $hamount == $amount) {
        $i++;
        if ($i < $rows) {
            $hcustomerMobile = $results[$i]["customerMobile"];
            if ($hcustomerMobile == $customerMobile) {
                $hdescription = $results[$i]["description"];
            }else {
                $i++;
            if ($i < $rows) {
                $hcustomerMobile = $results[$i]["customerMobile"];
                if ($hcustomerMobile == $customerMobile) {
                    $hdescription = $results[$i]["description"];
                }
            }
            }
        }
    }

    // Process SUCCESS transactions (Status = '3')
    if ($Status == '3' && $hdescription == $db_description && $hamount == $amount) {
        // Check for duplicate UTR in a single query
        $duplicate_check = "SELECT COUNT(*) as count FROM orders WHERE utr='$issuerRefNo'";
        $dup_result = getXbyY($duplicate_check);

        if ($dup_result[0]['count'] == 0) {
            // Check if transaction already exists in reports
            $txn_check = "SELECT COUNT(*) as count FROM reports WHERE transactionId = '$txnid'";
            $txn_result = getXbyY($txn_check);

            if ($txn_result[0]['count'] == 0) {
                $bytetoday = date("Y-m-d H:i:s");

                // Prepare batch insert for reports
                $batch_inserts[] = "('$txnid', '$Status', '$payerVpa', '$paymentApp', '$amount', '$user_token', '$issuerRefNo', '$hdescription', '$mobile', '$bytetoday', '$megabyteuserid')";

                // Prepare update queries
                $update_queries[] = "UPDATE `orders` SET status='SUCCESS',utr='$issuerRefNo',payerUpi='$payerVpa' WHERE order_id='$order_id' AND user_token='$user_token'";
                $update_queries[] = "UPDATE `hdfc` SET failCount='0' WHERE number='$hdfc_number' AND user_token='$user_token'";

                $success_found = true;
                break; // Exit loop on first success
            }
        }
    }
    // Process FAILURE transactions (Status = '4')
    elseif ($Status == '4' && ($hdescription == $db_description || $customerMobile == $hcustomerMobile) && $hamount == $amount) {
        $txn_check = "SELECT COUNT(*) as count FROM reports WHERE transactionId = '$txnid'";
        $txn_result = getXbyY($txn_check);

        if ($txn_result[0]['count'] == 0) {
            // Prepare batch insert for reports
            $batch_inserts[] = "('$txnid', '$Status', '$payerVpa', '$paymentApp', '$amount', '$user_token', '$issuerRefNo', '$hdescription', '$mobile', '', '$megabyteuserid')";

            $update_queries[] = "UPDATE `orders` SET status='FAILURE' WHERE order_id='$order_id' AND user_token='$user_token'";

            $failure_found = true;
        }
    }
}

// Execute batch operations
if (!empty($batch_inserts)) {
    $batch_sql = "INSERT INTO reports (transactionId, status, vpa, paymentApp, amount, user_token, UTR, description, mobile, date, user_id) VALUES " . implode(',', $batch_inserts);
    setXbyY($batch_sql);
}

// Execute update queries
foreach ($update_queries as $query) {
    setXbyY($query);
}

// Update reports with order_id
if (!empty($db_description)) {
    $update_reports = "UPDATE reports SET order_id='$order_id' WHERE description='$db_description'";
    setXbyY($update_reports);
}

// Final status check and response
if (!empty($db_description)) {
    $status_check = "SELECT status, UTR FROM reports WHERE description='$db_description' AND order_id ='$order_id' LIMIT 1";
    $res_pp = getXbyY($status_check);

    if (!empty($res_pp) && isset($res_pp[0]['status'])) {
        $db_status = $res_pp[0]['status'];

        if ($db_status == '3') {
            $utrf = $res_pp[0]['UTR'];
            echo json_encode(['status' => 'success', 'massage' => 'Transaction Successful']); // Original success response
            sendCallback("SUCCESS", $utrf, $amount, $order_id, $megabyteuserid);
        } elseif ($db_status == '4') {
            echo json_encode(['status' => 'FAILURE', 'massage' => 'Transaction Failed']); // Updated to JSON
            sendCallback("FAILURE", "NA", $amount, $order_id, $megabyteuserid);
        } else {
            echo json_encode(['status' => 'pending', 'massage' => 'Transaction pending']); // Updated to JSON
        }
    } else {
        echo json_encode(['status' => 'pending', 'massage' => 'Transaction status not found in reports']); // Updated to JSON
    }
} else {
    // sendCallback("FAILURE", "error", $amount, $order_id, $megabyteuserid);
    // echo 'db_description not generated'; // Commented out
    echo json_encode(['status' => 'pending', 'massage' => 'Database description not generated']); // Updated to JSON
    // $sqf = "UPDATE `orders` SET status='FAILURE' WHERE order_id='$order_id'";
    // setXbyY($sqf);
}
?>