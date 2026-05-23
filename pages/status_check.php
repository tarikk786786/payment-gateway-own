<?php
// PhonePay Payment Page
date_default_timezone_set("Asia/Kolkata");

// Define the base directory constant
define('ROOT_DIR', realpath(dirname(__FILE__)) . '/../');

// Securely include files using the ROOT_DIR constant
include ROOT_DIR . 'pages/dbFunctions.php';
include ROOT_DIR . 'pages/dbInfo.php';
include ROOT_DIR . 'auth/config.php';

header('Content-Type: application/json'); // JSON रिस्पॉन्स सेट करें

if (!isset($_POST['order_id'])) {
    echo json_encode(["status" => "error", "message" => "Invalid Order ID"]);
    exit;
}
$order_id = $_POST['order_id'];
$user_token = $_POST['user_token'];
$byteTransactionId = $_POST['byteTransactionId'];
// echo("user_token=".$user_token);

if (!$byteTransactionId && $order_id) {
$row = db_select($conn, "orders", "*", "order_id='$order_id'");
$rows = [];
while ($rowData = mysqli_fetch_assoc($row)) {
    $rows[] = $rowData;
}
$result = ["Rows" => $rows];

// यह मानते हुए कि byteTransactionId पहले ऑर्डर में मौजूद है
$byteTransactionId = !empty($rows) ? $rows[0]['byteTransactionId'] ?? null : null;
}

if (isset($_POST['description'])) {
    $description = $_POST['description'];
}else{
    $description = "1";
}



// echo($description);


// ऑर्डर की पेमेंट method निकालें
$sql = "SELECT method FROM orders WHERE order_id = ? AND user_token = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $order_id,$user_token);
$stmt->execute();
$stmt->bind_result($method);
$stmt->fetch();
$stmt->close();

if (!$method) {
    echo json_encode(["status" => "error", "message" => "Order ID not found"]);
    exit;
}

function performPostRequest($url, $postData) {
    // cURL session initialize करें
    $ch = curl_init();

    // URL सेट करें
    curl_setopt($ch, CURLOPT_URL, $url);
    // POST मेथड सेट करें
    curl_setopt($ch, CURLOPT_POST, true);
    // POST डेटा सेट करें; http_build_query() POST array को URL encoded string में बदल देता है
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    // Response को string के रूप में return करने के लिए
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // SSL certificate verification बंद करना (development/testing के लिए; production में सक्षम करें)
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    // Request execute करें और response प्राप्त करें
    $response = curl_exec($ch);
    // यदि कोई error हो, तो उसे handle करें
    if (curl_errno($ch)) {
        $error = curl_error($ch);
        curl_close($ch);
        return json_encode(["status" => "error", "message" => "cURL Error: " . $error]);
    }
    // cURL session close करें
    curl_close($ch);
    // Response return करें
    return $response;
}

