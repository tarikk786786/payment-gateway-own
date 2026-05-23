<!--Manual Pay Page-->
<?php

date_default_timezone_set("Asia/Kolkata");

define('ROOT_DIR', realpath(dirname(__FILE__)) . '/../');
include ROOT_DIR . 'pages/dbFunctions.php';
include ROOT_DIR . 'pages/dbInfo.php';
include ROOT_DIR . 'auth/config.php';

$link_token = ($_GET["token"]);

// Fetch order_id based on the token from the payment_links table
$sql_fetch_order_id = "SELECT order_id, created_at,user_token , merchentMobile FROM payment_links WHERE link_token = '$link_token'";
$result = getXbyY($sql_fetch_order_id);

if (count($result) === 0) {
    echo "Token not found or expired";
    exit;
}

$order_id = $result[0]['order_id'];
$user_token = $result[0]['user_token'];
$merchentMobile = $result[0]['merchentMobile'];
$created_at = strtotime($result[0]['created_at']);
$current_time = time();

if (($current_time - $created_at) > (5 * 60)) {
    echo "Token has expired";
    exit;
}

$slq_p = "SELECT * FROM orders where order_id='$order_id' AND user_token = '$user_token'";
$res_p = getXbyY($slq_p);    
$amount = $res_p[0]['amount'];
// $user_token = $res_p[0]['user_token'];
$redirect_url = $res_p[0]['redirect_url'];
$cxrkalwaremark = $res_p[0]['byteTransactionId'];
$cxrbytectxnref = $res_p[0]['paytm_txn_ref'];
$cxruser_id = $res_p[0]['user_id'];
$method = $res_p[0]['method'];

if ($redirect_url == '') {
    $redirect_url = 'https://'.$_SERVER["SERVER_NAME"].'/';
}

$sql_p = "SELECT * FROM `manual_token` WHERE user_token = '$user_token' AND phoneNumber = '$merchentMobile' LIMIT 1";


$res_p = getXbyY($sql_p);
$upi_id = $res_p[0]['upi_id'];
$merchentMobile = $res_p[0]['phoneNumber'];
// echo($upi_id);
$slq_p = "SELECT * FROM users where user_token='$user_token'";
$res_p = getXbyY($slq_p);
$unitId = $res_p[0]['name'];
$Intent_unable = $res_p[0]['Intent_unable'];
$USERNAME = $res_p[0]['company'];
$asdasd23 = "ARC" . rand(111, 999) . time() . rand(1, 100);
$logo = $res_p[0]['logo'];
$description = $cxrbytectxnref;

// logo unable disable via vip expiry
$vipExpiry = $res_p[0]['vip_expiry'];
$expiry_timestamp = strtotime($vipExpiry);
$today = time(); // Define today's timestamp
$logoo = ($expiry_timestamp > $today) ? false : true;
$vip_plan = ($expiry_timestamp < $today) ? false : true;

$intd = "pa=$upi_id&am=$amount&pn=$unitId&tn=$cxrbytectxnref&tr=$cxrkalwaremark";
$orders = "upi://pay?".$intd;
$paytmintent = "paytmmp://cash_wallet?pa=$upi_id&pn=$unitId&am=$amount&cu=INR&tn=$description&tr=$cxrkalwaremark&mc=4722&&sign=AAuN7izDWN5cb8A5scnUiNME+LkZqI2DWgkXlN1McoP6WZABa/KkFTiLvuPRP6/nWK8BPg/rPhb+u4QMrUEX10UsANTDbJaALcSM9b8Wk218X+55T/zOzb7xoiB+BcX8yYuYayELImXJHIgL/c7nkAnHrwUCmbM97nRbCVVRvU0ku3Tr&featuretype=money_transfer";

// Redirect URL for payment confirmation
$payment_verification_url = "https://".$_SERVER["SERVER_NAME"]."/payment9/verify/" . ($link_token);

// Your custom QR code API URL
// $url = 'https://imbx.in/secret/create_qr.php';
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
   
    <!-- Countdown Timer Script -->

    <script>
function payViaUPI() {
    Swal.fire({
        title: 'Enter UTR Number',
        input: 'text',
        inputLabel: 'UTR Number',
        inputPlaceholder: 'Enter your 12-digit UTR number',
        inputAttributes: {
            autocapitalize: 'off',
            inputmode: 'numeric',
            pattern: '\\d*',
            maxlength: 12,
            oninput: "this.value = this.value.replace(/\\D/g, '').slice(0, 12);"
        },
        showCancelButton: true,
        confirmButtonText: 'Submit',
        didOpen: () => {
            const confirmButton = Swal.getConfirmButton();
            confirmButton.setAttribute('name', 'utrverify');
        },
        preConfirm: (utr_number) => {
            if (!utr_number || utr_number.length !== 12) {
                Swal.showValidationMessage('Please enter a valid 12-digit UTR number!');
                return false;
            } else {
                return new Promise((resolve) => {
                    $.post("https://<?php echo $_SERVER["SERVER_NAME"] ?>/payment9/status.php", {
                        utr_number: utr_number,
                        token: '<?php echo $link_token; ?>',
                        utrverify: ''
                    }, function(response) {
                        if (response.status === 'success') {
                            Swal.fire('Success', response.message, 'success').then(() => {
                                if (response.redirect_url) {
                                    window.location.href = response.redirect_url;
                                }
                            });
                        } else if (response.status === 'error') {
                            Swal.fire('Error', response.message, 'error');
                        } else if (response.status === 'info') {
                            Swal.fire('Info', response.message, 'info');
                        }
                        resolve(response);
                    }, 'json').fail(function() {
                        Swal.fire('Error', 'There was an issue processing your UTR number!', 'error');
                    });
                });
            }
        }
    });
}


        // window.onload = function () {
        //     var fiveMinutes = 60 * 5,
        //         display = document.querySelector('#timeout');
        //     // startTimer(fiveMinutes, display);
        // };

        // function startTimer(duration, display) {
        //     var timer = duration, minutes, seconds;
        //     setInterval(function () {
        //         minutes = parseInt(timer / 60, 10);
        //         seconds = parseInt(timer % 60, 10);

        //         minutes = minutes < 10 ? "0" + minutes : minutes;
        //         seconds = seconds < 10 ? "0" + seconds : seconds;

        //         display.textContent = minutes + ":" + seconds;

        //         if (--timer < 0) {
        //             clearInterval(this);
        //         }
        //     }, 1000);
        // }
    </script>
    <!--<script disable-devtool-auto="" src="https://pay.imb.org.in/Qrcode/disable-devtool.js" data-url="https://www.google.com/"></script> -->
</body>
</html>
