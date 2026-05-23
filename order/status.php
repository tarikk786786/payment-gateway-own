<?php
include "../auth/config.php";
// request_log.txt mein write karne ke liye path
$log_file = 'request_log.txt';

// Function to log messages
function log_message($message) {
    global $log_file;
    $timestamp = date('Y-m-d H:i:s'); // Current timestamp
    file_put_contents($log_file, "[$timestamp] $message\n", FILE_APPEND);
}

// Function to log full request details
function log_request_details() {
    // Log request method
    // log_message("Request Method: " . $_SERVER['REQUEST_METHOD']);

    // Log request body (for POST requests)
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Log raw input (useful for JSON or other formats)
        $raw_input = file_get_contents('php://input');
        // log_message("Raw Input: " . $raw_input); // Log the raw input
        
        // Decode the JSON request body
        $decoded_body = json_decode($raw_input, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            log_message("Error decoding JSON: " . json_last_error_msg());
        } else {
            // log_message("Decoded JSON: " . print_r($decoded_body, true)); // Log decoded JSON body
        }
        return $decoded_body; // Return the decoded body for further processing
    } else {
        log_message("Request is not POST. Skipping body logging.");
    }
    return null; // Return null for non-POST requests
}

// Handle the incoming request
// log_message("Starting request handling."); // Start logging
$request_data = log_request_details(); // Log the full request details and get the decoded body

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $request_data) {
    // Capture parameters from the decoded JSON
    $user_token = $request_data['token'] ?? null;
    $order_id = $request_data['order_id'] ?? null;

    // Log received parameters
    // log_message("Received parameters - user_token: $user_token, order_id: $order_id");

    // Check if required parameters are present
    if ($user_token && $order_id) {
        // Log that we are attempting a database query
        // log_message("Attempting to query the database for user_token: $user_token and order_id: $order_id");

        // API URL
        $api_url = 'https://' . $_SERVER['HTTP_HOST'] . '/api/check-order-status';

        // Form-encoded payload data
        $post_data = [
            'user_token' => $user_token,
            'order_id' => $order_id
        ];

        // Initialize cURL session
        $ch = curl_init($api_url);

        // Set cURL options
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data)); // to format POST data
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded'
        ]);

        // Execute the cURL session and capture the response
        $api_response = curl_exec($ch);

        // Check for cURL errors
        if (curl_errno($ch)) {
            echo 'cURL Error: ' . curl_error($ch);
        } else {
            // Decode the API response
            $api_data = json_decode($api_response, true);
            if (json_last_error() === JSON_ERROR_NONE && $api_data['status'] === true) {
                // Map the API response to your desired format
                $mapped_response = [
                    "status" => true,
                    "message" => "Transaction Details",
                    "results" => [
                        "txn_id" => rand(10000000, 99999999), // Random transaction ID
                        "order_id" => $api_data['result']['orderId'],
                        "merchant_id" => 9, // Static value for merchant ID
                        "merchant_name" => "SBI Merchant",
                        "merchant_vpa" => "HSBIMOPAD.YMXXXXX-0XXXXXX0000@sbi",
                        "txn_date" => $api_data['result']['date'],
                        "txn_amount" => $api_data['result']['amount'],
                        "txn_note" => "Pay For Gateway", // Static value for txn_note
                        "product_name" => "One Plus", // Static value for product_name
                        "customer_name" => "Customer Name", // Static customer name
                        "customer_mobile" => 9012345678, // Static customer mobile
                        "customer_email" => "customer@gmail.com", // Static customer email
                        "customer_vpa" => "customer@sbi", // Static customer VPA
                        "bank_orderid" => "IT23051017552064734296", // Static bank order ID
                        "utr_number" => $api_data['result']['utr'] ?? "N/A", // UTR number or default
                        "payment_mode" => "UPI", // Static payment mode
                        "status" => $api_data['result']['txnStatus'] === "SUCCESS" 
                             ? "Success" 
                             : ($api_data['result']['txnStatus'] === "PENDING" ? "Pending" : "Failure")
                    ]
                ];

                // Send the mapped response
                header('Content-Type: application/json');
                echo json_encode($mapped_response);
                log_message("Sending mapped response: " . json_encode($mapped_response));
            } else {
                // Log and send an error if the response is invalid
                $error_response = json_encode(['status' => false, 'message' => 'Error in API response', 'results' => []]);
                log_message("Error: Invalid API response");
                header('Content-Type: application/json');
                echo $error_response;
            }
        }

        // Close the cURL session
        curl_close($ch);       

    } else {
        $response_data = json_encode(['status' => false, 'message' => 'Missing user_token or order_id', 'results' => []]);
        log_message('Error: Missing user_token or order_id');
    }

    // Return response data
    header('Content-Type: application/json');
    // log_message("Sending response: " . $response_data);
    echo $response_data;
} else {
    log_message("Received request with method: " . $_SERVER['REQUEST_METHOD'] . ". No data to process.");
}
?>
