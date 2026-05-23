<?php
// Define the base directory constant
date_default_timezone_set("Asia/Kolkata");
define('PROJECT_ROOT', realpath(dirname(__FILE__)) . '/../');

// Securely include files using the PROJECT_ROOT constant
include PROJECT_ROOT . 'pages/dbFunctions.php';
include PROJECT_ROOT . 'pages/dbInfo.php';
include PROJECT_ROOT . 'auth/config.php';

// Validation Functions
function isInteger($value) {
    return filter_var($value, FILTER_VALIDATE_INT) !== false;
}

function isCustomerNumberValid($value) {
    return isInteger($value) && strlen($value) <= 10;
}

function RandomNumber($length) {
    $str = "";
    for ($i = 0; $i < $length; $i++) {
        $str .= mt_rand(0, 9);
    }
    return $str;
}

function GenRandomString($length = 10) {
    $characters = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $charactersLength = strlen($characters);
    $randomString = "";
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function generateUniqueToken() {
    $token = time() . bin2hex(random_bytes(16)) . rand(1, 50);
    return hash("sha256", $token);
}

// Request Method Check
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    http_response_code(400);
    header("Content-Type: application/json");
    $json = ["status" => false, "message" => "Unauthorized Access"];
    echo json_encode($json);
    exit();
}

// Main Processing
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    header("Content-Type: application/json");

    // Check for missing parameters
    $required_fields = [
        "customer_mobile" => "customer_mobile",
        "user_token" => "user_token",
        "amount" => "amount",
        "order_id" => "order_id",
        "redirect_url" => "redirect_url",
        "remark1" => "remark1",
        "remark2" => "remark2",
        "route" => "route"
    ];

    // Input Variables
    $customer_mobile = $_POST["customer_mobile"];
    $user_token = $_POST["user_token"];
    $amount = $_POST["amount"];
    $order_id = $_POST["order_id"];
    $redirect_url = $_POST["redirect_url"];
    $remark1 = $_POST["remark1"];
    $remark2 = $_POST["remark2"];
    $route = $_POST["route"];
    
    $byteorderid = "BYTE" . rand(1111, 9999) . time();

    // Original Validation (only for customer_mobile)
    if (!isCustomerNumberValid($customer_mobile)) {
        http_response_code(400);
        echo json_encode([
            "status" => false,
            "message" => "Invalid customer_mobile: Must be numeric and max 10 digits"
        ]);
        exit();
    }
    
    if ($amount < 1 || $amount > 200000) {
        http_response_code(400);
        echo json_encode([
            "status" => false,
            "message" => "Invalid Amount: Must be between 1 and 200000"
        ]);
        exit();
    }

    // User Data Fetch
    $slq_pbbyt = "SELECT * FROM users where user_token='$user_token'";
    $res_pslq_pbbyt = getXbyY($slq_pbbyt);
    
    if (empty($res_pslq_pbbyt)) {
        http_response_code(400);
        echo json_encode([
            "status" => false,
            "message" => "Invalid user_token: User not found"
        ]);
        exit();
    }
    
    $bydb_unq_user_id = $res_pslq_pbbyt[0]["id"];
    $bydb_order_hdfc_conn = $res_pslq_pbbyt[0]["hdfc_connected"];
    $bydb_order_phonepe_conn = $res_pslq_pbbyt[0]["phonepe_connected"];
    $bydb_order_paytm_conn = $res_pslq_pbbyt[0]["paytm_connected"];
    $bydb_order_bharatpe_conn = $res_pslq_pbbyt[0]["bharatpe_connected"];
    $bydb_order_googlepay_conn = $res_pslq_pbbyt[0]["googlepay_connected"];
    $bydb_order_freecharge_conn = $res_pslq_pbbyt[0]["freecharge_connected"];
    $bydb_order_sbi_conn = $res_pslq_pbbyt[0]["sbi_connected"];
    $bydb_order_mobikwik_conn = $res_pslq_pbbyt[0]["mobikwik_connected"];
    $bydb_order_manual_conn = $res_pslq_pbbyt[0]["manual_connected"];
    $bydb_order_amazon_conn = $res_pslq_pbbyt[0]["amazon_connected"];
    $merchentRouting = $res_pslq_pbbyt[0]["merchentRouting"];
    $isuserbanned = $res_pslq_pbbyt[0]["acc_ban"];
    $isacc_lock = $res_pslq_pbbyt[0]["acc_lock"];
    $planId = $res_pslq_pbbyt[0]["planId"];
    $tranjection_Count = $res_pslq_pbbyt[0]["tranjection_Count"];
