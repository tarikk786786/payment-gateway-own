<?php
// Include the database connection
include "config.php";

// Fetch site settings from the database
$query = "SELECT * FROM site_settings LIMIT 1";
$result = mysqli_query($conn, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $site_settings = mysqli_fetch_assoc($result);
} else {
    $site_settings = [];
    echo "Error: No site settings found.";
}

// Fetch API settings from the database
$query = "SELECT * FROM api_settings LIMIT 1";
$result = mysqli_query($conn, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $api_settings = mysqli_fetch_assoc($result);

    $whatsapp_api_url = $api_settings['whatsapp_api_url'];
    $sender_id = $api_settings['sender_id'];
    $apikey = $api_settings['api_key'];
    $sender_email = $api_settings['sender_email'];
} else {
    die("Error: No API settings found.");
}

function getResponse($url, $verifySSL = true, $timeout = 30, $connectTimeout = 10) {
    // Validate URL
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        return [
            'success' => false,
            'error' => 'Invalid URL provided',
            'response' => null
        ];
    }

    $ch = curl_init();

    // Basic cURL options
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $verifySSL);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $verifySSL ? 2 : 0);
    
    // Set timeouts
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $connectTimeout);
    
    // Follow redirects
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
    
    // Set user agent
    curl_setopt($ch, CURLOPT_USERAGENT, 'PHP-CURL/1.0');
    
    // Enable HTTP headers
    curl_setopt($ch, CURLOPT_HEADER, false);
    
    // Execute cURL request
    $response = curl_exec($ch);
    
    // Get HTTP status code
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    // Check for cURL errors
    if (curl_errno($ch)) {
        $error = curl_error($ch);
        curl_close($ch);
        return [
            'success' => false,
            'error' => 'cURL error: ' . $error,
            'response' => null
        ];
    }
    
    // Close cURL session
    curl_close($ch);
    
    // Check HTTP status code
    if ($httpCode >= 400) {
        return [
            'success' => false,
            'error' => 'HTTP error: ' . $httpCode,
            'response' => $response
        ];
    }
    
    // Return raw response as in the original function
    return [
        'success' => true,
        'error' => null,
        'response' => $response
    ];
}


function sendWA($mobile_no, $msg) {
    // Disabled WhatsApp API to use free email service instead
    return false;
}



