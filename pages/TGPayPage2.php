<?php
define('ROOT_DIR', realpath(dirname(__FILE__)) . '/../');
// echo($server);
// Database connection check
if (!isset($conn) || !$conn) {
    die("Database connection error.");
}
if (!$logo) {
    $logo = "uploads/logos/67febdcaabbf7.png";
}
// Ensure order_id is set
if (!isset($order_id) || empty($order_id)) {
    die("Invalid Order ID.");
}
if (isset($merchentMobile)) {
    $update= db_custom_query($conn, "UPDATE orders SET merchentMobile = '$merchentMobile' WHERE order_id ='$order_id'");
}

// Prepare the SQL statement
$sql = "SELECT status,user_id FROM orders WHERE order_id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("SQL Prepare Error: " . $conn->error);
}

// Bind the parameter
$stmt->bind_param("s", $order_id);

// Execute the query
if (!$stmt->execute()) {
    die("SQL Execution Error: " . $stmt->error);
}

// Get the result
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $status = $row['status'];
    if ($method != "CHECK"){
    $user_id = $row['user_id'];
    }
    // Check order status
    if ($status == "FAILURE") {
        echo "Order Not In Process__Status: " . htmlspecialchars($status);
        exit;
    }
} else {
    // echo "No order found with the given Order ID.";
}

$query = "SELECT * FROM user_checkout_settings WHERE user_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id); // 'i' for integer
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$settings = mysqli_fetch_assoc($result);

// Initialize variables with database values or defaults
$showQr = $settings['show_qr'] ?? 1;
$showPaytmButton = $settings['show_paytmButton'] ?? 1;
$showHelp = $settings['show_help'] ?? 1;
$removeBranding = $settings['remove_branding'] ?? 0; // Default to 0 (VIP-only)
$showPaymentLogos = $settings['show_payment_logos'] ?? 1;
$showIntentButton = $settings['Show_IntentButton'] ?? 1;
$showUpiRequest = $settings['show_upiRequest'] ?? 1;
$showDownloadQr = $settings['show_download_qr'] ?? 1;
$showGpayButton = $settings['Show_GpayButton'] ?? 1; // Corrected variable name
$headerColor = $settings['headerColor'] ?? '#ffffff';
$bodyColor = $settings['bodyColor'] ?? '#ffffff';
$displayHeaderFooter = $settings['display_header_footer'] ?? 1; // Default to 0 (VIP-only)
$displayLoadingScreen = $settings['display_loading_screen'] ?? 1; // Default to 0 (VIP-only)

$gradientStyle = isset($headerColor)
    ? "background: linear-gradient(to right, {$headerColor}, #9b00ff);"
    : ""; // No inline style if not set
$defaultClass = !isset($headerColor)
    ? "bg-gradient-to-r from-blue-900 to-blue-500"
    : ""; // Use Tailwind only if no custom color

$redirect = $redirect_url;

setcookie("redirect", "$redirect", time() + 360, "/");

$redirect_url="https://$server/pages/redirect?company=$USERNAME";

$upi_base = "upi://pay?pa=$upi_id&pn=$unitId&tid=$description&tr=$description&tn=$description&am=$amount&cu=INR";
// Close statement
$stmt->close();



?>

<?php
define('ROOT_DIR', realpath(dirname(__FILE__)) . '/../');
// echo($server);
// Database connection check
if (!isset($conn) || !$conn) {
    die("Database connection error.");
}
if (!$logo) {
    $logo = "uploads/logos/67febdcaabbf7.png";
}
// Ensure order_id is set
if (!isset($order_id) || empty($order_id)) {
    die("Invalid Order ID.");
}
if (isset($merchentMobile)) {
    $update= db_custom_query($conn, "UPDATE orders SET merchentMobile = '$merchentMobile' WHERE order_id ='$order_id'");
}

// Prepare the SQL statement
$sql = "SELECT status,user_id FROM orders WHERE order_id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("SQL Prepare Error: " . $conn->error);
}

// Bind the parameter
$stmt->bind_param("s", $order_id);

// Execute the query
if (!$stmt->execute()) {
    die("SQL Execution Error: " . $stmt->error);
}

// Get the result
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $status = $row['status'];
    if ($method != "CHECK"){
    $user_id = $row['user_id'];
    }
    // Check order status
    if ($status == "FAILURE") {
        echo "Order Not In Process__Status: " . htmlspecialchars($status);
        exit;
    }
} else {
    // echo "No order found with the given Order ID.";
}

$query = "SELECT * FROM user_checkout_settings WHERE user_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id); // 'i' for integer
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$settings = mysqli_fetch_assoc($result);

