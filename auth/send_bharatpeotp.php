<?php

// Dene the absolute path to the functions.php file
define('ABSPATH', dirname(__FILE__) . '/'); // Adjust the path as needed
// Include the database connection file
require_once(ABSPATH . 'header.php');


?>

<?php

// // Function to sanitize user input
//     function sanitizeInput($input) {
//         if (is_string($input)) {
//             return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
//         } else {
//             // Handle non-string input here (e.g., arrays, objects, etc.) if needed.
//             return $input;
//         }
//     }
function strContains(string $mainString, string $substring): bool {
    // For PHP 8.0 and later, str_contains() is the most direct and readable way.
    // return str_contains($mainString, $substring);

    // For compatibility with older PHP versions (e.g., PHP 7.x),
    // you would use strpos() and check if it returns false.
    return strpos($mainString, $substring) !== false;
}

function bharatpe_trans($merchantId, $token, $cookie) {
    // Calculate the date range
    $fromDate = date('Y-m-d', strtotime('-2 days'));
    $toDate = date('Y-m-d');

    // Initialize cURL
    $curl = curl_init();

    // Set up cURL options
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://payments-tesseract.bharatpe.in/api/v1/merchant/transactions?module=PAYMENT_QR&merchantId=' . $merchantId . '&sDate=' . $fromDate . '&eDate=' . $toDate,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 120,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
            'token: ' . $token,
            'user-agent: Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/112.0.0.0 Mobile Safari/537.36',
            'Cookie: ' . $cookie
        ),
    ));

    // Execute cURL request
    $response = curl_exec($curl);
    curl_close($curl);

    // Decode the JSON response
    $decodedResponse = json_decode($response, true);

    // Return the decoded JSON response
    return $decodedResponse;
}






