<?php
error_reporting(0);

// Define the base directory constant
define('ROOT_DIR', realpath(dirname(__FILE__)) . '/../');

// Securely include files using the ROOT_DIR constant
include ROOT_DIR . 'pages/dbFunctions.php';
include ROOT_DIR . 'auth/config.php';
include ROOT_DIR . 'pages/dbInfo.php';


// Debugging - Check if UTR Number and Order ID are received
if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $order_id = isset($_POST['order_id']) ? $_POST['order_id'] : '';
        $utr_number = isset($_POST['utr_number']) ? $_POST['utr_number'] : '';
        if (!empty($utr_number) && !empty($order_id)) {
        // 🔹 यहां डेटाबेस से चेक करें कि UTR और Order ID मैच हो रहे हैं य
        $slq_uc = "SELECT * FROM orders WHERE utr='$utr_number'";

$result1 = mysqli_query($conn, $slq_uc);

if (mysqli_num_rows($result1) > 0) {
    echo "DUPLICATE";  // 🔴 अगर UTR नंबर पहले से मौजूद हैyy
    exit;
}
    
$slq_p = "SELECT * FROM orders where order_id='$order_id'";
$res_p = getXbyY($slq_p);    
$user_token = $res_p[0]['user_token'];
$db_description = $res_p[0]['description'];
$hdfc_txn = $res_p[0]['HDFC_TXNID'];
$bbbyteremark1 = $res_p[0]['remark1'];
$amount = $res_p[0]['amount'];



$slq_p = "SELECT * FROM hdfc where user_token='$user_token'";
$res_p = getXbyY($slq_p);    
$seassion_id_hdfc = $res_p[0]['seassion'];
$hdfc_number = $res_p[0]['number'];        

$slq_pc = "SELECT * FROM users where user_token='$user_token'";
$res_pc = getXbyY($slq_pc);    
$callback_url = $res_pc[0]['callback_url'];
$mobile = $res_pc[0]['mobile'];
$name = $res_pc[0]['name'];
$megabyteuserid = $res_pc[0]['id'];

// Replace file_get_contents with cURL
$url = 'https://' . $server . '/payment/mstatement.php?no=' . $hdfc_number . '&session=' . $seassion_id_hdfc;

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Disable SSL verification for testing only
$txn_data = curl_exec($ch);
curl_close($ch);

$json0 = json_decode($txn_data, true);
$results = $json0["transactionParams"];

$query0 = "SELECT * FROM reports WHERE order_id = '$order_id'";
$result1 = mysqli_query($conn, $query0);

$rows = count($results);

// echo($rows);
for ($i = 0; $i < $rows; $i++) { 
    $txnid = $results[$i]["txnid"];
    $Status = $results[$i]["status"];
    $utr = $results[$i]["utr"];
    $txnmessage = $results[$i]["txnmessage"];
    $description = $results[$i]["description"];
    $payerVpa = $results[$i]["payerVpa"];
    $issuerRefNo = $results[$i]["issuerRefNo"];
    $paymentApp = $results[$i]["paymentApp"];
    $hamount = $results[$i]["amount"];
    
    

    if ($Status == '3'  && $utr_number == $issuerRefNo && $hamount == $amount) {
        if (mysqli_num_rows($result1) == 0) {
            $bytetoday = date("Y-m-d H:i:s");
            $sql = "INSERT INTO reports (transactionId, status, vpa, paymentApp, amount, user_token, UTR, description, mobile, date, user_id)
                    VALUES ('$txnid', '$Status', '$payerVpa', '$paymentApp', '$amount', '$user_token', '$issuerRefNo', '$utr_number', '$mobile', '$bytetoday', '$megabyteuserid')";
            setXbyY($sql);

            $sq = "UPDATE `orders` SET status='SUCCESS',utr='$issuerRefNo' WHERE order_id='$order_id'";
            setXbyY($sq);
        }
    }

    $sql = "UPDATE reports SET order_id='$order_id' WHERE description='$utr_number'";
    setXbyY($sql);
}




$slq_p = "SELECT * FROM reports where description='$utr_number'";


$res_pp = getXbyY($slq_p);    
$db_status = $res_pp[0]['status'];
if ($db_status == '3') {
    sendCallback("SUCCESS", $issuerRefNo, $amount, $order_id, $megabyteuserid);
    echo 'SUCCESS';  
} elseif ($db_status == '4') {
    sendCallback("FAILURE", "NA", $amount, $order_id, $megabyteuserid);
    echo 'FAILURE';    
} else {
    echo 'PENDING';
}
    } else {
        echo "ERROR: Missing UTR Number or Order ID!";
        exit;
    }
    


} else {
    echo "ERROR: Invalid Request!";
}


?>
