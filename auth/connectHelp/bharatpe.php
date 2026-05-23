<?php
header('Content-Type: application/json');

$action = $_GET['action'] ?? null;
$baseUrl = 'https://enterprise.bharatpe.in';
$mobile = $_GET['mobile'] ?? null;
$uuid = $_GET['uuid'] ?? null;
$otp = $_GET['otp'] ?? null;
$token = $_GET['token'] ?? null;
if (!$action) {
    echo json_encode([ 'message' => 'Well-come To BharatPe Enter Your Registerd mobile number & Verify With OTP']);
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
        CURLOPT_USERAGENT => 'Mozilla/5.0',
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_CONNECTTIMEOUT => 15,
        CURLOPT_COOKIEJAR => $cookieFileToUse,
        CURLOPT_COOKIEFILE => $cookieFileToUse
    ]);

    $headers = ['accept-language: en-US,en;q=0.9'];
    if ($isInitialGet) {
        $headers[] = 'upgrade-insecure-requests: 1';
    } else {
        $headers = array_merge($headers, [
            'accept: application/json',
            'origin: https://enterprise.bharatpe.in',
            'referer: https://enterprise.bharatpe.in/',
            'x-requested-with: XMLHttpRequest',
            'content-type: application/x-www-form-urlencoded; charset=UTF-8',
        ]);
    }

    if ($postData) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    }

    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $response = curl_exec($ch);
    return ['response' => $response, 'http_code' => curl_getinfo($ch, CURLINFO_HTTP_CODE), 'error' => curl_error($ch)];
}
    if (file_exists($cookieFile)) unlink($cookieFile);

    // Step 1: Get CSRF token
    $res = executeCurlRequest($baseUrl . '/', $cookieFile, null, [], true);
    preg_match('/<input[^>]*name="_token"[^>]*value="([^"]+)"/', $res['response'], $matches);
    $_token = $matches[1] ?? null;


if ($action === 'send') {
    // Remove old cookie file

    if (!$_token) {
        echo json_encode(['success' => false, 'message' => 'Failed to fetch CSRF token']);
        exit;
    }

    // Step 2: Send OTP
    $postData = ['mobile' => $mobile, '_token' => $_token];
    $res = executeCurlRequest($baseUrl . '/v1/api/user/requestotp', $cookieFile, $postData);
    $response = json_decode($res['response'], true);

    if ($response['success'] && isset($response['data']['uuid'])) {
        echo json_encode([
            'success' => true,
            'message' => 'OTP sent',
            'uuid' => $response['data']['uuid'],
            'token' => $_token
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'OTP request failed', 'response' => $response]);
    }

} elseif ($action === 'verify') {
    // Validation
    if (!$uuid || !$otp || !$token) {
        echo json_encode(['success' => false, 'message' => 'Missing OTP, UUID, or Token']);
        exit;
    }

    if (!preg_match('/^\d{4,6}$/', $otp)) {
        echo json_encode(['success' => false, 'message' => 'Invalid OTP format']);
        exit;
    }

    // Verify OTP
    $postData = [
        'mobile' => $mobile,
        'uuid' => $uuid,
        'otp' => $otp,
        '_token' => $_token
    ];

    $res = executeCurlRequest($baseUrl . '/v1/api/user/verifyotp', $cookieFile, $postData);
    $response = json_decode($res['response'], true);
    


    if ($response['success']) {
        // Fetch merchant info
        $midRes = executeCurlRequest($baseUrl . '/v1/api/brandMerchants', $cookieFile);
        $midData = json_decode($midRes['response'], true);
        $merchant = $midData['data'][0] ?? [];

        // Clean cookie (remove comment lines)
        $cookieText = file_exists($cookieFile) ? file_get_contents($cookieFile) : '';
        $cookieTextCleaned = implode("\n", array_filter(
            explode("\n", $cookieText),
            fn($line) => trim($line) !== '' && strpos(trim($line), '#') !== 0
        ));
        
        // $randomToken = bin2hex(random_bytes(16)); // 32-character hexadecimal token


        echo json_encode([
            'success' => true,
            'message' => 'OTP verified',
            'merchant_id' => $merchant['id'] ?? null,
            'business_name' => $merchant['business_name'] ?? '',
            'token' => $response['data']['accessToken'],
            'cookie_text' => $cookieTextCleaned
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'OTP verification failed', 'response' => $response]);
    }

} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