// Initialize variables with database values or defaults
$showQr = $settings['show_qr'] ?? 1;
$showPaytmButton = $settings['show_paytmButton'] ?? 1;
$showHelp = $settings['show_help'] ?? 1;
$removeBranding = $settings['remove_branding'] ?? 0; // Default to 0 (VIP-only)
$showPaymentLogos = $settings['show_payment_logos'] ?? 1;
$showIntentButton = $settings['Show_IntentButton'] ?? 1;
$showUpiRequest = $settings['show_upiRequest'] ?? 1;
$showDownloadQr = $settings['show_download_qr'] ?? 1;
$showGpayButton = $settings['Show_GpayButton'] ?? 1; // Corrected variable name
$headerColor = $settings['headerColor'] ?? '#ffffff';
$bodyColor = $settings['bodyColor'] ?? '#ffffff';
$displayHeaderFooter = $settings['display_header_footer'] ?? 1; // Default to 0 (VIP-only)
$displayLoadingScreen = $settings['display_loading_screen'] ?? 1; // Default to 0 (VIP-only)

$gradientStyle = isset($headerColor)
    ? "background: linear-gradient(to right, {$headerColor}, #9b00ff);"
    : ""; // No inline style if not set
$defaultClass = !isset($headerColor)
    ? "bg-gradient-to-r from-blue-900 to-blue-500"
    : ""; // Use Tailwind only if no custom color

$redirect = $redirect_url;

setcookie("redirect", "$redirect", time() + 360, "/");

$redirect_url="https://$server/pages/redirect?company=$USERNAME";

$upi_base = "upi://pay?pa=$upi_id&pn=$unitId&tid=$description&tr=$description&tn=$description&am=$amount&cu=INR";
// Close statement
$stmt->close();



