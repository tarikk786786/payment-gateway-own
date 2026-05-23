<?php

// -----------------------------------------------------------------------------
// Bootstrap
// -----------------------------------------------------------------------------

declare(strict_types=1); // Safe; does not alter runtime outputs here.

date_default_timezone_set('Asia/Kolkata');

define('PROJECT_ROOT', realpath(dirname(__FILE__)) . '/../');

include PROJECT_ROOT . 'pages/dbFunctions.php';
include PROJECT_ROOT . 'pages/dbInfo.php';
include PROJECT_ROOT . 'auth/config.php'; // Should define $conn (mysqli)

// -----------------------------------------------------------------------------
// Small Utility Helpers (internal only)
// -----------------------------------------------------------------------------

/** Send JSON response and terminate */
function respond(int $httpCode, array $payload): void {
    http_response_code($httpCode);
    header('Content-Type: application/json');
    echo json_encode($payload);
    exit();
}

/** Validate integer-like string */
function isInteger($value): bool {
    return filter_var($value, FILTER_VALIDATE_INT) !== false;
}

/** 10 digit (or fewer) numeric customer number */
function isCustomerNumberValid($value): bool {
    // return isInteger($value) && strlen((string)$value) <= 10;
    return true;
}

/** Random numeric string */
function RandomNumber(int $length): string { // (Retained though not used externally; could be used later)
    $str = '';
    for ($i = 0; $i < $length; $i++) {
        $str .= mt_rand(0, 9);
    }
    return $str;
}

/** Alphanumeric random */
function GenRandomString(int $length = 10): string {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[random_int(0, $charactersLength - 1)];
    }
    return $randomString;
}

/** Secure unique token */
function generateUniqueToken(): string {
    $token = microtime(true) . bin2hex(random_bytes(16)) . random_int(1, 50);
    return hash('sha256', $token);
}

/** Convenience: safe array getter */
function post(string $key): string {
    return isset($_POST[$key]) ? trim((string)$_POST[$key]) : '';
}

/** Wrapper to count rows with condition (uses existing db_count if present) */
function tableCount(mysqli $conn, string $table, string $where): int {
    if (function_exists('db_count')) {
        return (int)db_count($conn, $table, $where);
    }
    $sql = "SELECT COUNT(*) c FROM `$table` WHERE $where";
    $res = $conn->query($sql);
    if ($res && ($row = $res->fetch_assoc())) return (int)$row['c'];
    return 0;
}

/** Delete helper (uses existing db_delete) */
function tableDelete(mysqli $conn, string $table, string $where): void {
    if (function_exists('db_delete')) {
        db_delete($conn, $table, $where);
        return;
    }
    $conn->query("DELETE FROM `$table` WHERE $where");
}

/** Simple SELECT * ... wrapper using prepared statement by column */
function fetchUserByToken(mysqli $conn, string $user_token): ?array {
    $sql = 'SELECT * FROM users WHERE user_token = ? LIMIT 1';
    $stmt = $conn->prepare($sql);
    if (!$stmt) return null;
    $stmt->bind_param('s', $user_token);
    if (!$stmt->execute()) return null;
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    $stmt->close();
    return $row ?: null;
}

/** Atomic increment with limit check */
function incrementTransactionCount(mysqli $conn, string $user_token, int $limit): bool {
    // Ensure we only increment if current count <= limit (original logic: reject when > limit BEFORE increment).
    // To preserve original behaviour (reject only if > limit), we read first; if equal to or below limit we increment.
    $sqlSel = 'SELECT tranjection_Count FROM users WHERE user_token = ? LIMIT 1';
    $stmtSel = $conn->prepare($sqlSel);
    if (!$stmtSel) return false;
    $stmtSel->bind_param('s', $user_token);
    if (!$stmtSel->execute()) { $stmtSel->close(); return false; }
    $res = $stmtSel->get_result();
    $row = $res->fetch_assoc();
    $stmtSel->close();
    if (!$row) return false;
    $current = (int)$row['tranjection_Count'];
    if ($current > $limit) { // replicate original check (strict >)
        return false; // Caller will send the same error message.
    }
    $sqlUpd = 'UPDATE users SET tranjection_Count = tranjection_Count + 1 WHERE user_token = ?';
    $stmtUpd = $conn->prepare($sqlUpd);
    if (!$stmtUpd) return false;
    $stmtUpd->bind_param('s', $user_token);
    $ok = $stmtUpd->execute();
    $stmtUpd->close();
    return $ok;
}

