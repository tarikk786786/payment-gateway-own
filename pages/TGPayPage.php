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

// Prepare the SQL statement
$sql = "SELECT status,user_id FROM orders WHERE order_id = ? AND user_token= ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("SQL Prepare Error: " . $conn->error);
}
// Bind the parameter
$stmt->bind_param("ss", $order_id,$user_token);

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
    if ($status == "FAILURE"||$status == "SUCCESS") {
        echo "Order Not In Process__Status: " . htmlspecialchars($status);
        exit;
    }
} else {
    // echo "No order found with the given Order ID.";
}
$intData = $_GET['intData'];
$dialog = $_GET['dialog'];

// echo($dialog);
// exit;
if($intData){
if (!$vip_plan) {
    $intd = "pa=NO_VIP_PLAN@TEZ&pn=NO VIP&am=1&tr=Activate Vip PLan&tn=Activate Vip PLan";
    $qrCodeBase64 = "data:image/png;base64,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";
    $paytmintent = "paytmmp://cash_wallet?pa=NO_VIP_PLAN@TEZ&pn=NO VIP&am=1&cu=INR&tn=Activate Vip PLan&tr=Activate Vip PLan&mc=4722&&sign=AAuN7izDWN5cb8A5scnUiNME+LkZqI2DWgkXlN1McoP6WZABa/KkFTiLvuPRP6/nWK8BPg/rPhb+u4QMrUEX10UsANTDbJaALcSM9b8Wk218X+55T/zOzb7xoiB+BcX8yYuYayELImXJHIgL/c7nkAnHrwUCmbM97nRbCVVRvU0ku3Tr&featuretype=money_transfer";
    $intPermit = "Access Denied: Please Activate VIP PLAN";
}else{
    $intPermit = "Allowd";
}
    
http_response_code($httpCode);
header('Content-Type: application/json');
echo json_encode(array(
    "intPermit"     => $intPermit,
    "bhim_link"     => "upi://pay?$intd",
    "phonepe_link"  => "phonepe://pay?$intd",
    "paytm_link"    => $paytmintent,
    "gpay_link"     => "tez://upi/pay?$intd",
    "amazonpay_link"=> "amazonpay://upi/pay?$intd",
    "cred_link"     => "cred://upi/pay?$intd",
    "qr_image" => $qrCodeBase64

));
    exit;
}



$query = "SELECT * FROM user_checkout_settings WHERE user_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id); // 'i' for integer
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$settings = mysqli_fetch_assoc($result);

// Initialize variables with database values or defaults
$displayLoadingScreen = $settings['display_loading_screen'] ?? 1; // Default to 0 (VIP-only)
$displayHeaderFooter = $settings['display_header_footer'] ?? 1; // Default to 0 (VIP-only)
$showPaymentLogos = $settings['show_payment_logos'] ?? 1;
$showIntentButton = $settings['Show_IntentButton'] ?? 1;
$showPaytmButton = $settings['show_paytmButton'] ?? 1;
$removeBranding = $settings['remove_branding'] ?? 0; // Default to 0 (VIP-only)
$showUpiRequest = $settings['show_upiRequest'] ?? 1;
$showDownloadQr = $settings['show_download_qr'] ?? 1;
$showGpayButton = $settings['Show_GpayButton'] ?? 1; // Corrected variable name
$show_phonepe = $settings['show_phonepe'] ?? 1; // Corrected variable name
$headerColor = $settings['headerColor'] ?? '#ffffff';
$selectTheme = $settings['theme'] ?? 1;
$bodyColor = $settings['bodyColor'] ?? '#ffffff';
$showHelp = $settings['show_help'] ?? 1;
$showQr = $settings['show_qr'] ?? 1;
$news = $settings['news'];

$gradientStyle = isset($headerColor)
    ? "background: linear-gradient(to right, {$headerColor}, #9b00ff);"
    : ""; // No inline style if not set
$defaultClass = !isset($headerColor)
    ? "bg-gradient-to-r from-blue-900 to-blue-500"
    : ""; // Use Tailwind only if no custom color
