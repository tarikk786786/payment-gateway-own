<?php
// PAnearby Status Page

// Define the base directory constant
define('ROOT_DIR', realpath(dirname(__FILE__)) . '/../');

// Securely include files using the ROOT_DIR constant

include ROOT_DIR . 'pages/dbInfo.php';



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

function paynearby_trans(int $agentRefId,string $bearerToken, string $deviceId): array {
    
    // Set default dates to today if not provided
    $fromDate = date('Y-m-d'); // Current date in YYYY-MM-DD format
    $toDate = date('Y-m-d'); // Current date in YYYY-MM-DD format
    $upiApiKey ="29143dcad2b014512f6db9b84be0b9aa6e6b8deb";    
    $url = "https://bankingdc-services.paynearby.in/upi/upi-qr-reports/retailer/report";
    $payload = json_encode([
        "from_date" => $fromDate,
        "to_date" => $toDate,
        "search_option" => "0",
        "search_value" => "",
        "agent_ref_id" => $agentRefId,
        "account_type" => "0",
        "service_id" => "26",
        "row_start_count" => 0,
        "row_end_count" => 10
    ]);

    $headers = [
        "accept: application/json, text/plain, */*",
        "accept-encoding: gzip, deflate, br, zstd",
        "accept-language: en-US,en;q=0.9",
        "authorization: Bearer " . $bearerToken,
        "connection: keep-alive",
        "content-length: " . strlen($payload), // Dynamically set content-length
        "content-type: application/json",
        "deviceid: " . $deviceId,
        "host: bankingdc-services.paynearby.in",
        "origin: https://retailerportal.paynearby.in",
        "platform: nbt-agent-angular",
        "referer: https://retailerportal.paynearby.in/",
        "sec-ch-ua: \"Not)A;Brand\";v=\"8\", \"Chromium\";v=\"138\", \"Google Chrome\";v=\"138\"",
        "sec-ch-ua-mobile: ?0",
        "sec-ch-ua-platform: \"Windows\"",
        "sec-fetch-dest: empty",
        "sec-fetch-mode: cors",
        "sec-fetch-site: same-site",
        "upiapikey: " . $upiApiKey,
        "user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36",
        "versionid: 1.0.0"
    ];

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return the response as a string
    curl_setopt($ch, CURLOPT_ENCODING, ""); // Handle all encodings, including gzip

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if (curl_errno($ch)) {
        $error_msg = curl_error($ch);
        curl_close($ch);
        // It's good practice to log or handle this error appropriately
        // For demonstration, we'll return an error array
        return ['status' => 'error', 'message' => 'cURL error: ' . $error_msg];
    }

    curl_close($ch);

    $decodedResponse = json_decode($response, true);

    if ($httpCode >= 200 && $httpCode < 300) {
        return $decodedResponse; // Return the decoded data if successful
    } else {
        // Handle API errors (e.g., non-2xx status codes)
        return ['status' => 'error', 'http_code' => $httpCode, 'response' => $decodedResponse];
    }
}

function sanitize_input($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Invalid request method. Only POST requests are accepted.']);
    exit;
}

// Validate UTR - it should be set, not empty, exactly 12 digits
    if (isset($_POST['utr']) && !empty($_POST['utr'])) {
        $utr = sanitize_input($_POST['utr']);
        if (!ctype_digit($utr) || strlen($utr) != 12) {
            echo json_encode(['status' => 'pending', 'redirect_url' => $row['redirect_url'], 'error' => 'Invalid UTR. UTR must be exactly 12 digits.']);
            exit;
        }
    } else {
        echo json_encode(['status' => 'pending', 'redirect_url' => $row['redirect_url'], 'error' => 'UTR is missing']);
        exit;
    }

    // Validate TransactionId - it should be set, not empty, and alphanumeric
    if (isset($_POST['TransactionId']) && !empty($_POST['TransactionId'])) {
        $transactionId = sanitize_input($_POST['TransactionId']);
        if (!ctype_alnum($transactionId)) {
            echo "Error: Invalid TransactionId. It must be alphanumeric.";
            exit;
        }
    } else {
        echo json_encode(['status' => 'pending', 'redirect_url' => $row['redirect_url'], 'error' => 'Transaction is Missing Pls contact support']);
        exit;
    }

