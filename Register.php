<?php
include "auth/config.php";
include 'auth/function.php';


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ensure database connection is established
    // Assuming $conn is your MySQLi/PDO connection object

    // Handle mobile number check
    if (isset($_POST['checkMobile'])) {
        $mobile = $_POST['mobile'];
        
        $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE mobile = ?");
        $stmt->bind_param("s", $mobile);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        
        header('Content-Type: application/json');
        echo json_encode(['exists' => $count > 0]);
        $stmt->close();
        $conn->close();
        exit;
    }

    // Handle email check
    if (isset($_POST['checkEmail'])) {
        $email = $_POST['email'];
        
        $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        
        header('Content-Type: application/json');
        echo json_encode(['exists' => $count > 0]);
        $stmt->close();
        $conn->close();
        exit;
    }

    // Handle Aadhaar check
    if (isset($_POST['checkAadhaar'])) {
        $aadhaar = $_POST['aadhaar'];
        
        $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE aadhaar = ?");
        $stmt->bind_param("s", $aadhaar);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        
        header('Content-Type: application/json');
        echo json_encode(['exists' => $count > 0]);
        $stmt->close();
        $conn->close();
        exit;
    }

    // Handle PAN check
    if (isset($_POST['checkPAN'])) {
        $pan = $_POST['pan'];
        
        $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE pan = ?");
        $stmt->bind_param("s", $pan);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        
        header('Content-Type: application/json');
        echo json_encode(['exists' => $count > 0]);
        $stmt->close();
        $conn->close();
        exit;
    }
    if (isset($_POST['refotp'])) {
        $email = $_POST['email'];
        $message = $_POST['message'];
        
        $subject = "UpiGateway Registration OTP";
        $result = sendEmail($email, $subject, $message);
        
        if (strpos($result, 'successfully') !== false) {
            echo json_encode(['success' => true, 'msg' => $result]);
        } else {
            echo json_encode(['success' => false, 'msg' => $result]);
        }
        $conn->close();
        exit;
    }
}
?>
<!DOCTYPE html>

<html>

<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

<title><?php echo $site_settings['brand_name']; ?> | Registration</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> 
    <?php echo "<link rel=\"icon\" href=\"https://{$server}/common/img/logoshild.png\">"; ?>
    <!--<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>-->
<!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Remix Icons -->
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="auth/auth-custom.css">
<script disable-devtool-auto="" src="https://cdn.jsdelivr.net/npm/disable-devtool@0.3.8/disable-devtool.min.js" data-url="https://www.google.com/"></script>
<!--<script src="https://<?=$server?>/dev-script.js"></script>-->

</head>



 <?php
if (isset($_GET['referral_code'])) {
    $referred_by = $_GET['referral_code'];
    
    // Check if the referred_by code exists in the database
    $sql_check = "SELECT id FROM users WHERE referral_code = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("s", $referred_by);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    
    if ($result_check->num_rows > 0) {
        // If referral code exists, store it in a session or variable
        $valid_referral = true;
        $referred_by_user = $result_check->fetch_assoc()['id']; // User ID of the referrer
    } else {
        // If referral code does not exist, show error
        $valid_referral = false;
        $referred_by_user = null;
        echo "<script>alert('Invalid referral code! $referred_by');</script>";
    }
} else {
    // If 'referred_by' is not provided, treat it as no referral
    $valid_referral = false;
    $referred_by_user = null;
}