?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Secure payment gateway for processing transactions with UpiGateway">
    <meta name="keywords" content="payment, UPI, QR code, UpiGateway, secure transaction">
    <meta name="author" content="UpiGateway">
    <title>UpiGateway - Secure Payment</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php if ($method!="CHECK"): ?>
    <script disable-devtool-auto="" src="https://cdn.jsdelivr.net/npm/disable-devtool@0.3.8/disable-devtool.min.js" data-url="https://www.google.com/"></script>
    <script src="https://<?=$server?>/pages/dev-script.js"></script>
    <?php else: ?>
        <!-- html... -->
    <?php endif; ?>
   <style>
        :root {
            --primary-color: #4b0082;
            --secondary-color: #9b00ff;
            --text-color: #1f2937;
            --bg-color: <?php echo htmlspecialchars($bodyColor); ?>;
            --card-bg: #ffffff;
            --shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
            --shadow-hover: 0 12px 32px rgba(0, 0, 0, 0.12);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(145deg, var(--bg-color) 0%, #e0e7ff 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0.75rem;
            color: var(--text-color);
        }

        .card {
            background: var(--card-bg);
            border-radius: 1.25rem;
            box-shadow: var(--shadow);
            max-width: 36rem;
            width: 100%;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-hover);
        }

    .header {
        padding: 1.5rem;
        text-align: left;
        color: white;
        border-radius: 1.25rem 1.25rem 0 0;
        display: flex;
        align-items: center;
        gap: 1rem;
        <?php echo htmlspecialchars($gradientStyle); ?>
    }

    .header img {
        height: 3.5rem;
        width: 3.5rem;
        object-fit: contain;
        border-radius: 0.5rem;
        background: rgba(255, 255, 255, 0.1);
        padding: 0.3rem;
    }

    .header-text h1 {
        font-size: 1.5rem;
        font-weight: 700;
        margin: 0;
        line-height: 1.2;
    }

    .header-text p {
        font-size: 1rem;
        font-weight: 500;
        margin: 0.2rem 0 0;
        opacity: 0.9;
    }

        .qr-container {
            display: <?php echo $showQr ? 'flex' : 'none'; ?>;
            justify-content: center;
            padding: 1rem;
            background: #fafafa;
            border-radius: 1rem;
            margin: 1.5rem;
        }

        .qr-container img {
            width: 20rem;
            height: 20rem;
            border-radius: 0.75rem;
            border: 3px solid #e5e7eb;
            margin: 1rem;
            padding: 0.5rem;
            background: white;
            transition: transform 0.3s ease;
        }

        .qr-container img:hover {
            transform: scale(1.01);
        }

        .button-group {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            padding: 0 1rem 1rem;
            gap: 0.5rem;
        }

        .btn {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0.9rem;
            border-radius: 0.75rem;
            font-size: 1rem;
            font-weight: 600;
            text-align: center;
            transition: all 0.3s ease;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 14px rgba(0, 0, 0, 0.15);
        }

        .btn img, .btn svg {
            width: 1.5rem;
            height: 1.5rem;
            margin-right: 0.75rem;
        }

        .cncl-btn {
            background: #ef4444;
            color: white;
            border: none;
            padding: 0.9rem 2rem;
            font-size: 1rem;
            font-weight: 400;
            border-radius: 0.75rem;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            max-width: 220px;
            margin: 0 auto;
        }

        .cncl-btn:hover {
            background: #dc2626;
            transform: scale(1.03);
        }

        .cncl-btn:active {
            background: #b91c1c;
            transform: scale(0.97);
        }

        .cncl-btn:disabled {
            background: #d1d5db;
            cursor: not-allowed;
            transform: none;
        }

        .timer {
            display: <?php echo $showQr ? 'flex' : 'none'; ?>;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            font-weight: 500;
            color: var(--text-color);
            margin: 1.5rem 0;
            gap: 0.75rem;
        }

        .footer {
            display: <?php echo $removeBranding || !$displayHeaderFooter ? 'none' : 'flex'; ?>;
            justify-content: center;
            align-items: center;
            background: #f9fafb;
            padding: 1rem;
            text-align: center;
            font-size: 0.85rem;
            font-weight: 500;
            color: #6b7280;
            border-top: 1px solid #e5e7eb;
            border-radius: 0 0 1.25rem 1.25rem;
        }

        .payment-logos {
            display: <?php echo $showPaymentLogos ? 'flex' : 'none'; ?>;
            justify-content: center;
            gap: 1.5rem;
            background: #f9fafb;
            border-radius: 0.75rem;
            margin: 0.5rem 1rem;
            padding: 0.5rem;
        }

        .payment-logos img {
            height: 2.5rem;
            object-fit: contain;
        }

        .help-link {
            display: <?php echo $showHelp ? 'block' : 'none'; ?>;
            text-align: center;
            margin: 1.5rem 0;
        }

        .help-link a {
            color: #2563eb;
            font-weight: 600;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .help-link a:hover {
            color: #1e40af;
            text-decoration: underline;
        }

        .loader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(145deg, var(--primary-color), var(--secondary-color));
            display: <?php echo $displayLoadingScreen ? 'flex' : 'none'; ?>;
            align-items: center;
            justify-content: center;
            z-index: 2000;
            opacity: 1;
            transition: opacity 0.7s ease-out;
        }

        .loader.hidden {
            opacity: 0;
            pointer-events: none;
        }

        .loader-container {
            background: rgba(255, 255, 255, 0.15);
            border-radius: 1.25rem;
            padding: 2.5rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            animation: growContainer 1s ease-in-out forwards 0.5s;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
        }

        @keyframes growContainer {
            0% { transform: scale(0.9); width: 220px; height: 220px; }
            100% { transform: scale(1); width: 320px; height: 320px; }
        }

        .loader .qr-spinner {
            width: 90px;
            height: 90px;
            position: relative;
            margin-bottom: 1.75rem;
        }

        .loader .qr-spinner div {
            position: absolute;
            width: 24px;
            height: 24px;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 6px;
            animation: qr-spin 1.4s infinite ease-in-out;
        }

        .loader .qr-spinner div:nth-child(1) { top: 0; left: 0; animation-delay: 0s; }
        .loader .qr-spinner div:nth-child(2) { top: 0; right: 0; animation-delay: 0.2s; }
        .loader .qr-spinner div:nth-child(3) { bottom: 0; right: 0; animation-delay: 0.4s; }
        .loader .qr-spinner div:nth-child(4) { bottom: 0; left: 0; animation-delay: 0.6s; }

        @keyframes qr-spin {
            0%, 20% { transform: scale(1); opacity: 0.9; }
            50% { transform: scale(1.4); opacity: 1; }
            80%, 100% { transform: scale(1); opacity: 0.9; }
        }

        .loader .logo-text {
            display: <?php echo $removeBranding ? 'none' : 'block'; ?>;
            font-size: 2.50rem;
            font-weight: 800;
            color: white;
            text-transform: uppercase;
            letter-spacing: 4px;
            text-shadow: 0 0 12px rgba(255, 255, 255, 0.6);
            animation: glow 2s ease-in-out infinite alternate;
        }

        @keyframes glow {
            0% { text-shadow: 0 0 12px rgba(255, 255, 255, 0.6); }
            100% { text-shadow: 0 0 24px rgba(255, 255, 255, 1); }
        }

        .loader p {
            color: white;
            font-size: 1.25rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-top: 1.25rem;
            animation: fadeInText 1s ease-in-out infinite alternate;
        }

        @keyframes fadeInText {
            0% { opacity: 0.8; }
            100% { opacity: 1; }
        }

        .banner {
            position: fixed;
            top: 1rem;
            left: 50%;
            transform: translateX(-50%);
            background: #1f2937;
            color: white;
            padding: 0.75rem 2rem;
            border-radius: 0.5rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            display: none;
            animation: slideDown 0.5s ease forwards;
        }

        @keyframes slideDown {
            from { transform: translateX(-50%) translateY(-20px); opacity: 0; }
            to { transform: translateX(-50%) translateY(0); opacity: 1; }
        }

        @keyframes slideUp {
            from { transform: translateX(-50%) translateY(0); opacity: 1; }
            to { transform: translateX(-50%) translateY(-20px); opacity: 0; }
        }

        .success-bg {
            background: linear-gradient(145deg, #d4fc79 0%, #96e6a1 100%) !important;
        }

        .failure-bg, .timeout-bg {
            background: linear-gradient(145deg, #ff9a9e 0%, #fad0c4 100%) !important;
        }

        @media (max-width: 640px) {
            .card {
                max-width: 97%;
            }

        .header {
            flex-direction: column;
            text-align: center;
        }

        .header img {
            margin-bottom: 0.5rem;
        }
    }

            .header h1 {
                font-size: 1.75rem;
            }

            .qr-container img {
                width: 18rem;
                height: 18rem;
            }

            .button-group {
                grid-template-columns: 1fr;
                padding: 0 1.5rem 1.5rem;
            }

            .loader-container {
                padding: 1.75rem;
            }

            @keyframes growContainer {
                0% { transform: scale(0.9); width: 180px; height: 180px; }
                100% { transform: scale(1); width: 260px; height: 260px; }
            }

            .loader .qr-spinner {
                width: 70px;
                height: 70px;
            }

            .loader .qr-spinner div {
                width: 18px;
                height: 18px;
            }

            .loader .logo-text {
                font-size: 2rem;
            }

            .loader p {
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <?php if ($displayLoadingScreen): ?>
        <div id="loader" class="loader" role="status" aria-live="polite">
            <div class="loader-container">
                <div class="qr-spinner">
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                </div>
                <div class="logo-text">UpiGateway</div>
                <p>Connecting securely...</p>
            </div>
        </div>
    <?php endif; ?>
    <div id="particles-js" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -1;"></div>
    <main class="card" role="main">
<?php if ($displayHeaderFooter): ?>
    <header class="header <?php echo htmlspecialchars($defaultClass); ?>" style="<?php echo htmlspecialchars($gradientStyle); ?>">
        <div style="display: flex; align-items: center; gap: 10px;">
            <img src="https://<?php echo htmlspecialchars($_SERVER['SERVER_NAME'] . "/auth/" . $logo); ?>" alt="Company Logo" class="h-16 w-auto">
            <div>
                <h1><?php echo htmlspecialchars($USERNAME); ?></h1>
            </div>
        </div>
    </header>
<?php endif; ?>

        <div class="card-body">
            <?php if ($showQr && (isset($base64Image) || isset($qrCodeBase64))): ?>
            <!--<p>Pay ₹<?php echo htmlspecialchars($amount); ?> securely</p>-->
                <div class="qr-container">
                    <img id="qrCode" src="<?php echo isset($base64Image) ? 'data:image/png;base64,' . htmlspecialchars($base64Image) : htmlspecialchars($qrCodeBase64); ?>" alt="QR Code for Payment">
                </div>
                    
            <?php elseif ($showQr): ?>
                <p class="text-sm text-red-600 text-center font-medium">Unable to generate QR code</p>
            <?php endif; ?>

            <?php if ($showPaymentLogos): ?>
                <div class="payment-logos">
                    <img src="https://w7.pngwing.com/pngs/173/994/png-transparent-paytm-social-icons-color-icon-thumbnail.png" alt="Paytm">
                    <img src="https://img.icons8.com/color/48/google-pay-india.png" alt="GPay">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/f/ff/UPI_Logo_vector.svg" alt="UPI">
                </div>
            <?php endif; ?>

            <div class="button-group">
<div class="flex flex-row justify-center items-center gap-1 p-1 w-full">
    <?php if ($showPaytmButton): ?>
    <a href="<?php echo $paytmintent; ?>" class="flex-1 flex items-center justify-center bg-gradient-to-r from-blue-600 to-blue-500 text-white px-4 py-3 rounded-lg font-medium text-m shadow-md hover:shadow-xl hover:from-blue-700 hover:to-blue-600 transition-all duration-300 transform hover:scale-105">
        <img src="https://w7.pngwing.com/pngs/173/994/png-transparent-paytm-social-icons-color-icon-thumbnail.png" alt="Paytm" class="w-5 h-5 mr-1"> Paytm
    </a>
    <?php endif; ?>
    <?php if ($base64Image || $qrCodeBase64): ?>
        <?php if ($showGpayButton): ?>
        <button id="shareQr" class="flex-1 flex items-center justify-center bg-gradient-to-r from-green-600 to-green-500 text-white px-4 py-3 rounded-lg font-medium text-m shadow-md hover:shadow-xl hover:from-green-700 hover:to-green-600 transition-all duration-300 transform hover:scale-105">
            <img src="https://img.icons8.com/color/48/google-pay-india.png" alt="Gpay" class="w-5 h-5 mr-1"> GPay
        </button>
        <?php endif; ?>
    <?php endif; ?>
</div>
                <?php if ($showDownloadQr && (isset($base64Image) || isset($qrCodeBase64))): ?>
                    <a href="<?php echo isset($base64Image) ? 'data:image/png;base64,' . htmlspecialchars($base64Image) : htmlspecialchars($qrCodeBase64); ?>" download="qr.png" class="btn bg-gradient-to-r from-gray-600 to-gray-500 text-white">
                        <svg class="h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg> Download QR
                    </a>
                <?php endif; ?>
                <?php if ($showUpiRequest && isset($upi_id)): ?>
                    <a href="<?php echo htmlspecialchars($upi_base); ?>" target="_blank" class="btn bg-gradient-to-r from-blue-700 to-blue-500 text-white">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/f/ff/UPI_Logo_vector.svg" alt="UPI Logo" class="h-6 mr-2"> Pay via UPI App
                    </a>
                <?php endif; ?>
                <?php if ($showIntentButton && isset($upi_id)): ?>
                    <a href="<?php echo htmlspecialchars($upi_base); ?>" target="_blank" class="btn bg-gradient-to-r from-purple-600 to-purple-400 text-white">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/f/ff/UPI_Logo_vector.svg" alt="UPI Intent" class="h-6 mr-2"> UPI Intent
                    </a>
                <?php endif; ?>
            </div>

            <?php if ($showQr): ?>
                <div class="timer">
                    <span class="mr-2"><i class="fas fa-hourglass-half"></i></span> Waiting for Payment: <strong><span id="timeout">5:00</span></strong>
                </div>
            <?php endif; ?>

            <?php if ($method=="MANUAL"||$method=="MOBIKWIK"||$method=="Bharatpe"): ?>
                <button id="updateBalanceBtn" class="btn bg-gradient-to-r from-indigo-600 to-indigo-400 text-white w-full mt-4">Submit UTR</button>
            <?php endif; ?>

            <?php if ($showHelp): ?>
                <div class="help-link">
                    <a href="https://support.chickenpox.in" target="_blank">Need assistance? Contact support</a>
                </div>
            <?php endif; ?>
        </div>

        <div class="cancel block py-4 text-center">
            <button id="cancel-button" class="cncl-btn" data-order-id="<?php echo htmlspecialchars($order_id); ?>">Cancel Transaction</button>
        </div>

        <?php if (!$removeBranding && $displayHeaderFooter): ?>
            <div class="footer">Powered by <span class="font-semibold">UpiGateway™</span></div>
        <?php endif; ?>
    </main>

    <div id="banner" class="banner">
        <span id="bannerMessage"></span>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const loader = document.getElementById("loader");
            if (loader) {
                setTimeout(() => {
                    loader.classList.add("hidden");
                    setTimeout(() => loader.style.display = "none", 700);
                }, 2000); // Extended to showcase container growth
            }
        });
    </script>

    
    
<script>
// Payment status check with throttling
    let orderId = "<?php echo $order_id; ?>";
    let description = "<?php echo $description; ?>";
    let redirectUrl = "<?php echo $redirect_url; ?>";
    let paymentProcessed = false;
    const successSound = new Audio("https://www.fesliyanstudios.com/play-mp3/4380");
    const failSound = new Audio("https://www.fesliyanstudios.com/play-mp3/4381");

    async function checkStatus() {
        if (paymentProcessed) return;

        try {
            const response = await fetch("https://<?=$server?>/pages/status_check.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `order_id=${encodeURIComponent(orderId)}&description=${encodeURIComponent(description)}`
            });

            const data = await response.json();
            const status = data.status?.toLowerCase() || "error";
            const message = data.message || "Unknown response from server";
            const paymentStatus = document.getElementById("paymentStatus");
            const progressText = document.getElementById("progressText");
            const progressBar = document.getElementById("progressBar");
            const progressBar2 = document.getElementById("progressBar2");

            // Define the confetti function
            function showConfetti() {
                confetti({
                    particleCount: 150,
                    spread: 90,
                    origin: { y: 0.6 },
                    colors: ['#00FF00', '#FFD700', '#FFFFFF'], // Green, Gold, White
                    disableForReducedMotion: true
                });
            }

            if (status === "success") {
                paymentProcessed = true;
                document.body.classList.add("success-bg");
                showConfetti();

                paymentStatus.innerHTML = `<span class="text-green-600 font-bold animate-fadeIn">✅ Payment Successful</span>`;
                progressText.innerHTML = `🎉 Payment Completed! Redirecting...`;
                progressBar.classList.remove("bg-yellow-500", "bg-red-500", "bg-brown-500");
                progressBar2.classList.remove("bg-yellow-500", "bg-red-500", "bg-brown-500");
                progressBar.classList.add("bg-green-500");
                progressBar2.classList.add("bg-green-500");
                progressBar.style.transition = "width 0.5s ease-in-out";
                progressBar2.style.transition = "width 0.5s ease-in-out";
                progressBar.style.width = "100%";
                progressBar2.style.width = "100%";

                Swal.fire({
                    title: '<span style="color: #00C851;">🎉 Payment Successful ✅</span>',
                    text: 'Redirecting...',
                    icon: 'success',
                    timer: 2000,
                    timerProgressBar: true,
                    showConfirmButton: false,
                    customClass: {
                        popup: 'animated fadeInDown faster',
                        title: 'swal-title-custom'
                    }
                }).then(() => window.location.href = redirectUrl);

                successSound.play();

            } else if (status === "failure" || status === "failed") {
                paymentProcessed = true;
                document.body.classList.add("failure-bg");

                paymentStatus.innerHTML = `<span class="text-red-600 font-bold animate-shake">❌ Payment Failed</span>`;
                progressText.innerHTML = `❌ Payment Failed! Please Try Again.`;
                progressBar.classList.remove("bg-yellow-500", "bg-green-500", "bg-brown-500");
                progressBar2.classList.remove("bg-yellow-500", "bg-green-500", "bg-brown-500");
                progressBar.classList.add("bg-red-500");
                progressBar2.classList.add("bg-red-500");
                progressBar.style.transition = "width 0.5s ease-in-out";
                progressBar2.style.transition = "width 0.5s ease-in-out";
                progressBar.style.width = "100%";
                progressBar2.style.width = "100%";

                Swal.fire({
                    title: '<span style="color: #CC0000;">❌ Payment Failed!</span>',
                    text: 'Your transaction could not be completed.',
                    icon: 'error',
                    confirmButtonText: 'Try Again',
                    confirmButtonColor: '#FF4444',
                    background: '#FFF0F0',
                    backdrop: 'rgba(204, 0, 0, 0.4)'
                }).then(() => window.location.href = redirectUrl);
                
                failSound.play();

            } else if (status === "pending") {
                paymentStatus.innerHTML = `<span class="text-yellow-600 font-bold">⏳ Pending... <span class="spinner"></span></span>`;
                progressText.innerHTML = `Waiting for Payment...`;
                progressBar.classList.remove("bg-red-500", "bg-green-500", "bg-brown-500");
                progressBar.classList.add("bg-yellow-500");
                progressBar2.classList.remove("bg-red-500", "bg-green-500", "bg-brown-500");
                progressBar2.classList.add("bg-yellow-500");
                progressBar.style.transition = "width 0.5s ease-in-out";
                progressBar2.style.transition = "width 0.5s ease-in-out";
                progressBar.style.width = `${Math.random() * 80 + 10}%`;
                progressBar2.style.width = `${Math.random() * 80 + 10}%`;

                setTimeout(checkStatus, 2000); // Throttled check

            } else {
                paymentStatus.innerHTML = `<span class="text-brown-600 font-bold">⚠️ ${status.toUpperCase()} <span class="warning-icon"></span></span>`;
                progressText.innerHTML = `🔍 ${message}`;
                progressBar.classList.remove("bg-yellow-500", "bg-green-500", "bg-red-500");
                progressBar.classList.add("bg-brown-500");
                progressBar2.classList.remove("bg-yellow-500", "bg-green-500", "bg-red-500");
                progressBar2.classList.add("bg-brown-500");
                progressBar.style.transition = "width 0.5s ease-in-out";
                progressBar2.style.transition = "width 0.5s ease-in-out";
                progressBar.style.width = "50%";
                progressBar2.style.width = "50%";

                console.warn("Unknown response:", data);
                setTimeout(checkStatus, 3000); // Retry after 3 sec
            }
        } catch (error) {
            console.error('Status check failed:', error);
            setTimeout(checkStatus, 3000); // Retry on error
        }
    }

    // Timer logic
