<?php

/**
 * Freecharge Transaction Status Checker
 *
 * This script fetches Freecharge transaction details, updates the order status
 * in the local database, and sends a callback to the user's defined URL.
 * It now returns JSON responses for better programmatic handling.
 */

// Disable error reporting for production to prevent sensitive information leaks.
error_reporting(0);

// --- Configuration and Setup ---

// Define the base directory constant for secure file inclusion.
define('ROOT_DIR', realpath(dirname(__FILE__)) . '/../');

// Include necessary functions and configurations.
// Using 'require_once' to prevent multiple inclusions and fatal errors if files are missing.
require_once ROOT_DIR . 'pages/dbFunctions.php'; // Assuming this sets up the $conn object
require_once ROOT_DIR . 'auth/config.php';       // Assuming this contains DB connection details
require_once ROOT_DIR . 'pages/dbInfo.php';       // Assuming this contains other DB related info

// Set default timezone for consistent timestamp handling.
date_default_timezone_set('Asia/Kolkata');

// Function to send JSON response and exit
function sendJsonResponse($status, $message) {
    header('Content-Type: application/json');
    echo json_encode(['status' => $status, 'message' => $message]);
    exit();
}

// Ensure the database connection is established.
if (!isset($conn) || $conn->connect_error) {
    sendJsonResponse('error', 'Internal Server Error: Database connection failed.');
}

// --- Input Sanitization and Order ID Retrieval ---

$link_token = filter_input(INPUT_POST, 'LINKID', FILTER_SANITIZE_SPECIAL_CHARS);
$order_id = null;
$user_token = null;

if ($link_token) {
    // Fetch order_id and user_token based on the link_token from payment_links table.
    $sql_fetch_link_data = "SELECT order_id, user_token, created_at FROM payment_links WHERE link_token = ?";
    $stmt = $conn->prepare($sql_fetch_link_data);

    if (!$stmt) {
        sendJsonResponse('error', 'Internal Server Error: Failed to prepare statement for link data.');
    }

    $stmt->bind_param('s', $link_token);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    if (!$row || !isset($row['order_id'], $row['user_token'])) {
        sendJsonResponse('error', 'Order ID or User Token not found for the provided link.');
    }

    $order_id = $row['order_id'];
    $user_token = $row['user_token'];

} else {
    // Fallback if link_token is not provided, try to get order_id and user_token directly.
    $order_id = filter_input(INPUT_POST, 'order_id', FILTER_SANITIZE_SPECIAL_CHARS);
    $user_token = filter_input(INPUT_POST, 'user_token', FILTER_SANITIZE_SPECIAL_CHARS);
}

// Validate retrieved or inputted order_id and user_token
if (empty($order_id) || empty($user_token) || !ctype_alnum($order_id) || !ctype_alnum($user_token)) {
    sendJsonResponse('error', 'Invalid or missing order_id or user_token.');
}

// --- Fetch Order Details ---

$sql_fetch_order = "SELECT amount, paytm_txn_ref, user_id, remark1, merchentMobile FROM orders WHERE order_id = ? AND user_token = ?";
$stmt = $conn->prepare($sql_fetch_order);

if (!$stmt) {
    sendJsonResponse('error', 'Internal Server Error: Failed to prepare statement for order details.');
}

$stmt->bind_param('ss', $order_id, $user_token);
$stmt->execute();
$res_p = $stmt->get_result();
$order_details = $res_p->fetch_assoc();
$stmt->close();

if (!$order_details) {
    sendJsonResponse('error', 'Order details not found for the given order ID and user token.');
}

$db_amount = $order_details['amount'];
$cxrbytectxnref = $order_details['paytm_txn_ref'];
$userid = $order_details['user_id'];
$cxrremark1 = $order_details['remark1'];
$merchentMobile = $order_details['merchentMobile'];

// --- Fetch User Callback URL ---

$sql_fetch_user_callback = "SELECT callback_url FROM users WHERE id = ?";
$stmt = $conn->prepare($sql_fetch_user_callback);

if (!$stmt) {
    sendJsonResponse('error', 'Internal Server Error: Failed to prepare statement for user callback URL.');
}

$stmt->bind_param('i', $userid); // Assuming user ID is an integer
$stmt->execute();
$sql_fetch_user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$sql_fetch_user || !isset($sql_fetch_user['callback_url'])) {
    sendJsonResponse('error', 'Callback URL not found for the user.');
}
$callback_url = $sql_fetch_user['callback_url'];

// --- Fetch Freecharge Token ---

$sql_fetch_fc = "SELECT app_fc FROM freecharge_token WHERE user_token = ? AND phoneNumber = ?";
$stmt = $conn->prepare($sql_fetch_fc);

if (!$stmt) {
    sendJsonResponse('error', 'Internal Server Error: Failed to prepare statement for Freecharge token.');
}

