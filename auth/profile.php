<?php
include "header.php";
include "database_connection.php";
session_start();

// Initialize variables
$mobile = $_SESSION['username'] ?? $_GET['mobile'] ?? '';
$userdata = []; // FIXED: Initialize $userdata as empty array
$upload_error = "";
$kyc_error = "";
$success_message = "";

// KYC API Configuration
$kyc_api_base_url = "https://server.webtechly.co.in/"; // Replace with actual API base URL
$kyc_api_key = "0b3539-88747b-7ab430-171283-2f7460"; // Replace with actual API key

// FIXED: Load user data at the beginning
if (!empty($mobile)) {
    $query = "SELECT * FROM users WHERE mobile = ?";
    $stmt = $conn->prepare($query);
    if ($stmt) {
        $stmt->bind_param("s", $mobile);
        $stmt->execute();
        $result = $stmt->get_result();
        $userdata = $result->fetch_assoc() ?: []; // Ensure it's always an array
        $stmt->close();
    }
}

/**
 * Function to make KYC API calls
 */
function makeKYCApiCall($endpoint, $data = [], $method = 'POST') {
    global $kyc_api_base_url, $kyc_api_key;
    
    $url = $kyc_api_base_url . $endpoint;
    $headers = [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $kyc_api_key,
        'Accept: application/json'
    ];
    
    // For GET requests, append data to the URL as query parameters
    if ($method === 'GET' && !empty($data)) {
        $url .= '?' . http_build_query($data);
    }
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_SSL_VERIFYPEER => false // Note: For production, this should be true
    ]);
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);
    
    // FIXED: Better error handling
    if ($curl_error) {
        error_log("CURL Error: " . $curl_error);
        return [
            'success' => false,
            'data' => ['error' => 'Connection failed: ' . $curl_error],
            'http_code' => 0
        ];
    }
    
    $decoded_response = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("JSON Decode Error: " . json_last_error_msg());
        return [
            'success' => false,
            'data' => ['error' => 'Invalid response format'],
            'http_code' => $http_code
        ];
    }
    
    return [
        'success' => $http_code === 200,
        'data' => $decoded_response,
        'http_code' => $http_code
    ];
}

/**
 * PAN Verification API Call
 */
function verifyPAN($pan_number, $name, $kyc_api_key) {
    $data = [
        'pan_no' => $pan_number,
        'application_no' => str_pad(mt_rand(0, 999999999999), 12, '0', STR_PAD_LEFT),
        'api_key' => $kyc_api_key,
    ];
    
    return makeKYCApiCall('pan_details', $data, "GET");
}

/**
 * Send Aadhaar OTP API Call - FIXED
 */
function sendAadhaarOTP($aadhaar_number, $kyc_api_key) {
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => "https://server.webtechly.co.in/send-otp?no=$aadhaar_number",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
    ));

    $response = curl_exec($curl);
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($curl);
    curl_close($curl);
    
    // FIXED: Better error handling
    if ($curl_error) {
        error_log("Aadhaar OTP CURL Error: " . $curl_error);
        return [
            'success' => false,
            'data' => ['error' => 'Connection failed: ' . $curl_error],
            'http_code' => 0
        ];
    }
    
    $decoded_response = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("Aadhaar OTP JSON Error: " . json_last_error_msg());
        error_log("Raw Response: " . $response);
        return [
            'success' => false,
            'data' => ['error' => 'Invalid response format'],
            'http_code' => $http_code
        ];
    }
    
    return [
        'success' => $http_code === 200 && isset($decoded_response['status']) && $decoded_response['status'] === "true",
        'data' => $decoded_response,
        'http_code' => $http_code
    ];
}

/**
 * Verify Aadhaar OTP API Call - FIXED
 */