if (!$vip_plan) {
    $news = "Do Not go back if you made any payment on qr or buttons &nbsp;&nbsp;&nbsp; Important : click on Cancel/go back button if you want to Cancel this transaction.";
    $removeBranding = 0;
    $displayHeaderFooter = 1;
    $displayLoadingScreen = 1;
}

$redirect = $redirect_url;

if ($removeBranding == 0) {
setcookie("redirect", "$redirect", time() + 360, "/");
$redirect_url="https://$server/pages/redirect?company=$USERNAME";
}else{
  $redirect_url = $redirect_url;
}

$upi_base = "upi://pay?pa={$upi_id}&pn={$unitId}&am={$amount}&cu=INR&tn={$description}&tr={$description}&mc=5411";
// Close statement
$stmt->close();

$message = "Hello! I am facing an issue with UpiGateway payment. My registered mobile number is $cnumber. Please help me.";

// URL-encode the message
$encodedMessage = urlencode($message);
$whatsappUrl = "https://api.whatsapp.com/send?phone=$mobile&text=$encodedMessage&app_absent=0";
$infoMassage = "NOTE : Fot Cancel this transaction Click on Cancel/go back <br>इस ट्रॅनजेकशन को कॅन्सल कराना हे तो Cancel बटण दबाये ";


// Get the length of the string to adjust animation duration (approximate)
$text_length = strlen(str_replace('&nbsp;', ' ', $news));
$animation_duration = max(10, $text_length * 0.16); // Adjust multiplier (0.1) for speed
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UpiGateway</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!--<?php if ($selectTheme=="1"||$selectTheme=="6"): ?>-->
    <!--<link rel="stylesheet" href="https://<?=$server?>/pages/styles.css">-->
    <!--<?php endif; ?>-->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>
    <?php if (!$dialog): ?>
    <?php if ($method!="CHECK"): ?>
    <!--<script disable-devtool-auto="" src="https://cdn.jsdelivr.net/npm/disable-devtool@0.3.8/disable-devtool.min.js" data-url="https://www.google.com/"></script>-->
    <!--<script src="https://<?=$server?>/pages/dev-script.js"></script>-->
    <?php endif; ?>
    <?php endif; ?>
    
<!--<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>-->
<!-- Bootstrap CSS CDN -->
<!--<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">-->
<!-- Go Back Button -->
    <!-- Go Back Button -->
<style>
/* Custom CSS for the scrolling text */
@keyframes scroll-left {
    0% {
        /* Start position: 100% of the parent container's width (on the right side) */
        /* This ensures the text starts completely outside the view */
        transform: translateX(100%);
    }
    100% {
        /* End position: Move the text to the left by 100% of its own width */
        /* This ensures the whole text scrolls off the screen to the left */
        transform: translateX(-100%);
    }
}

