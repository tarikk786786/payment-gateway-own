<?php
// Set the content type to JSON
header('Content-Type: application/json');

// Load allowed IPs from the file
$allowed_ips_file = 'server_ip.php'; // File ka naam jisme IPs hongi

if (file_exists($allowed_ips_file)) {
    $allowed_ips = include $allowed_ips_file; // File include karke IPs load karna
    if (!is_array($allowed_ips)) {
        $allowed_ips = []; // Agar file galat format me ho to default empty array
    }
} else {
    $allowed_ips = []; // Agar file nahi mili to default empty array
}

// Get the client's IP address
$client_ip = $_SERVER['REMOTE_ADDR'];

// Check if the client's IP is allowed
if (!in_array($client_ip, $allowed_ips)) {
    echo json_encode(['error' => 'Unauthorized request.']);
    exit;
}

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve the POST data
    $inputData = json_decode(file_get_contents('php://input'), true);

    // Validate the input
    if (!isset($inputData['data']) || empty($inputData['data'])) {
        echo json_encode(['error' => 'No data provided for QR code.']);
        exit;
    }

    // API URL
    $url = 'https://' . $_SERVER['HTTP_HOST'] . '/secret/Qrcode/do.php';

    // Data to be sent in the POST request
    $data = [
        'data' => $inputData['data'], // Data for the QR code
        'ecc' => isset($inputData['ecc']) ? $inputData['ecc'] : 'M', // Error correction level
        'size' => isset($inputData['size']) ? $inputData['size'] : 8  // Size of the QR code
    ];

    // Convert the data array into a URL-encoded query string
    $postData = http_build_query($data);

    // Initialize cURL session
    $ch = curl_init($url);

    // Set cURL options
    curl_setopt($ch, CURLOPT_POST, true);  // Set method to POST
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  // Return the response as a string
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);  // Send data as application/x-www-form-urlencoded

    // Execute the cURL request
    $response = curl_exec($ch);

    // Check for errors
    if (curl_errno($ch)) {
        echo json_encode(['error' => 'Error: ' . curl_error($ch)]);
        exit;
    }

    // Decode the JSON response
    $result = json_decode($response, true);

    // Close the cURL session
    curl_close($ch);

    // Check if there is an error in the response
    if (isset($result['error'])) {
        echo json_encode(['error' => 'Error: ' . $result['error']]);
    } else {
        // Success! The QR code is in base64 format.
        $qrCodeBase64 = $result['qr_code'];
        echo json_encode(['qr_code' => $qrCodeBase64]);
    }
} else {
    // Handle unsupported request methods
    echo json_encode(['error' => 'Unsupported request method.']);
}
?>
