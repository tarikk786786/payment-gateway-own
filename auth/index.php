<?php
include "config.php";
include 'function.php';
session_start();
?>

<!DOCTYPE html>
<html lang="en" class="light-style layout-wide customizer-hide" dir="ltr" data-theme="theme-default" data-assets-path="common/assets/" data-template="vertical-menu-template" data-style="light">

<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
<link rel="icon" href="https://chickenpox.in/common/img/logoshild.png">
<title><?php echo $site_settings['brand_name']; ?> | Login</title>
<link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
<link rel="stylesheet" href="auth-custom.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<?php
session_start();

if (isset($_POST['submit'])) {

    $username = $_POST['username'];
    $password = $_POST['password'];

    $query = "SELECT * FROM users WHERE mobile = '$username'";
    $run = mysqli_query($conn, $query);
    $row = mysqli_fetch_array($run);

    if (mysqli_num_rows($run) > 0) {
        $hashFromDatabase = $row['password'];
        $acc_lock = $row['acc_lock'];
        $acc_ban = $row['acc_ban'];
        $byteuserid = $row['id'];

        if ($acc_ban == 'on') {
            echo '
            <script>
            Swal.fire({
                title: "Account Locked!",
                text: "Please contact the administrator.",
                icon: "error",
                confirmButtonText: "Ok"
            }).then(() => {
                window.location.href = "index";
            });
            </script>';
            exit;
        }

        if (password_verify($password, $hashFromDatabase)) {
            // Reset failed attempts on successful login
            $query = "UPDATE users SET acc_lock = 0 WHERE mobile = '$username'";
            mysqli_query($conn, $query);

            // Set complete session
            $_SESSION['username'] = $username;
            $_SESSION['user_id'] = $byteuserid;
            $token = bin2hex(random_bytes(32));
            $_SESSION['login_token'] = $token;
            $_SESSION['login_complete'] = true;
            mysqli_query($conn, "UPDATE users SET login_token='$token' WHERE mobile='$username'");

            echo '<script>window.location.href = "dashboard";</script>';
            exit;

        } else {
            // Increment failed attempts
            $acc_lock++;
            $query = "UPDATE users SET acc_lock = $acc_lock WHERE mobile = '$username'";
            mysqli_query($conn, $query);

            if ($acc_lock >= 3) {
                echo '
                <script>
                Swal.fire({
                    title: "Account Locked!",
                    text: "Too many failed login attempts. Please contact the administrator.",
                    icon: "error",
                    confirmButtonText: "Ok"
                }).then(() => {
                    window.location.href = "index";
                });
                </script>';
                exit;
            }

            echo '<script>Swal.fire("Invalid Password!", "Please try again.", "error");</script>';
        }
    } else {
        echo '<script>Swal.fire("Invalid Username!", "No account found with this mobile number.", "error");</script>';
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
                <h1>Welcome to<br>Advanced Payments</h1>
                <p>Experience seamless, secure, and instant settlements with our enterprise-grade infrastructure.</p>
                
                <ul class="feature-list">
                    <li><i class="ri-shield-check-fill"></i> Bank-Grade Security & PCI-DSS</li>
                    <li><i class="ri-flashlight-fill"></i> Instant Settlements 24/7</li>
                    <li><i class="ri-bar-chart-box-fill"></i> Real-time Analytics & Dashboard</li>
                </ul>
            </div>
        </div>

        <!-- Right Form Panel -->
        <div class="premium-auth-right">
            <div class="auth-form-wrapper">
                <?php if(!empty($site_settings['logo_url'])): ?>
                    <img src="<?php echo $site_settings['logo_url']; ?>" alt="Logo" class="auth-logo">
                <?php else: ?>
                    <h2 style="color: var(--auth-primary); margin-bottom: 2rem;">Gateway</h2>
                <?php endif; ?>
                
                <h2>Sign in to your account</h2>
                <p>Welcome back! Please enter your details.</p>
                
                <form id="formAuthentication" action="index.php" method="POST">
                    
                    <div class="floating-group">
                        <input type="text" class="floating-input" id="mobileNumber" name="username" 
                               placeholder=" " maxlength="10" pattern="\d{10}" required>
                        <label class="floating-label" for="mobileNumber">Mobile Number</label>
                    </div>

                    <div class="floating-group">
                        <input type="password" class="floating-input" id="password" name="password" 
                               placeholder=" " required>
                        <label class="floating-label" for="password">Password</label>
                        <span class="password-toggle" id="togglePassword">
                            <i class="ri-eye-off-line"></i>
                        </span>
                    </div>

                    <div class="auth-links">
                        <label style="display:flex; align-items:center; cursor:pointer; color: var(--auth-text-muted);">
                            <input type="checkbox" id="remember-me" required style="margin-right: 8px;">
                            Accept Terms
                        </label>
                        <a href="../forgot-password">Forgot password?</a>
                    </div>

                    <button class="auth-btn" type="submit" id="loginBtn" name="submit">Sign in</button>
                    
                </form>

                <div class="social-login-divider">or continue with</div>
                
                <a href="#" class="social-btn">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/5/53/Google_%22G%22_Logo.svg" alt="Google">
                    Sign in with Google
                </a>

                <p class="text-center mt-4" style="color: var(--auth-text-muted);">
                    Don't have an account? <a href="../Register" style="color: var(--auth-primary); text-decoration: none; font-weight: 600;">Sign up</a>
                </p>

                <div class="security-badges">
                    <div class="badge-item"><i class="ri-lock-2-fill"></i> 256-bit Encryption</div>
                    <div class="badge-item"><i class="ri-shield-check-line"></i> PCI DSS Compliant</div>
                </div>
            </div>
        </div>

    </div>

    <script>
        // Login Button Spinner
        document.getElementById('loginBtn').addEventListener('click', function(event) {
            if(document.getElementById('formAuthentication').checkValidity()) {
                this.innerHTML = 'Processing...';
                this.disabled = true;
                this.form.submit();
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
    </script>
</body>
</html>