function sendEmail($to_email, $subject, $message) {
    require_once __DIR__ . '/../vendor/autoload.php'; // Composer autoload

    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

    try {
        // SMTP Server Configuration
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Google SMTP server
        $mail->SMTPAuth = true;
        $mail->Username = 'india.business@gmail.com'; // Your Gmail address
        $mail->Password = 'vyhv sxav ffqj nfno'; // App password generated in Google Account
        $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465; // SMTP Port for SSL

        // Email Details
        $mail->setFrom('india.business@gmail.com', 'UpiGateway'); // Sender email and name
        $mail->addAddress($to_email); // Recipient email

        $mail->isHTML(true); // Email format
        $mail->Subject = $subject;

        // HTML Email Body with Branding
$server = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : "chickenpox.in";

$brandingMessage = <<<HTML
<html>
<head>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <style>
        .email-container { 
            max-width: 700px; 
            margin: 50px auto; 
            background: #adc9ff; 
            border-radius: 16px; 
            overflow: hidden; 
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5); 
            border: 2px solid #00d9ff; 
            animation: slideIn 0.6s ease-in-out; 
        }
        @keyframes slideIn { 
            from { opacity: 0; transform: translateY(30px); } 
            to { opacity: 1; transform: translateY(0); } 
        }
        .email-header { 
            background: linear-gradient(135deg, #00d9ff, #ff007a); 
            color: #fff; 
            padding: 40px; 
            text-align: center; 
            font-family: 'Orbitron', sans-serif; 
            font-size: 32px; 
            font-weight: 700; 
            letter-spacing: 2px; 
            text-transform: uppercase; 
            border-bottom: 6px solid rgba(255, 255, 255, 0.3); 
            position: relative;
            overflow: hidden;
        }
        .email-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        }
        .email-body { 
            padding: 40px; 
            line-height: 1.8; 
            font-size: 16px; 
            color: #b0c4de; 
            background: #2a3b5a; 
        }
        .email-body p { 
            margin: 0 0 20px; 
        }
        .email-footer { 
            background: #1b263b; 
            padding: 10px; 
            text-align: center; 
            font-size: 14px; 
            color: #a0b1cb; 
            border-top: 1px solid #00d9ff; 
        }
        .btn { 
            display: inline-block; 
            padding: 16px 40px; 
            margin: 25px 0; 
            background: linear-gradient(45deg, #00d9ff, #ff007a); 
            color: #fff; 
            font-family: 'Orbitron', sans-serif; 
            font-size: 18px; 
            font-weight: 700; 
            text-decoration: none; 
            border-radius: 10px; 
            transition: all 0.4s ease; 
            box-shadow: 0 0 20px rgba(0, 217, 255, 0.7); 
            position: relative;
            overflow: hidden;
        }
        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: all 0.5s;
        }
        .btn:hover::before {
            left: 100%;
        }
        .btn:hover { 
            transform: scale(1.05); 
            box-shadow: 0 0 30px rgba(0, 217, 255, 0.9); 
        }
        .social-icons { 
            margin-top: 20px; 
        }
        .social-icons a { 
            display: inline-block; 
            margin: 0 15px; 
            color: #00d9ff; 
            font-size: 16px; 
            font-weight: 500; 
            text-decoration: none; 
            transition: all 0.3s ease; 
        }
        .social-icons a:hover { 
            color: #ff007a; 
            transform: translateY(-2px); 
        }
        .highlight { 
            color: #00d9ff; 
            font-weight: bold; 
            text-shadow: 0 0 5px rgba(0, 217, 255, 0.5); 
        }
textarea {
  width: 80%;
  min-height: 100px;
  padding: 12px 16px;
  font-size: 16px;
  color: #e0e0e0;
  background: linear-gradient(145deg, #1e1e1e, #2a2a2a);
  border: 1px solid #0084ff;
  border-radius: 10px;
  outline: none;
  box-shadow: inset 0 0 5px #000, 0 0 8px rgba(0, 255, 255, 0.2);
  transition: all 0.3s ease-in-out;
  font-family: 'Roboto Mono', monospace;
}
        @media (max-width: 600px) {
            .email-container { 
                margin: 20px; 
                border-radius: 10px; 
            }
            .email-header { 
                font-size: 24px; 
                padding: 20px; 
            }
            .email-body { 
                padding: 20px; 
                font-size: 14px; 
            }
            .btn { 
                padding: 12px 30px; 
                font-size: 16px; 
            }
        }
    </style>
</head>
<body>
    <div class='email-container'>
        <div class='email-header'>UpiGateway</div>
        <div class='email-body'>
            <p>Hello <span class='highlight'>Partner</span>,</p>
            <textarea id="w3review" name="w3review" rows="4" cols="50" readonly>{$message}</textarea>
            <p> Dive into the future with our vibrant community! Unlock <span class='highlight'>exclusive updates</span> and <span class='highlight'>special offers</span> tailored for you. Join our official WhatsApp channel now and stay ahead of the curve!</p>
            <a href='https://whatsapp.com/channel/0029Vas5C79Eawdhj46eCc1J' class='btn'>Join Support Channel</a>
        </div>
        <div class='email-footer'>
            <p>© 2025 <strong>UpiGateway™</strong>. All Rights Reserved.</p>
            <p><a href='https://{$server}' style='color: #00d9ff; text-decoration: none; font-weight: 600;'>Explore UpiGateway</a></p>
            <div class='social-icons'>
                <a href='https://facebook.com/UpiGateway' target='_blank'>Facebook</a> | 
                <a href='https://twitter.com/UpiGateway' target='_blank'>Twitter</a> | 
                <a href='https://linkedin.com/company/UpiGateway' target='_blank'>LinkedIn</a>
            </div>
        </div>
    </div>
</body>
</html>
HTML;


$mail->Body = $brandingMessage;

        // Send Email
        $mail->send();
        return "Email sent successfully to $to_email.";
    } catch (Exception $e) {
        return "Email could not be sent. Error: {$mail->ErrorInfo}";
    }
}


