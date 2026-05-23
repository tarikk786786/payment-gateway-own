<?php
session_start();
include "auth/config.php";
include 'auth/function.php';
?>

<!DOCTYPE html>

<html lang="en" class="light-style layout-wide  customizer-hide" dir="ltr" data-theme="theme-default" data-assets-path="common/assets/" data-template="vertical-menu-template" data-style="light">

<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

<title>Register | <?php echo $site_settings['brand_name']; ?></title>


<!-- Favicon -->
<link rel="icon" type="image/x-icon" href="common/img/favicon.png" />

<!-- Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&ampdisplay=swap" rel="stylesheet">

<!-- Icons -->
<link rel="stylesheet" href="common/assets/vendor/fonts/remixicon/remixicon.css" />
<link rel="stylesheet" href="common/assets/vendor/fonts/flag-icons.css" />

<!-- Menu waves for no-customizer fix -->
<link rel="stylesheet" href="common/assets/vendor/libs/node-waves/node-waves.css" />

<!-- Core CSS -->
<link rel="stylesheet" href="common/assets/vendor/css/rtl/core.css" class="template-customizer-core-css" />
<link rel="stylesheet" href="common/assets/vendor/css/rtl/theme-default.css" class="template-customizer-theme-css" />
<link rel="stylesheet" href="common/assets/css/demo.css" />

<!-- Vendors CSS -->
<link rel="stylesheet" href="common/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />
<link rel="stylesheet" href="common/assets/vendor/libs/typeahead-js/typeahead.css" />
<!-- Vendor -->
<link rel="stylesheet" href="common/assets/vendor/libs/@form-validation/form-validation.css" />

<!-- Page CSS -->
<!-- Page -->
<link rel="stylesheet" href="common/assets/vendor/css/pages/page-auth.css">

<!-- Helpers -->
<script src="common/assets/vendor/js/helpers.js"></script>
<!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->
<!--? Template customizer: To hide customizer set displayCustomizer value false in config.js.  -->
<script src="common/assets/vendor/js/template-customizer.js"></script>
<!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
<script src="common/assets/js/config.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="../common/css/login-Register.css">

</head>

<body>

<!-- Content -->

<div class="authentication-wrapper authentication-cover">

<div class="authentication-inner row m-0">

<!-- /Left Text -->
<div class="d-none d-lg-flex col-lg-7 col-xl-8 align-items-center justify-content-center p-12 pb-2">
<div>
<img src="common/img/auth-v2-login-illustration.Cx3OW7g8.png" class="authentication-image-model d-none d-lg-block" alt="auth-model" data-app-light-img="illustrations/auth-cover-register-illustration-light.png" data-app-dark-img="illustrations/auth-cover-register-illustration-dark.png">
</div>
<!--<img src="common/assets/img/illustrations/tree-2.png" alt="tree" class="authentication-image-tree z-n1">-->
<img src="common/assets/img/illustrations/auth-cover-mask-light.png" class="scaleX-n1-rtl authentication-image d-none d-lg-block w-75" height="362" alt="triangle-bg" data-app-light-img="illustrations/auth-cover-mask-light.png" data-app-dark-img="illustrations/auth-cover-mask-dark.png">
</div>
<!-- /Left Text -->

<!-- Register -->
<div class="d-flex col-12 col-lg-5 col-xl-4 align-items-center authentication-bg position-relative py-sm-5 px-12 py-4">
<div class="w-px-400 mx-auto pt-5 pt-lg-0">
<img src="<?php echo $site_settings['logo_url']; ?>" alt="Logo" class="img-fluid" style="max-width: 150px; height: auto;">
<div>
 <div>
  <div>
<h4 class="mb-1">Register <?php echo $site_settings['brand_name']; ?> 🚀</h4>
<p class="mb-5">Make your app management easy and fun!</p>

 <?php
include "auth/config.php";

if (isset($_POST['create'])) {
    $mobile = $_POST['mobile'];
    $email = $_POST['email'];

    $checkMobileQuery = "SELECT * FROM `users` WHERE `mobile` = '$mobile'";
    $checkMobileResult = mysqli_query($conn, $checkMobileQuery);

    $checkEmailQuery = "SELECT * FROM `users` WHERE `email` = '$email'";
    $checkEmailResult = mysqli_query($conn, $checkEmailQuery);

    if (mysqli_num_rows($checkMobileResult) > 0) {
        echo '<script>Swal.fire({title: "Opps! Mobile Number Already Exist.", icon: "error"})</script>';
    } elseif (mysqli_num_rows($checkEmailResult) > 0) {
        echo '<script>Swal.fire({title: "Opps! Email Already Exist.", icon: "error"})</script>';
    } else {
        $pan = $_POST['pan'];
        $aadhaar = $_POST['aadhaar'];

        $checkpan = "SELECT * FROM `users` WHERE `pan` = '$pan'";
        $checkpanResult = mysqli_query($conn, $checkpan);

        $checkaadhar = "SELECT * FROM `users` WHERE `aadhaar` = '$aadhaar'";
        $checkAadharResult = mysqli_query($conn, $checkaadhar);

        if (mysqli_num_rows($checkpanResult) > 0) {
            echo '<script>Swal.fire({title: "Opps! Pan Number Already Exist.", icon: "error"})</script>';
        } elseif (mysqli_num_rows($checkAadharResult) > 0) {
            echo '<script>Swal.fire({title: "Opps! Aadhaar Number Already Exist.", icon: "error"})</script>';
        } else {
            // Generate OTP
            $otp = rand(100000, 999999);
            $_SESSION['registration_data'] = $_POST;
            $_SESSION['registration_otp'] = $otp;

            // Send OTP via Email
            $msg = "Your UpiGateway Registration OTP is: $otp\n\nPlease enter this OTP to complete your registration.";
            $subject = "UpiGateway Registration OTP";
            sendEmail($email, $subject, nl2br($msg));

            // Set flag to show OTP modal
            $_SESSION['show_otp_modal'] = true;
        }
    }
}