if(isset($_POST['verifyotp'])) {
    
   
    
  $bbbyteuserid=$_SESSION['user_id'];
  $bharatpe_mobile = ($_POST["bharatpe_mobile"]);  
  $bbytebharatpeuserid=  $userdata['user_token'];
  $bbytebharatpeusermid = ($_POST["MID"]);
  $bbytebharatpeusertoken= ($_POST["token"]);
  $bbytebharatpeusercookie= ($_POST["cookie"]);
  $bharatpeupiid= ($_POST["upiid"]);
  
    
    

// Call the function and store the response in a variable
// Call the function and store the response in a variable
$response = bharatpe_trans($bbytebharatpeusermid, $bbytebharatpeusertoken, $bbytebharatpeusercookie);

// Check if the response is an array or an object
if (is_array($response) || is_object($response)) {
    // echo json_encode($response, JSON_PRETTY_PRINT); // Uncomment for debugging the full response

    // Check if the top-level response has 'message' as 'SUCCESS' and 'status' as true
    if (isset($response['message']) && $response['message'] === 'SUCCESS' &&
        isset($response['status']) && $response['status'] === true) {

        // Now, access the 'data' key, and then the 'transactions' key within 'data'
        if (isset($response['data']) && (is_array($response['data']) || is_object($response['data'])) &&
            isset($response['data']['transactions']) && is_array($response['data']['transactions']) &&
            !empty($response['data']['transactions'])) {

            $transactions = $response['data']['transactions']; // Get the transactions array

            // Initialize a flag for the loop to true *before* the loop,
            // or keep $bbytebharatpestatus as false and set to true only on match
            // We'll stick to the "set to false, set to true on match" logic.
            // $bbytebharatpestatus = false; // Already initialized outside the if block

            foreach ($transactions as $transaction) {
                // Ensure 'type' and 'payeeIdentifier' exist in the current transaction
                if (isset($transaction['type']) && $transaction['type'] === "PAYMENT_RECV" &&
                    isset($transaction['payeeIdentifier'])) {

                    $bharatUpi = $transaction['payeeIdentifier'];
                    
                    $lower1 = strtolower($bharatpeupiid);
                    $lower2 = strtolower($bharatUpi);


                    if (strContains(strtolower($bharatpeupiid), strtolower($bharatUpi))) {
                        $bbytebharatpestatus = true; // Set to true if a match is found
                        echo "Match found: '$bharatUpi' is present in '$bharatpeupiid'.\n";
                        break; // Exit the loop as soon as a match is found (if one match is enough)
                    } else {
                        echo "No match: '$bharatUpi' is NOT present in '$bharatpeupiid'.\n";
                    }
                }
            }
        } else {
            $bbytebharatpestatus = true; // Set to true if no tranjections
            // 'data' or 'transactions' key is missing, not an array, or empty
            echo "No 'data' or 'transactions' array found, or it's empty in the response.\n";
            // $bbytebharatpestatus remains false, which is appropriate
        }
    } else {
        // Top-level message is not 'SUCCESS' or status is not true
        echo "Response message is not 'SUCCESS' or status is not true.\n";
        // $bbytebharatpestatus remains false, which is appropriate
    }
} else {
    // If it's not an array or an object (e.g., raw string error from API)
    echo "Response is not an array or object. Raw response: " . $response . "\n";
    $bbytebharatpestatus = false;
}

echo "Final \$bbytebharatpestatus: " . ($bbytebharatpestatus ? 'true' : 'false') . "\n";



if($bbytebharatpestatus==true) {   
    
    
$sqlw = "UPDATE bharatpe_tokens SET merchantId='$bbytebharatpeusermid', token='$bbytebharatpeusertoken', status='Active', Upiid = '$bharatpeupiid',user_id=$bbbyteuserid, cookie='$bbytebharatpeusercookie' WHERE user_token='$bbytebharatpeuserid' AND phoneNumber = '$bharatpe_mobile'";
$result = mysqli_query($conn, $sqlw);

 $sqlUpdateUser = "UPDATE users SET bharatpe_connected='Yes' WHERE user_token='$bbytebharatpeuserid'";
    $resultUpdateUser = mysqli_query($conn, $sqlUpdateUser);

   if ($result) {
       
  
    
     
    // Show SweetAlert2 success message
                           
echo '<script src="js/jquery-3.2.1.min.js"></script>';echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.18"></script>';echo '<script>$("#loading_ajax").hide();
    Swal.fire({
        icon: "success",
        title: "Congratulations! Your Bharatpe Hasbeen Connected Successfully!",
        showConfirmButton: true, // Show the confirm button
        confirmButtonText: "Ok!", // Set text for the confirm button
        allowOutsideClick: false, // Prevent the user from closing the popup by clicking outside
        allowEscapeKey: false // Prevent the user from closing the popup by pressing Escape key
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = "connect_merchant"; // Redirect to "connect_merchant" when the user clicks the confirm button
        }
    });
</script>';

    //exit;
    
    
} else {
    // Query failed
  //  echo "Error: " . mysqli_error($conn);
  
   // Show SweetAlert2 error message
                           
echo '<script src="js/jquery-3.2.1.min.js"></script>';echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.18"></script>';echo '<script>$("#loading_ajax").hide();
    Swal.fire({
        icon: "error",
        title: "Please Try Again Later!!",
        showConfirmButton: true, // Show the confirm button
        confirmButtonText: "Ok!", // Set text for the confirm button
        allowOutsideClick: false, // Prevent the user from closing the popup by clicking outside
        allowEscapeKey: false // Prevent the user from closing the popup by pressing Escape key
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = "connect_merchant"; // Redirect to "connect_merchant" when the user clicks the confirm button
        }
    });
</script>';
exit;

  
}

} elseif (!$bbytebharatpestatus){
    
    
     // Show SweetAlert2 error message
                           
echo '<script src="js/jquery-3.2.1.min.js"></script>';echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.18"></script>';echo '<script>$("#loading_ajax").hide();
    Swal.fire({
        icon: "error",
        title: "Invaild BharatPe Details!!",
        showConfirmButton: true, // Show the confirm button
        confirmButtonText: "Ok!", // Set text for the confirm button
        allowOutsideClick: false, // Prevent the user from closing the popup by clicking outside
        allowEscapeKey: false // Prevent the user from closing the popup by pressing Escape key
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = "connect_merchant"; // Redirect to "connect_merchant" when the user clicks the confirm button
        }
    });
