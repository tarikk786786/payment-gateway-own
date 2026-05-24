<?php
include "header.php";

    $expiry = $userdata['expiry']; // डेटाबेस से एक्सपायरी डेट लें
    $expiry_timestamp = strtotime($expiry); // Expiry date को timestamp में बदलें
    $today = strtotime(date('Y-m-d')); // आज की तारीख का timestamp

    // **Check limits**
    if ($expiry_timestamp < $today) { // यदि expiry date आज से पहले की है तो प्लान एक्सपायर है
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Plan Expired!',
                text: 'Your plan expired on: " . date('d M, Y', $expiry_timestamp) . ". Please renew your plan to Access.',
                confirmButtonText: 'OK'
            }).then(() => { window.location.href='subscription'; });
        </script>";
        exit;
    }


if(isset($_POST['get_api_token'])){
    
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "CSRF token verification failed!";
        exit(); // Stop processing the request
    }
    
    $bbbyteuserid=$_SESSION['user_id'];
    // Assuming $mobile is already defined in header.php
    $sanitizedMobile = mysqli_real_escape_string($conn, $mobile);

    $uniqueNumber = mt_rand(1000000000, 9999999999);
    $uniqueNumber = str_pad($uniqueNumber, 10, '0', STR_PAD_LEFT); 

    $key = md5($uniqueNumber);
    $keyquery = "UPDATE `users` SET user_token='$key' WHERE mobile = '$sanitizedMobile'";
    $queryres = mysqli_query($conn, $keyquery);
    
    //update token in orders table
    
    $keyqueryorders = "UPDATE `orders` SET user_token='$key' WHERE user_id = $bbbyteuserid";
    $queryorders = mysqli_query($conn, $keyqueryorders);
    
     //update token in reports table
    
    $keyqueryordersreports = "UPDATE `reports` SET user_token='$key' WHERE user_id = $bbbyteuserid";
    $queryordersreports = mysqli_query($conn, $keyqueryordersreports);
    
    
    
    
    //hdfc token update 
    
    $keyqueryhdfc = "UPDATE `hdfc` SET user_token='$key' WHERE user_id = $bbbyteuserid";
    $queryreshdfc = mysqli_query($conn, $keyqueryhdfc);
    
    // Updating user_token in bharatpe_tokens table
    $keyquerybharatpe = "UPDATE `bharatpe_tokens` SET user_token='$key' WHERE user_id = '$bbbyteuserid'";
    $queryresbharatpe = mysqli_query($conn, $keyquerybharatpe);
    
    
    //update for phonepe  Updating user_token in phonepe_tokens  table and store_id table
    
    $keyqueryphonepetoken = "UPDATE `phonepe_tokens` SET user_token='$key' WHERE user_id = '$bbbyteuserid'";
    $queryresphonepetoken = mysqli_query($conn, $keyqueryphonepetoken);
    
    //now to update user_token in table store_id
    
    $keyqueryphonepetoken2 = "UPDATE `store_id` SET user_token='$key' WHERE user_id = '$bbbyteuserid'";
    $queryresphonepetoken2 = mysqli_query($conn, $keyqueryphonepetoken2);
    
    //now to update user_token in table paytm_tokens
    
    $keyquerypaytm2 = "UPDATE `paytm_tokens` SET user_token='$key' WHERE user_id = '$bbbyteuserid'";
    $queryrespaytm = mysqli_query($conn, $keyquerypaytm2);
    
    //now to update user_token in table googlepay_transactions
    
    $keyquerygooglepay = "UPDATE `googlepay_transactions` SET user_token='$key' WHERE user_id = '$bbbyteuserid'";
    $queryresgooglepay = mysqli_query($conn, $keyquerygooglepay);
    
    //now to update user_token in table googlepay_tokens
    
     $keyquerygooglepay1 = "UPDATE `googlepay_tokens` SET user_token='$key' WHERE user_id = '$bbbyteuserid'";
    $queryresgooglepay1 = mysqli_query($conn, $keyquerygooglepay1);
    
    
    if($queryres && $queryreshdfc){
        
        
        
        // Show SweetAlert2 success message
        echo '<script>
    Swal.fire({
        icon: "success",
        title: "New API Key generated!!",
        showConfirmButton: true,
        confirmButtonText: "Ok!",
        allowOutsideClick: false,
        allowEscapeKey: false
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = "developers";
        }
    });