.news-ticker {
    /* Set the animation duration based on the text length.
       A higher number will make it slower. Adjust as needed. */
    animation: scroll-left <?=$animation_duration?>s linear infinite;
    
    /* Make the content a single line */
    white-space: nowrap;
    
    /* Prevent the text from wrapping */
    display: inline-block;
    
    /* Hide overflowing text from the parent container */
    overflow: visible; /* Important: Change this from 'hidden' to 'visible' */
    
    /* Optimize for smooth animation */
    will-change: transform;
}


        :root {
            --primary-purple: #6a0dad;
            --light-purple: #8a2be2;
            --green-confirm: #4CAF50;
            --light-green: #45a049;
            --text-color: #333;
            --bg-color: #f0f2f5;
            --card-bg: #ffffff;
            --shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
        }

        .swal2-popup {
            font-family: 'Poppins', sans-serif;
            border-radius: 18px !important;
            box-shadow: var(--shadow) !important;
            max-height: 85vh !important;
            overflow-y: auto !important;
        }
        .swal2-title {
            font-size: 1.3rem !important;
            font-weight: 700 !important;
            color: var(--primary-purple) !important;
            margin-bottom: 15px !important;
        }
        .swal2-html-container {
            font-size: 0.9rem !important;
            color: var(--text-color) !important;
            line-height: 1.5 !important;
            text-align: left !important;
            padding: 0 !important;
        }
        .swal2-confirm.swal2-styled {
            background-color: var(--green-confirm) !important;
            border-radius: 10px !important;
            font-weight: 600 !important;
            padding: 12px 30px !important;
            font-size: 1.1rem !important;
            box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3) !important;
            transition: all 0.3s ease !important;
        }
        .swal2-confirm.swal2-styled:hover {
            background-color: var(--light-green) !important;
            transform: translateY(-2px);
        }
        .lang-switcher {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 15px;
        }
        .lang-btn {
            background: none;
            border: 2px solid var(--primary-purple);
            color: var(--primary-purple);
            padding: 5px 12px;
            border-radius: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
            font-size: 0.85rem;
        }
        .lang-btn.active, .lang-btn:hover {
            background-color: var(--primary-purple);
            color: #fff;
            transform: scale(1.05);
        }
        .speech-controls {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin: 10px 0;
        }
        .speech-btn {
            background: #4CAF50;
            color: #fff;
            border: none;
            padding: 6px 12px;
            border-radius: 15px;
            font-weight: 600;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            font-size: 0.8rem;
            flex: 1; /* This makes the buttons take equal width */
            max-width: 120px; /* Optional: to prevent them from becoming too wide */
        }
        .speech-btn:hover { background: #45a049; }
        .speech-btn.stop { background: #ff6b6b; }
</style>


<?php
include ROOT_DIR . "pages/themes/$selectTheme.php";
?>
    

<!-- Place this script at the end of your body tag -->
<script>
// Prevent back/forward navigation
(function() {
    history.pushState(null, null, location.href);
    window.onpopstate = function() {
        history.pushState(null, null, location.href);
    };
})();


// Utility to load external scripts dynamically
function loadScript(src) {
    return new Promise((resolve, reject) => {
        const script = document.createElement('script');
        script.src = src;
        script.async = true;
        script.onload = resolve;
        script.onerror = reject;
        document.body.appendChild(script);
    });
}


// Optimized Payment status check with faster response times
let orderId = "<?php echo $order_id; ?>";
let description = "<?php echo $description; ?>";
let user_token = "<?php echo $user_token; ?>";
let redirectUrl = "<?php echo $redirect_url; ?>";
let byteTransactionId = "<?php echo $byteTransactionId?>";
let paymentProcessed = false;

// Preload and cache audio for faster playback
const successSound = new Audio("https://www.fesliyanstudios.com/play-mp3/4380");
const failSound = new Audio("https://www.fesliyanstudios.com/play-mp3/4381");
successSound.preload = "auto";
failSound.preload = "auto";

// Cache DOM elements to avoid repeated queries
const paymentStatus = document.getElementById("paymentStatus");
const progressText = document.getElementById("progressText");
const progressBar = document.getElementById("progressBar");
const progressBar2 = document.getElementById("progressBar2");

// Optimized confetti function
function showConfetti() {
    confetti({
        particleCount: 150,
        spread: 90,
        origin: { y: 0.6 },
        colors: ['#00FF00', '#FFD700', '#FFFFFF'],
        disableForReducedMotion: true
    });
}

// Optimized progress bar update function
function updateProgressBars(colorClass, width1, width2 = width1) {
    const colors = ["bg-yellow-500", "bg-red-500", "bg-brown-500", "bg-green-500"];
    
    // Remove all color classes at once
    progressBar.classList.remove(...colors);
    progressBar2.classList.remove(...colors);
    
    // Add new color class
    progressBar.classList.add(colorClass);
    progressBar2.classList.add(colorClass);
    
    // Set faster transitions
    progressBar.style.transition = "width 0.3s ease-in-out";
    progressBar2.style.transition = "width 0.3s ease-in-out";
    
    // Update widths
    progressBar.style.width = width1;
    progressBar2.style.width = width2;
}

async function checkStatus() {
    if (paymentProcessed) return;

    try {
        const response = await fetch("https://<?=$server?>/pages/status_check.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `order_id=${encodeURIComponent(orderId)}&description=${encodeURIComponent(description)}&user_token=${encodeURIComponent(user_token)}&byteTransactionId=${encodeURIComponent(byteTransactionId)}`
        });

        const data = await response.json();
        const status = data.status?.toLowerCase() || "error";
        const message = data.message || "Unknown response from server";

        if (status === "success") {
            paymentProcessed = true;
            document.body.classList.add("success-bg");
            showConfetti();

            paymentStatus.innerHTML = `<span class="text-green-600 font-bold animate-fadeIn">✅ Payment Successful</span>`;
            progressText.innerHTML = `🎉 Payment Completed! Redirecting...`;
            updateProgressBars("bg-green-500", "100%");

            // Play success sound immediately
            successSound.play().catch(() => {}); // Ignore audio errors

            Swal.fire({
                title: '<span style="color: #00C851;">🎉 Payment Successful ✅</span>',
                text: 'Redirecting...',
                icon: 'success',
                timer: 1500, // Reduced from 2000ms
                timerProgressBar: true,
                showConfirmButton: false,
                customClass: {
                    popup: 'animated fadeInDown faster',
                    title: 'swal-title-custom'
                }
            }).then(() => window.location.href = redirectUrl);

        } else if (status === "failure" || status === "failed") {
            paymentProcessed = true;
            document.body.classList.add("failure-bg");

            paymentStatus.innerHTML = `<span class="text-red-600 font-bold animate-shake">❌ Payment Failed</span>`;
            progressText.innerHTML = `❌ Payment Failed! Please Try Again.`;
            updateProgressBars("bg-red-500", "100%");

            // Play fail sound immediately
            failSound.play().catch(() => {}); // Ignore audio errors

            Swal.fire({
                title: '<span style="color: #CC0000;">❌ Payment Failed!</span>',
                text: 'Your transaction could not be completed.',
                icon: 'error',
                confirmButtonText: 'Try Again',
                confirmButtonColor: '#FF4444',
                background: '#FFF0F0',
                backdrop: 'rgba(204, 0, 0, 0.4)'
            }).then(() => window.location.href = redirectUrl);

        } else if (status === "pending") {
            paymentStatus.innerHTML = `<span class="text-yellow-600 font-bold">⏳ Pending... <span class="spinner"></span></span>`;
            progressText.innerHTML = `Waiting for Payment...`;
            
            // Generate random widths once for both bars
            const randomWidth = `${Math.random() * 70 + 15}%`;
            updateProgressBars("bg-yellow-500", randomWidth);

            setTimeout(checkStatus, 2000); // Faster polling: 2 second

        } else {
            paymentStatus.innerHTML = `<span class="text-brown-600 font-bold">⚠️ ${status.toUpperCase()} <span class="warning-icon"></span></span>`;
            progressText.innerHTML = `🔍 ${message}`;
            updateProgressBars("bg-brown-500", "50%");

            console.warn("Unknown response:", data);
            setTimeout(checkStatus, 2000); // Faster retry: 2 seconds instead of 4
        }
    } catch (error) {
        console.error('Status check failed:', error);
        setTimeout(checkStatus, 2000); // Faster error retry: 2 seconds instead of 4
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
                // background: '#FFF0F0',
                // backdrop: 'rgba(204, 0, 0, 0.4)'
            }).then(() => {
                window.location.href = redirectUrl;
            });
            failSound.play().catch(() => {}); // Silent catch for audio errors
        }
    }, 1000);
}

