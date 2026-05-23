<?php
include "config.php"; // Database connection

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

if (isset($data['id']) && isset($data['order_id']) && isset($data['user_token'])) {
    $id = $data['id'];
    $order_id = $data['order_id'];
    $user_token = $data['user_token'];
    $byteTransactionId = $data['byteTransactionId'] ?? '';
    
    // Call to status_check.php to get status
    $url = "https://$server/pages/status_check.php";
    $postData = [
        'order_id' => $order_id,
        'user_token' => $user_token,
        'description' => '1',
        'byteTransactionId' => $byteTransactionId
    ];
    
    // Initialize cURL session
    $ch = curl_init($url);
    
    // Set cURL options
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    // Execute cURL request and get response
    $response = curl_exec($ch);
    // echo($response);
    // Check for cURL errors
    if (curl_errno($ch)) {
        echo json_encode(['success' => false, 'error' => 'cURL Error: ' . curl_error($ch)]);
        curl_close($ch);
        exit;
    }
    
    // Close cURL session
    curl_close($ch);
    
    // Decode the JSON response
     $statusData = json_decode($response, true);

     // Check if status is success
     if (isset($statusData['status']) && strtolower($statusData['status']) === 'success') {
         // Get UTR from API response if available
         $utr = '';
         $apiResp = $statusData['apiResp'];
         
         // Try to extract UTR from apiResp (which could be a JSON string or already decoded array)
         if (is_string($apiResp)) {
             $apiRespData = json_decode($apiResp, true);
             if (json_last_error() === JSON_ERROR_NONE && isset($apiRespData['utr'])) {
                 $utr = $apiRespData['utr'];
             }
         } elseif (is_array($apiResp) && isset($apiResp['utr'])) {
             $utr = $apiResp['utr'];
         }
         
         // Update the database with success status
         $query = "UPDATE orders SET status = 'SUCCESS', webhook_sent = 'no'";
         
         // Add UTR if available
         if (!empty($utr)) {
             $utr = mysqli_real_escape_string($conn, $utr);
             $query .= ", utr = '$utr'";
         }
         
         $query .= " WHERE id = '$id' AND order_id = '$order_id'";
         
         if (mysqli_query($conn, $query)) {
             echo json_encode(['success' => true, 'message' => $statusData['message'], 'status' => $statusData['status']]);
         } else {
             echo json_encode(['success' => false, 'error' => mysqli_error($conn)]);
         }
     } else {
         // Return the status check response to the client
         echo json_encode(['success' => true, 'data' => $statusData]);
     }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid input data']);
}
?>