</script>';
exit;
    
    
}
    
}

// bharatpe end verify


//form start

if(isset($_POST['Verify'])) {
    
   

    
    $bharatpe_mobile = ($_POST["bharatpe_mobile"]);
    
    
    if ($userdata['bharatpe_connected']=="Yes"){
     $ixcheck = db_exists($conn, "bharatpe_tokens", "phoneNumber='$bharatpe_mobile' AND status = 'Active'");
    if ($userdata['bharatpe_connected'] == "Yes" && $ixcheck) {
echo '<script src="js/jquery-3.2.1.min.js"></script>';
echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.18"></script>';
echo '<script>$("#loading_ajax").hide();
    Swal.fire({
        icon: "error",
        title: "Merchant Already Connected !!",
        showConfirmButton: true, // Show the confirm button
        confirmButtonText: "Ok!", // Set text for the confirm button
        allowOutsideClick: false, // Prevent the user from closing the popup by clicking outside
        allowEscapeKey: false // Prevent the user from closing the popup by pressing Escape key
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = "connect_merchant"; // Redirect to "connect_merchant" when the user clicks the confirm button
        }
    });
</script>';
exit;
    }       
         
                              // Show SweetAlert2 error message
    

        
        
    }

    // Now, you can use the $bharatpe_mobile variable as needed
?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const mobile = "<?php echo $bharatpe_mobile; ?>";

// Step 1: Send OTP
function sendOtp() {
    fetch(`https://chickenpox.in/auth/connectHelp/bharatpe.php?action=send&mobile=${mobile}`)
        .then(res => res.json())
        .then(data => {
            if (data.success && data.uuid && data.token) {
                promptOtp(data.uuid, data.token); // ✅ pass token here
            } else {
                Swal.fire("Failed", data.message || "Failed to send OTP", "error");
            }
        })
        .catch(error => {
            console.error(error);
            Swal.fire("Error", "Error while sending OTP", "error");
        });
}

// Step 2: Prompt OTP Input
function promptOtp(uuid, token) {
    Swal.fire({
        title: 'Enter OTP',
        input: 'text',
        inputLabel: 'OTP sent to your BharatPe number',
        inputPlaceholder: 'Enter 4-digit OTP',
        inputAttributes: {
            maxlength: 6,
            autocapitalize: 'off',
            autocorrect: 'off'
        },
        showCancelButton: false,
        confirmButtonText: 'Verify OTP',
        preConfirm: (otp) => {
            if (!otp) {
                Swal.showValidationMessage('Please enter the OTP');
                return false;
            }

            return fetch(`https://chickenpox.in/auth/connectHelp/bharatpe.php?action=verify&uuid=${uuid}&otp=${otp}&mobile=${mobile}&token=${token}`)
                .then(res => res.json())
                .then(response => {
                    if (!response.success) {
                        throw new Error(response.message || 'OTP verification failed');
                    }
                    return response;
                })
                .catch(err => {
                    Swal.showValidationMessage(err.message);
                });
        }
    }).then(result => {
        if (result.isConfirmed && result.value) {
            showFormWithVerifiedData(result.value);
        }
    });
}