// Function to handle timeout scenario
function handleTimeout(redirectUrl) {
    paymentProcessed = true;
    document.body.classList.add("timeout-bg");
    const paymentStatus = document.getElementById("paymentStatus");
    const progressText = document.getElementById("progressText");
    const progressBar = document.getElementById("progressBar");
    const progressBar2 = document.getElementById("progressBar2");
    const timeoutDisplay = document.getElementById("timeout");
    
    if (timeoutDisplay) {
        timeoutDisplay.textContent = "00:00";
    }
    
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
        // background: '#FFF0F0',
        // backdrop: 'rgba(204, 0, 0, 0.4)'
    }).then(() => {
        window.location.href = redirectUrl;
    });
    
    if (typeof failSound !== 'undefined') {
        failSound.play().catch(() => {}); // Silent catch for audio errors
    }
}

// Main timer initialization with stored time
document.addEventListener("DOMContentLoaded", function() {
    var timeoutDisplay = document.getElementById("timeout");
    if (!timeoutDisplay) {
        console.error("timeout element not found");
        return;
    }
    
    // Get the created_at timestamp (UNIX timestamp in seconds)
    var createdAtTimestamp = <?php echo $created_at; ?>; // Replace with your timestamp
    var currentTimestamp = Math.floor(Date.now() / 1000);
    
    // Calculate remaining time
    var fiveMinutes = 5 * 60; // 5 minutes in seconds
    var timePassed = currentTimestamp - createdAtTimestamp;
    var remainingTime = fiveMinutes - timePassed;
    
    // If time has already expired
    if (remainingTime <= 0) {
        handleTimeout(redirectUrl);
        return;
    }
    
    // Start timer with remaining time
    startTime(remainingTime, timeoutDisplay, redirectUrl);
    checkStatus();
});
// Share QR code
document.getElementById("shareQr")?.addEventListener("click", async () => {
    const qrCodeElement = document.getElementById("qrCode");
    if (!qrCodeElement) return;

    try {
        const response = await fetch(qrCodeElement.src);
        const blob = await response.blob();

        const qrFile = new File([blob], "tezqr.png", { type: "image/png" });

        if (navigator.share && navigator.canShare({ files: [qrFile] })) {
            await navigator.share({
                title: "Share QR Code",
                text: "Scan this QR code to make a payment!",
                files: [qrFile]
            });
        } else {
            alert("Sharing not supported on this device. Please download the QR manually.");
        }
    } catch (error) {
        console.error("Share failed:", error);
        alert("Error sharing QR code.");
    }
});

