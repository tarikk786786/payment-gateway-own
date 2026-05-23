<?php 
// Set maximum execution time to 5 minutes (300 seconds)
// ini_set('max_execution_time', 600);
// Define the base directory constant

    date_default_timezone_set("Asia/Kolkata");
    include '../auth/function.php';
    $today = date('Y-m-d'); 
    $startDate = date('Y-m-d', strtotime($today . ' -4 days'));
    $endDate = date('Y-m-d', strtotime($today . ' +1 days'));
    
    $query = "SELECT `mobile`, `expiry`,`name`,`email` FROM `users` WHERE `expiry` BETWEEN '$startDate' AND '$endDate'";
    $query_run = mysqli_query($conn, $query);

    if ($query_run) {
    while ($row = mysqli_fetch_assoc($query_run)) {
    $name = htmlspecialchars($row['name']); // XSS से बचने के लिए
    $mobile = htmlspecialchars($row['mobile']);
    $email = htmlspecialchars($row['email']);
    $expiryDate = htmlspecialchars($row['expiry']);
    $today = date('Y-m-d');
    echo($email);
    if (strtotime($expiryDate) >= strtotime($today)) {
        $xstatus = "expiring";
    } else {
        $xstatus = "expired";
        // Clear login_token if expired
        $clearTokenQuery = "UPDATE users SET login_token=NULL WHERE mobile='$mobile'";
        mysqli_query($conn, $clearTokenQuery);
    }
    
    $message = "*Dear $name,*\n\n"
        . "🛑 Your plan is *$xstatus* on *$expiryDate*.\n"
        . "🔄 Please renew your subscription to avoid service interruption.\n\n"
        . "👉 *Renew Now*: https://$server/auth/subscription\n\n"
        . " >Team UpiGateway\n\n"
        . "`Thanks for using our services!`";
    // URL एन्कोड करें
$emailMessage = "
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; color: #333; line-height: 1.6; }
        .header { font-size: 20px; font-weight: bold; color: #4caf50; }
        .content { margin-top: 15px; }
        .highlight { font-weight: bold; color: #d32f2f; }
        .cta-button { display: inline-block; padding: 10px 20px; margin-top: 20px; background-color: #4caf50; color: #fff; text-decoration: none; border-radius: 5px; }
        .cta-button:hover { background-color: #388e3c; }
        .footer { font-size: 12px; margin-top: 20px; color: #777; }
    </style>
</head>
<body>
    <div class='email-container'>
        <p class='header'>Dear $name,</p>
        <div class='content'>
            <p>🛑 Your plan is <span class='highlight'>$xstatus</span> on <span class='highlight'>$expiryDate</span>.</p>
            <p>🔄 Please renew your subscription to avoid service interruption.</p>
            <a href='https://$server/auth/subscription' class='cta-button'>Renew Now</a>
        </div>
        <div class='footer'>
            <p>Thank you for using our services!</p>
        </div>
    </div>
</body>
</html>";

    
    // Check if sendWA function exists
    if (function_exists('sendWA')) {
        if (!sendWA($mobile, $message)) {
            error_log("Failed to send message to $mobile");
        }
    } else {
        error_log("sendWA function is not defined.");
    }
    if (function_exists('sendEmail')) {
        // if (!sendEmail($email, "UpiGateway Alert", $emailMessage)) {
        //     error_log("Failed to send message to $email");
        // }
    } else {
        error_log("sendEmail function is not defined.");
    }

    // Add random delay between 2 to 5 seconds
    $delay = rand(2, 4);
    sleep($delay);
}

        echo "<script>alert('Notifications sent successfully!');</script>";
    } else {
        echo "<script>alert('Error in sending notifications: " . mysqli_error($conn) . "');</script>";
    }
?>