function verifyAadhaarOTP($client_id, $otp, $kyc_api_key) {
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => "https://server.webtechly.co.in/fetch-info?api_key=$kyc_api_key&otp=$otp&client_id=$client_id",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
    ));

    $response = curl_exec($curl);
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($curl);
    curl_close($curl);
    
    // FIXED: Better error handling
    if ($curl_error) {
        error_log("Aadhaar Verify CURL Error: " . $curl_error);
        return [
            'success' => false,
            'data' => ['error' => 'Connection failed: ' . $curl_error],
            'http_code' => 0
        ];
    }
    
    $decoded_response = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("Aadhaar Verify JSON Error: " . json_last_error_msg());
        error_log("Raw Response: " . $response);
        return [
            'success' => false,
            'data' => ['error' => 'Invalid response format'],
            'http_code' => $http_code
        ];
    }
    
    return [
        'success' => $http_code === 200 && isset($decoded_response['status']) && $decoded_response['status'] === true,
        'data' => $decoded_response,
        'http_code' => $http_code
    ];
}

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Handle Aadhaar OTP Send - FIXED
    if (isset($_POST['send_aadhaar_otp'])) {
        if (!empty($userdata['aadhaar'])) {
            $otp_result = sendAadhaarOTP($userdata['aadhaar'], $kyc_api_key);
            
            // FIXED: Better response validation based on actual API response
            if ($otp_result['success'] && isset($otp_result['data']['client_id'])) {
                // Store client_id in session for OTP verification
                $_SESSION['aadhaar_client_id'] = $otp_result['data']['client_id'];
                $_SESSION['aadhaar_otp_sent'] = true;
                $_SESSION['otp_timestamp'] = time(); // FIXED: Add timestamp for OTP expiry
                $success_message = "OTP sent to your registered mobile number! Valid for 10 minutes.";
            } else {
                $error_msg = 'Unknown error';
                if (isset($otp_result['data']['message'])) {
                    $error_msg = $otp_result['data']['message'];
                } elseif (isset($otp_result['data']['error'])) {
                    $error_msg = $otp_result['data']['error'];
                }
                $kyc_error = "Failed to send OTP: " . $error_msg;
                
                // FIXED: Log detailed error for debugging
                error_log("Aadhaar OTP Send Failed: " . print_r($otp_result, true));
            }
        } else {
            $kyc_error = "Aadhaar number not found in profile.";
        }
    }
    
    // Handle Aadhaar OTP Verify - FIXED
    if (isset($_POST['verify_aadhaar_otp'])) {
        $otp = trim($_POST['aadhaar_otp'] ?? '');
        $client_id = $_SESSION['aadhaar_client_id'] ?? '';
        $otp_timestamp = $_SESSION['otp_timestamp'] ?? 0;
        
        // FIXED: Check OTP expiry (10 minutes)
        if ($otp_timestamp && (time() - $otp_timestamp) > 600) {
            $kyc_error = "OTP has expired. Please request a new OTP.";
            // Clear expired session data
            unset($_SESSION['aadhaar_client_id']);
            unset($_SESSION['aadhaar_otp_sent']);
            unset($_SESSION['otp_timestamp']);
        } elseif (!empty($otp) && !empty($client_id)) {
            $verify_result = verifyAadhaarOTP($client_id, $otp, $kyc_api_key);
            
            // FIXED: Better response validation based on actual API response structure
            if ($verify_result['success'] && isset($verify_result['data']['data'])) {
                
                // Update Aadhaar verification status
                $aadhaar_data = $verify_result['data']['data'];
                $name = $aadhaar_data['name'] ?? $userdata['name'];
                $dob = $aadhaar_data['dob'] ?? $userdata['dob'];
                $gender = $aadhaar_data['gender'] ?? $userdata['gender'];
                $profile = $userdata['profile'];
                if (!empty($aadhaar_data['photo_base64'])) {
                    $target_dir = "uploads/profiles/";
                    if (!is_dir($target_dir)) {
                        mkdir($target_dir, 0777, true);
                    }
                    $filename = $target_dir . uniqid('aadhaar_') . '.jpg';
                    $imgdata = base64_decode($aadhaar_data['photo_base64']);
                    file_put_contents($filename, $imgdata);
                    $profile = $filename;
                }
                $update_query = "UPDATE users SET aadhaar_verified = 'YES', name=?, dob=?, gender=?, profile=?, aadhaar_verified_date = NOW() WHERE mobile = ?";
                $stmt = $conn->prepare($update_query);
                if ($stmt) {
                    $stmt->bind_param("sssss", $name, $dob, $gender,$profile, $mobile);
                    if ($stmt->execute()) {
                        // Clear session data
                        unset($_SESSION['aadhaar_client_id']);
                        unset($_SESSION['aadhaar_otp_sent']);
                        unset($_SESSION['otp_timestamp']);
                        unset($_SESSION['both_verification']);
                        
                        $success_message = "Aadhaar verification completed successfully!";
                    } else {
                        $kyc_error = "Database update failed: " . $stmt->error;
                    }
                    $stmt->close();
                } else {
                    $kyc_error = "Database prepare failed: " . $conn->error;
                }
            } else {
                $error_msg = 'Invalid OTP or verification failed';
                if (isset($verify_result['data']['message'])) {
                    $error_msg = $verify_result['data']['message'];
                } elseif (isset($verify_result['data']['error'])) {
                    $error_msg = $verify_result['data']['error'];
                }
                $kyc_error = $error_msg;
                
                // FIXED: Log detailed error for debugging
                error_log("Aadhaar OTP Verify Failed: " . print_r($verify_result, true));
            }
        } else {
            $kyc_error = "Please enter the OTP.";
        }
    }
    
    // Handle KYC Verification - FIXED
    if (isset($_POST['verify_kyc'])) {
        $kyc_type = $_POST['kyc_type'] ?? '';
        
        if ($kyc_type === 'pan' && !empty($userdata['pan']) && !empty($userdata['name'])) {
            $pan_result = verifyPAN($userdata['pan'], $userdata['name'], $kyc_api_key);
            
            if ($pan_result['success'] && 
                isset($pan_result['data']['result']['PAN']) && 
                $pan_result['data']['result']['PAN'] === $userdata['pan']) {
                
                // Update PAN verification status
                $first_name = $pan_result['data']['result']['FIRST_NAME'] ?? '';
                $middle_name = $pan_result['data']['result']['MIDDLE_NAME'] ?? '';
                $last_name = $pan_result['data']['result']['LAST_NAME'] ?? '';
                $name = trim($first_name . ' ' . $middle_name . ' ' . $last_name);
                $dob = $pan_result['data']['result']['DOB'] ?? $userdata['dob'];
                
                $update_query = "UPDATE users SET pan_verified = 'YES', name=?, dob=?, pan_verified_date = NOW() WHERE mobile = ?";
                $stmt = $conn->prepare($update_query);
                if ($stmt) {
                    $stmt->bind_param("sss", $name, $dob, $mobile);
                    $stmt->execute();
                    $stmt->close();
                }
                $success_message = "PAN verification completed successfully!";
            } else {
                $error_msg = 'PAN verification failed';
                if (isset($pan_result['data']['message'])) {
                    $error_msg = $pan_result['data']['message'];
                } elseif (isset($pan_result['data']['error'])) {
                    $error_msg = $pan_result['data']['error'];
                }
                $kyc_error = $error_msg;
            }
        }
        
        // FIXED: Standalone Aadhaar verification
        if ($kyc_type === 'aadhaar' && !empty($userdata['aadhaar'])) {
            $otp_result = sendAadhaarOTP($userdata['aadhaar'], $kyc_api_key);
            
            if ($otp_result['success'] && isset($otp_result['data']['client_id'])) {
                $_SESSION['aadhaar_client_id'] = $otp_result['data']['client_id'];
                $_SESSION['aadhaar_otp_sent'] = true;
                $_SESSION['otp_timestamp'] = time();
                $success_message = "OTP sent for Aadhaar verification! Valid for 10 minutes.";
            } else {
                $kyc_error = "Failed to send Aadhaar OTP.";
            }
        }
        
        if ($kyc_type === 'both') {
            // Verify both PAN and Aadhaar
            $pan_result = verifyPAN($userdata['pan'], $userdata['name'], $kyc_api_key);
            
            $pan_verified = $pan_result['success'] && 
                           isset($pan_result['data']['result']['PAN']) && 
                           $pan_result['data']['result']['PAN'] === $userdata['pan'];
            
            if ($pan_verified) {
                // Update PAN first
                $first_name = $pan_result['data']['result']['FIRST_NAME'] ?? '';
                $middle_name = $pan_result['data']['result']['MIDDLE_NAME'] ?? '';
                $last_name = $pan_result['data']['result']['LAST_NAME'] ?? '';
                $name = trim($first_name . ' ' . $middle_name . ' ' . $last_name);
                $dob = $pan_result['data']['result']['DOB'] ?? $userdata['dob'];
                
                $update_query = "UPDATE users SET pan_verified = 'YES', name=?, dob=?, pan_verified_date = NOW() WHERE mobile = ?";
                $stmt = $conn->prepare($update_query);
                if ($stmt) {
                    $stmt->bind_param("sss", $name, $dob, $mobile);
                    $stmt->execute();
                    $stmt->close();
                }
                
                // Send Aadhaar OTP for both verification
                $otp_result = sendAadhaarOTP($userdata['aadhaar'], $kyc_api_key);
                
                if ($otp_result['success'] && isset($otp_result['data']['client_id'])) {
                    $_SESSION['aadhaar_client_id'] = $otp_result['data']['client_id'];
                    $_SESSION['aadhaar_otp_sent'] = true;
                    $_SESSION['otp_timestamp'] = time();
                    $_SESSION['both_verification'] = true;
                    $success_message = "PAN verified! OTP sent for Aadhaar verification.";
                } else {
                    $kyc_error = "PAN verified but failed to send Aadhaar OTP.";
                }
            } else {
                $kyc_error = "PAN verification failed";
            }
        }
    }
    
    // Handle Profile Update (keeping existing code)
    if (isset($_POST['update_profile'])) {
        $is_otp = $_POST['is_otp'] ?? 'NO';
        $whatsapp_alert = $_POST['whatsapp_alert'] ?? 'NO';
        $email_alert = $_POST['email_alert'] ?? 'NO';
        $Intent_unable = isset($_POST['Intent_unable']) ? (int)$_POST['Intent_unable'] : 0;
        
        // Handle logo upload
        $logo_path = $userdata['logo'] ?? '';
        $profile_path = $userdata['profile'] ?? '';
        
        // Handle profile image upload
        if (!empty($_FILES['profile']['name'])) {
            $target_dir = "uploads/profiles/";
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            
            $file_name = basename($_FILES["profile"]["name"]);
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $unique_name = uniqid() . '.' . $file_ext;
            $target_file = $target_dir . $unique_name;
            
            // Validate file
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
            $max_size = 50 * 1024; // 50KB
            
            if (!in_array($file_ext, $allowed_types)) {
                $upload_error = "Only JPG, JPEG, PNG, and GIF files are allowed for profile image.";
            } elseif ($_FILES["profile"]["size"] > $max_size) {
                $upload_error = "Profile image size exceeds 50KB limit.";
            } else {
                list($width, $height) = getimagesize($_FILES["profile"]["tmp_name"]);
                if ($width != 128 || $height != 128) {
                    $upload_error = "Profile image must be exactly 128x128 pixels.";
                } else {
                    if (!empty($profile_path) && file_exists($profile_path)) {
                        unlink($profile_path);
                    }
                    if (move_uploaded_file($_FILES["profile"]["tmp_name"], $target_file)) {
                        $profile_path = $target_file;
                    } else {
                        $upload_error = "Error uploading profile image.";
                    }
                }
            }
        }
        
        // Handle logo upload
        if (!empty($_FILES['logo']['name']) && empty($upload_error)) {
            $target_dir = "uploads/logos/";
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            
            $file_name = basename($_FILES["logo"]["name"]);
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $unique_name = uniqid() . '.' . $file_ext;
            $target_file = $target_dir . $unique_name;
            
            // Validate file
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
            $max_size = 50 * 1024; // 50KB
            
            if (!in_array($file_ext, $allowed_types)) {
                $upload_error = "Only JPG, JPEG, PNG, and GIF files are allowed for logo.";
            } elseif ($_FILES["logo"]["size"] > $max_size) {
                $upload_error = "Logo size exceeds 50KB limit.";
            } else {
                list($width, $height) = getimagesize($_FILES["logo"]["tmp_name"]);
                if ($width != 128 || $height != 128) {
                    $upload_error = "Logo must be exactly 128x128 pixels.";
                } else {
                    if (!empty($logo_path) && file_exists($logo_path)) {
                        unlink($logo_path);
                    }
                    if (move_uploaded_file($_FILES["logo"]["tmp_name"], $target_file)) {
                        $logo_path = $target_file;
                    } else {
                        $upload_error = "Error uploading logo.";
                    }
                }
            }
        }
        
        if (empty($upload_error)) {
            $query = "UPDATE users SET is_otp = ?, whatsapp_alert = ?, email_alert = ?, Intent_unable = ?, logo = ?, profile = ?, updated_at = NOW() WHERE mobile = ?";
            $stmt = $conn->prepare($query);
            
            if ($stmt) {
                $stmt->bind_param("sssisss", $is_otp, $whatsapp_alert, $email_alert, $Intent_unable, $logo_path, $profile_path, $mobile);
                if ($stmt->execute()) {
                    $success_message = "Profile updated successfully!";
                } else {
                    $upload_error = "Error updating profile: " . $stmt->error;
                }
                $stmt->close();
            }
        }
    }
}

