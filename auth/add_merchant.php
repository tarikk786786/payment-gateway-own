<?php include "header.php"; ?>
    <!-- Include the SweetAlert CDN link -->

<?php
  include "config.php";
  if (isset($_POST['create'])) {
      
   $mobile =  $_POST['mobile'];
    $email = $_POST['email'];

    // Check if the mobile number already exists in the database
    $checkMobileQuery = "SELECT * FROM `users` WHERE `mobile` = '$mobile'";
    $checkMobileResult = mysqli_query($conn, $checkMobileQuery);

    // Check if the email already exists in the database
    $checkEmailQuery = "SELECT * FROM `users` WHERE `email` = '$email'";
    $checkEmailResult = mysqli_query($conn, $checkEmailQuery);

    if (mysqli_num_rows($checkMobileResult) > 0) {
        // The mobile number already exists, display an error message
        echo '
    <script>
        Swal.fire({
            title: "Opps! Your Mobile no Already Exist!",
            text: "Please Click Ok Button!!",
            confirmButtonText: "Ok",
            icon: "error"
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "add_merchant"; // Replace with your desired redirect URL
            }
        });
    </script>
';
    } elseif (mysqli_num_rows($checkEmailResult) > 0) {
        // The email already exists, display an error message
        echo '
    <script>
        Swal.fire({
            title: "Opps! Your Email no Already Exist!",
            text: "Please Click Ok Button!!",
            confirmButtonText: "Ok",
            icon: "error"
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "adduser"; // Replace with your desired redirect URL
            }
        });
    </script>
';
exit;
    } else {
    $mobile   = mysqli_real_escape_string($conn, $_POST['mobile']);
    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $name     = mysqli_real_escape_string($conn, $_POST['name']);
    $company  = mysqli_real_escape_string($conn, $_POST['company']);
    $pin      = mysqli_real_escape_string($conn, $_POST['pin']);
    $pan      = strtoupper(mysqli_real_escape_string($conn, $_POST['pan']));
    $aadhaar  = mysqli_real_escape_string($conn, $_POST['aadhaar']);
    $location = mysqli_real_escape_string($conn, $_POST['location']);
    $Parent   = mysqli_real_escape_string($conn, $_POST['Parent']);

    // Check for duplicates
    $checkQuery = "SELECT * FROM users WHERE mobile='$mobile' OR email='$email' OR pan='$pan' OR aadhaar='$aadhaar'";
    $checkResult = mysqli_query($conn, $checkQuery);

    if (mysqli_num_rows($checkResult) > 0) {
        echo '
        <script>
            Swal.fire({
                title: "Already Registered!",
                text: "Mobile, Email, PAN, or Aadhaar already exists.",
                confirmButtonText: "Ok",
                icon: "warning"
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "adduser"; // Go back
                }
            });
        </script>';
        exit;
    }

    // Proceed with registration
    $key = md5(rand(10000000, 99999999));
    $pass = password_hash($password, PASSWORD_BCRYPT);
    $today = date("Y-m-d", strtotime("+1 day"));

    $register = "INSERT INTO `users`(`name`, `mobile`, `role`, `password`, `email`, `company`, `pin`, `pan`, `aadhaar`, `location`, `user_token`, `expiry`, `referred_by`, `planId`) 
    VALUES ('$name','$mobile','User','$pass','$email','$company','$pin','$pan','$aadhaar','$location','$key','$today','$Parent','5')";

    $result = mysqli_query($conn, $register);

    $msg = "Dear $name You Are Registered Successfully
Your Username: $mobile
Your Password: $password
Thanks & Regards
UpiGateway™";

    $encodedMsg = urlencode($msg);

    if ($result) {
        $notification_response = sendNotification($mobile, $email, $msg, "WELLCOME PARTNER");
        echo '
        <script>
            Swal.fire({
                title: "Congratulations! User Added!",
                text: "Please Click Ok Button!!",
                confirmButtonText: "Ok",
                icon: "success"
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "dashboard";
                }
            });
        </script>';
        exit;
    } else {
        echo '
        <script>
            Swal.fire({
                title: "Oops! Something Went Wrong!",
                text: "Please Click Ok Button!!",
                confirmButtonText: "Ok",
                icon: "error"
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "adduser";
                }
            });
        </script>';
        exit;
    }
}
  }
  
if ($userdata["role"] != 'Admin' && $userdata["role"] != 'Developer') {
    echo '<script>
        window.location.href = "dashboard";
    </script>';
    exit;
}
  
  ?>
            <!-- START PAGE CONTENT-->
            <div class="page-heading">
                <h1 class="page-title">Merchant Add Setting</h1>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="index.html"><i class="la la-home font-20"></i></a>
                    </li>
                    <!-- <li class="breadcrumb-item">Icons</li> -->
                </ol>
            </div>
            <div class="page-content fade-in-up">
                <div class="ibox">
                    <div class="ibox-body">
                        <!-- <div class="row"> -->
                            <!-- <div class="col-md-4"> -->
                                <div class="card m-t-20 m-b-20">
                                    <div class="card-body">
                                    <div class="main-panel">

                                    <div class="main-panel">
				<div class="content">
					<div class="container-fluid">

						<!-- <h4 class="page-title">My Profile</h4>	 -->
										
						<div class="row row-card-no-pd">							
							<div class="col-md-12">