function statusCheck($order_id, $method, $description = '', $byteTransactionId = '', $user_token = '') {
    // Validate input parameters
    if (empty($order_id)) {
        return json_encode([
            "status" => "error",
            "method" => $method,
            "message" => "Order ID is required",
            "apiResp" => ""
        ]);
    }
    if (empty($method)) {
        return json_encode([
            "status" => "error",
            "method" => "",
            "message" => "Payment method is required",
            "apiResp" => ""
        ]);
    }

    $url = "";
    $postData = ["order_id" => $order_id,"user_token" => $user_token];

    switch ($method) {
        case 'SBI':
            $url = 'https://' . $_SERVER["SERVER_NAME"] . '/order7/payment-status';
            break;
        case 'Hdfc':
            $url = 'https://' . $_SERVER["SERVER_NAME"] . '/order/payment-status';
            break;
        case 'Phonepe':
            if (empty($description)) {
                return json_encode([
                    "status" => "error",
                    "method" => $method,
                    "message" => "Description is required for PhonePe",
                    "apiResp" => ""
                ]);
            }
            $url = 'https://' . $_SERVER["SERVER_NAME"] . '/order2/payment-status';
            $postData = ['byte_order_status' => $byteTransactionId,'user_token' => $user_token];
            break;
        case 'MOBIKWIK':
            if (empty($user_token)) {
                return json_encode([
                    "status" => "error",
                    "method" => $method,
                    "message" => "User token is required for Bharatpe",
                    "apiResp" => ""
                ]);
            }
            $url = 'https://' . $_SERVER["SERVER_NAME"] . '/order8/payment-statusAuto';
            $postData = ['order_id' => $order_id,'user_token' => $user_token];
            break;
        case 'Googlepay':
            if (empty($user_token)) {
                return json_encode([
                    "status" => "error",
                    "method" => $method,
                    "message" => "User token is required for Bharatpe",
                    "apiResp" => ""
                ]);
            }
            $url = 'https://' . $_SERVER["SERVER_NAME"] . '/order5/payment-status';
            $postData = ['order_id' => $order_id,'user_token' => $user_token];
            break;
        case 'Paytm':
            if (empty($byteTransactionId)) {
                return json_encode([
                    "status" => "error",
                    "method" => $method,
                    "message" => "Transaction ID is required for Paytm",
                    "apiResp" => ""
                ]);
            }
            $url = 'https://' . $_SERVER["SERVER_NAME"] . '/order3/payment-status';
            $postData = ['byte_order_status' => $byteTransactionId,'order_id' => $order_id,'user_token' => $user_token];
            break;
        case 'Bharatpe':
            if (empty($user_token)) {
                return json_encode([
                    "status" => "error",
                    "method" => $method,
                    "message" => "User token is required for Bharatpe",
                    "apiResp" => ""
                ]);
            }
            $url = 'https://' . $_SERVER["SERVER_NAME"] . '/order4/payment-statusAuto';
            $postData = ['order_id' => $order_id,'user_token' => $user_token];
            break;
        case 'Freecharge':
            if (empty($user_token)) {
                return json_encode([
                    "status" => "error",
                    "method" => $method,
                    "message" => "User token is required for FreeCharge",
                    "apiResp" => ""
                ]);
            }
            $url = 'https://' . $_SERVER["SERVER_NAME"] . '/order6/payment-status';
            $postData = ['order_id' => $order_id,'user_token' => $user_token];
            break;
        case 'MANUAL':
            return json_encode([
                "status" => "MANUAL",
                "method" => $method,
                "message" => "Manual Mode Please Make Payment & Submit UTR Number",
                "apiResp" => ""
            ]);
        case 'Amazon':
            $url = 'https://' . $_SERVER["SERVER_NAME"] . '/order92/payment-status';
            $postData = ['byte_order_status' => $order_id,'user_token' => $user_token];
            break;
        case 'Paynearby':
            $url = 'https://' . $_SERVER["SERVER_NAME"] . '/order93/payment-statusAuto';
            $postData = ['order_id' => $order_id,'user_token' => $user_token
            ];
            break;
        case 'Quintuspay':
            $url = 'https://' . $_SERVER["SERVER_NAME"] . '/order95/payment-status';
            $postData = ['order_id' => $order_id,'user_token' => $user_token];
            break;
        default:
            return json_encode([
                "status" => "error",
                "method" => $method,
                "message" => "No specific status check logic defined for Merchant: $method",
                "apiResp" => ""
            ]);
    }

    if ($url) {
    $response = performPostRequest($url, $postData);
    // echo($response);
    $decodedResponse = json_decode($response, true);
    $normalizedStatus = '';

    // Check if the response is a valid JSON and has a 'status' key
    if (json_last_error() === JSON_ERROR_NONE && isset($decodedResponse['status'])) {
        // If it's a JSON object, use its 'status' field
        $normalizedStatus = strtolower(trim($decodedResponse['status']));
    } else {
        // Otherwise, treat the entire response as the status string
        $normalizedStatus = strtolower(trim($response));
    }

        // Allowed statuses को check करके valid JSON return करें
        if (in_array($normalizedStatus, ["success", "pending", "failure","autochecktimeout","seasonexpire"])) {
            switch ($normalizedStatus) {
                case 'autochecktimeout':
            return json_encode([
                "status" => $normalizedStatus,
                "method" => $method,
                "message" => "Entr UTR in <strong>SUBMIT UTR</strong> Button ",
                "apiResp" => $response
            ]);
                break;
                case 'seasonexpire':
            return json_encode([
                "status" => $normalizedStatus,
                "method" => $method,
                "message" => "Pease Reconnect Your Merchent Account",
                "apiResp" => $response
            ]);
                break;
                
                default:
            return json_encode([
                "status" => $normalizedStatus,
                "method" => $method,
                "message" => "Transaction Status Updated",
                "apiResp" => $response
            ]);
                break;
            }
    
        } else {
            return json_encode([
                "status" => "error",
                "method" => $method,
                "message" => "Invalid response from API",
                "apiResp" => $response
            ]);
        }
    } else {
        return json_encode([
            "status" => "error",
            "method" => $method,
            "message" => "Invalid Payment Method",
            "apiResp" => ""
        ]);
    }
}

// स्टेटस चेक करो और JSON में रिटर्न करो
$response = statusCheck($order_id, $method, $description, $byteTransactionId,$user_token);
echo $response;

?>
