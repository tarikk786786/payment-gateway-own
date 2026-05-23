<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Smart Payments</title>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.qrcode/1.0/jquery.qrcode.min.js"></script>
  <style>
    /* General Styles */
    body {
      margin: 0;
      padding: 0;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      background-color: #f4f6f8;
      font-family: Arial, sans-serif;
    }

    /* Payment Container */
    .payment-container {
      background: #fff;
      border-radius: 12px;
      width: 360px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
      overflow: hidden;
    }

    /* Header Section */
    .header {
      background-color: #1bb1dc;
      color: white;
      padding: 15px 20px;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .header .logo-title {
      display: flex;
      align-items: center;
    }

    .header .logo {
      width: 25px;
      height: 25px;
      margin-right: 10px;
    }

    .header .title {
      font-size: 16px;
      font-weight: bold;
    }

    .header .amount {
      font-size: 22px;
      font-weight: bold;
    }

    /* QR Code Section */
    .qr-section {
      text-align: center;
      padding: 20px;
    }

    .qr-section p {
      font-size: 16px;
      font-weight: bold;
      margin: 0;
      color: #333;
    }

    .qr-section small {
      display: block;
      margin: 8px 0 16px;
      color: #666;
      font-size: 12px;
    }

    .qr-code img {
      width: 150px;
      height: 150px;
    }

    .status {
      margin-top: 16px;
      font-size: 14px;
      color: #666;
      display: flex;
      justify-content: center;
      align-items: center;
      gap: 5px;
    }

    .status .timer {
      font-weight: bold;
      color: #1bb1dc;
    }

    /* Center Loader */
    .loading {
      margin: 20px auto;
      width: 30px;
      height: 30px;
      border: 3px solid #ddd;
      border-top: 3px solid #1bb1dc;
      border-radius: 50%;
      animation: spin 1s linear infinite;
    }

    @keyframes spin {
      from {
        transform: rotate(0deg);
      }
      to {
        transform: rotate(360deg);
      }
    }

    /* Footer Section */
    .footer {
      background-color: #f8f8f8;
      padding: 10px;
      text-align: center;
      border-top: 1px solid #ddd;
    }

    .footer .brand {
      font-weight: bold;
      color: #ff5722;
    }
   
    /* UPI Buttons */
    .upi-buttons {
      margin-top: 20px;
    }

    .upi-buttons button {
      background-color: #1bb1dc;
      color: white;
      border: none;
      padding: 10px;
      margin: 5px;
      border-radius: 5px;
      cursor: pointer;
      width: 150px;
    }

    .upi-buttons button:hover {
      background-color: #1591c0;
    }
/* Style for the UPI Buttons - Only show on mobile */
@media (max-width: 768px) {
  .upi-buttons {
    display: block;  /* Show buttons on mobile */
  }
}

@media (min-width: 769px) {
  .upi-buttons {
    display: none;  /* Hide buttons on desktop/tablet */
  }
}

  </style>
</head>


<?php
date_default_timezone_set("Asia/Kolkata");

// Define the base directory constant
define('ROOT_DIR', realpath(dirname(__FILE__)) . '/../');

// Securely include files using the ROOT_DIR constant
include ROOT_DIR . 'pages/dbFunctions.php';
include ROOT_DIR . 'pages/dbInfo.php';

$link_token = sanitizeInput($_GET["token"]);

// Fetch order_id based on the token from the payment_links table
$sql_fetch_order_id = "SELECT order_id, created_at FROM payment_links WHERE link_token = '$link_token'";
$result = getXbyY($sql_fetch_order_id);

if (count($result) === 0) {
    // Token not found or expired
    echo "Token not found or expired";
    exit;
}

$order_id = $result[0]['order_id'];
$created_at = strtotime($result[0]['created_at']);
$current_time = time();

// Check if the token has expired (more than 5 minutes)
if (($current_time - $created_at) > (5 * 60)) {
    echo "Token has expired";
    exit;
}

$slq_p = "SELECT * FROM orders where order_id='$order_id'";
$res_p = getXbyY($slq_p);    
$amount = $res_p[0]['amount'];
$user_token = $res_p[0]['user_token'];
$redirect_url = $res_p[0]['redirect_url'];
$cxrkalwaremark = $res_p[0]['byteTransactionId'];  //remark
$cxrbytectxnref = $res_p[0]['order_id'];

if ($redirect_url == '') {
    $redirect_url = 'https://' . $_SERVER["SERVER_NAME"] . '/';    
}

// Fetch UPI ID
$slq_p = "SELECT * FROM amazon_token where user_token='$user_token'";
$res_p = getXbyY($slq_p);    
$upi_id = $res_p[0]['upi_id']; // UPI ID from Paytm tokens

// Fetch user information
$slq_p = "SELECT * FROM users where user_token='$user_token'";
$res_p = getXbyY($slq_p);    
$unitId = $res_p[0]['name'];

// Generate a unique transaction remark
$asdasd23 = "TXN" . rand(111, 999) . time() . rand(1, 100);
$orders = "upi://pay?pa=$upi_id&am=$amount&pn=$unitId&tn=$cxrbytectxnref&tid=$cxrbytectxnref&tr=$cxrbytectxnref";
$paytm="paytmmp://cash_wallet?pa=$upi_id&am=$amount&pn=$unitId&tn=$asdasd23&tr=$cxrbytectxnref&amp;mc=5641&amp;cu=INR&amp;url=&amp;mode=02&amp;purpose=00&amp;orgid=159002&amp;sign=MEUCIHldtBS8sv53BbdI9jtTN4vRokbPT91Fm6wlPQCN/sVkAiEAs4p9TPwTvLvPsceQLjSOBL1lAKhrsHdHMnfiDFyu1Aw=&amp;featuretype=money_transfer";

// URL-encode the UPI URL
$encoded_orders = urlencode($orders);

$qr_code_url = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . $encoded_orders;
?>

<body>
  <div class="payment-container">
    <!-- Header -->
    <div class="header">
      <div class="logo-title">
<img class="logo" src="/bag.jpeg" alt="Logo" style="width: 50px; height: 50px; border-radius: 10%; object-fit: cover;">
            <div class="qr-title"><?php echo $unitId?></div>
             </div>
      <div class="amount">₹ <?php echo number_format($amount, 2); ?></div>
    </div>
    <!-- QR Code Section -->
<div class="qr-section">
    <p>Scan QR Code to Pay</p>
    <small>Open UPI app and scan</small>
    
            <div class="qr-code">
                <img src="<?php echo $qr_code_url; ?>" alt="QR Code" style="max-width: 100%;">
                 <div id="qr-code"></div>
      <button class="btn" id="download-button" onclick="downloadQRCode()" style="margin-top: 20px; padding: 10px 20px; background-color: #1bb1dc; color: white; border: none; border-radius: 5px; cursor: pointer;">
        Save QR Code
    </button>

  <div class="loading"></div>
 <!-- UPI Buttons -->
      <div class="upi-buttons">
       
        <button onclick="window.location.href='upi://pay?pa=<?php echo $upi_id; ?>&am=<?php echo $amount; ?>&pn=<?php echo $unitId; ?>&tn=<?php echo $asdasd23; ?>&tr=<?php echo $cxrbytectxnref; ?>'">Pay via PhonePe</button>
        <button onclick="window.location.href='<?php echo $paytm; ?>'">Pay via Paytm</button>
        
      </div>

      <!-- Paytm Link -->
 
    </div>

    <!-- Timer Section -->
    <div class="status">
      <div>Expired Time : <span id="timeout" class="timer">5:00</span></div>
    </div>


  <style>
    /* Style for the footer */
    .footer {
      text-align: center;
      padding: 10px;
      background-color: #f1f1f1;
      font-family: Arial, sans-serif;
    }

    /* Style for the Paytm logo */
    .paytm-logo {
      width: 100px;  /* Adjust the width to make the logo smaller or bigger */
      height: auto; /* Maintain aspect ratio */
      vertical-align: middle; /* Align the logo with the text */
      margin-left: 5px;  /* Optional: adds space between the text and the logo */
    }
  </style>
</head>
<body>

  <!-- Footer -->
  <div class="footer">
    Powered by <img src="https://<?php echo $_SERVER["SERVER_NAME"] ?>//payment9/amazon.png" alt="Paytm Logo" class="paytm-logo">
  </div>

           
        </div>
    </div>
    

    
    
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

        function checkPaymentStatus() {
            $.ajax({
                type: 'post',
                url: 'https://<?php echo $_SERVER["SERVER_NAME"] ?>/payment92/payment-status',
                data: 'byte_order_status=<?php echo $cxrbytectxnref?>',
                success: function (data) {
                    if (!paymentProcessed) {
                        if (data == 'success') {
                            paymentProcessed = true;
                            Swal.fire({
                                title: '',
                                text: 'Your Payment Received Successfully 👍 Please Wait',
                                icon: 'success'
                            });
                            window.location.href = "<?php echo $redirect_url?>";
                        } else if (data == 'FAILURE') {
                            paymentProcessed = true;
                            Swal.fire({
                                title: '',
                                text: 'Your Payment Was Failed',
                                icon: 'error'
                            });
                            window.location.href = "<?php echo $redirect_url?>";
                        }
                    }
                }
            });    
        }

        setInterval(checkPaymentStatus, 1000);
    </script>
<script disable-devtool-auto="" src="/Qrcode/dev-script.js" data-url="https://www.google.com/"></script> 
</body>
</html>