// Combined function for sending WhatsApp and Email (Now Email Only)
function sendNotification($mobile_no, $email, $msg, $subject) {
    $email_success = sendEmail($email, $subject, $msg);

    $email_response = [
        "success" => $email_success,
        "message" => $email_success ? "Email notification sent" : "Failed to send Email notification"
    ];

    return [
        "whatsapp" => ["success" => false, "message" => "WhatsApp disabled"],
        "email" => $email_response
    ];
}


// Wallet function_exist

// Credit balance function
function credit_balance($user_id, $amount, $utr, $remark) {
    global $conn;

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Start transaction
    $conn->begin_transaction();

    try {
        // Get current balance and user details
        $sql = "SELECT balance, mobile, email FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            throw new Exception("User not found");
        }

        $row = $result->fetch_assoc();
        $opening_balance = $row['balance'];
        $mobile = $row['mobile'];
        $email = $row['email'];

        // Update balance
        $closing_balance = $opening_balance + $amount;
        $sql_update = "UPDATE users SET balance = ? WHERE id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("di", $closing_balance, $user_id);
        $stmt_update->execute();

        // Record transaction
        $sql_transaction = "INSERT INTO wallet_transactions (user_id, amount, type, opening_balance, closing_balance, utr, Remark) 
                           VALUES (?, ?, 'credit', ?, ?, ?, ?)";
        $stmt_transaction = $conn->prepare($sql_transaction);
        $stmt_transaction->bind_param("idddss", $user_id, $amount, $opening_balance, $closing_balance, $utr, $remark);
        $stmt_transaction->execute();

        // Send notification
        $msg = "🔔 Wallet Credit Alert!

💸 Amount Credited: $amount
💼 New Balance: $closing_balance
🔗 UTR: $utr
📝 Remark: $remark

*Thanks for using UpiGateway! 🚀*";
        $subject = "Wallet Credit Notification";
        $notification_response = sendNotification($mobile, $email, $msg, $subject);

        // Commit transaction
        $conn->commit();

        // Close statements
        $stmt->close();
        $stmt_update->close();
        $stmt_transaction->close();

        return true;
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        error_log("Credit balance error: " . $e->getMessage());
        return false;
    }
}

// Debit balance function
function debit_balance($user_id, $amount, $utr, $remark) {
    global $conn;

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Start transaction
    $conn->begin_transaction();

    try {
        // Get current balance and user details
        $sql = "SELECT balance, mobile, email FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            throw new Exception("User not found");
        }

        $row = $result->fetch_assoc();
        $opening_balance = $row['balance'];
        $mobile = $row['mobile'];
        $email = $row['email'];

        // Check if sufficient balance
        if ($opening_balance < $amount) {
            throw new Exception("Insufficient balance");
        }

        // Update balance
        $closing_balance = $opening_balance - $amount;
        $sql_update = "UPDATE users SET balance = ? WHERE id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("di", $closing_balance, $user_id);
        $stmt_update->execute();

        // Record transaction
        $sql_transaction = "INSERT INTO wallet_transactions (user_id, amount, type, opening_balance, closing_balance, utr, Remark) 
                           VALUES (?, ?, 'debit', ?, ?, ?, ?)";
        $stmt_transaction = $conn->prepare($sql_transaction);
        $stmt_transaction->bind_param("idddss", $user_id, $amount, $opening_balance, $closing_balance, $utr, $remark);
        $stmt_transaction->execute();

        // Send notification
        $msg = "🔔 Wallet Debit Alert!

💸 Amount Debited: $amount
💼 New Balance: $closing_balance
🔗 UTR: $utr
📝 Remark: $remark

*Thanks for using UpiGateway! 🚀*";
        $subject = "Wallet Debit Notification";
        $notification_response = sendNotification($mobile, $email, $msg, $subject);

        // Commit transaction
        $conn->commit();

        // Close statements
        $stmt->close();
        $stmt_update->close();
        $stmt_transaction->close();

        return true;
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        error_log("Debit balance error: " . $e->getMessage());
        return false;
    }
}

