
<?php
// freecharge
date_default_timezone_set("Asia/Kolkata");

// Define the base directory constant
define('ROOT_DIR', realpath(dirname(__FILE__)) . '/../');

// Securely include files using the ROOT_DIR constant
include ROOT_DIR . 'pages/dbFunctions.php';
include ROOT_DIR . 'pages/dbInfo.php';
include ROOT_DIR . 'auth/config.php';



$link_token = $_GET["token"];

// Fetch order_id based on the token from the payment_links table
$sql_fetch_order_id = "SELECT order_id, created_at,user_token,merchentMobile FROM payment_links WHERE link_token = '$link_token'";
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
$cxrbytectxnref=$res_p[0]['paytm_txn_ref'];
$cxruser_id= $res_p[0]['user_id'];
$description = $cxrbytectxnref ;
if($redirect_url==''){
$redirect_url='https://'.$_SERVER["SERVER_NAME"].'/';    
}


$sql_p = "SELECT * FROM `freecharge_token` WHERE user_token = '$user_token' AND phoneNumber = '$merchentMobile' AND status='Active' LIMIT 1";


// echo($mcount);
$res_p = getXbyY($sql_p);
$upi_id = $res_p[0]['Upiid']; // UPI ID from freecharge_token
$merchentMobile = $res_p[0]['phoneNumber'];
 
 $slq_p = "SELECT * FROM users where user_token='$user_token'";
        $res_p = getXbyY($slq_p);    
 $unitId=$res_p[0]['name'];
 $USERNAME = $res_p[0]['company'];
 $logo = $res_p[0]['logo'];
 $Intent_unable = $res_p[0]['Intent_unable'];
 // logo unable disable via vip expiry
$vipExpiry = $res_p[0]['vip_expiry'];
$expiry_timestamp = strtotime($vipExpiry);
$today = time(); // Define today's timestamp
$logoo = ($expiry_timestamp > $today) ? false : true;
$vip_plan = ($expiry_timestamp < $today) ? false : true;

 $asdasd23="ARC".rand(111,999).time().rand(1,100);
 //$cxrbytectxnref=time().rand(11111,99999);
$paytmintent = "paytmmp://cash_wallet?pa=$upi_id&am=$amount&pn=$unitId&tn=$cxrbytectxnref&tr=$cxrbytectxnref&amp;mc=5641&amp;cu=INR&amp;url=&amp;mode=02&amp;purpose=00&amp;orgid=159002&amp;sign=MEUCIHldtBS8sv53BbdI9jtTN4vRokbPT91Fm6wlPQCN/sVkAiEAs4p9TPwTvLvPsceQLjSOBL1lAKhrsHdHMnfiDFyu1Aw=&amp;featuretype=money_transfer";
 
// $orders = "upi://pay?pa=$upi_id&am=$amount&pn=$unitId&tn=$cxrbytectxnref&tr=$asdasd23";
$intd = "pa=$upi_id&am=$amount&pn=$unitId&tn=$cxrbytectxnref&tid=$cxrbytectxnref&tr=$cxrbytectxnref";
$orders = "upi://pay?".$intd;
// URL-encode the UPI URL
$encoded_orders = urlencode($orders);

// Your custom QR code API URL
// $url = 'https://imbx.in/secret/create_qr.php';
$url = 'https://chickenpox.in/secret/create_qr.php';
// Data to be sent in the POST request
$data = [
    'data' => $orders, // The data to encode
    'ecc' => 'M', // Error correction level ('L', 'M', 'Q', 'H')
    'size' => 8  // Size of the QR code
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
        if (!$qrCodeBase64) {
            // UPI डाटा
            $data = "upi://pay?pa=$upi_id&pn=$unitId&am=$amount&tr=$asdasd23&tn=$cxrbytectxnref";
            // URL एन्कोडिंग
            $encodedData = rawurlencode($data);
            // QR कोड URL
            $url = "https://api.qrserver.com/v1/create-qr-code/?&data=$encodedData";
            // QR कोड डाउनलोड करें
            $qrImage = file_get_contents($url);
            if ($qrImage !== false) {
                // Base64 में कन्वर्ट करें
                $base64Image = base64_encode($qrImage);
            } else {
                echo "QR कोड डाउनलोड करने में समस्या हुई।";
            }
                           }

        // Display the QR code image in the browser
        // echo '<img src="' . $qrCodeBase64 . '" alt="QR Code" />';
    }
}