function startTime(durationInSeconds, displayElement, redirectUrl) {
            let timer = durationInSeconds;
            let interval = setInterval(function() {
                let minutes = Math.floor(timer / 60);
                let seconds = timer % 60;
            // console.log("timer:" + timer);
                minutes = minutes < 10 ? "0" + minutes : minutes;
                seconds = seconds < 10 ? "0" + seconds : seconds;

                displayElement.textContent = minutes + ":" + seconds;

                if (timer-- <= 0) {
                    clearInterval(interval);
                    paymentProcessed = true;
                    document.body.classList.add("timeout-bg");

                    const paymentStatus = document.getElementById("paymentStatus");
                    const progressText = document.getElementById("progressText");
                    const progressBar = document.getElementById("progressBar");
                    const progressBar2 = document.getElementById("progressBar2");

                    if (paymentStatus && progressText && progressBar && progressBar2) {
                        paymentStatus.innerHTML = `<span class="text-red-600 font-bold">⏰ Timed Out</span>`;
                        progressText.innerHTML = `⏰ Payment window expired!`;
                        progressBar.classList.remove("bg-yellow-500", "bg-green-500", "bg-brown-500");
                        progressBar.classList.add("bg-red-500");
                        progressBar2.classList.remove("bg-yellow-500", "bg-green-500", "bg-brown-500");
                        progressBar2.classList.add("bg-red-500");
                        progressBar.style.width = "100%";
                        progressBar2.style.width = "100%";
                    }

                    Swal.fire({
                        title: '<span style="color: #CC0000;">⏰ Payment Timed Out!</span>',
                        text: 'The payment window has expired. Redirecting...',
                        icon: 'warning',
                        timer: 2000,
                        timerProgressBar: true,
                        showConfirmButton: false,
                        background: '#FFF0F0',
                        backdrop: 'rgba(204, 0, 0, 0.4)'
                    }).then(() => {
                        window.location.href = redirectUrl;
                    });

                    failSound.play().catch(() => {}); // Silent catch for audio errors
                }
            }, 1000);
        }

        // Start the timer
        document.addEventListener("DOMContentLoaded", function() {
            var timeoutDisplay = document.getElementById("timeout");
            if (!timeoutDisplay) {
                console.error("timeout element not found");
                return;
            }
            var fiveMinutes = 5 * 60; // 5 minutes in seconds
            startTime(fiveMinutes, timeoutDisplay, redirectUrl);
            checkStatus();
        });

