<?php
include "config.php";
include 'function.php';
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $site_settings['brand_name']; ?> | Secure Login</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%);
            min-height: 100vh;
            color: #f8fafc;
        }
        .glass-panel {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }
        .floating-input:focus ~ .floating-label,
        .floating-input:not(:placeholder-shown) ~ .floating-label {
            transform: translateY(-1.5rem) scale(0.85);
            color: #38bdf8;
        }
    </style>
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

<body class="flex items-center justify-center min-h-screen p-4">
    <div class="glass-panel w-full max-w-4xl rounded-3xl overflow-hidden flex flex-col md:flex-row">
        <!-- Left Banner -->
        <div class="hidden md:flex md:w-1/2 bg-gradient-to-br from-indigo-600 to-blue-900 p-12 flex-col justify-between relative overflow-hidden">
            <div class="absolute top-0 right-0 -mt-20 -mr-20 w-80 h-80 bg-white opacity-10 rounded-full blur-3xl"></div>
            <div class="absolute bottom-0 left-0 -mb-20 -ml-20 w-80 h-80 bg-blue-400 opacity-20 rounded-full blur-3xl"></div>
            
            <div class="z-10">
                <h1 class="text-4xl font-extrabold text-white mb-4">UpiGateway<br><span class="text-blue-300">Modernized.</span></h1>
                <p class="text-blue-100 text-lg leading-relaxed">Experience seamless, ultra-secure, and lightning-fast settlements with our completely upgraded enterprise infrastructure.</p>
            </div>
            
            <div class="z-10 space-y-4">
                <div class="flex items-center text-blue-100"><i class="fas fa-shield-alt w-6 text-xl"></i> <span>Bank-Grade 256-bit Security</span></div>
                <div class="flex items-center text-blue-100"><i class="fas fa-bolt w-6 text-xl"></i> <span>Instant Settlements 24/7</span></div>
                <div class="flex items-center text-blue-100"><i class="fas fa-chart-line w-6 text-xl"></i> <span>Real-time Analytics</span></div>
            </div>
        </div>

        <!-- Login Form -->
        <div class="w-full md:w-1/2 p-8 md:p-12">
            <div class="mb-8 text-center md:text-left">
                <?php if(!empty($site_settings['logo_url']) && $site_settings['logo_url'] !== 'default_logo.png'): ?>
                    <img src="<?php echo $site_settings['logo_url']; ?>" alt="Logo" class="h-12 mb-4 mx-auto md:mx-0">
                <?php else: ?>
                    <div class="inline-block bg-blue-600 p-3 rounded-xl mb-4 text-white">
                        <i class="fas fa-wallet text-2xl"></i>
                    </div>
                <?php endif; ?>
                <h2 class="text-3xl font-bold text-white mb-2">Welcome Back</h2>
                <p class="text-gray-400">Sign in to access your dashboard</p>
            </div>

            <form id="formAuthentication" action="index.php" method="POST" class="space-y-6">
                <div class="relative">
                    <input type="text" id="mobileNumber" name="username" class="floating-input w-full bg-gray-800 bg-opacity-50 text-white border border-gray-600 rounded-xl px-4 py-3 outline-none focus:border-blue-500 transition-colors peer" placeholder=" " maxlength="10" pattern="\d{10}" required>
                    <label for="mobileNumber" class="floating-label absolute left-4 top-3 text-gray-400 transition-all pointer-events-none">Mobile Number</label>
                </div>

                <div class="relative">
                    <input type="password" id="password" name="password" class="floating-input w-full bg-gray-800 bg-opacity-50 text-white border border-gray-600 rounded-xl px-4 py-3 outline-none focus:border-blue-500 transition-colors peer" placeholder=" " required>
                    <label for="password" class="floating-label absolute left-4 top-3 text-gray-400 transition-all pointer-events-none">Password</label>
                    <button type="button" id="togglePassword" class="absolute right-4 top-3 text-gray-400 hover:text-white transition-colors">
                        <i class="fas fa-eye-slash"></i>
                    </button>
                </div>

                <div class="flex items-center justify-between text-sm">
                    <label class="flex items-center text-gray-400 cursor-pointer hover:text-white transition-colors">
                        <input type="checkbox" required class="mr-2 rounded border-gray-600 text-blue-600 focus:ring-blue-500 bg-gray-800">
                        <span>I accept the Terms</span>
                    </label>
                    <a href="../forgot-password" class="text-blue-400 hover:text-blue-300 transition-colors font-medium">Forgot password?</a>
                </div>

                <button type="submit" id="loginBtn" name="submit" class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-bold py-3 px-4 rounded-xl shadow-lg transition-all transform hover:-translate-y-1">
                    Sign In to Dashboard
                </button>
            </form>

            <div class="mt-8 text-center">
                <p class="text-gray-400 text-sm">
                    Don't have an account? <a href="../Register" class="text-blue-400 hover:text-blue-300 font-bold transition-colors">Sign up</a>
                </p>
            </div>
        </div>
    </div>

    <script>
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

        // Loading state on submit
        document.getElementById('formAuthentication').addEventListener('submit', function() {
            if(this.checkValidity()) {
                const btn = document.getElementById('loginBtn');
                btn.innerHTML = '<i class="fas fa-circle-notch fa-spin mr-2"></i> Authenticating...';
                btn.style.opacity = '0.8';
                btn.style.pointerEvents = 'none';
            }
        });
    </script>
</body>
</html>