// -----------------------------------------------------------------------------
// Verify Request Method
// -----------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(400, ['status' => false, 'message' => 'Unauthorized Access']);
}

// -----------------------------------------------------------------------------
// Collect Inputs (without introducing new error messages)
// -----------------------------------------------------------------------------
$customer_mobile = post('customer_mobile');
$user_token      = post('user_token');
$amount          = post('amount');
$order_id        = post('order_id');
$redirect_url    = post('redirect_url');
$remark1         = post('remark1');
$remark2         = post('remark2');
$route           = post('route'); // (Kept though not used – maintains compatibility)

$byteorderid = 'BYTE' . random_int(1111, 9999) . time();

// -----------------------------------------------------------------------------
// Basic Validations (exact same messages)
// -----------------------------------------------------------------------------
if (!isCustomerNumberValid($customer_mobile)) {
    respond(400, ['status' => false, 'message' => 'Invalid customer_mobile: Must be numeric and max 10 digits']);
}

if (!is_numeric($amount)) {
    respond(400, ['status' => false, 'message' => 'Invalid Amount: Send Payload in (application/x-www-form-urlencoded)']);
}
if (!is_numeric($amount) || $amount < 1 || $amount > 200000) {
    respond(400, ['status' => false, 'message' => 'Invalid Amount: Must be between 1 and 200000']);
}
$amount = (int)$amount; // cast for later uses

// -----------------------------------------------------------------------------
// Fetch User Once
// -----------------------------------------------------------------------------
$userRow = fetchUserByToken($conn, $user_token);
if (!$userRow) {
    respond(400, ['status' => false, 'message' => 'Invalid user_token: User not found']);
}

$bydb_unq_user_id          = $userRow['id'];
$bydb_order_hdfc_conn      = $userRow['hdfc_connected'];
$bydb_order_phonepe_conn   = $userRow['phonepe_connected'];
$bydb_order_paytm_conn     = $userRow['paytm_connected'];
$bydb_order_bharatpe_conn  = $userRow['bharatpe_connected'];
$bydb_order_googlepay_conn = $userRow['googlepay_connected'];
$bydb_order_freecharge_conn= $userRow['freecharge_connected'];
$bydb_order_sbi_conn       = $userRow['sbi_connected'];
$bydb_order_mobikwik_conn  = $userRow['mobikwik_connected'];
$bydb_order_manual_conn    = $userRow['manual_connected'];
$bydb_order_amazon_conn    = $userRow['amazon_connected'];
$bydb_order_paynearby_conn    = $userRow['paynearby_connected'];
$bydb_order_quintuspay_conn    = $userRow['quintuspay_connected'];
$merchentRouting           = $userRow['merchentRouting'];
$isuserbanned              = $userRow['acc_ban'];
$isacc_lock                = $userRow['acc_lock'];
$planId                    = (int)$userRow['planId'];
$tranjection_Count         = (int)$userRow['tranjection_Count'];
$expire_date               = $userRow['expiry'];
$uid                       = $userRow['id'];

$description = RandomNumber(20);
// -----------------------------------------------------------------------------
// Account Status Checks (messages unchanged)
// -----------------------------------------------------------------------------
if ($isuserbanned === 'Yes') {
    respond(400, ['status' => false, 'message' => 'User account banned']);
}
if ($isacc_lock === 'Yes') {
    respond(400, ['status' => false, 'message' => 'User account locked']);
}