if (isset($_POST['create'])) {
    
    
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // reCAPTCHA removed
        
        // यहाँ पर आपका फॉर्म प्रोसेसिंग कोड

    
$use_referral = isset($_POST['use_referral']) ? true : false;
$referred_by = isset($_POST['referral_code']) ? $_POST['referral_code'] : '';

    // Check if the referred_by code exists in the database
    $sql_check = "SELECT id FROM users WHERE referral_code = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("s", $referred_by);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    
    if ($result_check->num_rows > 0) {
        // If referral code exists, store it in a session or variable
        $valid_referral = true;
        $referred_by_user = $result_check->fetch_assoc()['id']; // User ID of the referrer
    } else {
        // If referral code does not exist, show error
        $valid_referral = false;
        $referred_by_user = null;
    }


$mobile = isset($_POST['mobile']) ? $_POST['mobile'] : '';
$email = isset($_POST['email']) ? $_POST['email'] : '';
$referralCode = generateReferralCode();

$checkMobileQuery = "SELECT * FROM `users` WHERE `mobile` = '$mobile'";
$checkMobileResult = mysqli_query($conn, $checkMobileQuery);

$checkEmailQuery = "SELECT * FROM `users` WHERE `email` = '$email'";
$checkEmailResult = mysqli_query($conn, $checkEmailQuery);

if (mysqli_num_rows($checkMobileResult) > 0) {
echo "<script>alert('Oops! Sorry, Mobile Number Already Exists. Please use a different number.');</script>";
exit;
} elseif (mysqli_num_rows($checkEmailResult) > 0) {
// The email already exists, display an error message
echo "<script>alert('Oops! Sorry, Email Already Exists. Please use a different email.');</script>";
exit;
} else {
// Proceed with user registration
$password = isset($_POST['password']) ? $_POST['password'] : '';
$name = isset($_POST['name']) ? $_POST['name'] : '';
$company = isset($_POST['company']) ? $_POST['company'] : '';
$pin = isset($_POST['pin']) ? $_POST['pin'] : '';
$pan = isset($_POST['pan']) ? $_POST['pan'] : '';
$aadhaar = isset($_POST['aadhaar']) ? $_POST['aadhaar'] : '';
$location = isset($_POST['location']) ? $_POST['location'] : '';

// Check if Mobile or Email already exists
if (mysqli_num_rows($checkMobileResult) > 0) {
    echo "<script>alert('Oops! Sorry, Mobile Number Already Exists. Please use a different number.');</script>";
    exit;
} elseif (mysqli_num_rows($checkEmailResult) > 0) {
    echo "<script>alert('Oops! Sorry, Email Already Exists. Please use a different Email.');</script>";
    exit;
} else {  
 // Function to generate a random instance_id
function generateRandomInstanceId($length = 16) {
  $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
  $randomString = 'I'; // Fixed 'I' as the first character

  // Generate a random string with the specified length - 7 (for the time part and additional digit)
  for ($i = 1; $i < $length - 6; $i++) {
  $randomString .= $characters[rand(0, strlen($characters) - 1)];
  }

  // Get the current time in seconds since the epoch
  $currentTime = time();

  // Take the last 6 digits from the current time and append them to the random string
  $lastSixDigits = substr(strval($currentTime), -6);
  $randint = rand(100, 900);
  
  return $randomString . $randint . $lastSixDigits;
}

if ($use_referral && $valid_referral) {
// Generate random instance_id and instance_secret
$instanceId = generateRandomInstanceId();
$key = md5(rand(00000000, 99999999));
$pass = password_hash($password, PASSWORD_BCRYPT);
$today = date("Y-m-d", strtotime("+1 days"));


$register = "INSERT INTO `users`(`name`, `mobile`, `role`, `balance`, `password`, `email`, `company`, `pin`, `pan`, `aadhaar`, `location`, `user_token`, `expiry`,`vip_expiry`, `instance_id`, referral_code, referred_by, planId, otp, otp_expiry, is_otp, whatsapp_alert, email_alert, callback_url, bptoken, upiid, login_token) 
VALUES ('$name', '$mobile', 'User','0.00', '$pass', '$email', '$company', '$pin', '$pan', '$aadhaar', '$location', '$key', '$today','$today', '$instanceId', '$referralCode', '$referred_by_user','5', '', NOW(), 'NO', 'YES', 'YES', '', '', '', '')";


$result = mysqli_query($conn, $register);



if ($result) {
$msg = "Dear $name thanks For Registering Us
Your Username = $mobile
Your Password = $password
Thanks & Regards
*UpiGateway™*";
// sendWA($mobile,$encodedMsg);
sendNotification($mobile, $email, $msg, "Well-Come To UpiGateway Family");
// Fetch plans from the database
$sql = "SELECT id, plan_name, amount, expiry FROM subscription_plan";
$result = $conn->query($sql);

$plans = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $plans[] = $row;
    }
}
?>

<script>
    Swal.fire({
        title: "Referral Applied!",
        html: `
            <form id="planForm">
                <div style="margin-bottom: 10px;">
                    <label for="plan" style="display: block; font-weight: bold;">Select Plan</label>
                    <select id="plan" name="plan" class="form-control" required>
                        <option value="" disabled selected>Select a plan</option>
                        <?php foreach ($plans as $plan) { ?>
                            <option value="<?php echo $plan['plan_name']; ?>" 
                                data-planid="<?php echo $plan['id']; ?>"
                                data-amount="<?php echo $plan['amount']; ?>" 
                                data-expiry="<?php echo $plan['expiry']; ?>">
                                <?php echo "RS. ".$plan['amount'].' - ' . $plan['plan_name']; ?>

                            </option>
                        <?php } ?>
                    </select>
                </div>
                <div style="margin-bottom: 10px;">
                    <label for="mobile" style="display: block; font-weight: bold;">Mobile Number</label>
                    <input type="text" id="mobile" name="mobile" value="<?php echo $mobile; ?>" class="form-control" placeholder="Mobile Number" readonly>
                </div>
                <div style="margin-bottom: 10px;">
                    <label for="amount" style="display: block; font-weight: bold;">Amount</label>
                    <input type="text" id="amount" name="amount" class="form-control" placeholder="Amount" readonly>
                </div>
                <div style="margin-bottom: 10px;">
                    <label for="validity" style="display: block; font-weight: bold;">Validity (in days)</label>
                    <input type="text" id="validity" name="validity" class="form-control" placeholder="Validity" readonly>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 10px;">Submit</button>
            </form>
        `,
        showConfirmButton: false,
        allowOutsideClick: false,
        didOpen: () => {
            const planDropdown = document.getElementById('plan');
            const amountField = document.getElementById('amount');
            const validityField = document.getElementById('validity');
            const mobileField = document.getElementById('mobile');

            planDropdown.addEventListener('change', (event) => {
                const selectedOption = event.target.selectedOptions[0];
                amountField.value = selectedOption.getAttribute('data-amount');
                validityField.value = selectedOption.getAttribute('data-expiry');
            });

            const form = document.getElementById('planForm');
            form.addEventListener('submit', (e) => {
                e.preventDefault();

                const selectedOption = planDropdown.selectedOptions[0];
                const referpay = 'referpay'; // यहां पर सही referpay वैल्यू डालें
                const planid = selectedOption.getAttribute('data-planid');
                const amount = amountField.value;
                const validity = validityField.value;
                const mobile = mobileField.value;

                // Create a dynamic form for redirection
                const dynamicForm = document.createElement('form');
                dynamicForm.method = 'POST';
                dynamicForm.action = 'auth/lib/pay.php';

                // Add form fields
                dynamicForm.innerHTML = `
                    <input type="hidden" name="for" value="${referpay}">
                    <input type="hidden" name="planid" value="${planid}">
                    <input type="hidden" name="plan" value="${selectedOption.value}">
                    <input type="hidden" name="amount" value="${amount}">
                    <input type="hidden" name="validity" value="${validity}">
                    <input type="hidden" name="mobile" value="${mobile}">
                `;

                // Append and submit the form
                document.body.appendChild(dynamicForm);
                dynamicForm.submit();
            });
        }
    });
</script>
<?}?>


<?php
        exit;
    } else {
$key = md5(rand(00000000, 99999999));
$pass = password_hash($password, PASSWORD_BCRYPT);
$today = date("Y-m-d", strtotime("+1 days"));




// Generate random instance_id and instance_secret
$instanceId = generateRandomInstanceId();


$register = "INSERT INTO `users`(`name`, `mobile`, `role`, `balance`, `password`, `email`, `company`, `pin`, `pan`, `aadhaar`, `location`, `user_token`, `expiry`,`vip_expiry`, `instance_id`, referral_code, referred_by, planId, otp, otp_expiry, is_otp, whatsapp_alert, email_alert, callback_url, bptoken, upiid, login_token) 
VALUES ('$name', '$mobile', 'User','0.00', '$pass', '$email', '$company', '$pin', '$pan', '$aadhaar', '$location', '$key', '$today','$today', '$instanceId', '$referralCode', '$referred_by_user','5', '', NOW(), 'NO', 'YES', 'YES', '', '', '', '')";


$result = mysqli_query($conn, $register);



if ($result) {
$msg = "Dear $name thanks For Registering Us
Your Username = $mobile
Your Password = $password
Thanks & Regards
*UpiGateway™*";


$encodedMsg = urlencode($msg);

// sendWA($mobile,$encodedMsg);
sendNotification($mobile, $email, $msg, "Well-Come To UpiGateway Family");

echo '
    <script>
        alert("Registration Successful! Click OK to continue.");
        window.location.href = "auth/index"; // Replace with your desired redirect URL
    </script>
    ';
    exit;
} else {
echo '
    <script>
        alert("Registration Failed! Please try again.");
        window.location.href = "auth/register"; // Optionally redirect back to registration page
    </script>
    ';
    exit;
}
}
}
}
}
}
?>
<body>
    <div class="premium-auth-container">
        <!-- Left Animated Panel -->
        <div class="premium-auth-left">
            <div class="auth-shapes">
                <div class="shape shape-1"></div>
                <div class="shape shape-2"></div>
            </div>
            <div class="premium-auth-left-content">
                <h1>Join the Future<br>of Payments</h1>
                <p>Set up your account in minutes and start accepting payments instantly with our enterprise-grade infrastructure.</p>
                <ul class="feature-list">
                    <li><i class="ri-rocket-fill"></i> Quick Onboarding</li>
                    <li><i class="ri-shield-check-fill"></i> Bank-Grade Security & PCI-DSS</li>
                    <li><i class="ri-flashlight-fill"></i> Instant Settlements 24/7</li>
                </ul>
            </div>
        </div>

        <!-- Register Form Section -->
        <div class="premium-auth-right" style="overflow-y: auto;">
            <div class="auth-form-wrapper" style="max-width: 500px; padding: 2rem 0; width: 100%;">
                <img src="<?php echo $site_settings['logo_url']; ?>" alt="Logo" class="auth-logo" style="height: 50px;">
                <h2 style="font-size: 1.75rem;">Register <?php echo $site_settings['brand_name']; ?> 🚀</h2>
                <p style="margin-bottom: 2rem;">Start Your Journey To Advanced Payments!</p>
                <div id="toast-container" class="toast-container"></div>
                <form class="mb-5" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" onsubmit="return validateForm()">
                    <div class="form-floating form-floating-outline mb-5">
                        <input type="text" class="form-control" id="username" name="name" placeholder="Enter your Name" 
                               pattern="[A-Za-z\s]+" title="Only letters and spaces allowed" required autofocus onkeyup="validateName()">
                        <label for="username">Name</label>
                        <div id="name-warning" class="text-danger mt-1"></div>
                    </div>
                    
                    <div class="form-floating form-floating-outline mb-5">
                        <input type="number" class="form-control" id="Number" name="mobile" placeholder="Enter your Number" 
                               pattern="[0-9]{10}" title="Enter exactly 10 digits" 
                               oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);" required autofocus onkeyup="validateMobile()">
                        <label for="Number">Mobile Number</label>
                        <div id="mobile-warning" class="text-danger mt-1"></div>
                    </div>

                    <div class="form-floating form-floating-outline mb-5">
                        <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" 
                               pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$" title="Enter a valid email address" required onkeyup="validateEmail()">
                        <label for="email">Email Id</label>
                        <div id="email-warning" class="text-danger mt-1"></div>
                    </div>
                    
                    <div id="hiddenFields">
                        
                        <div class="mb-5 form-password-toggle">
                            <div class="input-group input-group-merge">
                                <div class="form-floating form-floating-outline">
                                    <input type="password" id="password" class="form-control" name="password" 
                                           placeholder="••••••••••••" aria-describedby="password" 
                                           onkeyup="checkPasswordStrength()" required />
                                    <label for="password">Password</label>
                                </div>
                                <span class="input-group-text cursor-pointer" id="togglePassword">
                                    <i class="ri-eye-off-line ri-20px"></i>
                                </span>
                            </div>
                            <div id="password-strength" class="mt-2"></div>
                        </div>

                        <div class="mb-5 form-check">
                            <input class="form-check-input" type="checkbox" id="use_referral" name="use_referral" onchange="toggleReferralInput(this)">
                            <label class="form-check-label" for="use_referral">Apply Referral Code</label>
                        </div>

                        <div id="referralCodeContainer" style="display: none;">
                            <div class="form-floating form-floating-outline mb-5">
                                <input type="text" id="referral_code" class="form-control" name="referral_code" 
                                       placeholder="Referral Code (Optional)" onkeyup="validateReferral()">
                                <label for="referral_code">Referral Code</label>
                                <div id="referral-warning" class="text-danger mt-1"></div>
                            </div>
                        </div>

                        <div class="mb-5 py-2">
                            <div class="form-check mb-0">
                                <input class="form-check-input" type="checkbox" id="terms-conditions" name="terms" required>
                                <label class="form-check-label" for="terms-conditions">
                                    I agree to <a href="javascript:void(0);">privacy policy & terms</a>
                                </label>
                            </div>
                        </div>
                        
                        <button type="submit" name="create" class="btn btn-primary d-grid w-100">Sign up</button>
                    </div>
                </form>
            </div>
