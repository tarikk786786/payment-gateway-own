<?php
// <!--Bajaj-->
date_default_timezone_set("Asia/Kolkata");

// Define the base directory constant
define('ROOT_DIR', realpath(dirname(__FILE__)) . '/../');

// Securely include files using the ROOT_DIR constant
include ROOT_DIR . 'pages/dbFunctions.php';
include ROOT_DIR . 'pages/dbInfo.php';
include ROOT_DIR . 'auth/config.php';
$link_token = sanitizeInput($_GET["token"]);

// Fetch order_id based on the token from the payment_links table
$sql_fetch_order_id = "SELECT order_id, created_at, user_token, merchentMobile FROM payment_links WHERE link_token = '$link_token'";
$result = getXbyY($sql_fetch_order_id);

if (count($result) === 0) {
    // Token not found or expired
    echo "Token not found or expired";
    exit;
}

$order_id = $result[0]['order_id'];
$user_token = $result[0]['user_token'];
$merchentMobile = $result[0]['merchentMobile'];
$created_at = strtotime($result[0]['created_at']);
$current_time = time();

// Check if the token has expired (more than 5 minutes)
if (($current_time - $created_at) > (5 * 60)) {
    echo "Token has expired";
    exit;
}

$slq_p = "SELECT * FROM orders where order_id='$order_id' AND user_token = '$user_token'";
$res_p = getXbyY($slq_p);    
$amount = $res_p[0]['amount'];
// $user_token = $res_p[0]['user_token'];
$redirect_url = $res_p[0]['redirect_url'];
$cxrkalwaremark = $res_p[0]['byteTransactionId'];  //remark
$cxrbytectxnref = $res_p[0]['order_id'];

if ($redirect_url == '') {
    $redirect_url = 'https://' . $_SERVER["SERVER_NAME"] . '/';    
}

// Fetch UPI ID
$slq_p = "SELECT * FROM amazon_token where user_token='$user_token' AND phoneNumber = '$merchentMobile'";
$res_p = getXbyY($slq_p);    
$upi_id = $res_p[0]['upi_id']; // UPI ID from Paytm tokens
$USERNAME = $res_p[0]['company'];
// Fetch user information
$slq_p = "SELECT * FROM users where user_token='$user_token'";
$res_p = getXbyY($slq_p);    
$unitId = urlencode($res_p[0]['company']);

// logo unable disable via vip expiry
$vipExpiry = $res_p[0]['vip_expiry'];
$expiry_timestamp = strtotime($vipExpiry);
$today = time(); // Define today's timestamp
$logoo = ($expiry_timestamp > $today) ? false : true;
$vip_plan = ($expiry_timestamp < $today) ? false : true;

// Generate a unique transaction remark
$asdasd23 = "TXN" . rand(111, 999) . time() . rand(1, 100);
$intd = "upi://pay?pa=$upi_id&am=$amount&pn=$unitId&tn=$cxrbytectxnref&tid=$cxrbytectxnref&tr=$cxrbytectxnref";
$orders = "upi://pay?$intd";

$paytmintent="paytmmp://cash_wallet?pa=$upi_id&am=$amount&pn=$unitId&tn=$cxrbytectxnref&tr=$cxrbytectxnref&tid=$cxrbytectxnref&amp;mc=5641&amp;cu=INR&amp;url=&amp;mode=02&amp;purpose=00&amp;orgid=159002&amp;sign=MEUCIHldtBS8sv53BbdI9jtTN4vRokbPT91Fm6wlPQCN/sVkAiEAs4p9TPwTvLvPsceQLjSOBL1lAKhrsHdHMnfiDFyu1Aw=&amp;featuretype=money_transfer";

// URL-encode the UPI URL
$url = 'https://' . $_SERVER['HTTP_HOST'] . '/secret/create_qr.php';
// Data to be sent in the POST request
$data = [
    'data' => $orders, // The data to encode in the QR
    'ecc' => 'M',      // Error correction level ('L', 'M', 'Q', 'H')
    'size' => 8        // Size of the QR code
];

// Convert the data array into a JSON string
$jsonData = json_encode($data);

// Initialize cURL session
$ch = curl_init($url);

// Set cURL options
curl_setopt($ch, CURLOPT_POST, true);  // Set method to POST
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  // Return the response as a string
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);  // Set content type to JSON
curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);  // Send data as JSON

// Execute the cURL request
$response = curl_exec($ch);

// Check for errors
if (curl_errno($ch)) {
    echo 'Error: ' . curl_error($ch);
} else {
    // Decode the JSON response
    $result = json_decode($response, true);

    // Check if there is an error in the response
    if (isset($result['error'])) {
        echo 'Error: ' . $result['error'];
    } else {
        // Success! The QR code is in base64 format.
        $qrCodeBase64 = $result['qr_code'];

        // Display the QR code image in the browser
        // echo '<img src="' . $qrCodeBase64 . '" alt="QR Code" />';
    }
}

// Close the cURL session
curl_close($ch);

// Generating a unique filename using the current timestamp
$timestamp = time();
$unique_filename = "qr_code_" . $timestamp . ".png";


include ROOT_DIR . 'pages/TGPayPage.php';
?>


    
    <script>
        // Flag to track whether payment status has already been processed
        var paymentProcessed = false;

        function payViaUPI() {
            // This function will be called when the user clicks the button
            window.location.href = "<?php echo $orders; ?>";
        }

        function upiCountdown(elm, minute, second, url) {
            document.getElementById(elm).innerHTML = minute + ":" + second;
            startTimer();

            function startTimer() {
                var presentTime = document.getElementById(elm).innerHTML;
                var timeArray = presentTime.split(/[:]+/);
                var m = timeArray[0];
                var s = checkSecond((timeArray[1] - 1));
                if (s == 59) {
                    m = m - 1;
                }
                if (m < 0) {
                    Swal.fire({
                        title: 'Oops',
                        text: 'Transaction Timeout!',
                        icon: 'error'
                    });
                    window.location.href = "https://<?php echo $_SERVER["SERVER_NAME"] ?>";
                }
                document.getElementById(elm).innerHTML = m + ":" + s;
                setTimeout(startTimer, 1000);
            }

            function checkSecond(sec) {
                if (sec < 10 && sec >= 0) { 
                    sec = "0" + sec;
                }
                if (sec < 0) { 
                    sec = "59"; 
                }
                return sec;
            }
        }

        upiCountdown("timeout", 5, 0, location.href);

        // function checkPaymentStatus() {
        //     $.ajax({
        //         type: 'post',
        //         url: 'https://<?php echo $_SERVER["SERVER_NAME"] ?>/payment92/payment-status',
        //         data: 'byte_order_status=<?php echo $cxrbytectxnref?>',
        //         success: function (data) {
        //             if (!paymentProcessed) {
        //                 if (data == 'success') {
        //                     paymentProcessed = true;
        //                     Swal.fire({
        //                         title: '',
        //                         text: 'Your Payment Received Successfully 👍 Please Wait',
        //                         icon: 'success'
        //                     });
        //                     window.location.href = "<?php echo $redirect_url?>";
        //                 } else if (data == 'FAILURE') {
        //                     paymentProcessed = true;
        //                     Swal.fire({
        //                         title: '',
        //                         text: 'Your Payment Was Failed',
        //                         icon: 'error'
        //                     });
        //                     window.location.href = "<?php echo $redirect_url?>";
        //                 }
        //             }
        //         }
        //     });    
        // }

        // setInterval(checkPaymentStatus, 1000);
    </script>
<script disable-devtool-auto="" src="/Qrcode/dev-script.js" data-url="https://www.google.com/"></script> 
</body>
</html>