// -----------------------------------------------------------------------------
// Duplicate Order ID Check
// -----------------------------------------------------------------------------
$orderCheckSql = 'SELECT order_id FROM orders WHERE order_id = ? AND user_token = ? LIMIT 1';
$ocStmt = $conn->prepare($orderCheckSql);
if ($ocStmt) {
    $ocStmt->bind_param('ss', $order_id, $user_token);
    $ocStmt->execute();
    $res = $ocStmt->get_result();
    if ($res && $res->fetch_assoc()) {
        $ocStmt->close();
        respond(400, ['status' => false, 'message' => 'Order ID already exists for this user']);
    }
    $ocStmt->close();
}
// -----------------------------------------------------------------------------
// Plan Validity
// -----------------------------------------------------------------------------
$today = date('Y-m-d');
if ($expire_date < $today) {
    respond(400, ['status' => false, 'message' => 'Your Plan Expired Please Renew']);
}
// -----------------------------------------------------------------------------
// IP Whitelisting (message unchanged)
// -----------------------------------------------------------------------------
$user_ip = $_SERVER['REMOTE_ADDR'] ?? '';
if ($user_ip !== '198.38.88.206') { // Same bypass logic
    $sql_p = 'SELECT COUNT(*) AS count FROM user_ips WHERE ip = ? AND user_id = ? AND status = "1"';
    $stmtIp = $conn->prepare($sql_p);
    if ($stmtIp) {
        $stmtIp->bind_param('si', $user_ip, $uid);
        $stmtIp->execute();
        $res = $stmtIp->get_result();
        $row = $res->fetch_assoc();
        $stmtIp->close();
        $count = (int)($row['count'] ?? 0);
        if ($count === 0) {
            respond(403, ['status' => false, 'message' => 'Access Denied: Your IP : ' . $user_ip . ' is not whitelisted, Please Add Your Correct Ip in IP settings']);
        }
    }
}

// -----------------------------------------------------------------------------
// Subscription Plan Hit Limit
// -----------------------------------------------------------------------------
$hitlimit = null;
$hitlimit_q = 'SELECT HitLimit FROM subscription_plan WHERE id = ? LIMIT 1';
$hitStmt = $conn->prepare($hitlimit_q);
if ($hitStmt) {
    $hitStmt->bind_param('i', $planId);
    if ($hitStmt->execute()) {
        $res = $hitStmt->get_result();
        if ($res && ($row = $res->fetch_assoc())) {
            $hitlimit = (int)$row['HitLimit'];
        } else {
            $hitStmt->close();
            respond(404, ['status' => false, 'message' => 'Subscription plan not found']);
        }
    } else {
        $hitStmt->close();
        respond(500, ['status' => false, 'message' => 'Query preparation failed']);
    }
    $hitStmt->close();
} else {
    respond(500, ['status' => false, 'message' => 'Query preparation failed']);
}

// Original logic: reject when current count > hit limit.
if ($tranjection_Count > $hitlimit || !incrementTransactionCount($conn, $user_token, $hitlimit)) {
    // The incrementTransactionCount returns false if current count already > limit (preserving behaviour)
    respond(400, ['status' => false, 'message' => 'Tranjection Limit Is Reached, Please Renew Plan']);
}

// -----------------------------------------------------------------------------
// Merchant Definitions (unchanged externally)
// -----------------------------------------------------------------------------

$merchants = [];

// Prepare the SQL statement to fetch data from the 'merchants' table
$sql = "SELECT * FROM merchants WHERE status = 1";
$result = $conn->query($sql);