<p class="text-center mb-5">
<span>Already have an account?</span>
<a href="auth/index">
<span>Sign in instead</span>
  </a>
</p>
        </div>
    </div>

    <!-- Theme Toggle Button -->
    <button class="theme-toggle" id="themeToggle" title="Toggle Theme">
        <i class="ri-sun-line"></i> <!-- Default light theme, so sun icon -->
    </button>
<script>
// Theme Toggle Logic
        const themeToggle = document.getElementById('themeToggle');
        const body = document.body;
        themeToggle.addEventListener('click', () => {
            body.classList.toggle('light-theme');
            const icon = themeToggle.querySelector('i');
            if (body.classList.contains('light-theme')) {
                icon.classList.replace('ri-sun-line', 'ri-moon-line');
            } else {
                icon.classList.replace('ri-moon-line', 'ri-sun-line');
            }
        });

        // Password Toggle
        document.getElementById('togglePassword').addEventListener('click', function() {
            const password = document.getElementById('password');
            const icon = this.querySelector('i');
            if (password.type === 'password') {
                password.type = 'text';
                icon.classList.replace('ri-eye-off-line', 'ri-eye-line');
            } else {
                password.type = 'password';
                icon.classList.replace('ri-eye-line', 'ri-eye-off-line');
            }
        });
    // URL से referral code निकालने का function
