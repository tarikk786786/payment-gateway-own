<?php
 global $conn;
 
 // Function to sanitize user input
 include "config.php";
 include "../config.php";
 include "../function.php";
 session_start();
 $mobile = $_SESSION["username"];
 $user = "SELECT * FROM users WHERE mobile = '$mobile'";
 $uu = mysqli_query($conn, $user);
 $userdata = mysqli_fetch_array($uu);

 if (isset($_POST["amount"])) {
     $amount = $_POST["amount"];
     $payfor = $_POST["for"];
     $order_id = "TEZ" . time() . rand(11111, 99999);
     $url = "https://" . $_SERVER["SERVER_NAME"] . "/api/create-order";
     $remark2 = " ";

     setcookie("order_id_cookie", $order_id, time() + 360, "/"); // Expires in 6 minuts (adjust the expiration time as needed)
     
     if ($_POST["for"] == "addbalance") {
         $userid = $_SESSION["user_id"];

         // URL of the PHP page
         $callbackurl =
             "https://" . $_SERVER["SERVER_NAME"] . "/auth/lib/callback";

         $email = $_SESSION["username"];

         // Data to be sent in the POST request
         $data = [
             "customer_mobile" => $email,
             "user_token" => $token,
             "amount" => $amount,
             "order_id" => $order_id,
             "redirect_url" => $callbackurl,
             "remark1" => $payfor,
             "remark2" => $payfor,
         ];

         // Initialize cURL session
         $ch = curl_init();

         // Set cURL options
         curl_setopt($ch, CURLOPT_URL, $url);
         curl_setopt($ch, CURLOPT_POST, 1);
         curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
         curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
         // Execute cURL session and store the response
         $response = curl_exec($ch);

         // Check for cURL errors
         if (curl_errno($ch)) {
             echo "cURL Error: " . curl_error($ch);
         }

         // Close cURL session
         curl_close($ch);
     }

     if (isset($_POST["upigate"]) || $_POST["for"] == "PlanRenew") {
         $planid = $_POST["planid"];
         $userid = $_SESSION["user_id"];
         $email = $_SESSION["username"];
         $isVIP = $_POST["vip_sub"]; 
         if ($_POST["use_wallet"] == "yes") {
         $walletdebit = $_POST["wallet_used"];
         $balance = $userdata["balance"];
         
        // echo('hi'.$planid);
// Set the monthsToAdd based on planid
$esql = "SELECT `expiry`,`amount` FROM `subscription_plan` WHERE id = $planid";
$qesql = mysqli_query($conn, $esql);

$row = mysqli_fetch_array($qesql);
$planamount = $row['amount'];
$monthsToAdd = $row['expiry'];

             if ($amount == 0) {
                 $debit = debit_balance($userid, $walletdebit, $order_id, "Plan Renew");
                 if ($debit == true) {
                     // Set the monthsToAdd based on planid
    $user_sql = "SELECT expiry FROM users WHERE mobile = '$email'";
    $user_query = mysqli_query($conn, $user_sql);
    $user_row = mysqli_fetch_array($user_query);
    $current_expiry = $user_row['expiry'];

    // Determine the base date for calculating new expiry
    $current_date = date('Y-m-d H:i:s');
    if ($current_expiry && strtotime($current_expiry) > strtotime($current_date)) {
        // If current expiry is in the future, extend from that date
        $newExpiryDate = date('Y-m-d H:i:s', strtotime($current_expiry . " +$monthsToAdd months"));
    } else {
        // If current expiry is in the past or null, start from today
        $newExpiryDate = date('Y-m-d H:i:s', strtotime("+$monthsToAdd months"));
    }

if ($isVIP == "yes") {
    $sql = "UPDATE users SET expiry = '$newExpiryDate', planId = $planid, tranjection_Count = 0, vip_expiry = '$newExpiryDate' WHERE mobile = '$email'";
} else {
    $sql = "UPDATE users SET expiry = '$newExpiryDate', planId = $planid, tranjection_Count = 0 WHERE mobile = '$email'";
}
$rrrr = mysqli_query($conn, $sql);
                     
                     if ($rrrr) {
                        $record = "INSERT INTO planorders (order_id, userid, planid, payment_date, expiry_date, utr, status, remark, userMobile, amount)
                        VALUES ('$order_id', '$userid', '$planid', NOW(), '$newExpiryDate', '', 'SUCCESS', 'FromWallet', '$email',$walletdebit)";
                         echo $record;
                         $irecord = mysqli_query($conn, $record);

                        echo '<script>
                            alert("Plan Successfully Activated From Wallet Balance");
                            window.location.href = "https://' . $_SERVER["SERVER_NAME"] . '/auth/dashboard";
                        </script>';


                         exit();
                     } else {
                         //echo "SQL Error: " . mysqli_error($conn);
                         // Show SweetAlert2 error message
                         header(
                             "Location: https://" .
                                 $_SERVER["SERVER_NAME"] .
                                 "/auth/subscription"
                         );
                         exit();
                     }
                 } else {
                     echo "Error updating order: " . $stmt->error;
                 }
                 exit();
             } else {
                //  $newAmount = $amount - $balance;
                //  $amount = $newAmount;
                 $remark2 = $_POST["use_wallet"]; //values : yes
             }
         }


     setcookie("planid_cookie", $planid, time() + 360, "/");
     setcookie("isVip_cookie", $isVIP, time() + 360, "/");
     // URL of the PHP page

     $callbackurl = "https://" . $_SERVER["SERVER_NAME"] . "/auth/lib/callback";

     if ($_POST["for"] == "referpay") {
         $email = $_POST["mobile"];
         $remark = "UpiGateway Join";
     }
     $remark = "UpiGateway Renew";
     // Data to be sent in the POST request
     $data = [
         "customer_mobile" => $email,
         "user_token" => $token,
         "amount" => $amount,
         "order_id" => $order_id,
         "redirect_url" => $callbackurl,
         "remark1" => $payfor,
         "remark2" => $remark2,
     ];

     // Initialize cURL session
     $ch = curl_init();

     // Set cURL options
     curl_setopt($ch, CURLOPT_URL, $url);
     curl_setopt($ch, CURLOPT_POST, 1);
     curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);


         // Execute cURL session and store the response
         $response = curl_exec($ch);
   
         // Check for cURL errors
         if (curl_errno($ch)) {
             echo "cURL Error: " . curl_error($ch);
         }

         // Close cURL session
         curl_close($ch);

     }
     $jsonResponse = json_decode($response, true);
    if ($jsonResponse["status"]==true) {
     // Check if decoding was successful
     if (
         $jsonResponse !== null &&
         isset($jsonResponse["result"]["payment_url"])
     ) {
         if($payfor=="PlanRenew"){
         $record = "INSERT INTO planorders (order_id, userid, planid, payment_date, expiry_date, utr, status, remark, userMobile, amount)
                        VALUES ('$order_id', '$userid', '$planid', NOW(), 'DATE_ADD(NOW(), INTERVAL 30 DAY)', '', 'PENDING', '$remark', '$email',$amount)";

     
         $irecord = mysqli_query($conn, $record);
         }

         // Redirect the user to the payment URL
         $paymentUrl = $jsonResponse["result"]["payment_url"];

        // if ($_POST["for"] == "addbalance") {
        echo($response);
        exit();
        // }
        header("Location: " . $paymentUrl);
     } else {
         echo "Failed to decode JSON response or missing payment URL.";
     }
    } else {
    $message = $jsonResponse["message"];
    echo '<script>
        alert(' . json_encode($message) . ');
        window.history.back();
    </script>';
}
    

 } else {
     echo "No Data Found";
 }
         
?>