$query = "SELECT byteTransactionId, redirect_url, status, user_token,merchentMobile,order_id, amount FROM orders WHERE byteTransactionId = :transactionId";
$stmt = $pdo->prepare($query);
$stmt->execute(['transactionId' => $transactionId]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

$checkdutr = $pdo->prepare("SELECT id FROM orders WHERE utr = :utr");
$checkdutr->execute(['utr' => $utr]);
$row_count =$checkdutr->fetchColumn();

if ($row_count > 0) {
    echo json_encode(['status' => 'invalid', 'error' => 'Duplicate UTR No Enter the new UTR.']);
    exit;
}

if ($row['status'] == 'SUCCESS') {
    echo json_encode(['status' => 'success', 'redirect_url' => $row['redirect_url']]);
    exit;
}

if ($row['status'] == 'PENDING') {
    $user_token = $row['user_token'];
    $tokenQuery = "SELECT agent_ref_id, authorization, deviceid FROM paynearby_token WHERE user_token = :user_token AND phoneNumber= :phoneNumber";
    $tokenStmt = $pdo->prepare($tokenQuery);
    $tokenStmt->execute(['user_token' => $user_token]);
    $tokenRow = $tokenStmt->fetch(PDO::FETCH_ASSOC);
    $merchentMobile = $row['merchentMobile'];
    $order_id = $row['order_id'];
    if ($tokenRow) {
        $agent_ref_id  =$tokenRow['agent_ref_id'];
        $authorization =$tokenRow['authorization'];
        $deviceid =$tokenRow['deviceid'];
        $transactions = paynearby_trans($agent_ref_id, "$authorization", "$deviceid");

        if (is_array($transactions)) {
            $bank_reference_no = sanitize_input($utr);
            if (!preg_match('/^\d{12}$/', $bank_reference_no)) {
                echo json_encode(['error' => 'Invalid UTR format.']);
                exit;
            }

            $matched_transaction = null;
            foreach ($transactions as $transaction) {
                if ($transaction['bank_reference_no'] == $bank_reference_no) {
                    $matched_transaction = $transaction;
                    break;
                }
            }

            if ($matched_transaction) {
                
                //amtch utr
if($matched_transaction["amount"] == $row["amount"]){
    
    // Fetching user_id (assuming it's the "id" column) based on user_token
    $fetchUserIdQuery = "SELECT id FROM users WHERE user_token = :user_token";
    $fetchUserIdStmt = $pdo->prepare($fetchUserIdQuery);
    $fetchUserIdStmt->execute(['user_token' => $user_token]);
    $userRow = $fetchUserIdStmt->fetch(PDO::FETCH_ASSOC);
    $Payer = $matched_transaction["customer_name"];
    $megabyteuserid = $userRow['id'];       
    
$updateQueryu = "UPDATE paynearby_token SET failCount = 0 WHERE phoneNumber = :phoneNumber AND user_token = :user_token";
$updateStmtu = $pdo->prepare($updateQueryu);

if ($updateStmtu) {
    $executed = $updateStmtu->execute(['phoneNumber' => $merchentMobile, 'user_token' => $user_token ]);

    if ($executed) {
        // echo "Update successful for phone number: " . $merchentMobile;
    } else {
        echo "Execution failed. Error info: ";
        print_r($updateStmtu->errorInfo());
    }
} else {
    echo "Prepare failed. Error info: ";
    print_r($pdo->errorInfo());
}
    $updateQuery = "UPDATE orders SET status = 'SUCCESS', utr = :utr, payerUpi = :payerUpi WHERE order_id = :order_id AND user_id = :user_id";
    $updateStmt = $pdo->prepare($updateQuery);
    $updateStmt->execute(['utr' => $matched_transaction['bank_reference_no'], 'payerUpi'=> $Payer ,'order_id' => $order_id , 'user_id'=>$megabyteuserid ]);

    $fetchQuery = "SELECT remark1, remark2, order_id, redirect_url FROM orders WHERE byteTransactionId = :transactionId";
    $fetchStmt = $pdo->prepare($fetchQuery);
    $fetchStmt->execute(['transactionId' => $transactionId]);
    $orderRow = $fetchStmt->fetch(PDO::FETCH_ASSOC);

    // Fetching callback_url from users table
    $callbackQuery = "SELECT callback_url FROM users WHERE user_token = :user_token";
    $callbackStmt = $pdo->prepare($callbackQuery);
    $callbackStmt->execute(['user_token' => $user_token]);
    $userRow = $callbackStmt->fetch(PDO::FETCH_ASSOC);

    $reportTransactionId = rand(1111111111, 9999999999);
    $insertQuery = "INSERT INTO reports (transactionId, status, order_id, vpa, paymentApp, amount, user_token, UTR, description, user_id) VALUES (?, 'SUCCESS', ?, 'test@bharatpe', ?, ?, ?, ?, ?, ?)";
    $insertStmt = $pdo->prepare($insertQuery);
    $insertStmt->execute([$reportTransactionId, $orderRow['order_id'], $matched_transaction['payerHandle'], $matched_transaction['amount'], $user_token, $matched_transaction['bank_reference_no'], rand(1111111111, 9999999999), $megabyteuserid]);

    $callback_url = $userRow['callback_url'];
    $url0 = $callback_url . "?status=SUCCESS&utr=" . urlencode($matched_transaction['bankReferenceNo']) . "&order_id=" . urlencode($orderRow['order_id']);
    file_get_contents($url0);
    echo json_encode(['status' => 'success', 'redirect_url' => $orderRow['redirect_url']]);
    exit;
    
}else{
            echo json_encode(['status' => 'invalid', 'redirect_url' => $row['redirect_url'], 'error' => 'Illigal Activity Detected Amount Not Matched ! this amount is not Refundable.']);
               exit;
            }
}

 else {   
     
     //wrong utr
     
     echo json_encode(['status' => 'invalid', 'redirect_url' => $row['redirect_url'], 'error' => 'Transaction not found.']);
               exit;

                
            }
        } else {
            
            echo json_encode(['status' => 'pending', 'redirect_url' => $row['redirect_url'], 'error' => 'Error fetching transactions.']);
exit;


        }
    } else {  
        
        //user token error
        
         echo json_encode(['status' => 'pending', 'redirect_url' => $row['redirect_url'], 'error' => 'Token information not found.']);
exit;
        
       
    }
} else {
    
    //status error in table orders
    
    echo json_encode(['status' => 'pending', 'redirect_url' => $row['redirect_url'], 'error' => 'Unhandled transaction status.']);
exit;

    
}

// [Add any additional code or functions here]

?>


<?php

// status.php

/*
$utr = $_POST['utr'];
$transactionId = $_POST['TransactionId'] ;

// Validate and process the UTR and TransactionId
// This is just an example. You should implement your own logic here.

if ($utr === "1234567890") {
    // Example of a successful response
    echo json_encode(['status' => 'success', 'redirect_url' => 'https://codesify.in']);
} else {
    // Example of a pending response
    echo json_encode(['status' => 'pending', 'redirect_url' => 'https://redirectforpending.com']);
}
?>
*/
