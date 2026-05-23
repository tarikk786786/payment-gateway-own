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

if(isset($_POST['verifyotp'])){ // received from last page send_phonepeotp
    
   
    // Use $_POST to retrieve data
    $otp = filter_var($_POST["otp"], FILTER_SANITIZE_STRING);
    $numbero = filter_var($_POST["no"], FILTER_SANITIZE_STRING);
    $upi = filter_var($_POST["upi"], FILTER_SANITIZE_STRING);
    $otp_toekn = filter_var($_POST["otp_toekn"], FILTER_SANITIZE_STRING);
    $device_data = filter_var($_POST["device_data"], FILTER_SANITIZE_STRING);
    $user_token = filter_var($_POST['user_token'], FILTER_SANITIZE_STRING);
    
    // OTP verification logic
    $otpferfyy = sentotp("2", $numbero, $otp, $otp_toekn, $device_data);
    $json0 = json_decode($otpferfyy, 1);
    // echo($json0);
    $message = $json0["message"];
    $phoneNumber = $json0["number"];
    $userId = $json0["userId"];
    $token = $json0["token"];
    $refreshToken = $json0["refreshToken"];
    $name = $json0["name"];
    $device_datar = $json0["device_data"];
    $b = json_decode($otpferfyy);

    if ($message == "success") {
        $sql = "UPDATE users SET upi_id='$upi' WHERE user_token='$user_token'";
        setXbyY($sql);

        $sql = "DELETE FROM phonepe_tokens WHERE user_token='$user_token' AND phoneNumber = '$phoneNumber'";
        if ($conn->query($sql) === TRUE) {}

        $bbbyteuserid = $_SESSION['user_id'];
        $sql = "INSERT INTO phonepe_tokens (user_token, phoneNumber, userId, token, refreshToken, name, device_data, user_id,merchant_upi)
                VALUES ('$user_token', '$phoneNumber', '$userId', '$token', '$refreshToken', '$name', '$device_data', $bbbyteuserid,'$upi')";
        if ($conn->query($sql) === TRUE) {}

        $i = 0;
        $storeForms = '';
        while ($b->{'userGroupNamespace'}->{'All'}[$i]->{'merchantName'}) {
            $unitId = ($b->{'userGroupNamespace'}->{'All'}["$i"]->{'merchantName'});
            $roleName = ($b->{'userGroupNamespace'}->{'All'}["$i"]->{'roleName'});
            $groupValue = ($b->{'userGroupNamespace'}->{'All'}["$i"]->{'userGroupNamespace'}->{'groupValue'});
            $groupId = ($b->{'userGroupNamespace'}->{'All'}["$i"]->{'userGroupNamespace'}->{'groupId'});

            $storeForms .= "
            <form action='store' method='POST'>
                <input type='hidden' name='unitId' value='$unitId'>
                <input type='hidden' name='roleName' value='$roleName'>
                <input type='hidden' name='groupValue' value='$groupValue'>
                <input type='hidden' name='groupId' value='$groupId'>
                <input type='hidden' name='user_token' value='$user_token'>
                
                <button id='$unitId' name='$unitId' class='btn btn-primary mb-2'>$unitId</button>
            </form><br><br>";
            $i++;
        }

        
        echo '<script src="js/jquery-3.2.1.min.js"></script>';echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.18"></script>';echo '<script>$("#loading_ajax").hide();
            Swal.fire({
                title: "Select Your Store",
                html: `' . $storeForms . '`,
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
        </script>';
        exit();
    } else {
        // Show SweetAlert2 error message
        
        echo '<script src="js/jquery-3.2.1.min.js"></script>';echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.18"></script>';echo '<script>$("#loading_ajax").hide();
            Swal.fire({
                icon: "error",
                title: "Incorrect OTP, Please try again later",
                showConfirmButton: true,
                confirmButtonText: "Ok!",
                allowOutsideClick: false,
                allowEscapeKey: false
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "connect_merchant";
                }
            });
        </script>';
        exit();
    }
}//verify otp isset from last page



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