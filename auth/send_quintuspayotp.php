<?php
// Dene the absolute path to the functions.php file
define('ABSPATH', dirname(__FILE__) . '/'); // Adjust the path as needed
// Include the database connection file
require_once(ABSPATH . 'header.php');
?>

<?php

function strContains(string $mainString, string $substring): bool {
    // For PHP 8.0 and later, str_contains() is the most direct and readable way.
    // return str_contains($mainString, $substring);
    // For compatibility with older PHP versions (e.g., PHP 7.x),
    // you would use strpos() and check if it returns false.
    return strpos($mainString, $substring) !== false;
}


if(isset($_POST['verifyotp'])) {
    
  $bbbyteuserid=$_SESSION['user_id'];
  $quintuspay_mobile = ($_POST["quintuspay_mobile"]);
  $bbytequintuspayuserid=  $userdata['user_token'];
  $bbytequintuspayusertoken= ($_POST["token"]);
  $quintuspayupiid= ($_POST["upiid"]);
  
    
$sqlw = "UPDATE quintuspay_token SET token='$bbytequintuspayusertoken', status='Active', upi_id = '$quintuspayupiid',user_id=$bbbyteuserid WHERE user_token='$bbytequintuspayuserid' AND phoneNumber = '$quintuspay_mobile'";
$result = mysqli_query($conn, $sqlw);

 $sqlUpdateUser = "UPDATE users SET quintuspay_connected='Yes' WHERE user_token='$bbytequintuspayuserid'";
    $resultUpdateUser = mysqli_query($conn, $sqlUpdateUser);

   if ($result) {
// Show SweetAlert2 success message
                           
echo '<script src="js/jquery-3.2.1.min.js"></script>';echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.18"></script>';echo '<script>$("#loading_ajax").hide();
    Swal.fire({
        icon: "success",
        title: "Congratulations! Your quintuspay Hasbeen Connected Successfully!",
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
    
}

// quintuspay end verify


//form start

if(isset($_POST['Verify'])) {
    
   

    
    $quintuspay_mobile = ($_POST["quintuspay_mobile"]);
    
    
    if ($userdata['quintuspay_connected']=="Yes"){
     $ixcheck = db_exists($conn, "quintuspay_token", "phoneNumber='$quintuspay_mobile' AND status = 'Active'");
    if ($userdata['quintuspay_connected'] == "Yes" && $ixcheck) {
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
    }

// Now, you can use the $quintuspay_mobile variable as needed
?>
<!--<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>-->
<script>
const mobile = "<?php echo $quintuspay_mobile; ?>";

// Step 1: Send OTP
function sendOtp() {
    fetch(`https://chickenpox.in/auth/connectHelp/quintuspay.php?action=send&mobile=${mobile}`)
        .then(res => res.json())
        .then(data => {
            if (data.success && data.authid) {
                promptOtp(data.authid, data.token); // ✅ pass token here
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
function promptOtp(uuid) {
    Swal.fire({
        title: 'Enter OTP',
        input: 'text',
        inputLabel: 'OTP sent to your quintuspay number',
        inputPlaceholder: 'Enter 5-digit OTP',
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

            return fetch(`https://chickenpox.in/auth/connectHelp/quintuspay.php?action=verify&otp=${otp}&mobile=${mobile}`)
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

    const tokenSafe = data.accessToken;
    const upiid = data.vpa;

    // Check if both accessToken and vpa are available
    if (tokenSafe && upiid) {
        // If data is present, automatically create and submit a form
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>';
        document.body.appendChild(form);

        // Add hidden fields for all the necessary data
        const tokenInput = document.createElement('input');
        tokenInput.type = 'hidden';
        tokenInput.name = 'token';
        tokenInput.value = tokenSafe;
        form.appendChild(tokenInput);

        const upiIdInput = document.createElement('input');
        upiIdInput.type = 'hidden';
        upiIdInput.name = 'upiid';
        upiIdInput.value = upiid;
        form.appendChild(upiIdInput);
        
        const mobileInput = document.createElement('input');
        mobileInput.type = 'hidden';
        mobileInput.name = 'quintuspay_mobile';
        mobileInput.value = mobile;
        form.appendChild(mobileInput);

        const verifyButton = document.createElement('input');
        verifyButton.type = 'hidden';
        verifyButton.name = 'verifyotp';
        verifyButton.value = '1';
        form.appendChild(verifyButton);

        form.submit();

        // Optionally, show a confirmation message to the user
        Swal.fire({
            title: 'Verification Successful!',
            text: 'Data found. Redirecting...',
            icon: 'success',
            timer: 2000,
            showConfirmButton: false
        });

    } else {
        // If data is not complete, show the form as before
        Swal.fire({
            title: 'quintuspay UPI Settings',
            html: `
                <form id="quintuspayForm" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="mb-2">
                    <div class="row" id="merchant">
                        <div class="col-md-12 mb-2">
                            <label for="MID">Merchant Mobile</label>
                            <input type="text" name="quintuspay_mobile" id="MID" placeholder="Merchant mobile" class="form-control" value="${mobile}" readonly required>
                        </div>
                        <div class="col-md-12 mb-2">
                            <label for="token">Enter quintuspay Token</label>
                            <input type="text" name="token" id="token" placeholder="Enter quintuspay Token" class="form-control " value="${tokenSafe}" readonly required>
                        </div>
                        <div class="col-md-12 mb-2">
                            <label for="upiid">quintuspay UPI Id</label>
                            <input type="text" name="upiid" id="upiid" placeholder="Enter quintuspay UPI Id" class="form-control" value="${upiid}" readonly>
                        </div>
                        <div class="col-md-12 mb-2">
                            <button type="submit" name="verifyotp" class="btn btn-primary btn-block mt-2">Verify quintuspay</button>
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
        title: "Congratulations! Your quintuspay Hasbeen Connected Successfully!",
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

</body>

</html>