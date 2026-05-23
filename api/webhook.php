<?php
// Webhook Listener for Razorpay
header("Content-Type: application/json");

require_once '../db.php'; // Database connection

// Your Razorpay Webhook Secret
$webhook_secret = $_ENV['RAZORPAY_WEBHOOK_SECRET'] ?? 'my_secure_webhook_secret_123';

// Read the raw POST data
$payload = file_get_contents('php://input');

// Get the Razorpay signature from the headers
$razorpay_signature = $_SERVER['HTTP_X_RAZORPAY_SIGNATURE'] ?? '';

if (empty($payload) || empty($razorpay_signature)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid payload or missing signature']);
    exit();
}

// Verify the signature
$expected_signature = hash_hmac('sha256', $payload, $webhook_secret);

if (!hash_equals($expected_signature, $razorpay_signature)) {
    // Log invalid webhook attempt for security monitoring
    $stmt = $conn->prepare("INSERT INTO `webhook_logs` (`event_type`, `payload`, `status`, `created_at`) VALUES ('invalid_signature', ?, 'failed', NOW())");
    if ($stmt) {
        $stmt->bind_param("s", $payload);
        $stmt->execute();
        $stmt->close();
    }

    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Invalid signature']);
    exit();
}

// Parse the payload
$event = json_decode($payload, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid JSON']);
    exit();
}

$event_type = $event['event'] ?? 'unknown';

// Log the valid webhook
$stmt = $conn->prepare("INSERT INTO `webhook_logs` (`event_type`, `payload`, `status`, `created_at`) VALUES (?, ?, 'processed', NOW())");
if ($stmt) {
    $stmt->bind_param("ss", $event_type, $payload);
    $stmt->execute();
    $stmt->close();
}

// Process the event
switch ($event_type) {
    case 'payment.captured':
        $payment_id = $event['payload']['payment']['entity']['id'];
        $order_id = $event['payload']['payment']['entity']['order_id'];
        $amount = $event['payload']['payment']['entity']['amount'] / 100; // Razorpay sends in paise
        
        // Update database (order status to SUCCESS)
        $update_stmt = $conn->prepare("UPDATE `orders` SET `status` = 'SUCCESS', `transaction_id` = ? WHERE `order_id` = ?");
        if ($update_stmt) {
            $update_stmt->bind_param("ss", $payment_id, $order_id);
            $update_stmt->execute();
            $update_stmt->close();
        }
        break;

    case 'payment.failed':
        $payment_id = $event['payload']['payment']['entity']['id'];
        $order_id = $event['payload']['payment']['entity']['order_id'];
        
        // Update database (order status to FAILURE)
        $update_stmt = $conn->prepare("UPDATE `orders` SET `status` = 'FAILURE' WHERE `order_id` = ?");
        if ($update_stmt) {
            $update_stmt->bind_param("s", $order_id);
            $update_stmt->execute();
            $update_stmt->close();
        }
        break;

    default:
        // Unhandled events
        break;
}

// Always return 200 OK to acknowledge receipt of the webhook
http_response_code(200);
echo json_encode(['status' => 'ok']);
?>