<form class="row mb-4" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" onsubmit="return validateForm();">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

    <div class="col-md-6 mb-2">
        <label>Mobile Number</label>
        <input type="text" name="mobile" id="mobile" placeholder="Enter Mobile Number" class="form-control"
            maxlength="10" required />
        <small class="text-danger" id="mobileError"></small>
    </div>

    <div class="col-md-6 mb-2">
        <label>Password</label>
        <input type="password" name="password" id="password" placeholder="Enter Password" class="form-control" required />
        <small class="text-danger" id="passwordError"></small>
    </div>

    <div class="col-md-6 mb-2">
        <label>Email Address</label>
        <input type="email" name="email" id="email" placeholder="Enter Email Address" class="form-control" required />
        <small class="text-danger" id="emailError"></small>
    </div>

    <div class="col-md-6 mb-2">
        <label>Name</label>
        <input type="text" name="name" id="name" placeholder="Enter Name" class="form-control" required />
        <small class="text-danger" id="nameError"></small>
    </div>

    <div class="col-md-6 mb-2">
        <label>Company</label>
        <input type="text" name="company" id="company" placeholder="Enter Company" class="form-control" required />
    </div>

    <div class="col-md-6 mb-2">
        <label>Area Pin</label>
        <input type="text" name="pin" id="pin" placeholder="Area Pin" class="form-control" maxlength="6" required />
        <small class="text-danger" id="pinError"></small>
    </div>

    <div class="col-md-6 mb-2">
        <label>PAN Number</label>
        <input type="text" name="pan" id="pan" placeholder="Enter PAN Number" class="form-control" maxlength="10" required />
        <small class="text-danger" id="panError"></small>
    </div>

    <div class="col-md-6 mb-2">
        <label>Aadhaar Number</label>
        <input type="text" name="aadhaar" id="aadhaar" placeholder="Enter Aadhaar Number" class="form-control" maxlength="12" required />
        <small class="text-danger" id="aadhaarError"></small>
    </div>

    <div class="col-md-12 mb-2">
        <label>Location</label>
        <input type="text" name="location" id="location" placeholder="Enter Location" class="form-control" required />
    </div>

    <div class="col-md-12 mb-2">
        <label>Parent</label>
        <input type="text" name="Parent" value="<?=$userdata['id']?>" class="form-control" readonly required />
    </div>

    <div class="col-md-12 mb-2 mt-2">
        <button type="submit" name="create" class="btn btn-primary btn-sm">Add Now</button>
    </div>
</form>


              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

</div>
<!-- Include necessary scripts -->
<!--<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>-->
<!--<script src="./assets/vendors/jquery/dist/jquery.min.js" type="text/javascript"></script>-->
<!--<script src="assets/js/plugin/jquery-ui-1.12.1.custom/jquery-ui.min.js"></script>-->
<!--<script src="assets/js/core/popper.min.js"></script>-->
<!--<script src="assets/js/core/bootstrap.min.js"></script>-->
<!--<script src="assets/js/plugin/bootstrap-notify/bootstrap-notify.min.js"></script>-->
<!--<script src="assets/js/plugin/bootstrap-toggle/bootstrap-toggle.min.js"></script>-->
<!--<script src="assets/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js"></script>-->
<!--<script src="assets/js/ready.min.js"></script>-->
<!--<script src="assets/js/scripts/dashboard_1_demo.js" type="text/javascript"></script>-->

<script>
function validateForm() {
    let valid = true;

    // Clear previous error messages
    document.querySelectorAll("small.text-danger").forEach(el => el.innerText = "");

    // Mobile validation
    const mobile = document.getElementById("mobile").value.trim();
    if (!/^[6-9]\d{9}$/.test(mobile)) {
        document.getElementById("mobileError").innerText = "Enter valid 10-digit mobile starting with 6-9.";
        valid = false;
    }

    // Password validation (min 6 chars, one uppercase, one number)
    const password = document.getElementById("password").value;
    if (!/(?=.*[A-Z])(?=.*\d).{6,}/.test(password)) {
        document.getElementById("passwordError").innerText = "Password must be at least 6 characters with 1 uppercase & 1 number.";
        valid = false;
    }

    // Email validation
    const email = document.getElementById("email").value.trim();
    if (!/^[\w\.-]+@[a-zA-Z\d-]+(\.[a-zA-Z]{2,})+$/.test(email)) {
        document.getElementById("emailError").innerText = "Enter a valid email address.";
        valid = false;
    }

    // Name validation
    const name = document.getElementById("name").value.trim();
    if (!/^[a-zA-Z\s]{2,}$/.test(name)) {
        document.getElementById("nameError").innerText = "Enter a valid name (letters and spaces only).";
        valid = false;
    }

    // Pin validation
    const pin = document.getElementById("pin").value.trim();
    if (!/^\d{6}$/.test(pin)) {
        document.getElementById("pinError").innerText = "Enter valid 6-digit pin code.";
        valid = false;
    }

    // PAN validation
    const pan = document.getElementById("pan").value.trim().toUpperCase();
    if (!/[A-Z]{5}[0-9]{4}[A-Z]{1}/.test(pan)) {
        document.getElementById("panError").innerText = "Enter valid PAN (e.g., ABCDE1234F).";
        valid = false;
    }

    // Aadhaar validation
    const aadhaar = document.getElementById("aadhaar").value.trim();
    if (!/^\d{12}$/.test(aadhaar)) {
        document.getElementById("aadhaarError").innerText = "Enter valid 12-digit Aadhaar number.";
        valid = false;
    }

    return valid; // Prevent form submission if false
}
</script>

