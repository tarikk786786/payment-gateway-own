<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Function to log messages to request_log.txt
function log_message($message) {
    $logFile = 'request_log.txt';
    $logEntry = "[" . date('Y-m-d H:i:s') . "] " . $message . PHP_EOL;
    file_put_contents($logFile, $logEntry, FILE_APPEND);
}

// Log the start of the process
// log_message("Received a new request.");

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Get the raw POST data
    $data = json_decode(file_get_contents('php://input'), true);
    // log_message("POST data received: " . json_encode($data));

    // Validate the required parameters
    if (!isset($data['order_id'])) {
        // Log the error
        // log_message("Error: Required parameters missing.");
        
        // Return an error response if required parameters are missing
        $response = [
            'status' => 'false',
            'message' => 'Required parameters missing'
        ];
        echo json_encode($response);
        exit();
    }

    // // Token validation (use your actual API token here)
    // $valid_token = "9856ce42fc26349fe5fab9c6b630e9c6";
    // if ($data['token'] !== $valid_token) {
    //     // Log invalid token error
    //     log_message("Error: Invalid API token.");
        
    //     $response = [
    //         'status' => 'false',
    //         'message' => 'Invalid API token'
    //     ];
    //     echo json_encode($response);
    //     exit();
    // }

    // Example of processing the payment (you can replace this with actual payment gateway logic)
    include "../auth/config.php";

    // Fetch the user_token for user with id 636

    $customer_mobile = $data['customer_mobile'];
    $order_id = $data['order_id'];
    $amount = $data['txn_amount'];
    $redirect_url = $data['redirect_url'];
    $user_token = $data['token'];

    // log_message("Customer mobile: $customer_mobile, Order ID: $order_id, Amount: $amount, Callback URL: $redirect_url");

    // Part 4 code
    $url = 'https://chickenpox.in/api/create-order';

    // Data to be sent in the POST request
    $postData = array(
        'customer_mobile' => $customer_mobile,
        'user_token' => $user_token,
        'amount' => $amount,
        'order_id' => $order_id,
        'redirect_url' => $redirect_url,
        'remark1' => 'test1',
        'remark2' => 'test2',
    );

    // Log the outgoing request
    // log_message("Sending POST request to $url with data: " . json_encode($postData));

    // Initialize cURL session
    $ch = curl_init();

    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Execute cURL session and store the response
    $response = curl_exec($ch);

    // Check for cURL errors
    if (curl_errno($ch)) {
        $curlError = curl_error($ch);
        // log_message("cURL Error: $curlError");
        echo 'cURL Error: ' . $curlError;
        exit();
    }

    // Close cURL session
    curl_close($ch);

    // Log the response from the API
    // log_message("Response received: $response");

    // Decode the JSON response
    $jsonResponse = json_decode($response, true);

    // Check if decoding was successful
    if ($jsonResponse !== null) {
        // Log the payment URL
// Example JSON response (the one you get from the API)
$jsonResponse = json_decode($response, true);

$status = $jsonResponse['status'];
$message = $jsonResponse['message'];
if ($status=="true") {
// Extract relevant data
$paymentUrl = $jsonResponse['result']['payment_url'];

$txnId = $jsonResponse['result']['orderId'];
// Extract UPI intent details
// $bhimUpi = $jsonResponse['result']['upi_intent']['bhim'];
// $phonepeUpi = $jsonResponse['result']['upi_intent']['phonepe'];
// $paytmUpi = $jsonResponse['result']['upi_intent']['paytm'];
// $gpayUpi = $jsonResponse['result']['upi_intent']['gpay'];
$bhimUpi = "test1";
$phonepeUpi = "test2";
$paytmUpi = "test3";
$gpayUpi = "test4";
// Prepare the response array
$response = [
    'status' => $status,
    'message' => $message,
    'results' => [
        'txn_id' => $txnId,
        'payment_url' => $paymentUrl,
        'upi_intent' => [
            'bhim' => $bhimUpi,
            'phonepe' => $phonepeUpi,
            'paytm' => $paytmUpi,
            'gpay' => $gpayUpi
        ]
    ]
];

}else{
    $response = [
    'status' => $status,
    'message' => $message,
];
}




// Return the response as JSON
echo json_encode($response, JSON_PRETTY_PRINT);

        
        // log_message("Redirecting to payment URL: $paymentUrl");

        // Redirect the user to the payment URL
        // header('Location: ' . $paymentUrl);
        exit();
    } else {
        // Log the error for debugging
        // log_message("Error: Failed to decode JSON response.");
        echo $response;
        exit();
    }

} else {
    // Log the invalid request method error
    // log_message("Error: Invalid request method.");

    // Return an error if the request method is not POST
    $response = [
        'status' => 'false',
        'message' => 'Invalid request method'
    ];
    echo json_encode($response);
}
?>
