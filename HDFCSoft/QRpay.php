<?php


// Validate and sanitize user inputs from $_GET
function sanitizeInput($input) {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

$tidList = isset($_GET['terminalId']) ? sanitizeInput($_GET['terminalId']) : '';
$amount = isset($_GET['amount']) ? sanitizeInput($_GET['amount']) : '';
$dis = isset($_GET['description']) ? sanitizeInput($_GET['description']) : '';
$cnumber = isset($_GET['customerMobileNumber']) ? sanitizeInput($_GET['customerMobileNumber']) : '';
$apptxn_id = isset($_GET['appTxnid']) ? sanitizeInput($_GET['appTxnid']) : '';
$sessionid = isset($_GET['sessionid']) ? sanitizeInput($_GET['sessionid']) : '';

// Function to fetch public key from the URL
// function fetchPublicKeyFromURL($url) {
//     $public_key = file_get_contents($url); // Fetch the public key from the URL
//     if (!$public_key) {
//         return ['error' => 'Failed to fetch public key from URL'];
//     }

//     // Format the public key correctly
//     $pemFormattedKey = "-----BEGIN PUBLIC KEY-----\n";
//     $pemFormattedKey .= chunk_split($public_key, 64, "\n");  // Split the key into chunks of 64 characters
//     $pemFormattedKey .= "-----END PUBLIC KEY-----\n";
//     return $pemFormattedKey;
// }

// Fetch the public key from the provided URL
$public_key_url = "https://hdfcmmp.mintoak.com/OneAppAuth/getKey";  // URL to fetch the public key
$public_key = file_get_contents('public.key');

if (isset($public_key['error'])) {
    echo json_encode(['status' => false, 'error' => $public_key['error']]);
    exit;
}

// Function to send requests securely using cURL
function request($url, $data0, $type, $headers, $yes) {
    $typee = "CURLOPT_" . strtoupper($type);  // Ensure the type is valid
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, constant($typee), 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);  // Ensure SSL certificate is verified
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);  // Ensure SSL certificate host is verified
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
    curl_setopt($ch, CURLOPT_HEADER, $yes);
    $output1 = curl_exec($ch);
    if (curl_errno($ch)) {
        // Handle cURL errors
        return ['error' => curl_error($ch)];
    }
    return $output1;
}

// RSA encryption function
function RsaPcs1($data, $public_key) {
    if (!$public_key) {
        return ['error' => 'Public key not found'];
    }
    openssl_public_encrypt($data, $rsa_key, $public_key, OPENSSL_PKCS1_OAEP_PADDING);
    return base64_encode($rsa_key);
}

// Encrypt data using AES-128-GCM
function encrypt($data, $key, $iv) {
    $tag = '';
    $encrypted = openssl_encrypt(
        $data,
        'aes-128-gcm',
        $key,
        OPENSSL_RAW_DATA,
        $iv,
        $tag,
        '', 16
    );
    return base64_encode($encrypted . $tag);
}

// Decrypt function using AES-128-GCM
function decrypt($data, $key, $iv) {
    $data = base64_decode($data);
    $tag = substr($data, -16);  // The last 16 bytes are the tag
    $data = substr($data, 0, -16);  // The remaining is the encrypted data

    try {
        return openssl_decrypt(
            $data,
            'aes-128-gcm',
            $key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );
    } catch (\Exception $e) {
        return false;
    }
}

// AES key and IV generation
$aeskey = random_bytes(16);  // Secure key generation
$aesiv = random_bytes(16);   // Secure IV generation

// Example data encryption
$encPayload = encrypt('{
    "terminalId": "' . $tidList . '",
    "amount": "' . $amount . '.00",
    "description": "' . $dis . '",
    "customerMobileNumber": "' . $cnumber . '",
    "appTxnid": "2560' . $apptxn_id . '",
    "pgId": 1,
    "redemptionId": []
}', $aeskey, $aesiv);

// Encrypt the AES key with the fetched public key
$key = RsaPcs1($aeskey, $public_key);
$iv = base64_encode($aesiv);

// Secure the API URL and headers
$url = "https://hdfcmmp.mintoak.com/HDFC/OneApp/QRPay";
$data0 = json_encode([
    "KEY" => $key,
    "IV" => $iv,
    "PAYLOAD" => $encPayload
]);
$headers = [
    "Host: hdfcmmp.mintoak.com",
    "motoken: ",
    "sessionid: $sessionid",
    "content-type: application/json",
    "accept-encoding: gzip",
    "user-agent: okhttp/4.9.1"
];

// Make the request
$userdetils = request($url, $data0, 'POST', $headers, 0);


if (isset($userdetils['error'])) {
    echo json_encode(['status' => false, 'error' => $userdetils['error']]);
    exit;
}

$userdetils1 = decrypt($userdetils, $aeskey, $aesiv);
$decoded_details = json_decode($userdetils1, true);

// Base64 encode the response to send back as QR image data
$base64Encoded = base64_encode($userdetils);

// Sending the response in the required format
header('Content-Type: application/json');
echo json_encode([
    'status' => 'success',
    'qr_code' => 'data:image/png;base64,' . $base64Encoded,
    'qr_data' => "$userdetils1"
]);

?>