$stmt->bind_param('si', $user_token, $merchentMobile); // Assuming phoneNumber is integer or string
$stmt->execute();
$res_fc = $stmt->get_result();
$fc_row = $res_fc->fetch_assoc();
$stmt->close();

if (!$fc_row || !isset($fc_row['app_fc'])) {
    sendJsonResponse('error', 'Freecharge token not found for the provided details.');
}
$app_fc = $fc_row['app_fc'];

// --- Freecharge API Interaction ---

$url = 'https://www.freecharge.in/thv/listv3?fcAppType=MSITE';

// Standardize headers for cURL request.
$headers = [
    'Content-Type: application/json',
    'Accept: application/json, text/plain, */*',
    'Accept-Encoding: gzip, deflate',
    'Accept-Language: en-GB,en-US;q=0.9,en;q=0.8',
    'Origin: https://www.freecharge.in',
    'Referer: https://www.freecharge.in/',
    'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36',
    'Sec-Fetch-Dest: empty',
    'Sec-Fetch-Mode: cors',
    'Sec-Fetch-Site: same-origin',
    'Sec-CH-UA: "Google Chrome";v="131", "Chromium";v="131", "Not_A Brand";v="24"',
    'Sec-CH-UA-Mobile: ?0',
    'Sec-CH-UA-Platform: "Windows"',
    'CSRFRequestIdentifier: YOUR_CSRF_TOKEN_PLACEHOLDER', // Replace with actual CSRF token if needed
    'Cookie: _ga_Q9NVXVJCL0=GS1.1.1734723375.4.1.1734723840.50.0.1170835981; _ga=GA1.1.697946827.1734678023; moe_uuid=6cad2ca1-4f5f-4942-a815-d7f0b940d3cf; app_fc=' . $app_fc . ';'
];

$data = json_encode(["fcAppType" => "MSITE"]);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate'); // Allow cURL to handle decompression

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($curlError) {
    // If there's an encoding error, retry without encoding
    if (strpos($curlError, 'Unrecognized content encoding type') !== false) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_ENCODING, ''); // Disable encoding
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        if ($curlError) {
            sendJsonResponse('error', 'Failed to connect to Freecharge API after retry.');
        }
    } else {
        sendJsonResponse('error', 'Failed to connect to Freecharge API.');
    }
}

if ($httpCode === 200) {
    $responseData = json_decode($response, true);

    if (json_last_error() === JSON_ERROR_NONE && isset($responseData['data']['globalTransactions'])) {
        $transactionFound = false;
        $utr = '';

        foreach ($responseData['data']['globalTransactions'] as $transaction) {
            $txnDetails = $transaction['txnDetails'];

            if ($txnDetails['globalTxnId'] == $cxrbytectxnref) {
                $transactionFound = true;

                // Get UTR from transaction details if available, otherwise generate.
                $utr = $transaction['billerInfo']['billerMetaData'][1]['sectionDetails'][1]['value'] ?? '43' . mt_rand(1000000000, 9999999999);

                // Start a database transaction for atomicity.
                $conn->begin_transaction();
                try {
                    // Update order status in the database.
                    $update_order_query = "UPDATE orders SET status = 'SUCCESS', utr = ? WHERE order_id = ? AND user_id = ?";
                    $stmt = $conn->prepare($update_order_query);
                    if (!$stmt) {
                        throw new Exception('Failed to prepare order update statement.');
                    }
                    $stmt->bind_param('sss', $utr, $order_id, $userid);
                    $stmt->execute();
                    $stmt->close();

                    // Reset failCount in freecharge_token.
                    $update_fc_query = "UPDATE freecharge_token SET failCount = '0' WHERE phoneNumber = ? AND user_id = ?";
                    $stmt = $conn->prepare($update_fc_query);
                    if (!$stmt) {
                        throw new Exception('Failed to prepare freecharge token update statement.');
                    }
                    $stmt->bind_param('si', $merchentMobile, $userid);
                    $stmt->execute();
                    $stmt->close();

                    $conn->commit(); // Commit the transaction.

                    // Send callback to the merchant (no JSON response for callback).
                    sendCallback("SUCCESS", $utr, $db_amount, $order_id, $userid);

                    sendJsonResponse('success', 'Transaction Successful');

                } catch (Exception $e) {
                    $conn->rollback(); // Rollback transaction on error.
                    sendJsonResponse('error', 'Database operation failed during status update.');
                }
            }
        }

        if (!$transactionFound) {
            sendJsonResponse('pending', 'Transaction is pending.');
        }
    } else {
        sendJsonResponse('error', 'Invalid or unexpected response from Freecharge API.');
    }
} else {
    sendJsonResponse('error', 'Freecharge API returned an error or unexpected HTTP code.');
}

// Close the database connection at the end of the script.
$conn->close();

?>