// Step 3: Show Form with Verified Data
function showFormWithVerifiedData(data) {
    const cleanCookie = (data.cookie_text || '')

    const tokenSafe = data.token;
    const merchantIdSafe = data.merchant_id;

        Swal.fire({
            title: 'Bharatpe UPI Settings',
            html: `
                <form id="bharatpeForm" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="mb-2">
                    <div class="row" id="merchant">
                    <div class="col-md-12 mb-2">
                        <label for="MID">Merchant Mobile</label>
                        <input type="text" name="bharatpe_mobile" id="MID" placeholder="Merchant mobile" class="form-control" value="${mobile}"  readonly required>
                    </div>
                    <div class="col-md-12 mb-2">
                        <label for="MID">Enter Merchant ID</label>
                        <input type="text" name="MID" id="MID" placeholder="Enter Merchant ID" class="form-control" value="${merchantIdSafe}"  readonly required>
                    </div>
                    <div class="col-md-12 mb-2">
                        <label for="cookie">Enter BharatPe Cookie</label>
                        <textarea name="cookie" id="cookie" placeholder="Enter Bharatpe Cookie" class="form-control" required readonly>${cleanCookie}</textarea>
                    </div>
                    <div class="col-md-12 mb-2">
                        <label for="token">Enter BharatPe Token</label>
                        <input type="text" name="token" id="token" placeholder="Enter BharatPe Token" class="form-control " value="${tokenSafe}" readonly required>
                    </div>
                    <div class="col-md-12 mb-2">
                        <label for="upiid">BharatPe UPI Id</label>
                        <input type="text" name="upiid" id="upiid" placeholder="Enter BharatPe UPI Id" class="form-control" required>
                    </div>
                        <div class="col-md-12 mb-2">
                            <button type="submit" name="verifyotp" class="btn btn-primary btn-block mt-2">Verify BharatPe</button>
                        </div>
                    </div>
                </form>
            `,
            showCancelButton: false,
            showConfirmButton: false,
            customClass: {
                popup: 'swal2-custom-popup',
                title: 'swal2-title',
                content: 'swal2-content'
            },
            allowOutsideClick: false,
            allowEscapeKey: false
        });
}

// Run on page load
window.onload = () => {
    sendOtp();
};
</script>

    <style>
        .swal2-custom-popup {
            max-width: 600px;
            padding: 2em;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .swal2-title {
            font-size: 24px;
            margin-bottom: 1em;
            color: #333;
            font-weight: bold;
        }
        .swal2-content {
            text-align: left;
        }
        .swal2-content form {
            display: flex;
            flex-direction: column;
        }
        .swal2-content .row {
            display: flex;
            flex-wrap: wrap;
        }
        .swal2-content .col-md-12 {
            flex: 0 0 100%;
            max-width: 100%;
            padding: 0 15px;
            box-sizing: border-box;
        }
        .swal2-content label {
            font-weight: bold;
            color: #555;
            margin-bottom: 5px;
        }
        .swal2-content input {
            margin-top: 0.5em;
            padding: 0.5em;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .swal2-content .btn-block {
            width: 100%;
            margin-top: 1em;
            padding: 0.75em;
            font-size: 16px;
            background-color: #007bff;
            border: none;
            border-radius: 5px;
            color: white;
            cursor: pointer;
        }
        .swal2-content .btn-block:hover {
            background-color: #0056b3;
        }
    </style>
    <?php
} // End of if(isset($_POST['Verify']))

else{
    
echo '<script src="js/jquery-3.2.1.min.js"></script>';echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.18"></script>';echo '<script>$("#loading_ajax").hide();
    Swal.fire({
        icon: "success",
        title: "Congratulations! Your Bharatpe Hasbeen Connected Successfully!",
        showConfirmButton: true, // Show the confirm button
        confirmButtonText: "Ok!", // Set text for the confirm button
        allowOutsideClick: false, // Prevent the user from closing the popup by clicking outside
        allowEscapeKey: false // Prevent the user from closing the popup by pressing Escape key
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = "connect_merchant"; // Redirect to "connect_merchant" when the user clicks the confirm button
        }
    });
</script>';
exit;
}
?>
<!--bootstrap js-->
  <script src="assets/js/bootstrap.bundle.min.js"></script>

  <!--plugins-->
  <script src="assets/js/jquery.min.js"></script>
  <!--plugins-->
  <script src="assets/plugins/perfect-scrollbar/js/perfect-scrollbar.js"></script>
  <script src="assets/plugins/metismenu/metisMenu.min.js"></script>
  <script src="assets/plugins/simplebar/js/simplebar.min.js"></script>
  <script src="assets/js/main.js"></script>


</body>

</html>