// Create & return Referal Code
function generateReferralCode($length = 12) {
    return strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, $length));
}

function PlanRenewal($orderid, $utr, $remark1, $txnStatus, $remark2, $site_settings, $userdata) {
    global $conn;
    // Ensure all required global variables/cookies/sessions are accessible or passed
    // In a real application, you might pass these as function arguments
    // For this example, we'll assume $_COOKIE and $_SESSION are available
    $planid = isset($_COOKIE['planid_cookie']) ? $_COOKIE['planid_cookie'] : null;
    $email = isset($_SESSION['username']) ? $_SESSION['username'] : null;
    $mobile = $userdata['mobile']; // Assuming mobile is part of $userdata, or passed separately

    if (!$planid || !$email || !$mobile) {
        return ['success' => false, 'message' => 'Missing required data (plan ID, email, or mobile).'];
    }

    // Set the monthsToAdd based on planid
    $esql = "SELECT `expiry` FROM `subscription_plan` WHERE id = ?";
    $stmt_esql = mysqli_prepare($conn, $esql);
    if (!$stmt_esql) {
        return ['success' => false, 'message' => 'Database error preparing expiry select: ' . mysqli_error($conn)];
    }
    mysqli_stmt_bind_param($stmt_esql, "i", $planid);
    mysqli_stmt_execute($stmt_esql);
    $qesql_result = mysqli_stmt_get_result($stmt_esql);
    $row = mysqli_fetch_array($qesql_result);
    $monthsToAdd = $row ? $row['expiry'] : 0;
    mysqli_stmt_close($stmt_esql);

    if ($monthsToAdd === 0) {
        return ['success' => false, 'message' => 'Invalid plan ID or expiry not found for the plan.'];
    }

    // Check for duplicate transaction
    $protectq = "SELECT COUNT(*) FROM planorders WHERE order_id = ? AND status = 'SUCCESS'";
    $stmt_protectq = mysqli_prepare($conn, $protectq);
    if (!$stmt_protectq) {
        return ['success' => false, 'message' => 'Database error preparing duplicate check: ' . mysqli_error($conn)];
    }
    mysqli_stmt_bind_param($stmt_protectq, "s", $orderid);
    mysqli_stmt_execute($stmt_protectq);
    $sqprotect_result = mysqli_stmt_get_result($stmt_protectq);
    $row = mysqli_fetch_array($sqprotect_result);
    $duplicate_transaction_count = $row[0];
    mysqli_stmt_close($stmt_protectq);

    if ($duplicate_transaction_count > 0) {
        return ['success' => false, 'message' => 'Duplicate Transaction.'];
    }

    // Get the user's current expiry date
    $user_sql = "SELECT expiry FROM users WHERE mobile = ?";
    $stmt_user_sql = mysqli_prepare($conn, $user_sql);
    if (!$stmt_user_sql) {
        return ['success' => false, 'message' => 'Database error preparing user expiry select: ' . mysqli_error($conn)];
    }
    mysqli_stmt_bind_param($stmt_user_sql, "s", $email);
    mysqli_stmt_execute($stmt_user_sql);
    $user_query_result = mysqli_stmt_get_result($stmt_user_sql);
    $user_row = mysqli_fetch_array($user_query_result);
    $current_expiry = $user_row ? $user_row['expiry'] : null;
    mysqli_stmt_close($stmt_user_sql);

    // Determine the base date for calculating new expiry
    $current_date = date('Y-m-d H:i:s');
    if ($current_expiry && strtotime($current_expiry) > strtotime($current_date)) {
        // If current expiry is in the future, extend from that date
        $newExpiryDate = date('Y-m-d H:i:s', strtotime($current_expiry . " +$monthsToAdd months"));
    } else {
        // If current expiry is in the past or null, start from today
        $newExpiryDate = date('Y-m-d H:i:s', strtotime("+$monthsToAdd months"));
    }

    // Update planorders table
    $sqll = "UPDATE planorders SET status = 'SUCCESS', utr = ?, expiry_date = ? WHERE order_id = ?";
    $stmt_sqll = mysqli_prepare($conn, $sqll);
    if (!$stmt_sqll) {
        return ['success' => false, 'message' => 'Database error preparing order update: ' . mysqli_error($conn)];
    }
    mysqli_stmt_bind_param($stmt_sqll, "sss", $utr, $newExpiryDate, $orderid);
    $sqrr = mysqli_stmt_execute($stmt_sqll);
    mysqli_stmt_close($stmt_sqll);

    if (!$sqrr) {
        return ['success' => false, 'message' => 'Error updating order: ' . mysqli_error($conn)];
    }

    // Handle referral and bonus logic
    if ($remark1 == "referpay" && $txnStatus == "SUCCESS") {
        $referUid = $userdata['id'];
        $sponserid = $userdata['referred_by'];
        $bonusamount = $site_settings['bonus_refer'];
        $referrramount = $site_settings['income_sponser'];

        // Assuming credit_balance function exists globally or is included
        $credit = credit_balance($sponserid, $referrramount, $utr, "Refer Income :" . $mobile);
        $credit2 = credit_balance($referUid, $bonusamount, $utr, "Refer JOIN Bonus");

        if ($credit && $credit2) {
            $msg = "Congratulations!! We Credited Your Joining Bonus of Rs. " . $bonusamount . " In UpiGateway™ Wallet";
            // sendWA disabled, using sendEmail instead for referral bonus
            $subject = "Referral Join Bonus Credited";
            sendEmail($email, $subject, $msg);
            // Instead of alert, you might return this message or log it
            // echo '<script>alert("Congratulations!! We Credited Your Joining Bonus In UpiGateway™ Wallet, Please Login Your Account Now");</script>';
        }
    }

    // Handle plan renewal debit
    if ($remark2 == "yes") {
        $balance = $userdata['balance'];
        $userid = $userdata['id'];
        // Assuming debit_balance function exists globally or is included
        $debit = debit_balance($userid, $balance, $utr, "Plan Renew");
        if (!$debit) {
            // Log or handle debit failure if necessary, but don't stop the whole process if plan update is primary
        }
    }

    // Update users table with new expiry date
    $sql = "UPDATE users SET expiry = ?, planId = ?, tranjection_Count = 0 WHERE mobile = ?";
    $stmt_sql = mysqli_prepare($conn, $sql);
    if (!$stmt_sql) {
        return ['success' => false, 'message' => 'Database error preparing user update: ' . mysqli_error($conn)];
    }
    mysqli_stmt_bind_param($stmt_sql, "sis", $newExpiryDate, $planid, $email);
    $rrrr = mysqli_stmt_execute($stmt_sql);
    mysqli_stmt_close($stmt_sql);

    if ($rrrr) {
        return ['success' => true, 'message' => 'Plan renewed successfully.'];
    } else {
        return ['success' => false, 'message' => 'Error updating user plan: ' . mysqli_error($conn)];
    }
}


?>