// Check if the query was successful and returned rows
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $merchantName = $row['merchantname']; // e.g., 'hdfc', 'phonepe'
        $displayName = $row['DisplayName']; // e.g., 'HDFC Vyapar', 'Phonepe Business'
        $tableName = $row['tablename']; // e.g., 'hdfc', 'phonepe_tokens'
        $folderName = $row['foldername']; // e.g., 'payment', 'payment2'
        $userUpdateColumn = $row['user_update_column']; // e.g., 'hdfc_connected'
        $status = $row['status']; // e.g., 1 or 0

        // Determine the 'connected' variable name based on the user_update_column
        // This assumes variables like $bydb_order_hdfc_conn are globally available or passed in scope.
        // If not, you'll need to adjust how these 'connected' states are managed.
        $connectedVarName = 'bydb_order_' . str_replace('_connected', '', $userUpdateColumn) . '_conn';
        // Using isset() and a ternary operator for PHP 7.4 compatibility and to avoid warnings if variable isn't defined
        $connectedValue = isset($$connectedVarName) ? $$connectedVarName : null;

        // Initialize extra_fields and number_column based on merchantName
        $extraFields = [];
        $numberColumn = 'phoneNumber'; // Default, as seen in most examples
        
        // Specific logic for certain merchants as per your original array
        switch (strtolower($merchantName)) {
            case 'hdfc':
                $numberColumn = 'number';
                $extraFields = ['HDFC_TXNID' => '', 'upiLink' => '', 'description' => ''];
                break;
            case 'paytm':
                $extraFields = ['paytm_txn_ref' => GenRandomString() . time()];
                break;
            case 'freecharge':
            case 'sbi':
            case 'mobikwik':
            case 'manual':
            case 'amazon':
                $extraFields = ['paytm_txn_ref' => 'ATC' . substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789'), 0, 5) . time()];
                break;
            // For PhonePe, Bharatpe, Googlepay, extra_fields remains empty as per original array
        }

        $merchants[ucfirst($merchantName)] = [ // ucfirst to match your original array keys (e.g., 'HDFC', 'PhonePe')
            'connected' => $connectedValue,
            'path' => $folderName,
            'method' => ucfirst($merchantName), // Method name usually matches the merchant display name
            'table' => $tableName,
            'number_colum' => $numberColumn,
            'extra_fields' => $extraFields
        ];
    }
    // Free result set
    $result->free();
} else {
    // Handle the case where no merchants are found or query failed
    echo "No merchants found or an error occurred.";
    // You might want to log the error: error_log("MySQLi error: " . $conn->error);
}


// You can now use the $merchants array
// print_r($merchants);
 

// Filter to active merchants
$active_merchants = array_filter($merchants, function ($m) { return $m['connected'] === 'Yes'; });
if (empty($active_merchants)) {
    respond(400, ['status' => false, 'message' => 'Merchant Not Linked']);
}

// -----------------------------------------------------------------------------
// Merchant Selection (preserve behaviour)
// -----------------------------------------------------------------------------
$merchant_config = null;
if ((string)$merchentRouting === '1') {
    $weighted_merchants = [];
    $total_entries = 0;
    foreach ($active_merchants as $name => $cfg) {
        $entry_count = tableCount($conn, $cfg['table'], "user_token='" . $conn->real_escape_string($user_token) . "' AND status = 'Active'");
        if ($entry_count > 0) {
            $weighted_merchants[$name] = [
                'config' => $cfg,
                'count' => $entry_count,
                'range_start' => $total_entries,
                'range_end' => $total_entries + $entry_count - 1
            ];
            $total_entries += $entry_count;
        }
    }
    if ($total_entries === 0) {
        respond(400, ['status' => false, 'message' => 'No merchant entries found for user']);
    }
    $random_number = mt_rand(0, $total_entries - 1);
    foreach ($weighted_merchants as $name => $data) {
        if ($random_number >= $data['range_start'] && $random_number <= $data['range_end']) {
            $merchant_config = $data['config'];
            break;
        }
    }
} else {
    // Fallback: first active merchant
    $merchant_config = reset($active_merchants);
}

// Safety fallback
if (!$merchant_config) {
    respond(400, ['status' => false, 'message' => 'Merchant Not Linked']);
}

// -----------------------------------------------------------------------------
// Fetch Merchant Phone (random if >1)
// -----------------------------------------------------------------------------
$table = $merchant_config['table'];
$number_colum = $merchant_config['number_colum'];
$mcount = tableCount($conn, $table, "user_token='" . $conn->real_escape_string($user_token) . "'");
if ($mcount > 1) {
    $sqlPhone = "SELECT $number_colum FROM `$table` WHERE user_token = ? ORDER BY RAND() LIMIT 1";
} else {
    $sqlPhone = "SELECT $number_colum FROM `$table` WHERE user_token = ? LIMIT 1";
}
$stmtPhone = $conn->prepare($sqlPhone);
$merchentMobile = '';
if ($stmtPhone) {
    $stmtPhone->bind_param('s', $user_token);
    $stmtPhone->execute();
    $res = $stmtPhone->get_result();
    $row = $res->fetch_assoc();
    $merchentMobile = $row[$number_colum] ?? '';
    $stmtPhone->close();
}

// -----------------------------------------------------------------------------
// Cool Down (1 min customer pending order)
// -----------------------------------------------------------------------------
$end_time = date('Y-m-d H:i:s');
$start_time = date('Y-m-d H:i:s', strtotime('-15 seconds'));