// QR Code download function - can be called from script or button click
async function downloadQRCode() {
    const qrCodeElement = document.getElementById('qrCode');
    if (!qrCodeElement) {
        console.error('QR Code element not found');
        Swal.fire({ icon: 'error', title: 'Error', text: 'QR Code not found.' });
        return;
    }

    try {
        const blob = await (await fetch(qrCodeElement.src)).blob();
        const blobUrl = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = blobUrl;
        // Create a unique filename with timestamp
        const fileName = `tezqr_${Date.now()}.png`;
        link.download = fileName;
        link.click();
        URL.revokeObjectURL(blobUrl);
    } catch (error) {
        console.error("Download failed:", error);
        Swal.fire({ 
            icon: 'error', 
            title: 'Error', 
            text: 'Download failed.' 
        });
    }
}

// Event listener for button click (existing functionality)
document.getElementById("downloadQr")?.addEventListener("click", downloadQRCode);

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
    // Select all buttons with the class 'cancel-trigger'
    document.querySelectorAll(".cancel-trigger").forEach(button => {
        button.addEventListener("click", async () => {
            // Check if the button has a data-order-id attribute.
            // If it doesn't, we'll try to find the cancel-button to get the ID.
            const orderId = button.dataset.orderId || document.getElementById("cancel-button")?.dataset.orderId;
            const redirectUrl = "<?php echo $redirect_url; ?>";
            
            if (orderId) {
                cancelOrder(orderId, redirectUrl);
            } else {
                // If there's no order ID, just go back. This is useful for the Go Back/Close buttons.
                history.back();
            }
        });
    });

    async function cancelOrder(orderId, redirectUrl) {
        const cancelButton = document.getElementById("cancel-button");
        if (cancelButton) {
            cancelButton.disabled = true;
            cancelButton.textContent = "Cancelling...";
        }

        try {
            const response = await fetch("https://<?=$server?>/pages/cancel_order.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `order_id=${encodeURIComponent(orderId)}`
            });

            const data = await response.json();

            // Using a shorter timer for better user experience
            await Swal.fire({
                title: "Cancelled!",
                text: data.message || "Transaction cancelled successfully.",
                icon: "success",
                timer: 100, // Reduced from 100 to 1000 for visibility
                showConfirmButton: false
            });

            window.location.href = redirectUrl;
        } catch (error) {
            Swal.fire({
                title: "Error!",
                text: error.message || "Cancellation failed.",
                icon: "error"
            });
        } finally {
            if (cancelButton) {
                cancelButton.disabled = false;
                cancelButton.textContent = "Cancel";
            }
        }
    }


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
    
       <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const t = {
            en: {
                title: 'How to use Phonepe:',
                steps: ['Your QR code has been saved to your gallery.', 'Click the button below to open the PhonePe app.', 'Upload the QR code image from your gallery and complete the payment.', 'After payment completion, return to this page.'],
                confirm: 'Open PhonePe',
                speech: 'How to use Phonepe: Step 1: Your QR code has been saved to your gallery. Step 2: Click the button below to open the PhonePe app. Step 3: Click on scan qr & Upload the QR code image from your gallery and complete the payment. Step 4: After payment completion, return to this page.'
            },
            hi: {
                title: 'Phonepe उपयोग कैसे करें:',
                steps: ['आपका QR कोड आपकी गैलरी में सेव हो गया है।', 'PhonePe ऐप खोलने के लिए नीचे वाले बटन को दबाएं।', 'स्कॅन QR पे क्लिक करे ओर गैलरी से QR कोड इमेज अपलोड करें और भुगतान पूरा करें।', 'भुगतान होने के बाद इसी जगह वापस आएं।'],
                confirm: 'PhonePe खोलें',
                speech: 'Phonepe का उपयोग कैसे करें: चरण 1: आपका QR कोड आपकी गैलरी में सेव हो गया है। चरण 2: PhonePe ऐप खोलने के लिए नीचे वाले बटन को दबाएं। चरण 3:स्कॅन QR पे क्लिक कारे ओर गैलरी से QR कोड इमेज अपलोड करें और भुगतान पूरा करें। चरण 4: भुगतान होने के बाद इसी जगह वापस आएं।'
            }
        };

        let lang = 'hi', speech = null;

        function speak(text, l) {
            if (speech) speechSynthesis.cancel();
            speech = new SpeechSynthesisUtterance(text);
            speech.lang = l === 'hi' ? 'hi-IN' : 'en-US';
            speech.rate = 0.9;
            speechSynthesis.speak(speech);
        }

        function showGuide() {
            downloadQRCode();
            if (speech) speechSynthesis.cancel();
            const tr = t[lang];
            
            Swal.fire({
                title: `<strong>${tr.title}</strong>`,
                html: `
                    <div class="lang-switcher">
                        <button class="lang-btn ${lang === 'hi' ? 'active' : ''}" onclick="switchLang('hi')">हिंदी</button>
                        <button class="lang-btn ${lang === 'en' ? 'active' : ''}" onclick="switchLang('en')">English</button>
                    </div>
                    <div class="speech-controls">
                        <button class="speech-btn" onclick="speak('${tr.speech}', '${lang}')">🔊 ${lang === 'hi' ? 'सुनें' : 'Play'}</button>
                        <button class="speech-btn stop" onclick="speechSynthesis.cancel()">⏹️ ${lang === 'hi' ? 'बंद' : 'Stop'}</button>
                    </div>
                    <ol style="text-align: left; padding-left: 18px; font-size: 0.9rem; margin: 10px 0;">
                        ${tr.steps.map(step => `<li style="margin-bottom: 6px;">${step}</li>`).join('')}
                    </ol>
                `,
                icon: 'info',
                confirmButtonText: tr.confirm,
                showCancelButton: true,
                confirmButtonColor: '#4CAF50',
                didOpen: () => setTimeout(() => speak(tr.speech, lang), 500),
                willClose: () => speechSynthesis.cancel()
            }).then(result => {
                if (result.isConfirmed) {
                    speechSynthesis.cancel();
                    window.location.href = 'phonepe://scanner';
                }
            });
        }

        function switchLang(newLang) {
            if (newLang !== lang) {
                lang = newLang;
                speechSynthesis.cancel();
                Swal.close();
                showGuide();
            }
        }

        document.getElementById('showGuideButton').onclick = showGuide;
    </script>
</body>
</html>