</script>';

    exit;
    
    } else {
        
        
        
        
          // Show SweetAlert2 error message
        echo '<script>
    Swal.fire({
        icon: "error",
        title: "API Key Generating Failed!!",
        showConfirmButton: true,
        confirmButtonText: "Ok!",
        allowOutsideClick: false,
        allowEscapeKey: false
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = "developers";
        }
    });
</script>';
exit;
    }
}
?>
<?php

// Custom function to validate URLs
function isValidUrl($url) {
    $parsed_url = parse_url($url);
    return isset($parsed_url['host']) && preg_match("/\.\w+$/", $parsed_url['host']);
}

if(isset($_POST['update_webhook'])){
    
    
    $bytecallbackurl=mysqli_real_escape_string($conn,$_POST['webhook_url']);
    
    // Validate the webhook URL
    // Check if the URL has a valid TLD
    if (!isValidUrl($bytecallbackurl)) {
        
        // Show SweetAlert2 error message
                            echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.18"></script>';
echo '<script>
    Swal.fire({
        icon: "error",
        title: "Invalid webhook url!!",
        showConfirmButton: true, // Show the confirm button
        confirmButtonText: "Ok!", // Set text for the confirm button
        allowOutsideClick: false, // Prevent the user from closing the popup by clicking outside
        allowEscapeKey: false // Prevent the user from closing the popup by pressing Escape key
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = "developers"; // Redirect to "dashboard" when the user clicks the confirm button
        }
    });
</script>';

        exit(); // Stop processing the request
    }

    
    
    // Assuming $mobile is already defined in header.php
    $sanitizedMobile = mysqli_real_escape_string($conn, $mobile);

    $keyquery = "UPDATE `users` SET callback_url='$bytecallbackurl' WHERE mobile = '$sanitizedMobile'";
    $queryres = mysqli_query($conn, $keyquery);
    if($queryres){
        
        
        
        // Show SweetAlert2 success message
        echo '<script>
    Swal.fire({
        icon: "success",
        title: "Webhook Updated Successfully",
        showConfirmButton: true,
        confirmButtonText: "Ok!",
        allowOutsideClick: false,
        allowEscapeKey: false
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = "apidetails";
        }
    });
</script>';

    exit;
    
    } else {
        
        
        
        
          // Show SweetAlert2 error message
        echo '<script>
    Swal.fire({
        icon: "error",
        title: "Error Updating Webhook Try again Later!!",
        showConfirmButton: true,
        confirmButtonText: "Ok!",
        allowOutsideClick: false,
        allowEscapeKey: false
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = "developers";
        }
    });
</script>';
exit;
    }
}
?>
<!-- API Token Section -->
        <div class="bg-white shadow-md rounded-lg p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-700 mb-4">API Token</h2>
            <form class="grid grid-cols-1 md:grid-cols-3 gap-4" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">API Token</label>
                    <div class="relative">
                        <input type="text" id="api-token" value="<?php echo htmlspecialchars($userdata['user_token'], ENT_QUOTES, 'UTF-8'); ?>" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm p-2 bg-gray-100 pr-10" placeholder="Click Generate Button for API Token" readonly>
                        <button type="button" onclick="copyToClipboard('api-token')" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500 hover:text-gray-700">
                            <i class="bi bi-copy"></i>
                        </button>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 invisible">Generate</label>
                    <button type="submit" name="get_api_token" class="mt-1 w-full bg-blue-600 text-white p-2 rounded-md hover:bg-blue-700">Generate API Token</button>
                </div>
            </form>
        </div>

        <!-- Webhook URL Section -->
        <div class="bg-white shadow-md rounded-lg p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-700 mb-4">Webhook URL</h2>
            <form class="grid grid-cols-1 md:grid-cols-3 gap-4" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Webhook URL</label>
                    <div class="relative">
                        <input type="url" id="webhook-url" name="webhook_url" value="<?php echo htmlspecialchars($userdata['callback_url'], ENT_QUOTES, 'UTF-8'); ?>" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm p-2 pr-10" placeholder="Enter Your Webhook URL" required pattern="https?://[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}(/?[a-zA-Z0-9._~-]*)?(\?[a-zA-Z0-9._~-=&]*)?" title="Enter a valid URL">
                        <button type="button" onclick="copyToClipboard('webhook-url')" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500 hover:text-gray-700">
                            <i class="bi bi-copy"></i>
                        </button>
                    </div>
                    <p class="text-red-500 text-sm mt-1">Note: URL must include protocol (http / https)</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 invisible">Update</label>
                    <button type="submit" name="update_webhook" class="mt-1 w-full bg-blue-600 text-white p-2 rounded-md hover:bg-blue-700">Update URL</button>
                </div>
            </form>
            <!-- Callback Format Details -->