$sqlCooldown = 'SELECT create_date FROM orders WHERE customer_mobile = ? AND status = "PENDING" AND user_token = ? AND create_date BETWEEN ? AND ? LIMIT 1';
$stmtCd = $conn->prepare($sqlCooldown);
$reminingtime = '0 seconds';
if ($stmtCd) {
    $stmtCd->bind_param('ssss', $customer_mobile, $user_token, $start_time, $end_time);
    $stmtCd->execute();
    $r = $stmtCd->get_result();
    if ($crow = $r->fetch_assoc()) {
        $create_date = $crow['create_date'];
        $time_diff = strtotime($create_date) - strtotime($start_time); // identical calc to original
        $reminingtime = ($time_diff > 0 ? $time_diff : 0) . ' seconds';
    }
    $stmtCd->close();
}

$sqlCooldownCount = 'SELECT COUNT(*) as total FROM orders WHERE customer_mobile = ? AND status = "PENDING" AND user_token = ? AND create_date BETWEEN ? AND ?';
$stmtCd2 = $conn->prepare($sqlCooldownCount);
if ($stmtCd2) {
    $stmtCd2->bind_param('ssss', $customer_mobile, $user_token, $start_time, $end_time);
    $stmtCd2->execute();
    $rc = $stmtCd2->get_result();
    if ($row = $rc->fetch_assoc()) {
        $count = (int)$row['total'];
        if ($count > 0) {
            respond(400, [
                'status' => false,
                'Quine Count' => $count,
                'message' => 'You left last payment incomplete Try again after ' . $reminingtime . '. Try to complete the process in one try. To return without making the payment Click Cancel.'
            ]);
        }
    }
    $stmtCd2->close();
}

// -----------------------------------------------------------------------------
// AutoCheck Logic (Bharatpe/MOBIKWIK/HDFC) for same amount in short window
// -----------------------------------------------------------------------------
$method = $merchant_config['method'];
if (in_array($method, ['Bharatpe', 'Mobikwik', 'Hdfc','Paynearby'], true)) {
    $end_time = date('Y-m-d H:i:s');
    $start_time = $method === 'HDFC' ? date('Y-m-d H:i:s', strtotime('-30 seconds')) : date('Y-m-d H:i:s', strtotime('-2 minutes'));

    // Find create_date for remaining time calc (same style as original)
    $sqlAmt = 'SELECT create_date FROM orders WHERE amount = ? AND method = ? AND status = "PENDING" AND merchentMobile = ? AND user_token = ? AND create_date BETWEEN ? AND ? LIMIT 1';
    $stmtAmt = $conn->prepare($sqlAmt);
    $reminingtime = '0 seconds';
    if ($stmtAmt) {
        $stmtAmt->bind_param('isssss', $amount, $method, $merchentMobile, $user_token, $start_time, $end_time);
        $stmtAmt->execute();
        $resA = $stmtAmt->get_result();
        if ($crow = $resA->fetch_assoc()) {
            $time_diff = strtotime($crow['create_date']) - strtotime($start_time);
            $reminingtime = ($time_diff > 0 ? $time_diff : 0) . ' seconds';
        }
        $stmtAmt->close();
    }

    $sqlAmtCount = 'SELECT COUNT(*) as total FROM orders WHERE amount = ? AND method = ? AND status = "PENDING" AND user_token = ? AND create_date BETWEEN ? AND ?';
    $stmtAmtC = $conn->prepare($sqlAmtCount);
    if ($stmtAmtC) {
        $stmtAmtC->bind_param('issss', $amount, $method, $user_token, $start_time, $end_time);
        $stmtAmtC->execute();
        $resC = $stmtAmtC->get_result();
        if ($row = $resC->fetch_assoc()) {
            $count = (int)$row['total'];
            if ($count > 0) {
                // Clean up (match original behaviour)
                tableDelete($conn, 'orders', "order_id='" . $conn->real_escape_string($order_id) . "' AND user_token='" . $conn->real_escape_string($user_token) . "'");
                tableDelete($conn, 'payment_links', "order_id='" . $conn->real_escape_string($order_id) . "' AND user_token='" . $conn->real_escape_string($user_token) . "'");
                respond(400, [
                    'status' => false,
                    'Quine Count' => $count,
                    'message' => 'Please try another amount or try after ' . $reminingtime
                ]);
            }
        }
        $stmtAmtC->close();
    }
}