// Close the cURL session
curl_close($ch);
include ROOT_DIR . 'pages/TGPayPage.php';
 ?>
    

    <!-- Countdown Timer Script -->
    
  <script>
        var paymentProcessedd = false;
        var interval;

        function startTimer(duration, display) {
            var timer = duration, minutes, seconds;
            interval = setInterval(function () {
                minutes = parseInt(timer / 60, 10);
                seconds = parseInt(timer % 60, 10);

                minutes = minutes < 10 ? "0" + minutes : minutes;
                seconds = seconds < 10 ? "0" + seconds : seconds;

                display.textContent = minutes + ":" + seconds;

                if (--timer < 0) {
                    clearInterval(interval);
                }
            }, 1000);
        }

        function check() {
            if (paymentProcessedd || !interval) {
                clearInterval(interval); 
                return;
            }

            $.ajax({
                type: 'POST',
                url: 'https://<?php echo $_SERVER["SERVER_NAME"] ?>/order6/payment-status',
                data: { LINKID: '<?php echo $link_token ?>' },
                dataType: 'text',
                success: function (data) {
                    if (data === 'success') {
                paymentProcessedd = true;
                document.body.classList.remove("failure-bg");
                document.body.classList.add("success-bg");
                // Confetti Effect 🎉
                const duration = 3 * 1000;
                const end = Date.now() + duration;
                (function frame() {
                    confetti({ particleCount: 3, angle: 60, spread: 55, origin: { x: 0 } });
                    confetti({ particleCount: 3, angle: 120, spread: 55, origin: { x: 1 } });
                    if (Date.now() < end) {
                        requestAnimationFrame(frame);
                    }
                })();
                // SweetAlert Success Popup
                         Swal.fire({
                            title: 'Payment Received Successfully ✅',
                            text: 'Please wait... Redirecting...',
                            icon: 'success',
                            background: '#f0fff0', // Light green background
                            timer: 2000, // Auto-close in 5 seconds
                            timerProgressBar: true,
                            showConfirmButton: false, // No confirmation button
                            didOpen: () => {
                                Swal.showLoading();
                            },
                            willClose: () => {
                                window.location.href = "<?php echo $redirect_url ?>";
                            }
                        });
                const successSound = new Audio("https://www.fesliyanstudios.com/play-mp3/4380");
                successSound.play();
                    } else if (data === 'FAILURE' || data === 'FAILED') {
                paymentProcessedd = true;
                document.body.classList.add("failure-bg");
                // SweetAlert Failure Popup
                Swal.fire({
                    title: '❌ Payment Failed!',
                    text: 'Your transaction could not be completed.',
                    icon: 'error',
                    background: '#fff0f0',
                    confirmButtonText: 'Try Again'
                }).then(() => {
                    window.location.href = "<?php echo $redirect_url ?>";
                });
                const failSound = new Audio("https://www.fesliyanstudios.com/play-mp3/4381");
                failSound.play();
                    }
                },
                error: function (xhr, status, error) {
                    console.log('AJAX Error:', status, error);
                }
            });
        }

        window.onload = function () {
            var fiveMinutes = 60 * 5,
                display = document.querySelector('#timeout');
            // startTimer(fiveMinutes, display);
            // check();
            // interval = setInterval(check, 5000);
        };
    </script>
    <script disable-devtool-auto="" src="https://pay.imb.org.in/Qrcode/disable-devtool.js" data-url="https://www.google.com/"></script> 
</body>
</html>