<!--Checkout Costem Page-->
<?php
// echo("HI");
date_default_timezone_set("Asia/Kolkata");

define('ROOT_DIR', realpath(dirname(__FILE__)) . '/../');
include ROOT_DIR . 'pages/dbFunctions.php';
include ROOT_DIR . 'pages/dbInfo.php';
include ROOT_DIR . 'auth/config.php';


$link_token = ($_GET["token"]);


// Fetch order_id based on the token from the payment_links table
$sql_fetch_order_id = "SELECT order_id, created_at FROM payment_links WHERE link_token = '$link_token'";
$result = getXbyY($sql_fetch_order_id);

$order_id = "TEZ12345678925";
$current_time = time();
$created_at = $current_time;
// $slq_p = "SELECT * FROM orders where order_id='$order_id'";
// $res_p = getXbyY($slq_p);    
$amount = "10";
$user_token = $link_token;
$redirect_url = $res_p[0]['redirect_url'];
$cxrkalwaremark = "46844848466446484644";
$cxrbytectxnref = "54684864486468464484";
$request_upi = "1" ;
$cxruser_id = "162";
$method = "CHECK";
$user_id = $_GET['id'];
if ($redirect_url == '') {
    $redirect_url = 'https://'.$_SERVER["SERVER_NAME"].'/';
}
$mcount = db_count($conn, "manual_token", "user_token='$user_token'");
// echo($mcount);
// अगर 1 से ज्यादा रिकॉर्ड हैं, तो रैंडम 1 सेलेक्ट करो
if ($mcount > 1) {
    $sql_p = "SELECT * FROM `manual_token` WHERE user_token = '$user_token' ORDER BY RAND() LIMIT 1";
} else {
    $sql_p = "SELECT * FROM `manual_token` WHERE user_token = '$user_token' LIMIT 1";
}




$res_p = getXbyY($sql_p);
$upi_id = "DUMMY@UPI";
$merchentMobile = $res_p[0]['phoneNumber'];
// echo($upi_id);
$slq_p = "SELECT * FROM users where user_token='$user_token'";
$res_p = getXbyY($slq_p);
$unitId = $res_p[0]['name'];
$Intent_unable = $res_p[0]['Intent_unable'];
$USERNAME = $res_p[0]['company'];
$asdasd23 = "ARC" . rand(111, 999) . time() . rand(1, 100);
$logo = $res_p[0]['logo'];
$user_id = $res_p[0]['id'];
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

// Data to be sent in the POST request
$data = [
    'data' => $orders, // The data to encode in the QR
    'ecc' => 'M',      // Error correction level ('L', 'M', 'Q', 'H')
    'logo' => $logoo,      // Error correction level ('L', 'M', 'Q', 'H')
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
            inputPlaceholder: 'Enter your UTR number here',
            inputAttributes: {
                autocapitalize: 'off',
                oninput: "this.value = this.value.replace(/\\D/g, '').slice(0, 12);"
            },
            showCancelButton: true,
            confirmButtonText: 'Submit',
            didOpen: () => {
                // Add the name attribute to the confirm button after the alert is opened
                const confirmButton = Swal.getConfirmButton();
                confirmButton.setAttribute('name', 'utrverify');
            },
            preConfirm: (utr_number) => {
                if (!utr_number) {
                    Swal.showValidationMessage('UTR number is required!');
                    return false;
                } else {
                    return new Promise((resolve) => {
                        // Submit UTR and token to khilaadixpro.shop/payment8/status.php via POST
                        $.post("https://<?php echo $_SERVER["SERVER_NAME"] ?>/payment9/status.php", {
                            utr_number: utr_number,    // Send UTR number
                            token: '<?php echo $link_token; ?>',  // Send token from PHP
                            utrverify: ''               // Send empty utrverify
                        }, function(response) {
                            // Handle the response from the PHP page
                            if (response.status === 'success') {
                                Swal.fire('Success', response.message, 'success').then(() => {
                                    // Redirect if the response contains a redirect URL
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
