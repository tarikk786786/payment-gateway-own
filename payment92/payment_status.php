<?php
// amazon status
error_reporting(0);

// Define the base directory constant
define('ROOT_DIR', realpath(dirname(__FILE__)) . '/../');

// Securely include files using the ROOT_DIR constant
include ROOT_DIR . 'pages/dbFunctions.php';
include ROOT_DIR . 'auth/config.php';
include ROOT_DIR . 'pages/dbInfo.php';

// Check if byte_order_status is received via POST
if (!isset($_POST['byte_order_status'])) {
    die("Order ID not received.");
}

$byte_order_status = $_POST['byte_order_status'];

// Fetch order details from the database
$sql_fetch_order = $conn->prepare("SELECT user_token, amount, order_id, user_id, merchentMobile, remark1 FROM orders WHERE order_id = ?");
$sql_fetch_order->bind_param('s', $byte_order_status);
$sql_fetch_order->execute();
$order_details = $sql_fetch_order->get_result()->fetch_assoc();

if (!$order_details) {
    die("Order not found.");
}

$user_token = $order_details['user_token'];
$db_amount = (int)$order_details['amount']; // Ensure amount is an integer
$cxrbytectxnref = trim($order_details['order_id']); // Trim to remove whitespace
$userid = $order_details['user_id'];
$merchentMobile = $order_details['merchentMobile'];
$cxrremark1 = $order_details['remark1'];

// Fetch user callback URL
$sql_fetch_user = $conn->query("SELECT callback_url FROM users WHERE id = '$userid'")->fetch_assoc();
$callback_url = $sql_fetch_user['callback_url']; // Assuming this is where the webhook URL is stored

// Fetch ubid_acbin, at_acbin, x_acbin from amazon_token table
$sql_fetch_fc = "SELECT ubid_acbin, at_acbin, x_acbin FROM amazon_token WHERE user_token = ? AND user_id = ?";
$stmt = $conn->prepare($sql_fetch_fc);
$stmt->bind_param('ss', $user_token, $userid);
$stmt->execute();
$res_fc = $stmt->get_result();
$fc_row = $res_fc->fetch_assoc();

if (!$fc_row || !isset($fc_row['ubid_acbin']) || !isset($fc_row['at_acbin']) || !isset($fc_row['x_acbin'])) {
    die("Amazon token data not found.");
}

$ubid_acbin = $fc_row['ubid_acbin'];
$at_acbin = $fc_row['at_acbin'];
$x_acbin = $fc_row['x_acbin'];

// Construct the cookie string
$cookies = "ubid-acbin=$ubid_acbin; at-acbin=$at_acbin; x-acbin=$x_acbin";

