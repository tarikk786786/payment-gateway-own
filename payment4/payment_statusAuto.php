<?php
// Bharatpe Status Page...
// Define the base directory constant
define('ROOT_DIR', realpath(dirname(__FILE__)) . '/../');
// Securely include files using the ROOT_DIR constant
include ROOT_DIR . 'pages/dbInfo.php';
include ROOT_DIR . 'pages/dbFunctions.php';
include ROOT_DIR . 'auth/config.php';
try {
    // Create a PDO database connection
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USERNAME, DB_PASSWORD);
    // Set PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Set character encoding
    $pdo->exec("set names utf8mb4");
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

function bharatpe_trans($pdo, $merchantId, $token, $cookie) {
    $fromDate = date('Y-m-d');
    $toDate = date('Y-m-d');

    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://payments-tesseract.bharatpe.in/api/v1/merchant/transactions?module=PAYMENT_QR&merchantId=' . $merchantId . '&sDate=' . $fromDate . '&eDate=' . $toDate,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 120,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
            'token: ' . $token,
            'user-agent: Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/112.0.0.0 Mobile Safari/537.36',
            'Cookie: ' . $cookie
        ),
    ));

    $response = curl_exec($curl);
    curl_close($curl);

    $decodedResponse = json_decode($response, true);
    if (is_array($decodedResponse) && isset($decodedResponse['status']) && $decodedResponse['status']) {
        return $decodedResponse['data']['transactions'];
    } else {
        return $response;
    }
}

function sanitize_input($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Invalid request method. Only POST requests are accepted.']);
    exit;
}

// Validate order_id - it should be set, not empty, exactly 12 digits
    if (isset($_POST['order_id']) && !empty($_POST['order_id'])) {
        $order_id = sanitize_input($_POST['order_id']);
    } else {
        echo json_encode(['status' => 'pending', 'redirect_url' => $row['redirect_url'], 'error' => 'order_id is missing']);
        exit;
    }

    // Validate user_token - it should be set, not empty, and alphanumeric
    if (isset($_POST['user_token']) && !empty($_POST['user_token'])) {
        $user_token = sanitize_input($_POST['user_token']);

    } else {
        echo json_encode(['status' => 'pending', 'redirect_url' => $row['redirect_url'], 'error' => 'user_token is Missing Pls contact support']);
        exit;
    }
    


$query = "SELECT byteTransactionId, redirect_url, create_date, status, amount, merchentMobile FROM orders WHERE order_id = :order_id AND user_token = :user_token";
$stmt = $pdo->prepare($query);
$stmt->execute(['order_id' => $order_id, 'user_token' => $user_token]);

$row = $stmt->fetch(PDO::FETCH_ASSOC);
// Assume $row['create_date'] is a valid datetime string
$order_time = $row['create_date'];
$merchentMobile = $row['merchentMobile'];
// Convert order time to timestamp (in seconds)
$timestamp = strtotime($order_time);

// Convert to milliseconds (optional, only if needed for other parts of your code)
$order_timestamp = $timestamp * 1000;

// Add 2 minutes (120 seconds = 120000 milliseconds) to get expiry timestamp
$expiry_timestamp = $order_timestamp + (2 * 60 * 1000);

// Get current timestamp in milliseconds (to match $expiry_timestamp)
$current_timestamp = time() * 1000;

// Calculate difference in milliseconds
$checktill_ms = $expiry_timestamp - $current_timestamp;
// Convert difference to seconds
$checktill = $checktill_ms / 1000;

if ($checktill<0) {
    echo json_encode(['status' => 'AutoCheckTimeout', 'massage' => 'Sumit UTR MANUALLY.']);
    // echo 'AutoCheckTimeout';
    exit;
}

// echo $order_timestamp."-";
$row_count = $stmt->rowCount(); // Use rowCount() instead of fetchColumn()

if ($row_count == 0) {
    echo json_encode(['status' => 'invalid', 'error' => 'Invalid Order Not Found.']);
    exit;
}