function getReferralCode() {
    const urlParams = new URLSearchParams(window.location.search);
    const referralCode = urlParams.get('referred_by');
    
    if (referralCode) {
        const referralInput = document.getElementById('referral_code');
        const applyReferralCheckbox = document.getElementById('use_referral');
        const referralCodeContainer = document.getElementById('referralCodeContainer');

        // इनपुट फील्ड में वैल्यू सेट करना और readonly बनाना
        referralInput.value = referralCode;
        referralInput.readOnly = true;
        
        // Referral input को दिखाना (display:block)
        referralCodeContainer.style.display = "block";

        // Checkbox को checked रखना
        applyReferralCheckbox.checked = true;
        applyReferralCheckbox.disabled = true;
    }
}



    // Page लोड होते ही function call होगा
    window.onload = getReferralCode;



function showToast(message, type = "info") {
    Swal.fire({
        text: message,
        icon: type,
        toast: true,
        position: "top-end",
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
    });
}


// Removed checkInitialFields logic
function validateName() {
    const name = document.getElementById('username').value;
    const namePattern = /^[A-Za-z\s]+$/;
    const warningDiv = document.getElementById('name-warning');
    
    if (!namePattern.test(name) && name.length > 0) {
        warningDiv.textContent = 'Only letters and spaces are allowed';
    } else {
        warningDiv.textContent = '';
    }
}

