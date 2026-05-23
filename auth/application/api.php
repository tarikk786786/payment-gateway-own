<?php
define('PROJECT_ROOT', realpath(dirname(__FILE__)) . '/../');

include PROJECT_ROOT . '../pages/dbFunctions.php';
include PROJECT_ROOT . '../auth/config.php';
include PROJECT_ROOT . '../auth/function.php';

// ✅ WhatsApp function include करो (अगर अलग फ़ाइल में है तो include करो)
// include PROJECT_ROOT . '../includes/sendWA.php';

header('Content-Type: application/json');

$mobile = $_GET['mobile'] ?? '';
$otp = $_GET['otp'] ?? '';

if (empty($mobile)) {
    echo json_encode(['status' => false, 'message' => 'mobile is required']);
    exit;
}

// Step 1: Send OTP if OTP is empty
if (empty($otp)) {
    $stmt = $conn->prepare("SELECT id,name,mobile,role,balance,company,expiry,vip_expiry,tranjection_Count,planId,acc_lock,acc_ban,referral_code,merchentRouting,logo FROM users WHERE mobile = ?");
    $stmt->bind_param("s", $mobile);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        // $otp_code = rand(100000, 999999);
        $otp_code = 123456;

        // Update OTP in users table
        $stmt = $conn->prepare("UPDATE users SET otp = ? WHERE mobile = ?");
        $stmt->bind_param("ss", $otp_code, $mobile);
        $stmt->execute();

        // ✅ OTP भेजो WhatsApp से
        $msg = "आपका लॉगिन OTP है: $otp_code\n\nयह OTP 5 मिनट तक वैध है।";
        sendWA($mobile, $msg);

        echo json_encode([
            'status' => true,
            'message' => 'OTP sent successfully via WhatsApp',
        ]);
    } else {
        echo json_encode(['status' => false, 'message' => 'Mobile not found']);
    }

// Step 2: Verify OTP
} else {
    $stmt = $conn->prepare("SELECT id,name,mobile,role,balance,company,expiry,vip_expiry,tranjection_Count,planId,acc_lock,acc_ban,referral_code,merchentRouting,logo FROM users WHERE mobile = ? AND otp = ?");
    $stmt->bind_param("ss", $mobile, $otp);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $userData = $result->fetch_assoc();

        echo json_encode([
            'status' => true,
            'message' => 'OTP verified successfully',
            'user' => $userData
        ]);
    } else {
        echo json_encode(['status' => false, 'message' => 'Invalid OTP or Mobile']);
    }
}
?>