if (isset($_POST['verify_otp'])) {
    $entered_otp = $_POST['otp_code'];
    
    if (isset($_SESSION['registration_otp']) && $entered_otp == $_SESSION['registration_otp']) {
        // OTP verified, proceed with registration
        $data = $_SESSION['registration_data'];
        
        $mobile = $data['mobile'];
        $email = $data['email'];
        $referralCode = generateReferralCode();
        $password = $data['password'];
        $name = $data['name'];
        $company = $data['company'];
        $pin = $data['pin'];
        $pan = $data['pan'];
        $aadhaar = $data['aadhaar'];
        $location = $data['location'];
        
        $key = md5(rand(00000000, 99999999));
        $pass = password_hash($password, PASSWORD_BCRYPT);
        $today = date("Y-m-d", strtotime("+3 days"));

        function generateRandomInstanceId($length = 16) {
            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $randomString = 'I'; 
            for ($i = 1; $i < $length - 6; $i++) {
                $randomString .= $characters[rand(0, strlen($characters) - 1)];
            }
            $currentTime = time();
            $lastSixDigits = substr(strval($currentTime), -6);
            $randint = rand(100, 900);
            return $randomString . $randint . $lastSixDigits;
        }

        $instanceId = generateRandomInstanceId();

        $register = "INSERT INTO `users`(`name`, `mobile`, `role`, `balance`, `password`, `email`, `company`, `pin`, `pan`, `aadhaar`, `location`, `user_token`, `expiry`, `instance_id`, referral_code) 
        VALUES ('$name', '$mobile', 'User','0.00', '$pass', '$email', '$company', '$pin', '$pan', '$aadhaar', '$location', '$key', '$today', '$instanceId', '$referralCode')";

        $result = mysqli_query($conn, $register);

        $msg = "Dear $name thanks For Registering Us\nYour Username = $mobile\nYour Password = $password\nThanks & Regards\n*UpiGateway™*";
        sendNotification($mobile, $email, $msg, "Well-Come To UpiGateway Family");

        // Clear session data
        unset($_SESSION['registration_data']);
        unset($_SESSION['registration_otp']);
        unset($_SESSION['show_otp_modal']);

        if ($result) {
            echo '<script>
            Swal.fire({title: "Registration Successfull!!", icon: "success"}).then(() => {
                window.location.href = "index.php";
            });
            </script>';
        } else {
            echo '<script>Swal.fire({title: "Registration Failed!!", icon: "error"})</script>';
        }
    } else {
        echo '<script>Swal.fire({title: "Invalid OTP", text: "The OTP you entered is incorrect.", icon: "error"})</script>';
        $_SESSION['show_otp_modal'] = true; // Keep modal open
    }
}
?>
    <div class="dynamic-background">
        <div class="circle"></div>
        <div class="circle"></div>
        <div class="circle"></div>
    </div>

<form class="mb-5" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
<div class="form-floating form-floating-outline mb-5">
<input type="text" class="form-control" id="username" name="name" placeholder="Enter your Name" autofocus>
<label for="username">Name</label>
</div>
<div class="form-floating form-floating-outline mb-5">
<input type="number" class="form-control" id="Number" name="mobile"  placeholder="Enter your Number" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);" autofocus>
<label for="username">Mobile Number</label>
</div>
<div class="form-floating form-floating-outline mb-5">
<input type="email" class="form-control" id="email" name="email" placeholder="Enter your email">
<label for="email">Email Id</label>
</div>
<div class="form-floating form-floating-outline mb-5">
<input type="text" class="form-control" id="company" name="company" placeholder="Enter your Company Name" required>
<label for="username">Company Name</label>
</div>
<div class="form-floating form-floating-outline mb-5">
<input type="number" class="form-control" id="aadhar" name="aadhaar" placeholder="Enter your Aadhaar Number" required>
<label for="username">Aadhaar Number</label>
</div>
<div class="form-floating form-floating-outline mb-5">
<input type="text" class="form-control" id="pan" name="pan" placeholder="Enter your Pan Number" required>
<label for="username">Pan Number</label>
</div>
<div class="form-floating form-floating-outline mb-5">
<input type="text" class="form-control" id="location" name="location" placeholder="Enter your Location" required autofocus>
<label for="username">Location</label>
</div>
<div class="form-floating form-floating-outline mb-5">
<input type="text" class="form-control" id="pin" name="pin" placeholder="Enter your Pincode" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 6);" required autofocus>
<label for="username">Pincode</label>
</div>