<div class="mt-6">
    <h3 class="text-lg font-semibold text-gray-700 bg-blue-100 p-2 rounded-t-md">Callback Format Details</h3>
    <div class="bg-gray-50 p-4 rounded-b-md">
        <p><strong>GET Callback URL:</strong> <code class="bg-gray-200 p-1 rounded"><?php echo htmlspecialchars($userdata['callback_url'], ENT_QUOTES, 'UTF-8'); ?>?status=SUCCESS&utr=104097179160&order_id=ORDR977135546&amount=500&customer_mobile=9766128730&method=HDFC&merchentMobile=9876543210&remark1=test1&remark2=test2</code></p>
        <p class="mt-2"><strong>POST Callback URL:</strong> <code class="bg-gray-200 p-1 rounded"><?php echo htmlspecialchars($userdata['callback_url'], ENT_QUOTES, 'UTF-8'); ?></code></p>
        <p class="mt-2"><strong>Request Data:</strong> <code class="bg-gray-200 p-1 rounded">{"status":"SUCCESS","utr":"104097179160","order_id":"ORDR977135546","amount":"500","customer_mobile":"9766128730","method":"HDFC","merchentMobile":"9876543210","remark1":"test1","remark2":"test2"}</code></p>
        <p class="mt-2 text-info text-gray-600">Important: Please ensure that the callback response is returned in JSON format only.</p>
    </div>