// New API call to fetch transaction history
$headers = [
    'Host: www.amazon.in',
    'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:134.0) Gecko/20100101 Firefox/134.0',
    'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
    'Accept-Language: en-US,en;q=0.5',
    'Accept-Encoding: gzip, deflate, br, zstd',
    'Connection: keep-alive',
    'Upgrade-Insecure-Requests: 1',
    'Sec-Fetch-Dest: document',
    'Sec-Fetch-Mode: navigate',
    'Sec-Fetch-Site: none',
    'Sec-Fetch-User: ?1',
    'Priority: u=0, i',
    'TE: trailers',
    'Cookie: ' . $cookies
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://www.amazon.in/pay/history?tab=ALL&filter={"paymentInstruments":[{"paymentInstrumentType":"UPI"}]}');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_ENCODING, '');
$response = curl_exec($ch);
curl_close($ch);

$html = $response;


if (empty($html)) {
    // First query - Update token status to Deactive
    $countq = "UPDATE amazon_token SET status = 'Deactive' WHERE phoneNumber = ? AND user_token = ?";
    $stmt = $conn->prepare($countq);
    $stmt->bind_param('is', $merchentMobile, $user_token);
    $stmt->execute();
    
    // Count remaining active tokens
    $countQuery = "SELECT COUNT(*) FROM amazon_token WHERE status = 'Active' AND user_token = ?";
    $countStmt = $conn->prepare($countQuery);
    $countStmt->bind_param('s', $user_token);
    $countStmt->execute();
    $result = $countStmt->get_result();
    $mcount = $result->fetch_row()[0];
    
    if ($mcount == 0) {
        // Update 'amazon_connected' status in users table
        $updateUserQuery = "UPDATE users SET amazon_connected = ? WHERE user_token = ?";
        $updateUserStmt = $conn->prepare($updateUserQuery);
        $updateUserStmt->bind_param('ss', $connected, $user_token);
        
        $connected = 'No';
        $updateUserStmt->execute();
    }
    
    echo "SeasonExpire";
    exit;
}

// Load HTML into DOMDocument
$dom = new DOMDocument();
libxml_use_internal_errors(true);
$dom->loadHTML($html);
libxml_clear_errors();
$xpath = new DOMXPath($dom);

// Initialize an array to hold filtered transactions
$transactions = [];

// Find all transaction containers
$transactionNodes = $xpath->query("//span[@class='a-declarative']");
// print_r($transactionNodes);
foreach ($transactionNodes as $node) {
    // Extract txid
    $data = json_decode(html_entity_decode($node->getAttribute('data-itemdetailexpandedview')), true);
    
    $txid = $data['idempotencyId'] ?? null;

    // Check if the txid contains 'INCOMING_PAY'
    if ($txid && strpos($txid, 'INCOMING_PAY') !== false) {
        // Extract name (Received from)
        $nameNode = $xpath->query(".//span[@class='a-size-medium a-color-base']", $node)->item(0);
        $received_from = trim(str_replace("Received from", "", $nameNode->nodeValue));

        // Extract amount
        $amountNode = $xpath->query(".//span[@class='a-size-medium a-color-attainable']", $node)->item(0);
        $amount = (int)preg_replace('/[^\d]/', '', $amountNode->nodeValue); // Convert to integer

        // Extract UTR (Bank Reference ID) using getUTRFromDetailPage
        $utr = getUTRFromDetailPage($cookies, $txid);

        // Extract transaction date
        $dateNode = $xpath->query(".//span[contains(@class, 'a-size-base a-color-tertiary') and not(contains(text(), 'Amazon Pay UPI'))]", $node)->item(0);
        $transaction_date = $dateNode ? trim($dateNode->nodeValue) : null;

        // Extract Payee (Credited to)
        $payeeNode = $xpath->query(".//span[contains(text(), 'Credited to')]/parent::div/following-sibling::div//span[@class='a-size-base a-color-base']", $node)->item(0);
        $payee = $payeeNode ? trim($payeeNode->nodeValue) : null;

        // Extract TXID (transaction identifier)
        $parts = explode('_', $txid);
        $txn_id = isset($parts[2]) ? trim($parts[2]) : null;

        // Add the formatted transaction to the list
        if ($utr) {
            $transactions[] = [
                "transactions_id" => $txid,
                "txn_id" => $txn_id,
                "received_from" => $received_from,
                "payment_method" => "Amazon Pay UPI",
                "transaction_date" => $transaction_date,
                "txn_status" => "Success",
                "amount" => $amount,
                "utr_number" => $utr,
            ];
        }
    }
}

// Check if the transaction matches the order details
foreach ($transactions as $transaction) {
    if ($transaction['txn_id'] == $cxrbytectxnref && $transaction['amount'] == $db_amount) {
        // Update order status in the database
        $update_query = "UPDATE orders SET status = 'SUCCESS', utr = ? WHERE order_id = ? AND user_id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param('sss', $transaction['utr_number'], $byte_order_status, $userid);
        $stmt->execute();
        
        $sql = "UPDATE amazon_token SET failCount='0' WHERE phoneNumber=? AND user_id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $merchentMobile,$userid);
        $stmt->execute();
        $stmt->close();
         
        // Send success response to webhook only after order status is updated
        echo 'success';
        sendCallback("SUCCESS", $transaction['utr_number'], $amount, $byte_order_status, $userid);
        exit;
    }
}

// If no matching transaction is found
echo 'pending';

// Function to fetch UTR from the transaction detail page
function getUTRFromDetailPage($cookie, $idempotencyId) {
    $url = "https://www.amazon.in/pay/transaction-details?idempotencyId=" . urlencode($idempotencyId) . "&useCase=p2p_incoming_pay&ingress=TRANSACTION_HISTORY";

    $headers = [
        'User-Agent: Mozilla/5.0',
        'Accept: text/html',
        'Cookie: ' . $cookie,
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_ENCODING, '');
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $html = curl_exec($ch);
    curl_close($ch);

    if (empty($html)) return null;

    libxml_use_internal_errors(true);
    $dom = new DOMDocument();
    $dom->loadHTML($html);
    libxml_clear_errors();

    $xpath = new DOMXPath($dom);
    $nodes = $dom->getElementsByTagName('payui-transaction-receipt');
    foreach ($nodes as $node) {
        $dataAttr = $node->getAttribute('data');
        $data = json_decode(html_entity_decode($dataAttr), true);

        if (isset($data['identifierEntities'])) {
            foreach ($data['identifierEntities'] as $entity) {
                if ($entity['identifierKey'] === 'UPI transaction ID') {
                    return $entity['identifierValues'][0]['ctaTitle'] ?? null;
                }
            }
        }
    }

    return null;
}
?>