<div class="mb-5 form-password-toggle">
  <div class="input-group input-group-merge">
    <div class="form-floating form-floating-outline">
      <input type="password" id="password" class="form-control" name="password" placeholder="••••••••••••" aria-describedby="password" />
      <label for="password">Password</label>
    </div>
    <span class="input-group-text cursor-pointer" id="togglePassword">
      <i class="ri-eye-off-line ri-20px"></i>
    </span>
  </div>
</div>
<div class="mb-5 py-2">
<div class="form-check mb-0">
<input class="form-check-input" type="checkbox" id="terms-conditions" name="terms">
<label class="form-check-label" for="terms-conditions">
I agree to
<a href="javascript:void(0);">privacy policy & terms</a>
  </label>
</div>
</div>
<button type="submit" name="create" class="btn btn-primary d-grid w-100">
Sign up
  </button>
</form>
<script>
  document.getElementById('togglePassword').addEventListener('click', function () {
    const passwordField = document.getElementById('password');
    const icon = this.querySelector('i');
    
    // Toggle the type attribute
    if (passwordField.type === 'password') {
      passwordField.type = 'text';
      icon.classList.remove('ri-eye-off-line');
      icon.classList.add('ri-eye-line');
    } else {
      passwordField.type = 'password';
      icon.classList.remove('ri-eye-line');
      icon.classList.add('ri-eye-off-line');
    }
  });
</script>
<p class="text-center mb-5">
<span>Already have an account?</span>
<a href="auth/index">
<span>Sign in instead</span>
  </a>
</p>

<div class="divider my-5">
<div class="divider-text">or</div>
</div>

<div class="d-flex justify-content-center gap-2">
<a href="javascript:;" class="btn btn-icon btn-lg rounded-pill btn-text-facebook">
<i class="tf-icons ri-facebook-fill ri-24px"></i>
  </a>

<a href="javascript:;" class="btn btn-icon btn-lg rounded-pill btn-text-twitter">
<i class="tf-icons ri-twitter-fill ri-24px"></i>
  </a>

<a href="javascript:;" class="btn btn-icon btn-lg rounded-pill btn-text-github">
<i class="tf-icons ri-github-fill ri-24px"></i>
  </a>

<a href="javascript:;" class="btn btn-icon btn-lg rounded-pill btn-text-google-plus">
<i class="tf-icons ri-google-fill ri-24px"></i>
  </a>
</div>
</div>
</div>
<!-- /Register -->
</div>
</div>

<!-- / Content -->




<!-- Core JS -->
<!-- build:js assets/vendor/js/core.js -->
<script src="common/assets/vendor/libs/jquery/jquery.js"></script>
<script src="common/assets/vendor/libs/popper/popper.js"></script>
<script src="common/assets/vendor/js/bootstrap.js"></script>
<script src="common/assets/vendor/libs/node-waves/node-waves.js"></script>
<script src="common/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
<script src="common/assets/vendor/libs/hammer/hammer.js"></script>
<script src="common/assets/vendor/libs/i18n/i18n.js"></script>
<script src="common/assets/vendor/libs/typeahead-js/typeahead.js"></script>
<script src="common/assets/vendor/js/menu.js"></script>

<!-- endbuild -->

<!-- Vendors JS -->
<script src="common/assets/vendor/libs/@form-validation/popular.js"></script>
<script src="common/assets/vendor/libs/@form-validation/bootstrap5.js"></script>
<script src="common/assets/vendor/libs/@form-validation/auto-focus.js"></script>

<!-- Main JS -->
<script src="common/assets/js/main.js"></script>


<!-- Page JS -->
<script src="common/assets/js/pages-auth.js"></script>

<?php if (isset($_SESSION['show_otp_modal']) && $_SESSION['show_otp_modal']): ?>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        Swal.fire({
            title: 'Verify Your Email',
            text: 'We have sent a 6-digit OTP to your email address. Please enter it below to complete registration.',
            input: 'text',
            inputAttributes: {
                maxlength: 6,
                autocapitalize: 'off',
                autocorrect: 'off',
                placeholder: '123456'
            },
            showCancelButton: true,
            cancelButtonText: 'Cancel Registration',
            confirmButtonText: 'Verify OTP',
            allowOutsideClick: false
        }).then((result) => {
            if (result.isConfirmed) {
                // Submit the OTP via a hidden form
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = window.location.href;
                
                const actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'verify_otp';
                actionInput.value = '1';
                form.appendChild(actionInput);
                
                const otpInput = document.createElement('input');
                otpInput.type = 'hidden';
                otpInput.name = 'otp_code';
                otpInput.value = result.value;
                form.appendChild(otpInput);
                
                document.body.appendChild(form);
                form.submit();
            } else {
                // Cancelled
                window.location.href = window.location.href;
            }
        });
    });
</script>
<?php endif; ?>

</body>

</html>

<!-- beautify ignore:end -->