// Prepare the SQL query

    // Account Status Check
    if ($isuserbanned == "Yes") {
        http_response_code(400);
        echo json_encode([
            "status" => false,
            "message" => "User account banned"
        ]);
        exit();
    }

    if ($isacc_lock == "Yes") {
        http_response_code(400);
        echo json_encode([
            "status" => false,
            "message" => "User account locked"
        ]);
        exit();
    }
    
    // Order ID Validation
    $check_order_id_query = "SELECT * FROM orders WHERE order_id='$order_id' AND user_token='$user_token'";
    $existing_order_result = getXbyY($check_order_id_query);

    if (!empty($existing_order_result)) {
        http_response_code(400);
        echo json_encode(["status" => false, "message" => "Order ID already exists for this user"]);
        exit();
    }

    // Payment Gateway Processing with Random Selection
    $today = date("Y-m-d");
    $slq_p = "SELECT * FROM users where user_token='$user_token'";
    $res_p = getXbyY($slq_p);
    
    if (empty($res_p)) {
        http_response_code(400);
        echo json_encode([
            "status" => false,
            "message" => "User data fetch failed"
        ]);
        exit();
    }
    
    $expire_date = $res_p[0]["expiry"];

    if ($expire_date < $today) {
        http_response_code(400);
        echo json_encode(["status" => false, "message" => "Your Plan Expired Please Renew"]);
        exit();
    }
    
    $user_ip = $_SERVER['REMOTE_ADDR'];  // यह यूजर का IP पकड़ता है
    $uid = $res_p[0]["id"];
    if ($user_ip !="198.38.88.206") {
    // चेक करो कि यह IP वाइटलिस्टेड है या नहीं
    $sql_p = "SELECT COUNT(*) AS count FROM user_ips WHERE ip = '$user_ip' AND user_id = '$uid' AND status = '1'";
    $result = mysqli_query($conn, $sql_p); // Assuming $connection is your database connection variable
    $row = mysqli_fetch_assoc($result); // Fetch the result
    $count = $row['count']; // Extract the count value
    if ($count == 0) {
        // अगर IP नहीं मिला, तो एक्सेस ब्लॉक करो
        http_response_code(403);
        header("Content-Type: application/json");
        echo json_encode(["status" => false, "message" => "Access Denied: Your IP : ".$user_ip." is not whitelisted, Please Add Your Correct Ip in IP settings"]);
        exit();
    }
    }
    
// Prepare the query to fetch the hit limit for the given subscription plan ID
$hitlimit_q = "SELECT `HitLimit` FROM `subscription_plan` WHERE id = ?";
$hitlimit_result = $conn->prepare($hitlimit_q);

if ($hitlimit_result) {
    // Bind the parameter and execute the query
    $hitlimit_result->bind_param("i", $planId);
    $hitlimit_result->execute();
    $result = $hitlimit_result->get_result();

    if ($result->num_rows > 0) {
        // Fetch the hit limit value
        $row = $result->fetch_assoc();
        $hitlimit = $row['HitLimit'];
    } else {
        // If no rows are found, handle appropriately
        http_response_code(404);
        echo json_encode([
            "status" => false,
            "message" => "Subscription plan not found"
        ]);
        exit();
    }
    // Close the statement
    $hitlimit_result->close();
} else {
    // If query preparation fails, return an error
    http_response_code(500);
    echo json_encode([
        "status" => false,
        "message" => "Query preparation failed"
    ]);
    exit();
}