// Refresh user data after updates
if (!empty($success_message) && !empty($mobile)) {
    $query = "SELECT * FROM users WHERE mobile = ?";
    $stmt = $conn->prepare($query);
    if ($stmt) {
        $stmt->bind_param("s", $mobile);
        $stmt->execute();
        $result = $stmt->get_result();
        $userdata = $result->fetch_assoc() ?: []; // Ensure it's always an array
        $stmt->close();
    }
}

// Calculate KYC completion percentage
$kyc_completion = 0;
$total_fields = 5; // name, email, pan, aadhaar, location
if (!empty($userdata['name'])) $kyc_completion++;
if (!empty($userdata['email'])) $kyc_completion++;
if (!empty($userdata['pan'])) $kyc_completion++;
if (!empty($userdata['aadhaar'])) $kyc_completion++;
if (!empty($userdata['location'])) $kyc_completion++;

$kyc_percentage = ($kyc_completion / $total_fields) * 100;

$pan_verified = ($userdata['pan_verified'] ?? 'NO') === 'YES';
$aadhaar_verified = ($userdata['aadhaar_verified'] ?? 'NO') === 'YES';
$aadhaar_otp_sent = $_SESSION['aadhaar_otp_sent'] ?? false;
?>

<!-- Rest of the HTML code remains the same -->
<style>
    .kyc-card {
        border-left: 4px solid #007bff;
        transition: all 0.3s ease;
    }
    .kyc-card:hover {
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    .verification-badge {
        font-size: 0.8rem;
    }
    .progress-circle {
        position: relative;
        width: 120px;
        height: 120px;
        margin: 0 auto;
    }
    .progress-ring {
        transform: rotate(-90deg);
    }
    .progress-ring__circle {
        stroke: #e6e6e6;
        fill: transparent;
        stroke-width: 8;
    }
    .progress-ring__progress {
        stroke: #007bff;
        fill: transparent;
        stroke-width: 8;
        stroke-linecap: round;
        transition: stroke-dasharray 0.5s ease-in-out;
    }
    .logo-preview {
        border: 2px dashed #ddd;
        border-radius: 8px;
        padding: 20px;
        text-align: center;
        background: #f8f9fa;
    }
    .otp-section {
        border: 2px solid #007bff;
        border-radius: 8px;
        padding: 15px;
        background: #f0f8ff;
        margin-top: 10px;
    }
</style>

<div class="container-fluid mt-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="dashboard"><i class="fas fa-home"></i> Dashboard</a></li>
                            <li class="breadcrumb-item active">Profile Management</li>
                        </ol>
                    </nav>
                </div>
                <div class="text-end">
                    <span class="badge bg-<?php echo ($userdata['kycstatus'] ?? '') === 'Verified' ? 'success' : (($userdata['kycstatus'] ?? '') === 'Pending' ? 'warning' : 'danger'); ?> fs-6">
                        KYC Status: <?php echo htmlspecialchars($userdata['kycstatus'] ?? 'Not Started', ENT_QUOTES, 'UTF-8'); ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- KYC Status Card -->
        <div class="col-lg-4 mb-4">
            <div class="card kyc-card h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-shield-alt me-2"></i>KYC Status</h5>
                </div>
                <div class="card-body text-center">
                    <!-- KYC Progress Circle -->
                    <div class="progress-circle mb-3">
                        <svg class="progress-ring" width="120" height="120">
                            <circle class="progress-ring__circle" stroke="#e6e6e6" cx="60" cy="60" r="52"></circle>
                            <circle class="progress-ring__progress" cx="60" cy="60" r="52" 
                                    style="stroke-dasharray: <?php echo $kyc_percentage * 3.26; ?> 326;"></circle>
                        </svg>
                        <div class="position-absolute top-50 start-50 translate-middle">
                            <h4 class="mb-0"><?php echo round($kyc_percentage); ?>%</h4>
                            <small class="text-muted">Complete</small>
                        </div>
                    </div>

                    <!-- Verification Status -->
                    <div class="mb-3">
                        <div class="row">
                            <div class="col-6">
                                <span class="badge bg-<?php echo $pan_verified ? 'success' : 'secondary'; ?> verification-badge">
                                    <i class="fas fa-<?php echo $pan_verified ? 'check-circle' : 'times-circle'; ?>"></i> PAN
                                </span>
                            </div>
                            <div class="col-6">
                                <span class="badge bg-<?php echo $aadhaar_verified ? 'success' : 'secondary'; ?> verification-badge">
                                    <i class="fas fa-<?php echo $aadhaar_verified ? 'check-circle' : 'times-circle'; ?>"></i> Aadhaar
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- KYC Actions -->
                    <?php if (!$pan_verified || !$aadhaar_verified): ?>
                        <div class="d-flex flex-column">
                            <?php if (!$pan_verified && !empty($userdata['pan'])): ?>
                                <form method="POST" class="mb-2">
                                    <button type="submit" name="verify_kyc" value="1" class="btn btn-outline-primary w-100 btn-sm">
                                        <input type="hidden" name="kyc_type" value="pan">
                                        <i class="fas fa-id-card me-1"></i> Verify PAN
                                    </button>
                                </form>
                            <?php endif; ?>

                            <?php if (!$aadhaar_verified && !empty($userdata['aadhaar'])): ?>
                                <!-- FIXED: Show OTP section if OTP is sent -->
                                <?php if ($aadhaar_otp_sent): ?>
                                    <div class="otp-section">
                                        <h6 class="text-primary mb-2">
                                            <i class="fas fa-mobile-alt me-1"></i>Enter Aadhaar OTP
                                        </h6>
                                        <form method="POST">
                                            <div class="input-group mb-2">
                                                <input type="text" name="aadhaar_otp" class="form-control" 
                                                       placeholder="Enter 6-digit OTP" maxlength="6" required
                                                       pattern="[0-9]{6}" title="Please enter 6-digit OTP">
                                                <button type="submit" name="verify_aadhaar_otp" class="btn btn-success btn-sm">
                                                    <i class="fas fa-check me-1"></i>Verify
                                                </button>
                                            </div>
                                            <small class="text-muted">
                                                OTP is valid for 10 minutes. Check your registered mobile number.
                                            </small>
                                        </form>
                                        
                                        <!-- Resend OTP option -->
                                        <form method="POST" class="mt-2">
                                            <button type="submit" name="send_aadhaar_otp" class="btn btn-outline-secondary btn-sm w-100">
                                                <i class="fas fa-redo me-1"></i>Resend OTP
                                            </button>
                                        </form>
                                    </div>
                                <?php else: ?>
                                    <form method="POST" class="mb-2">
                                        <button type="submit" name="verify_kyc" value="1" class="btn btn-outline-primary w-100 btn-sm">
                                            <input type="hidden" name="kyc_type" value="aadhaar">
                                            <i class="fas fa-fingerprint me-1"></i> Verify Aadhaar
                                        </button>
                                    </form>
                                <?php endif; ?>
                            <?php endif; ?>

                            <?php if (!$pan_verified && !$aadhaar_verified && !empty($userdata['pan']) && !empty($userdata['aadhaar']) && !$aadhaar_otp_sent): ?>
                                <form method="POST" class="mb-2">
                                    <button type="submit" name="verify_kyc" value="1" class="btn btn-primary w-100 btn-sm">
                                        <input type="hidden" name="kyc_type" value="both">
                                        <i class="fas fa-shield-alt me-1"></i> Complete KYC
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>KYC Verification Complete!
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Profile Information -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-user-edit me-2"></i>Profile Information</h5>
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <!-- Basic Information (Read Only) -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Instance ID</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($userdata['instance_id'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" readonly>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Mobile Number</label>
                                <input type="text" name="mobile" class="form-control" value="<?php echo htmlspecialchars($userdata['mobile'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" readonly>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email Address</label>
                                <input type="email" class="form-control" value="<?php echo htmlspecialchars($userdata['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" readonly>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Full Name</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($userdata['name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" readonly>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Company Name</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($userdata['company'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" readonly>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    PAN Number 
                                    <?php if ($pan_verified): ?>
                                        <span class="badge bg-success ms-1"><i class="fas fa-check"></i> Verified</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning ms-1"><i class="fas fa-clock"></i> Pending</span>
                                    <?php endif; ?>
                                </label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($userdata['pan'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" readonly>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    Aadhaar Number 
                                    <?php if ($aadhaar_verified): ?>
                                        <span class="badge bg-success ms-1"><i class="fas fa-check"></i> Verified</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning ms-1"><i class="fas fa-clock"></i> Pending</span>
                                    <?php endif; ?>
                                </label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($userdata['aadhaar'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" readonly>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Location</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($userdata['location'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" readonly>
                            </div>
                        </div>

                        <hr>

                        <!-- Editable Settings -->
                        <h6 class="mb-3"><i class="fas fa-cogs me-2"></i>Account Settings</h6>
                        
                        <div class="row">
                            <!-- Logo Upload -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Profile Photo</label>
                                <input type="file" name="profile" class="form-control" accept="image/*" id="profileInput" hidden>
                                <!--<small class="form-text text-muted">Upload a 128x128 pixel image (JPG, PNG). Max size: 50KB.</small>-->
                                
                                <div class="logo-preview mt-2" id="profilePreview">
                                    <?php
                                    $profileVal = $userdata['profile'] ?? '';
                                    if (!empty($profileVal) && file_exists($profileVal)) {
                                        echo '<img src="' . htmlspecialchars($profileVal, ENT_QUOTES, 'UTF-8') . '" alt="Current Profile" class="img-thumbnail" style="max-width: 100px;">';
                                        echo '<p class="mb-0 mt-1"><small class="text-muted">Current Profile Photo</small></p>';
                                    } else {
                                        echo '<i class="fas fa-upload fa-2x text-muted mb-2"></i>';
                                        echo '<p class="mb-0"><small class="text-muted">No profile photo uploaded</small></p>';
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Company Logo</label>
                                <input type="file" name="logo" class="form-control" accept="image/*" id="logoInput">
                                <small class="form-text text-muted">Upload a 128x128 pixel image (JPG, PNG). Max size: 50KB.</small>
                                
                                <div class="logo-preview mt-2" id="logoPreview">
                                    <?php if (!empty($userdata['logo']) && file_exists($userdata['logo'])): ?>
                                        <img src="<?php echo htmlspecialchars($userdata['logo'], ENT_QUOTES, 'UTF-8'); ?>" alt="Current Logo" class="img-thumbnail" style="max-width: 100px;">
                                        <p class="mb-0 mt-1"><small class="text-muted">Current Logo</small></p>
                                    <?php else: ?>
                                        <i class="fas fa-upload fa-2x text-muted mb-2"></i>
                                        <p class="mb-0"><small class="text-muted">No logo uploaded</small></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-12 mb-3">
                                        <label class="form-label">OTP Required</label>
                                        <select name="is_otp" class="form-select">
                                            <option value="YES" <?php echo ($userdata['is_otp'] ?? '') === 'YES' ? 'selected' : ''; ?>>YES</option>
                                            <option value="NO" <?php echo ($userdata['is_otp'] ?? '') === 'NO' ? 'selected' : ''; ?>>NO</option>
                                        </select>
                                    </div>
                                    
                                    <div class="col-12 mb-3">
                                        <label class="form-label">WhatsApp Alerts</label>
                                        <select name="whatsapp_alert" class="form-select">
                                            <option value="YES" <?php echo ($userdata['whatsapp_alert'] ?? '') === 'YES' ? 'selected' : ''; ?>>YES</option>
                                            <option value="NO" <?php echo ($userdata['whatsapp_alert'] ?? '') === 'NO' ? 'selected' : ''; ?>>NO</option>
                                        </select>
                                    </div>
                                    
                                    <div class="col-12 mb-3">
                                        <label class="form-label">Email Alerts</label>
                                        <select name="email_alert" class="form-select">
                                            <option value="YES" <?php echo ($userdata['email_alert'] ?? '') === 'YES' ? 'selected' : ''; ?>>YES</option>
                                            <option value="NO" <?php echo ($userdata['email_alert'] ?? '') === 'NO' ? 'selected' : ''; ?>>NO</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="text-end">
                            <button type="submit" name="update_profile" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Show success/error messages
<?php if (!empty($success_message)): ?>
Swal.fire({
    title: 'Success!',
    text: '<?php echo addslashes($success_message); ?>',
    icon: 'success',
    confirmButtonText: 'OK'
}).then((result) => {
    if (result.isConfirmed) {
        location.reload();
    }
});
<?php endif; ?>

<?php if (!empty($upload_error) || !empty($kyc_error)): ?>
Swal.fire({
    title: 'Error!',
    text: '<?php echo addslashes($upload_error ?: $kyc_error); ?>',
    icon: 'error',
    confirmButtonText: 'OK'
});
<?php endif; ?>

// Logo upload preview and validation
document.getElementById('logoInput').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const previewDiv = document.getElementById('logoPreview');
    
    if (file) {
        // Validate file size (50KB)
        if (file.size > 50 * 1024) {
            Swal.fire('Error!', 'File size exceeds 50KB limit.', 'error');
            e.target.value = '';
            return;
        }

        // Validate file type
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        if (!allowedTypes.includes(file.type)) {
            Swal.fire('Error!', 'Only JPG, JPEG, PNG, and GIF files are allowed.', 'error');
            e.target.value = '';
            return;
        }

        // Create image preview
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = new Image();
            img.onload = function() {
                if (img.width !== 128 || img.height !== 128) {
                    Swal.fire('Error!', 'Logo must be exactly 128x128 pixels.', 'error');
                    document.getElementById('logoInput').value = '';
                    return;
                }

                // Show preview
                previewDiv.innerHTML = `
                    <img src="${e.target.result}" alt="Logo Preview" class="img-thumbnail" style="max-width: 100px;">
                    <p class="mb-0 mt-1"><small class="text-success">New logo selected</small></p>
                `;
            };
            img.src = e.target.result;
        };
        reader.readAsDataURL(file);
    }
});

// Profile upload preview and validation  
document.getElementById('profileInput').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const previewDiv = document.getElementById('profilePreview');
    
    if (file) {
        // Validate file size (50KB)
        if (file.size > 50 * 1024) {
            Swal.fire('Error!', 'File size exceeds 50KB limit.', 'error');
            e.target.value = '';
            return;
        }

        // Validate file type
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        if (!allowedTypes.includes(file.type)) {
            Swal.fire('Error!', 'Only JPG, JPEG, PNG, and GIF files are allowed.', 'error');
            e.target.value = '';
            return;
        }

        // Create image preview
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = new Image();
            img.onload = function() {
                if (img.width !== 128 || img.height !== 128) {
                    Swal.fire('Error!', 'Profile photo must be exactly 128x128 pixels.', 'error');
                    document.getElementById('profileInput').value = '';
                    return;
                }

                // Show preview
                previewDiv.innerHTML = `
                    <img src="${e.target.result}" alt="Profile Preview" class="img-thumbnail" style="max-width: 100px;">
                    <p class="mb-0 mt-1"><small class="text-success">New profile photo selected</small></p>
                `;
            };
            img.src = e.target.result;
        };
        reader.readAsDataURL(file);
    }
});

// Form validation
document.querySelector('form').addEventListener('submit', function(e) {
    if (e.submitter && e.submitter.name === 'update_profile') {
        const fileInput = document.getElementById('logoInput');
        if (fileInput.files.length > 0) {
            const file = fileInput.files[0];
            if (file.size > 50 * 1024) {
                e.preventDefault();
                Swal.fire('Error!', 'Please select a file under 50KB.', 'error');
                return;
            }
        }
    }
});

// OTP input validation (only numbers)
document.querySelector('input[name="aadhaar_otp"]')?.addEventListener('input', function(e) {
    this.value = this.value.replace(/[^0-9]/g, '');
});

// Auto-focus on OTP input when section is visible
<?php if ($aadhaar_otp_sent): ?>
setTimeout(function() {
    const otpInput = document.querySelector('input[name="aadhaar_otp"]');
    if (otpInput) {
        otpInput.focus();
    }
}, 500);
<?php endif; ?>
</script>

<?php
$conn->close();
?>