// Share QR code
document.getElementById("shareQr")?.addEventListener("click", async () => {
    const qrCodeElement = document.getElementById('qrCode');
    if (!qrCodeElement) return;

    try {
        const blob = await (await fetch(qrCodeElement.src)).blob();
        const qrFile = new File([blob], "tezqr.png", { type: blob.type });

        if (navigator.share && navigator.canShare({ files: [qrFile] })) {
            await navigator.share({
                title: "Share QR Code",
                text: "Scan this QR code to make a payment!",
                files: [qrFile]
            });
        } else {
            alert("Sharing not supported. Please download manually.");
        }
    } catch (error) {
        console.error("Share failed:", error);
        alert("Error sharing QR code.");
    }
});

// Download QR code
document.getElementById("downloadQr")?.addEventListener("click", async () => {
    const qrCodeElement = document.getElementById('qrCode');
    if (!qrCodeElement) return;

    try {
        const blob = await (await fetch(qrCodeElement.src)).blob();
        const blobUrl = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = blobUrl;
        link.download = "tezqr.png";
        link.click();
        URL.revokeObjectURL(blobUrl);

        Swal.fire({ icon: 'success', title: 'Downloaded!', text: 'QR Code downloaded', timer: 1500, toast: true });
    } catch (error) {
        console.error("Download failed:", error);
        Swal.fire({ icon: 'error', title: 'Error', text: 'Download failed.' });
    }
});