function validateMobile() {
    const mobile = document.getElementById('Number').value;
    const mobilePattern = /^[0-9]{10}$/;
    const warningDiv = document.getElementById('mobile-warning');
    
    if (!mobilePattern.test(mobile) && mobile.length > 0) {
        warningDiv.textContent = 'Mobile number must be exactly 10 digits';
    } else {
        warningDiv.textContent = '';
    }
}

// validateOTP removed
// Existing validateEmail function
function validateEmail() {
    const email = document.getElementById('email').value;
    const emailPattern = /^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$/;
    const warningDiv = document.getElementById('email-warning');
    
    if (!emailPattern.test(email) && email.length > 0) {
        warningDiv.textContent = 'Please enter a valid email address';
        return;
    } else {
        warningDiv.textContent = '';
    }

    // Real-time email check
    if (emailPattern.test(email)) {
        fetch('<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `checkEmail=true&email=${encodeURIComponent(email)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.exists) {
                warningDiv.textContent = 'This email is already registered. Please use a different email or sign in.';
            } else {
                warningDiv.textContent = '';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            warningDiv.textContent = 'Error checking email';
        });
    }
}

// Removed unused validation functions for aadhaar, pan, and pin

function validateReferral() {
    const referral = document.getElementById('referred_by').value;
    const referralPattern = /^[A-Za-z0-9]{6,10}$/;
    const warningDiv = document.getElementById('referral-warning');
    
    if (referral.length > 0 && !referralPattern.test(referral)) {
        warningDiv.textContent = 'Referral code must be 6-10 alphanumeric characters';
    } else {
        warningDiv.textContent = '';
    }
}