// Check the transaction count against the hit limit
if ($tranjection_Count > $hitlimit) {
    http_response_code(400);
    echo json_encode([
        "status" => false,
        "message" => "Tranjection Limit Is Reached, Please Renew Plan"
    ]);
    exit();
} else {
    // Update the user's transaction count in the database
    $update_query = "UPDATE `users` SET `tranjection_Count` = `tranjection_Count` + 1 WHERE `user_token` = ?";
    $update_stmt = $conn->prepare($update_query);

    if ($update_stmt) {
        // Bind user ID and execute the update query
        $update_stmt->bind_param("s", $user_token);
        $update_stmt->execute();

        $update_stmt->close();
    } else {
        http_response_code(500);
        echo json_encode([
            "status" => false,
            "message" => "Failed to prepare update query"
        ]);
    }
}
    // Define available merchants with their configurations
    $merchants = [
        'HDFC' => [
            'connected' => $bydb_order_hdfc_conn,
            'path' => 'payment',
            'method' => 'HDFC',
            'extra_fields' => ['HDFC_TXNID' => '', 'upiLink' => '', 'description' => '']
        ],
        'PhonePe' => [
            'connected' => $bydb_order_phonepe_conn,
            'path' => 'payment2',
            'method' => 'PhonePe',
            'extra_fields' => []
        ],
        'Paytm' => [
            'connected' => $bydb_order_paytm_conn,
            'path' => 'payment3',
            'method' => 'Paytm',
            'extra_fields' => ['paytm_txn_ref' => GenRandomString() . time()]
        ],
        'Bharatpe' => [
            'connected' => $bydb_order_bharatpe_conn,
            'path' => 'payment4',
            'method' => 'Bharatpe',
            'extra_fields' => []
        ],
        'Googlepay' => [
            'connected' => $bydb_order_googlepay_conn,
            'path' => 'payment5',
            'method' => 'Googlepay',
            'extra_fields' => []
        ],
        'FreeCharge' => [
            'connected' => $bydb_order_freecharge_conn,
            'path' => 'payment6',
            'method' => 'FreeCharge',
            'extra_fields' => ['paytm_txn_ref' => "ATC" . substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789"), 0, 5) . time()]
        ],
        'SBI' => [
            'connected' => $bydb_order_sbi_conn,
            'path' => 'payment7',
            'method' => 'SBI',
            'extra_fields' => ['paytm_txn_ref' => "ATC" . substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789"), 0, 5) . time()]
        ],
        'MOBIKWIK' => [
            'connected' => $bydb_order_mobikwik_conn,
            'path' => 'payment8',
            'method' => 'MOBIKWIK',
            'extra_fields' => ['paytm_txn_ref' => "ATC" . substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789"), 0, 5) . time()]
        ],
        'MANUAL' => [
            'connected' => $bydb_order_manual_conn,
            'path' => 'payment9',
            'method' => 'MANUAL',
            'extra_fields' => ['paytm_txn_ref' => "ATC" . substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789"), 0, 5) . time()]
        ],
        'AMAZON' => [
            'connected' => $bydb_order_amazon_conn,
            'path' => 'payment92',
            'method' => 'AMAZON',
            'extra_fields' => ['paytm_txn_ref' => "ATC" . substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789"), 0, 5) . time()]
        ]
    ];

    // Filter active merchants
    $active_merchants = array_filter($merchants, function($merchant) {
        return $merchant['connected'] == "Yes";
    });

    if (empty($active_merchants)) {
        http_response_code(400);
        echo json_encode(["status" => false, "message" => "Merchant Not Linked"]);
        exit();
    }

    // Select merchant based on merchentRouting
    if ($merchentRouting == 1) {
        // Random selection
        $selected_merchant = array_rand($active_merchants);
        $merchant_config = $active_merchants[$selected_merchant];
    } else {
        // First available merchant (original behavior)
        $merchant_config = reset($active_merchants);
    }
//Cool DOwn 5 min//
$end_time = date("Y-m-d H:i:s");
// 3 minute before
$start_time = date('Y-m-d H:i:s', strtotime('-3 minute'));


$sql_c = "SELECT create_date FROM orders WHERE customer_mobile = '$customer_mobile' AND status = 'PENDING' AND user_token = '$user_token' AND create_date BETWEEN '$start_time' AND '$end_time'";
$result = $conn->query($sql_c);

// SQL query
if ($result) {
    $crow = $result->fetch_assoc();
    $create_date = $crow['create_date'];
    // Calculate remaining time (in seconds) until 2 minute is complete
    $time_diff = strtotime($create_date) - strtotime($start_time);
    $reminingtime = $time_diff; // 120 seconds = 2 minute
    $reminingtime = $reminingtime > 0 ? $reminingtime . " seconds" : "0 seconds";
} else {
    echo "Query Error: " . $conn->error;
}
// SQL query
$sql_o = "SELECT COUNT(*) as total FROM orders WHERE customer_mobile = '$customer_mobile' AND status = 'PENDING' AND user_token = '$user_token' AND create_date BETWEEN '$start_time' AND '$end_time'";

// Query run
$result = $conn->query($sql_o);

if ($result) {
    $row = $result->fetch_assoc();
    $count = $row['total'];
    // echo($count);
    if ($count > 0) {
        
        http_response_code(400);
        echo json_encode([
            "status" => false,
            "Quine Count" => $count,
            "message" => "आपने पिछला पेमेंट अधूरा छोडा⏳ $reminingtime बाद पुनः कोशिश करें। कृपया एक बार में ही पूरा ट्रांजेक्शन करें। बिना पेमेंट करे वापस आने के लिये Cancel पर क्लिक करें। 
You left last payment incomplete⏳ Try again after $reminingtime. Try to complete the process in one try. To return without making the payment Click Cancel."
        ]);
        exit();
    }
} else {
    echo "Query Error: " . $conn->error;
}
    
// End Cool Down 5 Min//

//***Bharatpe AutoCheck Logik Start***//
$method = $merchant_config['method'];

if ($method=="Bharatpe" || $method=="MOBIKWIK" || $method=="HDFC") {
$end_time = date("Y-m-d H:i:s");
// 2 minute before
if ($method == "HDFC") {
    $start_time = date('Y-m-d H:i:s', strtotime('-30 seconds'));
} else {
    $start_time = date('Y-m-d H:i:s', strtotime('-2 minutes'));
}

$sql_c = "SELECT create_date FROM orders WHERE amount = '$amount' AND method = '$method' AND status = 'PENDING' AND user_token = '$user_token' AND create_date BETWEEN '$start_time' AND '$end_time'";
$result = $conn->query($sql_c);

// SQL query
if ($result) {
    $crow = $result->fetch_assoc();
    $create_date = $crow['create_date'];
    // Calculate remaining time (in seconds) until 2 minute is complete
    $time_diff = strtotime($create_date) - strtotime($start_time);
    $reminingtime = $time_diff; // 120 seconds = 2 minute
    $reminingtime = $reminingtime > 0 ? $reminingtime . " seconds" : "0 seconds";
} else {
    echo "Query Error: " . $conn->error;
}

// SQL query
$sql_o = "SELECT COUNT(*) as total FROM orders WHERE amount = '$amount' AND method = '$method' AND status = 'PENDING' AND user_token = '$user_token' AND create_date BETWEEN '$start_time' AND '$end_time'";

// Query run
$result = $conn->query($sql_o);

if ($result) {
    $row = $result->fetch_assoc();
    $count = $row['total'];
    // echo($count);
    if ($count > 0) {
        
        db_delete($conn, "orders", "order_id='$order_id' AND user_token='$user_token'");
        db_delete($conn, "payment_links", "order_id='$order_id' AND user_token='$user_token'");
        
        http_response_code(400);
        echo json_encode([
            "status" => false,
            "Quine Count" => $count,
            "message" => "Please try another amount or try after $reminingtime"
        ]);
        exit();
    }
} else {
    echo "Query Error: " . $conn->error;
}
}

//***Bharatpe AutoCheck Logik End***// 
    

    // Process payment with selected merchant
    $link_token = generateUniqueToken();
    $cxrtoday = date("Y-m-d H:i:s");
    $sql_insert_link = "INSERT INTO payment_links (link_token, order_id, user_token, created_at) VALUES ('$link_token', '$order_id','$user_token', '$cxrtoday')";
    $link_result = setXbyY($sql_insert_link);
    
    if (!$link_result) {
        http_response_code(400);
        echo json_encode([
            "status" => false,
            "message" => "Failed to generate payment link"
        ]);
        exit();
    }
    

    $payment_link = "https://".$_SERVER["SERVER_NAME"]."/{$merchant_config['path']}/instant-pay/" . $link_token;
    $gateway_txn = ($merchant_config['method'] == 'HDFC') ? rand(1000000000, 9999999999) : uniqid();
    $currentTimestamp = date("Y-m-d H:i:s");
    $merchentMobile = "";
    // Build SQL query
    $fields = "gateway_txn, amount, order_id, status, user_token, utr, customer_mobile, redirect_url, Method, byteTransactionId, create_date, remark1, remark2, user_id, merchentMobile";
    $values = "'$gateway_txn', '$amount', '$order_id', 'PENDING', '$user_token', '', '$customer_mobile', '$redirect_url', '{$merchant_config['method']}', '$byteorderid', '$currentTimestamp', '$remark1', '$remark2', '$bydb_unq_user_id','$merchentMobile'";
    
    foreach ($merchant_config['extra_fields'] as $field => $value) {
        $fields .= ", $field";
        $values .= ", '$value'";
    }

    $sql = "INSERT INTO orders ($fields) VALUES ($values)";
    $order_result = setXbyY($sql);

    if (!$order_result) {
        http_response_code(400);
        echo json_encode([
            "status" => false,
            "message" => "Failed to create order"
        ]);
        exit();
    }

    http_response_code(201);
    echo json_encode([
        "status" => true,
        "message" => "Order Created Successfully",
        "result" => [
            "method" => $merchant_config['method'],
            "orderId" => $order_id,
            "payment_url" => $payment_link
        ]
    ]);
    exit();
}
?>