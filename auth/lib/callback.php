<?php
session_start();
require_once('../config.php');
require_once('config.php');
require_once('../function.php');

$orderid=$_COOKIE['order_id_cookie'];
// API endpoint URL
$url = "https://" . $_SERVER["SERVER_NAME"] . "/api/check-order-status";


// POST data
$postData = array(
    "user_token" => $token,
    "order_id" => $orderid
);

// Initialize cURL session
$ch = curl_init($url);

// Set cURL options
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));

// Execute cURL session and get the response
$response = curl_exec($ch);

// Check for cURL errors
if (curl_errno($ch)) {
    echo "cURL Error: " . curl_error($ch);
    exit;
}

// Close cURL session
curl_close($ch);

// Decode the JSON response
$responseData = json_decode($response, true);

// Check if the API call was successful
if ($responseData["status"] == true) {
    // API call was successful
    // Access the response data as needed
    $txnStatus = $responseData["result"]["txnStatus"];
    $orderId = $responseData["result"]["orderId"];
    $status = $responseData["result"]["status"];
    $amount = $responseData["result"]["amount"];
    $date = $responseData["result"]["date"];
    $utr = $responseData["result"]["utr"];
    $mobile = $responseData["result"]["customer_mobile"];
    $remark1 = $responseData["result"]["remark1"];
    $remark2 = $responseData["result"]["remark2"];
    $username = $_SESSION['user_id'];
    // fetch userdata            
    $user = "SELECT * FROM users WHERE mobile = '$mobile'";
    $uu = mysqli_query($conn, $user);
    $userdata = mysqli_fetch_array($uu);
    
if ($remark1 == "addbalance" && $txnStatus == "SUCCESS") {
    // Check if UTR already exists
    $sql_rprotect = "SELECT COUNT(*) as count FROM wallet_transactions WHERE utr = ?";
    $stmt = $conn->prepare($sql_rprotect);
    $stmt->bind_param("s", $utr); // Use `s` as UTR is VARCHAR
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    if ($row['count'] == 0) {
        // If UTR does not exist, call credit_balance
        $credit= credit_balance($username, $amount, $utr, "ONLINE ADD");
        
        // echo("Payment Status:".$credit);
    }else {
        echo("Duplicate Entry");
    }
    // Redirect after processing
    // exit('Balance Added successfully To UpiGateway Wallet');
    echo("<script>closeLinkDialog()</script>");
    exit('Balance Added successfully To UpiGateway Wallet');
} elseif ($remark1 == "addbalance" && $txnStatus == "FAILURE") {
    // header("Location: https://" . $_SERVER["SERVER_NAME"] . "/auth/dashboard");
    exit('Transation Failed');
    echo("<script>closeLinkDialog()</script>");
}elseif($txnStatus == "SUCCESS"){
  
$email = $_SESSION['username'];

// Step 1: Store cookie values in variables
$planid = $_COOKIE['planid_cookie'] ?? null;
$isVIP  = $_COOKIE['isVip_cookie'] ?? null;

// Step 2: Unset cookies by setting their expiration in the past
setcookie('planid_cookie', '', time() - 360, '/');
setcookie('isVip_cookie', '', time() - 360, '/');
setcookie('order_id_cookie', '', time() - 360, '/');

// Set the monthsToAdd based on planid
$esql = "SELECT `expiry` FROM `subscription_plan` WHERE id = $planid";
$qesql = mysqli_query($conn, $esql);

$row = mysqli_fetch_array($qesql);
$monthsToAdd = $row['expiry'];

// Check for duplicate transaction
$protectq = "SELECT COUNT(*) FROM planorders WHERE order_id = '$orderid' AND status = 'SUCCESS'";
$sqprotect = mysqli_query($conn, $protectq);
$row = mysqli_fetch_array($sqprotect);

if ($row[0] == 0) {
    // Get the user's current expiry date and plan
    $user_sql = "SELECT expiry, planId FROM users WHERE mobile = '$email'";
    $user_query = mysqli_query($conn, $user_sql);
    $user_row = mysqli_fetch_array($user_query);
    $current_expiry = $user_row['expiry'];
    $current_plan = $user_row['planId'];

    // Determine the base date for calculating new expiry
    $current_date = date('Y-m-d H:i:s');

    // Check if the new plan is the same as the current plan
    if ($current_plan == $planid && strtotime($current_expiry) > strtotime($current_date)) {
        // If plan is the same and a valid future expiry exists, extend from that date
        $newExpiryDate = date('Y-m-d H:i:s', strtotime($current_expiry . " +$monthsToAdd months"));
    } else {
        // If plans are different or the current expiry is in the past, start from today
        $newExpiryDate = date('Y-m-d H:i:s', strtotime("+$monthsToAdd months"));
    }

    // Update planorders table
    $sqll = "UPDATE planorders SET status = 'SUCCESS', utr = '$utr', expiry_date = '$newExpiryDate' WHERE order_id = '$orderid'";
    $sqrr = mysqli_query($conn, $sqll);

    if ($sqrr) {
        // Handle plan renewal debit
        if ($remark2 == "yes") {
            $balance = $userdata['balance'];
            $userid = $userdata['id'];
            $debit = debit_balance($userid, $balance, $utr, "Plan Renew");
        }

        // Update users table with new expiry date
        if ($isVIP == "yes") {
            $sql = "UPDATE users SET expiry = '$newExpiryDate', planId = $planid, tranjection_Count = 0, vip_expiry = '$newExpiryDate' WHERE mobile = '$email'";
        } else {
            $sql = "UPDATE users SET expiry = '$newExpiryDate', planId = $planid, tranjection_Count = 0 WHERE mobile = '$email'";
        }
        
        
        $rrrr = mysqli_query($conn, $sql);

        if ($rrrr) {
            // header("Location: https://" . $_SERVER["SERVER_NAME"] . "/auth/dashboard");
            exit("Plan Succefully renewd <br> New Expiry : $newExpiryDate , Thanks...");
        } else {
            echo "SQL Error: " . mysqli_error($conn);
            exit('Oops !! Something Went Wrong... ');
            exit;
        }
    } else {
        echo "Error updating order: " . mysqli_error($conn);
    }
} else {
    echo "Duplicate Transaction";
}



}elseif($txnStatus == "FAILURE"){
    $errorMessage = $responseData["message"];
    // echo "API Error: $errorMessage";
    $sqll = "UPDATE planorders SET status = 'FAILURE', utr = '' WHERE order_id = '$orderid';";
$sqrr = mysqli_query($conn, $sqll);
    exit("Tranjection Failed : $errorMessage , Please Try Again...");
}
 
} else {
    // API call failed
    $errorMessage = $responseData["message"];
    echo "API Error: $errorMessage";
}