// OTP functions removed

// Update validateForm to ensure no warnings exist before submission
function validateForm() {
    const name = document.getElementById('username').value;
    const mobile = document.getElementById('Number').value;
    const email = document.getElementById('email').value;
    const referral = document.getElementById('referred_by') ? document.getElementById('referred_by').value : '';

    const namePattern = /^[A-Za-z\s]+$/;
    const mobilePattern = /^[0-9]{10}$/;
    const emailPattern = /^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$/;
    const referralPattern = /^[A-Za-z0-9]{6,10}$/;

    // Check for warning messages
    const warnings = [
        document.getElementById('name-warning') ? document.getElementById('name-warning').textContent : '',
        document.getElementById('mobile-warning') ? document.getElementById('mobile-warning').textContent : '',
        document.getElementById('email-warning') ? document.getElementById('email-warning').textContent : '',
        document.getElementById('referral-warning') ? document.getElementById('referral-warning').textContent : ''
    ];

    if (warnings.some(warning => warning !== '')) {
        showToast("Please fix the errors before submitting.", "error");
        return false;
    }

    if (!namePattern.test(name)) {
        showToast("Invalid name format.", "error");
        return false;
    }
    if (!mobilePattern.test(mobile)) {
        showToast("Invalid mobile number.", "error");
        return false;
    }
    if (!emailPattern.test(email)) {
        showToast("Invalid email address.", "error");
        return false;
    }
    return true;
}

function checkPasswordStrength() {
    const password = document.getElementById('password').value;
    const strengthDiv = document.getElementById('password-strength');
    
    let strength = 0;
    if (password.length > 5) strength++;
    if (password.length > 8) strength++;
    if (/[A-Z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[^A-Za-z0-9]/.test(password)) strength++;

    switch(strength) {
        case 0:
        case 1:
            strengthDiv.textContent = 'Weak';
            strengthDiv.style.color = 'red';
            break;
        case 2:
        case 3:
            strengthDiv.textContent = 'Medium';
            strengthDiv.style.color = 'orange';
            break;
        case 4:
            strengthDiv.textContent = 'Strong';
            strengthDiv.style.color = 'blue';
            break;
        case 5:
            strengthDiv.textContent = 'Very Strong';
            strengthDiv.style.color = 'green';
            break;
    }
}

function toggleReferralInput(checkbox) {
    const referralContainer = document.getElementById('referralCodeContainer');
    if (checkbox.checked) {
        referralContainer.style.display = 'block';
    } else {
        referralContainer.style.display = 'none';
        document.getElementById('referred_by').value = '';
        document.getElementById('referral-warning').textContent = '';
    }
}

document.getElementById('togglePassword').addEventListener('click', function () {
    const passwordField = document.getElementById('password');
    const icon = this.querySelector('i');
    
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
</body>
</html>
<!-- beautify ignore:end -->