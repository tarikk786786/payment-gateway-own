<?php
header('Content-Type: application/json');

$action = $_GET['action'] ?? null;
$baseUrl = 'https://bapa-api.quintustech.in';
$mobile = $_GET['mobile'] ?? null;
$uuid = $_GET['uuid'] ?? null;
$otp = $_GET['otp'] ?? null;

// Use mobile to uniquely identify temp cookie file
if (!$action) {
    echo json_encode([ 'message' => 'Well-come To quintustespay Enter Your Registerd mobile number & Verify With OTP']);
    exit;
}
// Use mobile to uniquely identify temp cookie file
if (!$mobile || !preg_match('/^\d{10}$/', $mobile)) {
    echo json_encode(['success' => false, 'message' => 'Invalid or missing mobile number']);
    exit;
}
$cookieFile = sys_get_temp_dir() . "/bharatpe_cookies_" . $mobile . ".txt";

// --- Common cURL function ---
function executeCurlRequest($url, $cookieFileToUse, $postData = null, $customHeaders = [], $isInitialGet = false) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36',
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_CONNECTTIMEOUT => 15,
        CURLOPT_COOKIEJAR => $cookieFileToUse,
        CURLOPT_COOKIEFILE => $cookieFileToUse
    ]);

    $headers = array_merge([
        'accept-language: en-US,en;q=0.9',
        'Referer: https://enterprise.bharatpe.in/'
    ], $customHeaders);

    if ($postData) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
        $headers[] = 'content-type: application/x-www-form-urlencoded; charset=UTF-8';
    }

    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $response = curl_exec($ch);
    return ['response' => $response, 'http_code' => curl_getinfo($ch, CURLINFO_HTTP_CODE), 'error' => curl_error($ch)];
}

// Reset cookie file for a new session
if (file_exists($cookieFile)) unlink($cookieFile);

// Initial GET to get CSRF token and set session cookies
$res = executeCurlRequest($baseUrl . '/', $cookieFile, null, [], true);
preg_match('/<input[^>]*name="_token"[^>]*value="([^"]+)"/', $res['response'], $matches);
$_token = $matches[1] ?? null;

if ($action === 'send') {
    // Send OTP
    $postData = ['authid' => $mobile, '_token' => $_token];
    $res = executeCurlRequest($baseUrl . '/api/qt/user/sendOtp', $cookieFile, $postData);
    $response = json_decode($res['response'], true);

    if ($response && $response['success'] && $response['message'] == "OTP SENT SUCCESSFULLY") {
        echo json_encode([
            'success' => true,
            'message' => 'OTP sent',
            'authid' => $response['authid']
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'OTP request failed', 'response' => $response, 'debug_info' => $res]);
    }

} elseif ($action === 'verify') {
    // Validation
    if (!$otp) {
        echo json_encode(['success' => false, 'message' => 'Missing OTP']);
        exit;
    }
    if (!preg_match('/^\d{4,6}$/', $otp)) {
        echo json_encode(['success' => false, 'message' => 'Invalid OTP format']);
        exit;
    }

    // Verify OTP
    $postData = [
        'authid' => $mobile,
        'otp' => $otp,
        '_token' => $_token
    ];
    $res = executeCurlRequest($baseUrl . '/api/qt/user/verifyOtp', $cookieFile, $postData);
    $response = json_decode($res['response'], true);

    if ($response && $response['success']) {
        $accessToken = $response['accessToken'];

        // Add the Authorization header for subsequent requests
        $customHeaders = ["Authorization: Bearer " . $accessToken];

        // Fetch merchant info (which contains the VPA)
        $midRes = executeCurlRequest($baseUrl . '/api/qt/user/fetchQr', $cookieFile, null, $customHeaders);
        $midData = json_decode($midRes['response'], true);

        // Correctly access the VPA from the object
        $vpa = $midData['data']['vpa'] ?? null;

        if ($vpa) {
            echo json_encode([
                'success' => true,
                'message' => 'OTP verified and VPA fetched',
                'authid' => $mobile,
                'vpa' => $vpa,
                'accessToken' => $accessToken
            ]);
        } else {
            // Updated error message to reflect the new structure
            echo json_encode(['success' => false, 'message' => 'VPA not found in response', 'response' => $midData]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'OTP verification failed', 'response' => $response]);
    }

} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>