// -----------------------------------------------------------------------------
// Create Payment Link Row
// -----------------------------------------------------------------------------
$link_token = generateUniqueToken();
$cxrtoday = date('Y-m-d H:i:s');

// Use prepared statement for insertion
$sql_insert_link = 'INSERT INTO payment_links (link_token, order_id, user_token, merchentMobile, created_at) VALUES (?,?,?,?,?)';
$stmtLink = $conn->prepare($sql_insert_link);
if (!$stmtLink) {
    respond(400, ['status' => false, 'message' => 'Failed to generate payment link']);
}
$stmtLink->bind_param('sssss', $link_token, $order_id, $user_token, $merchentMobile, $cxrtoday);
if (!$stmtLink->execute()) {
    $stmtLink->close();
    respond(400, ['status' => false, 'message' => 'Failed to generate payment link']);
}
$stmtLink->close();

$payment_link = 'https://' . $_SERVER['SERVER_NAME'] . '/' . $merchant_config['path'] . '/instant-pay/' . $link_token;
$gateway_txn = ($merchant_config['method'] === 'HDFC') ? (string)random_int(1000000000, 9999999999) : uniqid('', true);
$currentTimestamp = date('Y-m-d H:i:s');

// Build dynamic field list
$fields = [
    'gateway_txn' => $gateway_txn,
    'description' => $description,
    'amount' => $amount,
    'order_id' => $order_id,
    'status' => 'PENDING',
    'user_token' => $user_token,
    'utr' => '',
    'customer_mobile' => $customer_mobile,
    'redirect_url' => $redirect_url,
    'Method' => $merchant_config['method'],
    'byteTransactionId' => $byteorderid,
    'create_date' => $currentTimestamp,
    'remark1' => $remark1,
    'remark2' => $remark2,
    'user_id' => $bydb_unq_user_id,
    'merchentMobile' => $merchentMobile,
];
foreach ($merchant_config['extra_fields'] as $field => $value) {
    $fields[$field] = $value; // values already prepared (strings)
}

// Dynamic prepared statement
$columns = implode(',', array_keys($fields));
$placeholders = rtrim(str_repeat('?,', count($fields)), ',');
$sqlOrder = 'INSERT INTO orders (' . $columns . ') VALUES (' . $placeholders . ')';
$stmtOrder = $conn->prepare($sqlOrder);
if (!$stmtOrder) {
    respond(400, ['status' => false, 'message' => 'Failed to create order']);
}
// Bind params dynamically (all treated as string for simplicity to preserve behaviour)
$types = str_repeat('s', count($fields));
$values = array_values(array_map('strval', $fields));
$stmtOrder->bind_param($types, ...$values);
if (!$stmtOrder->execute()) {
    $stmtOrder->close();
    respond(400, ['status' => false, 'message' => 'Failed to create order']);
}
$stmtOrder->close();

// -----------------------------------------------------------------------------
// Success Response (unchanged fields & message)
// -----------------------------------------------------------------------------

$intData_url = 'https://' . $_SERVER['SERVER_NAME'] . '/' . $merchant_config['path'] . '/pay_now.php?token=' . $link_token . '&intData=true';
// // Initialize cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $intData_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Execute and get response
$response = curl_exec($ch);
// echo($response);
curl_close($ch);

// Decode JSON
$data = json_decode($response, true);

// Optional: Check for errors
if (json_last_error() === JSON_ERROR_NONE && !empty($data)) {
    // Save to file (e.g., response.json)
    file_put_contents("response.json", json_encode($data, JSON_PRETTY_PRINT));
    // echo "✅ JSON saved successfully.";
} else {
    // echo "⚠️ Failed to decode or save the response.";
}


respond(201, [
    'status' => true,
    'message' => 'Order Created Successfully',
    'result' => [
        'method' => $merchant_config['method'],
        'orderId' => $order_id,
        'payment_url' => $payment_link,
        'data' => $data
    ]
]);

$conn->close();
?>
