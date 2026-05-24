<?php
include "auth/config.php";
include 'auth/function.php';

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ensure the connection uses modern charset
$conn->set_charset("utf8mb4");

// ---------------------------------------------------------
// AJAX HANDLERS (Return early)
// ---------------------------------------------------------

if ($_SERVER["REQUEST_METHOD"] == "POST") {
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
}

// ---------------------------------------------------------
// REGISTRATION HANDLER
// ---------------------------------------------------------

$registration_error = '';
$registration_success = false;
$registered_mobile = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create'])) {
    
    // 1. Sanitize and validate inputs
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $mobile = isset($_POST['mobile']) ? trim($_POST['mobile']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $use_referral = isset($_POST['use_referral']) ? true : false;
    $referral_code_input = isset($_POST['referral_code']) ? trim($_POST['referral_code']) : '';
    
    // Default empty strings for unused fields
    $company = '';
    $pin = '';
    $pan = '';
    $aadhaar = '';
    $location = '';

    // Validate lengths
    if(empty($name) || empty($mobile) || empty($email) || empty($password)) {
        $registration_error = "All fields are required.";
    } elseif(!preg_match('/^[0-9]{10}$/', $mobile)) {
        $registration_error = "Mobile number must be exactly 10 digits.";
    } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $registration_error = "Invalid email format.";
    } else {
        // 2. Check duplicates securely
        $stmt = $conn->prepare("SELECT id FROM users WHERE mobile = ? OR email = ?");
        $stmt->bind_param("ss", $mobile, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $registration_error = "Mobile Number or Email already exists. Please use different ones or log in.";
        }
        $stmt->close();

        if (empty($registration_error)) {
            // 3. Handle Referral System
            $valid_referral = false;
            $referred_by_user = null;

            if ($use_referral && !empty($referral_code_input)) {
                $stmt_check = $conn->prepare("SELECT id FROM users WHERE referral_code = ?");
                $stmt_check->bind_param("s", $referral_code_input);
                $stmt_check->execute();
                $result_check = $stmt_check->get_result();
                
                if ($result_check->num_rows > 0) {
                    $valid_referral = true;
                    $referred_by_user = $result_check->fetch_assoc()['id'];
                } else {
                    $registration_error = "Invalid referral code.";
                }
                $stmt_check->close();
            }

            if (empty($registration_error)) {
                // 4. Generate system keys
                if(!function_exists('generateReferralCode')) {
                    function generateReferralCodeLocal($length = 8) {
                        return strtoupper(substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, $length));
                    }
                    $referralCode = generateReferralCodeLocal();
                } else {
                    $referralCode = generateReferralCode();
                }
                
                $key = md5(random_bytes(16)); // Secure token
                $pass = password_hash($password, PASSWORD_BCRYPT);
                $today = date("Y-m-d", strtotime("+1 days"));
                
                if(!function_exists('generateSecureInstanceId')) {
                    function generateSecureInstanceId($length = 16) {
                        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                        $randomString = 'I'; 
                        for ($i = 1; $i < $length - 6; $i++) {
                            $randomString .= $characters[random_int(0, strlen($characters) - 1)];
                        }
                        $currentTime = time();
                        $lastSixDigits = substr(strval($currentTime), -6);
                        $randint = random_int(100, 900);
                        return $randomString . $randint . $lastSixDigits;
                    }
                }
                
                $instanceId = generateSecureInstanceId();
                $referred_by_val = empty($referred_by_user) ? null : (int)$referred_by_user;
                $role = 'User';
                $balance = '0.00';
                $planId = '5';
                $otp = '';
                $is_otp = 'NO';
                $whatsapp_alert = 'YES';
                $email_alert = 'YES';
                $empty_str = '';

                // 5. Insert User safely
                $insert_sql = "INSERT INTO users (
                    name, mobile, role, balance, password, email, company, pin, pan, aadhaar, location, 
                    user_token, expiry, vip_expiry, instance_id, referral_code, referred_by, planId, 
                    otp, otp_expiry, is_otp, whatsapp_alert, email_alert, callback_url, bptoken, upiid, login_token
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?, ?)";
                
                $stmt = $conn->prepare($insert_sql);
                // Types: ssssssssssssssssisssssssss
                // i for integer (referred_by) but wait, referred_by is nullable. 
                // If it's nullable and we bind it as 's' or 'i', we must be careful with null.
                $stmt->bind_param("ssssssssssssssssssssssssss", 
                    $name, $mobile, $role, $balance, $pass, $email, $company, $pin, $pan, $aadhaar, $location,
                    $key, $today, $today, $instanceId, $referralCode, $referred_by_val, $planId,
                    $otp, $is_otp, $whatsapp_alert, $email_alert, $empty_str, $empty_str, $empty_str, $empty_str
                );
                
                if ($stmt->execute()) {
                    $registration_success = true;
                    $registered_mobile = $mobile;

                    $msg = "Dear $name thanks For Registering Us\nYour Username = $mobile\nYour Password = $password\nThanks & Regards\n*UpiGateway™*";
                    // Send notification (assume function exists in function.php)
                    if (function_exists('sendNotification')) {
                        sendNotification($mobile, $email, $msg, "Well-Come To UpiGateway Family");
                    }
                } else {
                    $registration_error = "Registration Failed! Error: " . $stmt->error;
                }
                $stmt->close();
            }
        }
    }
}