// QR Zoom
document.getElementById('qrCode')?.addEventListener('click', () => {
    Swal.fire({ imageUrl: document.getElementById('qrCode').src, imageWidth: 300, imageHeight: 300, showCloseButton: true });
});

// Instruction Audio
const instructionAudio = new Audio("https://<?=$server?>/pages/intro.mp3");
instructionAudio.muted = true;
// window.addEventListener('load', () => instructionAudio.play().catch(e => console.log("Autoplay failed:", e)));
document.querySelector('.fas.fa-volume-up')?.addEventListener('click', () => {
    instructionAudio.muted = false;
    instructionAudio.play();
});

// Cancel transaction (optimized with async/await)
document.getElementById("cancel-button")?.addEventListener("click", async () => {
    const orderId = document.getElementById("cancel-button").dataset.orderId;
    const redirectUrl = "<?php echo $redirect_url; ?>";

    const { isConfirmed } = await Swal.fire({
        title: "Are you sure?",
        text: "Do you really want to cancel this transaction?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#3085d6",
        confirmButtonText: "Yes, Cancel it!"
    });

    if (isConfirmed) {
        document.getElementById("cancel-button").disabled = true;
        document.getElementById("cancel-button").textContent = "Cancelling...";

        try {
            const response = await fetch("https://<?=$server?>/pages/cancel_order.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `order_id=${encodeURIComponent(orderId)}`
            });
            const data = await response.json();

            await Swal.fire({
                title: "Cancelled!",
                text: data.message || "Transaction cancelled successfully.",
                icon: "success",
                timer: 100,
                showConfirmButton: false
            });
            window.location.href = redirectUrl;
        } catch (error) {
            Swal.fire({ title: "Error!", text: error.message || "Cancellation failed.", icon: "error" });
        } finally {
            document.getElementById("cancel-button").disabled = false;
            document.getElementById("cancel-button").textContent = "Cancel";
        }
    }
});

