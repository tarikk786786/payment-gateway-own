<!DOCTYPE html>
<html lang="en">
<!--paynearby Payment-->
<?php

date_default_timezone_set("Asia/Kolkata");
// Define the base directory constant
define('ROOT_DIR', realpath(dirname(__FILE__)) . '/../');

// Securely include files using the ROOT_DIR constant
include ROOT_DIR . 'pages/dbFunctions.php';
include ROOT_DIR . 'pages/dbInfo.php';
include ROOT_DIR . 'auth/config.php';
$link_token = sanitizeInput($_GET["token"]);

// Fetch order_id based on the token from the payment_links table
$sql_fetch_order_id = "SELECT order_id,user_token,merchentMobile, created_at FROM payment_links WHERE link_token = '$link_token'";
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



// echo($user_token);
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
$method = $res_p[0]['method'];
$create_date = $res_p[0]['create_date'];
$cnumber = $res_p[0]['customer_mobile'];
//$cxrbytectxnref=$res_p[0]['paytm_txn_ref'];

if($redirect_url==''){
$redirect_url='https://'.$_SERVER["SERVER_NAME"].'/';    
}

$sql_p = "SELECT * FROM paynearby_token WHERE user_token='$user_token' AND phoneNumber = '$merchentMobile' AND status = 'Active' LIMIT 1";

// echo($sql_p);
$res_p = getXbyY($sql_p);    
$upi_id = $res_p[0]['upi_id']; //upi id from paytm tokens

 
 $slq_p = "SELECT * FROM users where user_token='$user_token'";
        $res_p = getXbyY($slq_p);    
 $unitId=$res_p[0]['company'];
 $USERNAME=$res_p[0]['company'];
 $Intent_unable = $res_p[0]['Intent_unable'];
 $logo = $res_p[0]['logo'];
 $mobile = $res_p[0]['mobile'];
 $asdasd23="ARC".rand(111,999).time().rand(1,100);
 $cxrbytectxnref=time().rand(11111,99999);
 $description = $cxrkalwaremark ;
//  $orders = "upi://pay?pa=$upi_id&am=$amount&pn=$unitId&tn=$cxrkalwaremark&tr=$cxrbytectxnref";
 $intd = "pa=$upi_id&am=$amount&pn=$unitId&mc=7399&tid=&tr=$description&tn=$description&mam=null&cu=INR&url=https://www.paynearby.in&mode=01&sign=null&orgid=000000&mid=6739118&msid=null&mtid=$mtid";
 $orders = "upi://pay?$intd";
// logo unable disable via vip expiry
$vipExpiry = $res_p[0]['vip_expiry'];
$expiry_timestamp = strtotime($vipExpiry);
$today = time(); // Define today's timestamp
$logoo = ($expiry_timestamp > $today) ? false : true;
$vip_plan = ($expiry_timestamp < $today) ? false : true;

$paytmintent = "paytmmp://cash_wallet?pa=$upi_id&pn=$unitId&am=$amount&cu=INR&tn$description=&tr=$description&mc=7399&&sign=AAuN7izDWN5cb8A5scnUiNME+LkZqI2DWgkXlN1McoP6WZABa/KkFTiLvuPRP6/nWK8BPg/rPhb+u4QMrUEX10UsANTDbJaALcSM9b8Wk218X+55T/zOzb7xoiB+BcX8yYuYayELImXJHIgL/c7nkAnHrwUCmbM97nRbCVVRvU0ku3Tr&featuretype=money_transfer";
// Define the API endpoint
// Your custom QR code API URL
// $url = 'https://imbx.in/secret/create_qr.php';
$url = 'https://' . $_SERVER['HTTP_HOST'] . '/secret/create_qr.php';
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
 include ROOT_DIR . 'pages/TGPayPage.php';
 ?>
    
  

<!--<button class="pay-button" onclick="payViaUPI()">Confirm Payment</button>-->

<form id="paymentForm" action="" method="post">
    <!-- Other input fields -->
    <input type="hidden" name="TransactionId" value="<?php echo $cxrkalwaremark; ?>">
    <input type="hidden" name="redirect_url" value="<?php echo $redirect_url; ?>">
</form>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function payViaUPI() {
Swal.fire({
    title: 'Enter UTR Number',
    input: 'text',
    inputAttributes: {
        autocapitalize: 'off'
    },
    didOpen: () => {
        const input = Swal.getInput();

        input.addEventListener('input', function () {
            this.value = this.value.replace(/\D/g, '').slice(0, 12);
        });

        input.addEventListener('paste', function (e) {
            e.preventDefault();
            let paste = (e.clipboardData || window.clipboardData).getData('text');
            paste = paste.replace(/\D/g, '').slice(0, 12);
            document.execCommand('insertText', false, paste);
        });
    },
    showCancelButton: true,
    confirmButtonText: 'Submit',
    showLoaderOnConfirm: true,
    preConfirm: (utr) => {
        let formData = new FormData();

        // ✅ Get values from form
        const transactionId = document.querySelector('input[name="TransactionId"]').value;
        const redirectUrl = document.querySelector('input[name="redirect_url"]').value;

        formData.append('utr', utr);
        formData.append('TransactionId', transactionId);
        formData.append('redirect_url', redirectUrl);

        return fetch('https://<?php echo $_SERVER["SERVER_NAME"] ?>/order93/payment-status', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(response.statusText);
            }
            return response.json();
        })
        .catch(error => {
            Swal.showValidationMessage(`Request failed: ${error}`);
        });
    },
    allowOutsideClick: false
}).then((result) => {
    if (result.isConfirmed && result.value && result.value.status === 'success') {
        Swal.fire({
            title: 'Payment Received Successfully!',
            icon: 'success'
        }).then(() => {
            window.location.href = result.value.redirect_url;
        });

    } else if (result.isConfirmed && result.value && result.value.status === 'pending') {
        Swal.fire({
            title: 'Payment Pending',
            text: result.value.error,
            icon: 'warning'
        });

    } else if (result.isConfirmed && result.value && result.value.status === 'invalid') {
        Swal.fire({
            title: 'Invalid',
            text:  result.value.error,
            icon: 'error'
        });

    } else if (result.dismiss === Swal.DismissReason.cancel) {
        window.location.href = '<?php echo $cancelurl; ?>';
    }
});
}






function upiCountdown(elm, minute, second, url) {
    document.getElementById(elm).innerHTML = minute + ":" + second;
    // startTimer();

    function startTimer() {
        var presentTime = document.getElementById(elm).innerHTML;
        var timeArray = presentTime.split(/[:]+/);
        var m = timeArray[0];
        var s = checkSecond((timeArray[1] - 1));
        if(s == 59){m = m - 1}
        if(m < 0){
            Swal.fire({
              title: 'Oops',
              text: 'Transaction Timeout!',
              icon: 'error'
            });
            window.location.href = "https://<?php echo $_SERVER["SERVER_NAME"] ?>";
        }
        document.getElementById(elm).innerHTML = m + ":" + s;
        // setTimeout(startTimer, 1000);
    }

    function checkSecond(sec) {
        if (sec < 10 && sec >= 0) { sec = "0" + sec };
        if (sec < 0) { sec = "59" };
        return sec;
    }
}

upiCountdown("timeout", 5, 0, location.href);

</script>


<!--<script disable-devtool-auto="" src="https://pay.imb.org.in/Qrcode/disable-devtool.js" data-url="https://www.google.com/"></script> -->
</body>
</html>