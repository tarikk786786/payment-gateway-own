<?php
// quintuspay Status Page

// Define the base directory constant for secure file inclusion.
define('ROOT_DIR', realpath(dirname(__FILE__)) . '/../');

// Include necessary functions and configurations.
// Using 'require_once' to prevent multiple inclusions and fatal errors if files are missing.
require_once ROOT_DIR . 'pages/dbFunctions.php'; // Assuming this sets up the $conn object
require_once ROOT_DIR . 'auth/config.php';       // Assuming this contains DB connection details
require_once ROOT_DIR . 'pages/dbInfo.php';       // Assuming this contains other DB related info
    if (!function_exists(sendCallback)) {
    return ['status' => 'error', 'message' => 'callback function not exist'];  
    }
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

function quintuspay_trans(string $bearerToken): array
{
    // Use the provided dates or default to today if not specified
    $fromDate = $fromDate ?? date('Y-m-d');
    $toDate = $toDate ?? date('Y-m-d');

    // API Endpoint URL
    $url = "https://bapa-api.quintustech.in/api/qt/transaction/getList";

    // Build the payload with the correct date format
    $payload = json_encode([
        "endDate" => $toDate . 'T23:59:59.999Z',
        "referenceNo" => "",
        "selectedAccountNumber" => "",
        "selectedService" => [],
        "selectedStatus" => [],
        "selectedTransactionId" => "",
        "selectedType" => [],
        "startDate" => $fromDate . 'T00:00:00.000Z',
        "transactionType" => ["UPI_RESOLUTION"]
    ]);

    // Headers required by the API
    $headers = [
        "accept: application/json",
        "authorization: Bearer " . $bearerToken,
        "content-type: application/json",
        "content-length: " . strlen($payload), // It's good practice to set this
    ];

    // Initialize cURL session
    $ch = curl_init();

    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return the response as a string
    curl_setopt($ch, CURLOPT_ENCODING, ""); // Handle all encodings, including gzip

    // Execute the cURL request
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    // Check for cURL errors
    if (curl_errno($ch)) {
        $error_msg = curl_error($ch);
        curl_close($ch);
        return ['status' => 'error', 'message' => 'cURL error: ' . $error_msg];
    }

    // Close the cURL session
    curl_close($ch);

    // Decode the JSON response
    $decodedResponse = json_decode($response, true);

    // Handle the HTTP response code
    if ($httpCode >= 200 && $httpCode < 300) {
        return $decodedResponse;
    } else {
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

// Validate order_id - it should be set, not empty, exactly 12 digits
    if (isset($_POST['order_id']) && !empty($_POST['order_id'])) {
        $order_id = sanitize_input($_POST['order_id']);
    } else {
        echo json_encode(['status' => 'pending',  'error' => 'order_id is missing']);
        exit;
    }

    // Validate user_token - it should be set, not empty, and alphanumeric
    if (isset($_POST['user_token']) && !empty($_POST['user_token'])) {
        $user_token = sanitize_input($_POST['user_token']);

    } else {
        echo json_encode(['status' => 'pending',  'error' => 'user_token is Missing Pls contact support']);
        exit;
    }
  
$query = "SELECT create_date, status,description, amount, merchentMobile FROM orders WHERE order_id = :order_id AND user_token = :user_token";
$stmt = $pdo->prepare($query);
$stmt->execute(['order_id' => $order_id, 'user_token' => $user_token]);

$row = $stmt->fetch(PDO::FETCH_ASSOC);
// Assume $row['create_date'] is a valid datetime string
$order_time = $row['create_date'];
$merchentMobile = $row['merchentMobile'];
$description = $row['description'];
$amount = $row['amount'];

// echo $order_timestamp."-";
$row_count = $stmt->rowCount(); // Use rowCount() instead of fetchColumn()

if ($row_count == 0) {
    echo json_encode(['status' => 'invalid', 'error' => 'Invalid Order Not Found.']);
    exit;
}

if ($row['status'] == 'SUCCESS') {
    echo json_encode(['status' => 'success', 'massage' => 'Order Alradey Success In orders']);
    // echo 'success';
    exit;
}

if ($row['status'] == 'PENDING'||$row['status'] == 'FAILURE') {
    // $user_token = $row['user_token'];
    $tokenQuery = "SELECT token FROM quintuspay_token WHERE user_token = :user_token AND phoneNumber= :phoneNumber";
    $tokenStmt = $pdo->prepare($tokenQuery);
    $tokenStmt->execute(['user_token' => $user_token, 'phoneNumber'=>$merchentMobile]);
    $tokenRow = $tokenStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($tokenRow) {
        $token  =$tokenRow['token'];
        $transactions = quintuspay_trans($token);

if (!$transactions['success'] && isset($transactions['message'])
) {
    $errorMessage = $transactions['message'];
if ($errorMessage === "token_expired") {
    // echo($errorMessage);       
    $updateQuery = "UPDATE quintuspay_token 
                    SET status = :status 
                    WHERE phoneNumber = :phoneNumber AND user_token = :user_token";

    $updateStmt = $pdo->prepare($updateQuery);

    if ($updateStmt) {
        $executed = $updateStmt->execute([
            'status' => 'Deactive',
            'phoneNumber' => $merchentMobile,
            'user_token' => $user_token
        ]);

        if ($executed) {
            // Count remaining active tokens
            $countQuery = "SELECT COUNT(*) FROM quintuspay_token 
                           WHERE status = 'Active' AND user_token = :user_token";
            $countStmt = $pdo->prepare($countQuery);
            $countStmt->execute(['user_token' => $user_token]);
            $mcount = $countStmt->fetchColumn();

            if ($mcount == 0) {
                // Update 'paynearby_connected' status in users table
                $updateUserQuery = "UPDATE users 
                                    SET paynearby_connected = :connected 
                                    WHERE user_token = :user_token";
                $updateUserStmt = $pdo->prepare($updateUserQuery);
                $updateUserStmt->execute([
                    'connected' => 'No',
                    'user_token' => $user_token
                ]);
            }

            echo "SeasonExpire";
            exit;
        } else {
            echo "❌ Execution failed. Error info:<br><pre>";
            print_r($updateStmt->errorInfo());
            echo "</pre>";
        }
    } else {
        echo "❌ Prepare failed. PDO error info:<br><pre>";
        print_r($pdo->errorInfo());
        echo "</pre>";
    }
}
}else{
    $transactions = $transactions['data'];
}
        
        if (is_array($transactions)) {
            
            $matched_transaction = null;
            foreach ($transactions as $transaction) {
            $transAmount = (int)$transaction['amount']; // result: 1
            $merchantRequestId = $transaction['description']['merchantRequestId'];
                // echo $tranTimestamp."_".$order_timestamp.".";
                if ($amount == $transAmount && $merchantRequestId == $description){
                $utr = $transaction['referenceNo'];
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
if($matched_transaction["amount"] == $amount && $matched_transaction['status']=="SUCCESS"){
    // Fetching user_id (assuming it's the "id" column) based on user_token
    $fetchUserIdQuery = "SELECT id FROM users WHERE user_token = :user_token";
    $fetchUserIdStmt = $pdo->prepare($fetchUserIdQuery);
    $fetchUserIdStmt->execute(['user_token' => $user_token]);
    $userRow = $fetchUserIdStmt->fetch(PDO::FETCH_ASSOC);
    $Payer = $matched_transaction['description']['payerName'];
    $megabyteuserid = $userRow['id']; 

$updateQueryu = "UPDATE quintuspay_token SET failCount = 0 WHERE phoneNumber = :phoneNumber AND user_token = :user_token";
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
    $updateStmt->execute(['utr' => $utr, 'payerUpi'=> $Payer ,'order_id' => $order_id , 'user_id'=>$megabyteuserid ]);


    echo json_encode(['status' => 'success', 'massage' => 'Transaction Succefully prossed']);
    if (function_exists(sendCallback)) {
       sendCallback("SUCCESS", $utr, $amount, $order_id, $megabyteuserid);
    }
    
    // echo($callback);
    $updateQueryu = "UPDATE quintuspay_token SET failCount = 0 WHERE phoneNumber = :phoneNumber AND user_token = :user_token";
    $updateStmtu = $pdo->prepare($updateQueryu);
    $updateStmtu->execute([
        'phoneNumber' => $merchentMobile,
        'user_token' => $user_token
    ]);
    
    

    exit;
    
}else{
        echo json_encode(['status' => 'invalid',  'error' => 'Illigal Activity Detected Amount Not Matched ! this amount is not Refundable.']);
        exit;
            }
}

 else {   
    //wrong utr
    //  echo $user_token;
        echo json_encode(['status' => 'pending',  'massage' => 'Transaction not found.'.$megabyteuserid]);
        exit;

            }
        } else {
           
        echo json_encode(['status' => 'pending',  'error' => 'Error fetching transactions.']);
        exit;

        }
    } else {  
        //user token error
        
        echo json_encode(['status' => 'pending',  'error' => 'Token information not found.']);
        exit;
       
    }
} else {
    //status error in table orders
    
        echo json_encode(['status' => 'pending',  'error' => 'Unhandled transaction status.']);
        exit;

}

// [Add any additional code or functions here]

?>