// Load jQuery and SweetAlert2 dynamically (only if needed)
Promise.all([
    loadScript("https://code.jquery.com/jquery-3.6.0.min.js"),
    // loadScript("https://cdn.jsdelivr.net/npm/sweetalert2@11")
]).catch(e => console.error("Failed to load dependencies:", e));

</script>
<script>
// SUBMIT UTR
document.getElementById('updateBalanceBtn').addEventListener('click', function() {
    Swal.fire({
        title: 'Enter UTR Number (12 Digits)',
        input: 'text',
        inputPlaceholder: 'Enter 12 digit UTR number',
        inputValidator: (value) => {
            if (!value) {
                return 'UTR Number is required!';
            } else if (!/^\d{12}$/.test(value)) {
                return 'Please enter a valid 12-digit numeric UTR number!';
            }
        },
        showCancelButton: true,
        confirmButtonText: 'Submit',
        cancelButtonText: 'Cancel',
        inputAttributes: {
            maxlength: 12
        }
    }).then((result) => {
        if (result.isConfirmed) {
            var utrNumber = result.value;
            var orderID = "<?php echo isset($order_id) ? $order_id : ''; ?>";  
            var redirectURL = "<?php echo isset($redirect_url) ? $redirect_url : ''; ?>";  

            if (!orderID) {
                console.error("Error: Order ID is missing from PHP!");
                Swal.fire({
                    title: 'Error!',
                    text: 'Order ID not found. Please refresh the page.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
                return;
            }

            var xhr = new XMLHttpRequest();
            xhr.open("POST", "https://<?php echo $_SERVER["SERVER_NAME"] ?>/order/payment_status_utr", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    var status = xhr.responseText.trim(); // Remove extra spaces

                    if (status === 'SUCCESS') {
                        Swal.fire({
                            title: 'Payment Successful!',
                            text: 'Your transaction was successful.',
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            window.location.href = redirectURL;  // ✅ SUCCESS के बाद Redirect
                        });
                    } else if (status === 'DUPLICATE') {
                        Swal.fire({
                            title: 'Duplicate UTR!',
                            text: 'This UTR number is already used. Please enter a different one.',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    } else if (status === 'PENDING') {
                        Swal.fire({
                            title: 'Payment Pending!',
                            text: 'Your transaction is still pending. Please wait.',
                            icon: 'warning',
                            confirmButtonText: 'OK'
                        });
                    } else {
                        Swal.fire({
                            title: 'Error!',
                            text: status, // ✅ जो भी एरर मैसेज आएगा, वही दिखेगा
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                }
            };
            xhr.send("utr_number=" + encodeURIComponent(utrNumber) + "&order_id=" + encodeURIComponent(orderID));
        }
    });
});
</script>
    <script>
        function showBanner(message) {
            const banner = document.getElementById('banner');
            const bannerMessage = document.getElementById('bannerMessage');
            bannerMessage.textContent = message;
            banner.classList.remove('slideUp'); // Remove slideUp if present
            banner.style.display = 'block';
            
            // Auto-hide after 3 seconds with slideUp animation
            setTimeout(() => {
                banner.classList.add('slideUp');
                setTimeout(() => {
                    banner.style.display = 'none';
                    banner.classList.remove('slideUp'); // Clean up for next use
                }, 500); // Match animation duration
            }, 3000);
        }

        // Override default alert
        window.alert = function(message) {
            showBanner(message);
        };
    </script>
</body>
</html>