if ($row['status'] == 'SUCCESS') {
    echo json_encode(['status' => 'success', 'massage' => 'Tranjection alrady success in detabace']);
    // echo 'success';
    exit;
}

if ($row['status'] == 'PENDING') {

    $merchentMobile = $row['merchentMobile'];
    $tokenQuery = "SELECT token, cookie, merchantId FROM bharatpe_tokens WHERE user_token = :user_token AND phoneNumber= :phoneNumber";
    $tokenStmt = $pdo->prepare($tokenQuery);
    $tokenStmt->execute(['user_token' => $user_token, 'phoneNumber' => $merchentMobile]);
    $tokenRow = $tokenStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($tokenRow) {
        $transactions = bharatpe_trans($pdo, $tokenRow['merchantId'], $tokenRow['token'], $tokenRow['cookie']);
        // print_r($transactions);
        
        if (is_array($transactions)) {
            
            $matched_transaction = null;
            foreach ($transactions as $transaction) {
            $tranTimestamp = $transaction['paymentTimestamp']; // "22-07-2025 18:39:35"
            $transAmount = (int)$transaction['amount']; // result: 1
                
                // echo $tranTimestamp."_".$order_timestamp.".";
                if ($row['amount'] == $transAmount && $tranTimestamp > $order_timestamp && $tranTimestamp < $expiry_timestamp
                ){
                $utr = $transaction['bankReferenceNo'];
                $checkdutr = $pdo->prepare("SELECT id FROM orders WHERE utr = :utr AND status = 'SUCCESS'");
                $checkdutr->execute(['utr' => $utr]);
                $row_count =$checkdutr->fetchColumn();
                
                if ($row_count > 0) {
                    echo json_encode(['status' => 'invalid', 'error' => 'Duplicate UTR No Enter the new UTR.']);
                    exit;
                }else
                    $matched_transaction = $transaction;
                    break;
                }
            }

            if ($matched_transaction) {
       
                //amtch utr
if (isset($matched_transaction["amount"]) && isset($row["amount"]) && $matched_transaction["amount"] == $row["amount"] && isset($matched_transaction['status']) && $matched_transaction['status'] == "SUCCESS") {

    try {
        // Start a transaction for atomicity
        $pdo->beginTransaction();

        // Fetching user_id based on user_token
        // This query seems redundant if $user_id is already available from $row or another source.
        // If it's truly needed, keep it. Otherwise, consider if you can pass $user_id directly.
        $fetchUserIdQuery = "SELECT id FROM users WHERE user_token = :user_token";
        $fetchUserIdStmt = $pdo->prepare($fetchUserIdQuery);
        $fetchUserIdStmt->execute(['user_token' => $user_token]);
        $userRow = $fetchUserIdStmt->fetch(PDO::FETCH_ASSOC);

        if (!$userRow) {
            // Handle case where user is not found, rollback and exit
            $pdo->rollBack();
            echo json_encode(['status' => 'error', 'message' => 'User not found.']);
            exit;
        }

        $Payer = $matched_transaction["payerName"] ?? 'N/A'; // Use null coalescing for safety
        $megabyteuserid = $userRow['id'];

        // Update failCount in bharatpe_tokens
        $updateQueryu = "UPDATE bharatpe_tokens SET failCount = 0 WHERE phoneNumber = :phoneNumber AND user_token = :user_token";
        $updateStmtu = $pdo->prepare($updateQueryu);
        $updateStmtu->execute([
            'phoneNumber' => $merchentMobile,
            'user_token' => $user_token
        ]);

        // Update orders table
        $updateOrderQuery = "UPDATE orders SET status = 'SUCCESS', utr = :utr, payerUpi = :payerUpi WHERE order_id = :order_id AND user_id = :user_id";
        $updateOrderStmt = $pdo->prepare($updateOrderQuery);
        $updateOrderStmt->execute([
            'utr' => $utr, // Assuming $utr is defined elsewhere
            'payerUpi' => $Payer,
            'order_id' => $order_id,
            'user_id' => $megabyteuserid
        ]);

        // Fetch order details for callback/response
        $fetchOrderDetailsQuery = "SELECT remark1, remark2, order_id, redirect_url FROM orders WHERE order_id = :order_id AND user_id = :user_id";
        $fetchOrderDetailsStmt = $pdo->prepare($fetchOrderDetailsQuery);
        $fetchOrderDetailsStmt->execute(['order_id' => $order_id, 'user_id' => $megabyteuserid]);
        $orderRow = $fetchOrderDetailsStmt->fetch(PDO::FETCH_ASSOC);

        if (!$orderRow) {
            $pdo->rollBack();
            echo json_encode(['status' => 'error', 'message' => 'Order details not found after update.'.$order_id."-".$megabyteuserid]);
            exit;
        }

        // Insert into reports table
        // Ensure consistent data types for placeholders. Assuming all are strings except amount and user_id.
        $reportTransactionId = rand(1111111111, 9999999999);
        $insertReportQuery = "INSERT INTO reports (transactionId, status, order_id, vpa, paymentApp, amount, user_token, UTR, description, user_id) VALUES (:transactionId, 'SUCCESS', :order_id, :vpa, :paymentApp, :amount, :user_token, :utr, :description, :user_id)";
        $insertReportStmt = $pdo->prepare($insertReportQuery);
        $insertReportStmt->execute([
            'transactionId' => $reportTransactionId,
            'order_id' => $orderRow['order_id'],
            'vpa' => 'test@PAYNEARBY', // Hardcoded VPA, consider making this dynamic if possible
            'paymentApp' => $matched_transaction['payerHandle'] ?? 'Unknown App', // Use null coalescing
            'amount' => $matched_transaction['amount'], // Use amount from matched_transaction for consistency
            'user_token' => $user_token,
            'utr' => $utr,
            'description' => rand(1111111111, 9999999999), // This seems to be a random number for description, check if intended
            'user_id' => $megabyteuserid
        ]);

        // Commit the transaction
        $pdo->commit();

        // Call the external callback function
        // Ensure sendCallback function is properly defined and handles errors.
        $amount = $row["amount"]; // Using amount from $row, ensure consistency if $matched_transaction['amount'] is different.
        sendCallback("SUCCESS", $utr, $amount, $order_id, $megabyteuserid);

        echo json_encode(['status' => 'success', 'massage' => 'Transaction Successfully Processed.']);
        exit;

    } catch (PDOException $e) {
        // Rollback on any database error
        $pdo->rollBack();
        error_log("Database error during transaction processing: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'massage' => 'An internal error occurred. Please try again later.']);
        exit;
    } catch (Exception $e) {
        // Catch any other general exceptions
        $pdo->rollBack();
        error_log("General error during transaction processing: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'massage' => 'An unexpected error occurred.']);
        exit;
    }

} else {
    // This block handles cases where the initial conditions are not met
    echo json_encode(['status' => 'invalid', 'error' => 'Illegal Activity Detected: Amount Not Matched or Transaction Not Successful. This amount is not Refundable.']);
    exit;
}
}else {   
    //wrong utr
    //  echo $user_token;
     echo json_encode(['status' => 'pending', 'massage' => 'Transaction not found in reports.']);
    //  echo "pending";
        exit;
            }
        } else {
        echo json_encode(['status' => 'pending', 'error' => 'Error fetching transactions.']);
        exit;
        }
    } else {  
        //user token error
        echo json_encode(['status' => 'pending', 'error' => 'Token information not found.']);
        exit;
    }
} else {
    echo json_encode(['status' => 'pending', 'redirect_url' => $row['redirect_url'], 'error' => 'Unhandled transaction status.']);
    exit;
    }

// [Add any additional code or functions here]

?>