// If page loads with ?referred_by=XYZ
$url_referral = isset($_GET['referral_code']) ? $_GET['referral_code'] : (isset($_GET['referred_by']) ? $_GET['referred_by'] : '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($site_settings['brand_name']) ? $site_settings['brand_name'] : 'FastGateway'; ?> | Register</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php if(isset($server)) echo "<link rel=\"icon\" href=\"https://{$server}/common/img/logoshild.png\">"; ?>
    <style>
        body {
            background: linear-gradient(135deg, #e0e7ff 0%, #ede9fe 100%);
            min-height: 100vh;
            color: #1e293b;
            overflow-x: hidden;
            font-family: 'Inter', sans-serif;
        }
        
        /* Floating background shapes */
        .bg-shape {
            position: absolute;
            filter: blur(60px);
            z-index: -1;
            animation: float 10s infinite ease-in-out alternate;
        }
        .shape-1 {
            width: 400px; height: 400px;
            background: rgba(167, 139, 250, 0.4);
            top: -100px; left: -100px;
            border-radius: 50%;
        }
        .shape-2 {
            width: 500px; height: 500px;
            background: rgba(96, 165, 250, 0.3);
            bottom: -150px; right: -100px;
            border-radius: 40% 60% 70% 30% / 40% 50% 60% 50%;
            animation-delay: -5s;
        }

        @keyframes float {
            0% { transform: translateY(0) scale(1); }
            100% { transform: translateY(-30px) scale(1.1); }
        }

        .glass-panel {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.6);
            box-shadow: 0 25px 50px -12px rgba(100, 116, 139, 0.25);
        }

        /* Input animations */
        .input-wrapper {
            position: relative;
            transition: all 0.3s ease;
        }
        .input-wrapper:focus-within {
            transform: translateY(-2px);
        }
        
        .floating-input {
            background: rgba(241, 245, 249, 0.7);
            border: 2px solid transparent;
            transition: all 0.3s ease;
            color: #334155;
        }
        .floating-input:focus {
            background: #ffffff;
            border-color: #8b5cf6;
            box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.1);
        }
        .floating-input:focus ~ .floating-label,
        .floating-input:not(:placeholder-shown) ~ .floating-label {
            transform: translateY(-1.5rem) scale(0.85);
            color: #8b5cf6;
            font-weight: 600;
        }
        .floating-label {
            color: #64748b;
        }

        /* Hide scrollbar */
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        
        .btn-gradient {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            transition: all 0.4s ease;
            background-size: 200% auto;
        }
        .btn-gradient:hover {
            background-position: right center;
            transform: translateY(-3px);
            box-shadow: 0 10px 20px -10px rgba(139, 92, 246, 0.6);
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen p-4 relative">

    <!-- Animated Background Shapes -->
    <div class="bg-shape shape-1"></div>
    <div class="bg-shape shape-2"></div>

    <div class="glass-panel w-full max-w-5xl rounded-3xl overflow-hidden flex flex-col md:flex-row shadow-2xl animate__animated animate__zoomIn animate__faster">
        <!-- Left Banner -->
        <div class="hidden md:flex md:w-5/12 bg-gradient-to-br from-indigo-600 to-purple-700 p-12 flex-col justify-between relative overflow-hidden text-white">
            <div class="absolute top-0 right-0 -mt-20 -mr-20 w-80 h-80 bg-white opacity-10 rounded-full blur-3xl animate-pulse"></div>
            <div class="absolute bottom-0 left-0 -mb-20 -ml-20 w-80 h-80 bg-purple-400 opacity-20 rounded-full blur-3xl animate-pulse" style="animation-delay: 1s;"></div>
            
            <div class="z-10 animate__animated animate__fadeInLeft animate__delay-1s">
                <h1 class="text-4xl font-extrabold mb-4 leading-tight">Join the Future<br><span class="text-purple-200">of Payments.</span></h1>
                <p class="text-indigo-100 text-lg leading-relaxed">Set up your account in minutes and start accepting payments instantly with our beautiful, enterprise-grade infrastructure.</p>
            </div>
            
            <div class="z-10 space-y-6 animate__animated animate__fadeInUp animate__delay-1s">
                <div class="flex items-center text-indigo-100 hover:text-white transition-colors transform hover:translate-x-2 duration-300">
                    <div class="bg-white bg-opacity-20 p-3 rounded-xl mr-4"><i class="fas fa-rocket text-xl"></i></div>
                    <span class="font-medium">Quick Onboarding</span>
                </div>
                <div class="flex items-center text-indigo-100 hover:text-white transition-colors transform hover:translate-x-2 duration-300">
                    <div class="bg-white bg-opacity-20 p-3 rounded-xl mr-4"><i class="fas fa-shield-alt text-xl"></i></div>
                    <span class="font-medium">Bank-Grade Security</span>
                </div>
                <div class="flex items-center text-indigo-100 hover:text-white transition-colors transform hover:translate-x-2 duration-300">
                    <div class="bg-white bg-opacity-20 p-3 rounded-xl mr-4"><i class="fas fa-bolt text-xl"></i></div>
                    <span class="font-medium">Instant Settlements 24/7</span>
                </div>
            </div>
        </div>

        <!-- Register Form -->
        <div class="w-full md:w-7/12 p-8 md:p-12 max-h-[90vh] overflow-y-auto no-scrollbar relative z-10 bg-white bg-opacity-90">
            <div class="mb-8 text-center md:text-left animate__animated animate__fadeInDown">
                <?php if(!empty($site_settings['logo_url']) && $site_settings['logo_url'] !== 'default_logo.png'): ?>
                    <img src="<?php echo htmlspecialchars($site_settings['logo_url']); ?>" alt="Logo" class="h-14 mb-6 mx-auto md:mx-0 drop-shadow-md transition-transform hover:scale-105 duration-300">
                <?php else: ?>
                    <div class="inline-block bg-gradient-to-br from-blue-500 to-indigo-600 p-4 rounded-2xl mb-6 text-white shadow-lg transition-transform hover:scale-105 duration-300">
                        <i class="fas fa-user-plus text-3xl"></i>
                    </div>
                <?php endif; ?>
                <h2 class="text-3xl font-bold text-gray-800 mb-2">Register <?php echo isset($site_settings['brand_name']) ? $site_settings['brand_name'] : ''; ?> 🚀</h2>
                <p class="text-gray-500">Start Your Journey To Advanced Payments!</p>
            </div>

            <form id="formAuthentication" action="" method="POST" class="space-y-6" onsubmit="return validateForm()">
                <div class="relative input-wrapper animate__animated animate__fadeInUp animate__delay-1s">
                    <input type="text" id="username" name="name" class="floating-input w-full rounded-xl px-4 py-3 outline-none peer" placeholder=" " pattern="[A-Za-z\s]+" title="Only letters and spaces allowed" required>
                    <label for="username" class="floating-label absolute left-4 top-3 text-gray-400 transition-all pointer-events-none">Full Name</label>
                    <div id="name-warning" class="text-red-400 text-xs mt-1 absolute"></div>
                </div>

                <div class="relative mt-8 input-wrapper animate__animated animate__fadeInUp animate__delay-1s" style="animation-delay: 0.2s;">
                    <input type="text" id="Number" name="mobile" class="floating-input w-full rounded-xl px-4 py-3 outline-none peer" placeholder=" " maxlength="10" pattern="\d{10}" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10); validateMobile();" required>
                    <label for="Number" class="floating-label absolute left-4 top-3 text-gray-400 transition-all pointer-events-none">Mobile Number</label>
                    <div id="mobile-warning" class="text-red-400 text-xs mt-1 absolute"></div>
                </div>

                <div class="relative mt-8 input-wrapper animate__animated animate__fadeInUp animate__delay-1s" style="animation-delay: 0.3s;">
                    <input type="email" id="email" name="email" class="floating-input w-full rounded-xl px-4 py-3 outline-none peer" placeholder=" " pattern="[a-z0-9._%+\-]+@[a-z0-9.\-]+\.[a-z]{2,}$" onkeyup="validateEmail()" required>
                    <label for="email" class="floating-label absolute left-4 top-3 text-gray-400 transition-all pointer-events-none">Email Address</label>
                    <div id="email-warning" class="text-red-400 text-xs mt-1 absolute"></div>
                </div>

                <div class="relative mt-8 input-wrapper animate__animated animate__fadeInUp animate__delay-1s" style="animation-delay: 0.4s;">
                    <input type="password" id="password" name="password" class="floating-input w-full rounded-xl px-4 py-3 outline-none peer" placeholder=" " onkeyup="checkPasswordStrength()" required>
                    <label for="password" class="floating-label absolute left-4 top-3 text-gray-400 transition-all pointer-events-none">Password</label>
                    <button type="button" id="togglePassword" class="absolute right-4 top-3 text-gray-400 hover:text-white transition-colors">
                        <i class="fas fa-eye-slash"></i>
                    </button>
                    <div id="password-strength" class="text-xs mt-1 font-semibold absolute"></div>
                </div>

                <div class="mt-8 animate__animated animate__fadeInUp animate__delay-1s" style="animation-delay: 0.5s;">
                    <label class="flex items-center text-gray-600 cursor-pointer hover:text-indigo-600 transition-colors group">
                        <input type="checkbox" id="use_referral" name="use_referral" class="mr-3 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 h-5 w-5 transition-transform group-hover:scale-110" onchange="toggleReferralInput(this)">
                        <span class="font-medium">I have a referral code</span>
                    </label>
                </div>

                <div id="referralCodeContainer" class="relative mt-4 input-wrapper animate__animated animate__fadeIn" style="display: none;">
                    <input type="text" id="referral_code" name="referral_code" class="floating-input w-full rounded-xl px-4 py-3 outline-none peer" placeholder=" " value="<?php echo htmlspecialchars($url_referral); ?>">
                    <label for="referral_code" class="floating-label absolute left-4 top-3 text-gray-400 transition-all pointer-events-none">Referral Code</label>
                </div>

                <div class="mt-6 flex items-center animate__animated animate__fadeInUp animate__delay-1s" style="animation-delay: 0.6s;">
                    <label class="flex items-center text-gray-500 cursor-pointer hover:text-gray-700 transition-colors text-sm">
                        <input type="checkbox" required class="mr-3 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 h-4 w-4">
                        <span>I agree to the <a href="#" class="text-indigo-500 hover:text-indigo-600 font-medium underline">Terms and Privacy Policy</a></span>
                    </label>
                </div>

                <button type="submit" name="create" id="registerBtn" class="w-full btn-gradient text-white font-bold py-4 px-4 rounded-xl shadow-lg transition-all mt-8 animate__animated animate__fadeInUp animate__delay-1s" style="animation-delay: 0.7s;">
                    Create Account <i class="fas fa-arrow-right ml-2"></i>
                </button>
            </form>

            <div class="mt-10 text-center pt-6 animate__animated animate__fadeInUp animate__delay-1s" style="animation-delay: 0.8s;">
                <p class="text-gray-500 font-medium">
                    Already have an account? <a href="auth/index.php" class="text-indigo-600 hover:text-indigo-700 font-bold transition-colors ml-1">Sign in here</a>
                </p>
            </div>
        </div>
    </div>

    <?php
    $plans = [];
    $sql_plans = "SELECT id, plan_name, amount, expiry FROM subscription_plan";
    $result_plans = $conn->query($sql_plans);
    if ($result_plans && $result_plans->num_rows > 0) {
        while ($row = $result_plans->fetch_assoc()) {
            $plans[] = $row;
        }
    }
    $plans_json = json_encode($plans);
    ?>

    <script>
        const plans = <?php echo $plans_json; ?>;

        <?php if(!empty($registration_error)): ?>
            Swal.fire({
                icon: 'error',
                title: 'Registration Failed',
                text: <?php echo json_encode($registration_error); ?>,
                background: '#1e293b',
                color: '#fff'
            });
        <?php endif; ?>

        <?php if($registration_success): ?>
            <?php if(isset($valid_referral) && $valid_referral): ?>
                
                let planOptions = '<option value="" disabled selected>Select a plan</option>';
                plans.forEach(plan => {
                    planOptions += `<option value="${plan.plan_name}" data-planid="${plan.id}" data-amount="${plan.amount}" data-expiry="${plan.expiry}">RS. ${plan.amount} - ${plan.plan_name}</option>`;
                });

                Swal.fire({
                    title: "Referral Applied! 🎉",
                    background: '#1e293b',
                    color: '#fff',
                    html: `
                        <form id="planForm" class="text-left space-y-4 mt-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-1">Select Plan</label>
                                <select id="swal-plan" class="w-full bg-gray-800 text-white border border-gray-600 rounded-lg p-2 outline-none focus:border-blue-500" required>
                                    ${planOptions}
                                </select>
                            </div>
                            <div class="flex space-x-4">
                                <div class="w-1/2">
                                    <label class="block text-sm font-medium text-gray-300 mb-1">Amount</label>
                                    <input type="text" id="swal-amount" class="w-full bg-gray-700 text-gray-300 border border-gray-600 rounded-lg p-2 outline-none" readonly>
                                </div>
                                <div class="w-1/2">
                                    <label class="block text-sm font-medium text-gray-300 mb-1">Validity (days)</label>
                                    <input type="text" id="swal-validity" class="w-full bg-gray-700 text-gray-300 border border-gray-600 rounded-lg p-2 outline-none" readonly>
                                </div>
                            </div>
                        </form>
                    `,
                    showCancelButton: true,
                    confirmButtonText: 'Submit & Pay',
                    confirmButtonColor: '#3b82f6',
                    cancelButtonColor: '#64748b',
                    allowOutsideClick: false,
                    preConfirm: () => {
                        const planEl = document.getElementById('swal-plan');
                        if(!planEl.value) {
                            Swal.showValidationMessage('Please select a plan');
                            return false;
                        }
                        const selectedOption = planEl.selectedOptions[0];
                        return {
                            planid: selectedOption.getAttribute('data-planid'),
                            planName: planEl.value,
                            amount: document.getElementById('swal-amount').value,
                            validity: document.getElementById('swal-validity').value
                        };
                    },
                    didOpen: () => {
                        const planDropdown = document.getElementById('swal-plan');
                        const amountField = document.getElementById('swal-amount');
                        const validityField = document.getElementById('swal-validity');
                        
                        planDropdown.addEventListener('change', (e) => {
                            const opt = e.target.selectedOptions[0];
                            amountField.value = opt.getAttribute('data-amount');
                            validityField.value = opt.getAttribute('data-expiry');
                        });
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        const data = result.value;
                        const dynamicForm = document.createElement('form');
                        dynamicForm.method = 'POST';
                        dynamicForm.action = 'auth/lib/pay.php';
                        dynamicForm.innerHTML = `
                            <input type="hidden" name="for" value="referpay">
                            <input type="hidden" name="planid" value="${data.planid}">
                            <input type="hidden" name="plan" value="${data.planName}">
                            <input type="hidden" name="amount" value="${data.amount}">
                            <input type="hidden" name="validity" value="${data.validity}">
                            <input type="hidden" name="mobile" value="<?php echo $registered_mobile; ?>">
                        `;
                        document.body.appendChild(dynamicForm);
                        dynamicForm.submit();
                    } else {
                        window.location.href = "auth/index.php";
                    }
                });

            <?php else: ?>
                Swal.fire({
                    icon: 'success',
                    title: 'Welcome aboard! 🚀',
                    text: 'Registration successful. Redirecting to login...',
                    background: '#1e293b',
                    color: '#fff',
                    showConfirmButton: false,
                    timer: 2500,
                    timerProgressBar: true
                }).then(() => {
                    window.location.href = "auth/index.php";
                });
            <?php endif; ?>
        <?php endif; ?>

        // Password Toggle
        document.getElementById('togglePassword').addEventListener('click', function() {
            const password = document.getElementById('password');
            const icon = this.querySelector('i');
            if (password.type === 'password') {
                password.type = 'text';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            } else {
                password.type = 'password';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            }
        });

        function toggleReferralInput(checkbox) {
            const container = document.getElementById('referralCodeContainer');
            const input = document.getElementById('referral_code');
            if (checkbox.checked) {
                container.style.display = 'block';
            } else {
                container.style.display = 'none';
                input.value = '';
            }
        }

        window.onload = () => {
            const urlReferral = "<?php echo htmlspecialchars($url_referral); ?>";
            if(urlReferral) {
                const checkbox = document.getElementById('use_referral');
                checkbox.checked = true;
                toggleReferralInput(checkbox);
            }
        };

        let emailTimer;
        function validateEmail() {
            clearTimeout(emailTimer);
            const email = document.getElementById('email').value;
            const warning = document.getElementById('email-warning');
            const pattern = /^[a-z0-9._%+\-]+@[a-z0-9.\-]+\.[a-z]{2,}$/i;
            
            if(!email) { warning.textContent = ''; return; }
            if(!pattern.test(email)) { warning.textContent = 'Invalid email format.'; warning.className = 'text-red-400 text-xs mt-1 absolute'; return; }
            
            warning.textContent = 'Checking...';
            warning.className = 'text-yellow-400 text-xs mt-1 absolute';
            
            emailTimer = setTimeout(() => {
                fetch('<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: `checkEmail=true&email=${encodeURIComponent(email)}`
                }).then(r => r.json()).then(d => {
                    if(d.exists) {
                        warning.textContent = 'Email already registered.';
                        warning.className = 'text-red-400 text-xs mt-1 absolute';
                    } else {
                        warning.textContent = 'Available ✓';
                        warning.className = 'text-green-400 text-xs mt-1 absolute';
                    }
                }).catch(() => warning.textContent = '');
            }, 600);
        }

        let mobileTimer;
        function validateMobile() {
            clearTimeout(mobileTimer);
            const mobile = document.getElementById('Number').value;
            const warning = document.getElementById('mobile-warning');
            
            if(!mobile) { warning.textContent = ''; return; }
            if(mobile.length < 10) { warning.textContent = 'Must be 10 digits.'; warning.className = 'text-red-400 text-xs mt-1 absolute'; return; }
            
            warning.textContent = 'Checking...';
            warning.className = 'text-yellow-400 text-xs mt-1 absolute';

            mobileTimer = setTimeout(() => {
                fetch('<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: `checkMobile=true&mobile=${encodeURIComponent(mobile)}`
                }).then(r => r.json()).then(d => {
                    if(d.exists) {
                        warning.textContent = 'Mobile already registered.';
                        warning.className = 'text-red-400 text-xs mt-1 absolute';
                    } else {
                        warning.textContent = 'Available ✓';
                        warning.className = 'text-green-400 text-xs mt-1 absolute';
                    }
                }).catch(() => warning.textContent = '');
            }, 600);
        }

        function checkPasswordStrength() {
            const pwd = document.getElementById('password').value;
            const div = document.getElementById('password-strength');
            if(!pwd) { div.textContent = ''; return; }
            
            let s = 0;
            if(pwd.length > 5) s++;
            if(pwd.length > 8) s++;
            if(/[A-Z]/.test(pwd)) s++;
            if(/[0-9]/.test(pwd)) s++;
            if(/[^A-Za-z0-9]/.test(pwd)) s++;

            if(s <= 1) { div.textContent = 'Weak'; div.className = 'text-red-400 text-xs mt-1 font-semibold absolute'; }
            else if(s <= 3) { div.textContent = 'Medium'; div.className = 'text-yellow-400 text-xs mt-1 font-semibold absolute'; }
            else { div.textContent = 'Strong'; div.className = 'text-green-400 text-xs mt-1 font-semibold absolute'; }
        }

        function validateForm() {
            const mobileWarn = document.getElementById('mobile-warning').textContent;
            const emailWarn = document.getElementById('email-warning').textContent;
            if(mobileWarn.includes('already') || emailWarn.includes('already') || mobileWarn.includes('Invalid') || emailWarn.includes('Invalid')) {
                Swal.fire({icon:'error', title:'Hold up!', text:'Please fix the highlighted errors before submitting.', background:'#1e293b', color:'#fff'});
                return false;
            }
            const btn = document.getElementById('registerBtn');
            btn.innerHTML = '<i class="fas fa-circle-notch fa-spin mr-2"></i> Processing...';
            btn.style.opacity = '0.8';
            btn.style.pointerEvents = 'none';
            return true;
        }
    </script>
</body>
</html>