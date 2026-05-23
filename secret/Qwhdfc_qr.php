<?php
// Set the content type to JSON
header('Content-Type: application/json');

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve the POST data
    $inputData = json_decode(file_get_contents('php://input'), true);

    // Validate the input
    if (!isset($inputData['session_id']) || empty($inputData['session_id'])) {
        echo json_encode(['error' => 'No data provided for QR code.']);
        exit;
    }
    if (!isset($inputData['description']) || empty($inputData['description'])) {
        echo json_encode(['error' => 'No data provided for QR code.']);
        exit;
    }
    if (!isset($inputData['description']) || empty($inputData['apptxn_id'])) {
        echo json_encode(['error' => 'No data provided for QR code.']);
        exit;
    }
    if (!isset($inputData['tid_list']) || empty($inputData['tid_list'])) {
        echo json_encode(['error' => 'No data provided for QR code.']);
        exit;
    }

    // API URL
    $url = 'https://pay.imb.org.in/Qrcode/hdfc_qr.php';

    // Data to be sent in the POST request
   

    // Convert the data array into a URL-encoded query string
    $postData = json_encode($inputData);

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