</div>
        </div>

        <!-- Create Order API Section -->
        <div class="bg-white shadow-md rounded-lg p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-700 mb-4">Create Order API</h2>
            <form class="space-y-4" method="POST" action="">
                <div>
                    <label class="block text-sm font-medium text-gray-700">URL</label>
                    <div class="relative">
                        <input type="text" id="create-order-url" value="https://<?php echo htmlspecialchars($server, ENT_QUOTES, 'UTF-8'); ?>/api/create-order" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm p-2 bg-gray-100 pr-10" readonly>
                        <button type="button" onclick="copyToClipboard('create-order-url')" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500 hover:text-gray-700">
                            <i class="bi bi-copy"></i>
                        </button>
                    </div>
                    <p class="text-red-500 text-sm mt-1">Order Timeout: 30 Minutes. Order will be automatically failed after 30 minutes.</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Form-Encoded Payload (application/x-www-form-urlencoded)</label>
                    <textarea class="mt-1 block w-full border-gray-300 rounded-md shadow-sm p-2 bg-gray-100" rows="6" readonly>{
  "customer_mobile": "8145344963",
  "user_token": "<?php echo htmlspecialchars($userdata['user_token'], ENT_QUOTES, 'UTF-8'); ?>",
  "amount": "1",
  "order_id": "8787772321800",
  "redirect_url": "your website url",
  "remark1" : "testremark",
  "remark2" : "testremark2"
}</textarea>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Success Response</label>
                        <textarea class="mt-1 block w-full border-gray-300 rounded-md shadow-sm p-2 bg-gray-100" rows="8" readonly>{
    "status": true,
    "message": "Order Created Successfully",
    "result": {
        "method": "Bharatpe",
        "orderId": "123456789125994465655859",
        "payment_url": "https://chickenpox.in/payment4/instant-pay/98a186be1ba109aa4b19ef7f1d499d3251e319ef43b8b94eb9fd17939c130a21",
        "data": {
            "bhim_link": "upi://pay?pa=...",
            "phonepe_link": "phonepe://pay?pa=...",
            "paytm_link": "paytmmp://pay?pa=...",
            "gpay_link": "tez://upi/pay?pa=...",
            "amazonpay_link": "amazonpay://upi/pay?pa=...",
            "cred_link": "cred://upi/pay?pa=...",
            "qr_image": "data:image/png;base64,(imagedata_base64)"
        }
    }
}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Failed Response</label>
                        <textarea class="mt-1 block w-full border-gray-300 rounded-md shadow-sm p-2 bg-gray-100" rows="4" readonly>{
    "status": "false",
    "message": "Order_id Already Exist"
}</textarea>
                    </div>
                </div>
            </form>
        </div>

        <!-- Check Order Status API Section -->
        <div class="bg-white shadow-md rounded-lg p-6">
            <h2 class="text-xl font-semibold text-gray-700 mb-4">Check Order Status API</h2>
            <form class="space-y-4" method="POST" action="">
                <div>
                    <label class="block text-sm font-medium text-gray-700">URL</label>
                    <div class="relative">
                        <input type="text" id="check-order-url" value="https://<?php echo htmlspecialchars($server, ENT_QUOTES, 'UTF-8'); ?>/api/check-order-status" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm p-2 bg-gray-100 pr-10" readonly>
                        <button type="button" onclick="copyToClipboard('check-order-url')" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500 hover:text-gray-700">
                            <i class="bi bi-copy"></i>
                        </button>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Form-Encoded Payload (application/x-www-form-urlencoded)</label>
                    <textarea class="mt-1 block w-full border-gray-300 rounded-md shadow-sm p-2 bg-gray-100" rows="4" readonly>{
    "user_token": "2048f66bef68633fa3262d7a398ab577",
    "order_id": "8052313697"
}</textarea>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Success Response</label>
                        <textarea class="mt-1 block w-full border-gray-300 rounded-md shadow-sm p-2 bg-gray-100" rows="8" readonly>{
    "status": true,
    "message": "Transaction Successfully",
    "result": {
        "txnStatus": "SUCCESS",
        "orderId": "ORDR977132269",
        "amount": 2000,
        "date": "2025-01-10 18:49:33",
        "utr": "975253134097",
        "customer_mobile": "9325239266",
        "remark1": "test1",
        "remark2": "test2"
    }
}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">FAILURE Response</label>
                        <textarea class="mt-1 block w-full border-gray-300 rounded-md shadow-sm p-2 bg-gray-100" rows="8" readonly>{
    "status": true,
    "message": "Transaction FAILURE",
    "result": {
        "txnStatus": "FAILURE",
        "orderId": "ORDR977132269",
        "amount": 2000,
        "date": "2025-01-10 18:49:33",
        "utr": "NULL",
        "customer_mobile": "9325239266",
        "remark1": "test1",
        "remark2": "test2"
    }
}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">ERROR Response</label>
                        <textarea class="mt-1 block w-full border-gray-300 rounded-md shadow-sm p-2 bg-gray-100" rows="4" readonly>{
    "status": "ERROR",
    "message": "Error Message"
}</textarea>
                    </div>
                </div>
            </form>
        </div>
<a href="/auth/AndroidDOcs" class="fixed top-1/2 right-4 transform -translate-y-1/2 px-6 py-3 text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition duration-300 shadow-lg">
    <span class="relative z-10">Android Payment Page Guide</span>
    <span class="absolute -top-2 -right-3 px-2 py-1 text-xs font-bold text-white bg-red-500 rounded-full animate-ping">NEW</span>
    <span class="absolute -top-2 -right-3 px-2 py-1 text-xs font-bold text-white bg-red-500 rounded-full">NEW</span>
</a>


    </div>

    <!-- JavaScript for Copy Functionality -->
    <script>
        function copyToClipboard(elementId) {
            const element = document.getElementById(elementId);
            const text = element.value;
            navigator.clipboard.writeText(text).then(() => {
                // Optional: Add feedback (e.g., alert or visual cue)
                alert('Copied to clipboard: ' + text);
            }).catch(err => {
                console.error('Failed to copy: ', err);
            });
        }
    </script>