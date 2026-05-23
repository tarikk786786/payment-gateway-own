<?php

// Dene the absolute path to the functions.php file
define('ABSPATH', dirname(__FILE__) . '/'); // Adjust the path as needed
// Include the database connection file
require_once(ABSPATH . 'header.php');


?>

<?php

// include "../pages/dbFunctions.php";
include "../pages/dbInfo.php";
include "../phnpe/index.php";


if(isset($_POST['Verify'])){
    
  
    
if ($userdata['phonepe_connected']=="Yes"){
        
         
                              // Show SweetAlert2 error message
                           
echo '<script src="js/jquery-3.2.1.min.js"></script>';echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.18"></script>';echo '<script>$("#loading_ajax").hide();
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
    
    
    $no =sanitizeInput($_REQUEST['phonepe_mobile']);
    
   
    
    
     $sendotpresult=sentotp(1,$no,0,0,0);
        echo $sendotpresult;
        // exit;
        
$json0=json_decode($sendotpresult,1);
$otpSended=$json0["otpSended"];
$phoneNumber=$json0["phoneNumber"];
$token=$json0["token"];
$device=$json0["device"];

   
   
   
if($otpSended == 'true'){
    
    
    
    
 // Show SweetAlert2 success message
   
    echo '<script src="js/jquery-3.2.1.min.js"></script>';echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.18"></script>';echo '<script>$("#loading_ajax").hide();
        Swal.fire({
        title: "Your OTP Has Been Sent!!",
        text: "Please click Ok button!!",
        icon: "success",
        confirmButtonText: "Ok",
        allowOutsideClick: false,
        allowEscapeKey: false
    }).then((result) => {
        if (result.isConfirmed) {
            showOtpForm();
        }
    });
        
        function showOtpForm() {
            Swal.fire({
                title: "PhonePe UPI Settings",
                html: `
                    <form id="phonepeForm" method="POST" action="phonepe_verify" class="mb-2">
                        <div class="row" id="merchant">
                            <div class="col-md-12 mb-2">
                                <label for="otp">Enter OTP</label>
                                <input type="number" name="otp" id="otp" placeholder="Enter OTP" class="form-control" required>
                            </div>
                            <input type="hidden" name="number" value="' . $no . '">
                            <input type="hidden" name="upi" value="">
                            <input type="hidden" name="user_token" value="' . $userdata['user_token'] . '">
                    
                            <input type="hidden" name="no" value="' . $phoneNumber . '">
                            <input type="hidden" name="otp_toekn" value="' . $token . '">
                            <input type="hidden" name="device_data" value="' . $device . '">
                            <div class="col-md-12 mb-2">
                                <button type="submit" name="verifyotp" class="btn btn-primary btn-block mt-2">Verify OTP</button>
                            </div>
                        </div>
                    </form>
                `,
                showCancelButton: false,
                showConfirmButton: false,
                customClass: {
                    popup: "swal2-custom-popup",
                    title: "swal2-title",
                    content: "swal2-content"
                },
                allowOutsideClick: false,
                allowEscapeKey: false
            });
        }
    </script>';
    exit;


}


else{
    
          // Show SweetAlert2 error message
                           
echo '<script src="js/jquery-3.2.1.min.js"></script>';echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.18"></script>';echo '<script>$("#loading_ajax").hide();
    Swal.fire({
        icon: "error",
        title: "OTP Error!!",
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
} //phonepe otp noot sended 
   
   

} //isset veiryfy otp
else{
    
echo '<script src="js/jquery-3.2.1.min.js"></script>';echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.18"></script>';echo '<script>$("#loading_ajax").hide();
    Swal.fire({
        icon: "error",
        title: "Form Not Submitted!!",
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


