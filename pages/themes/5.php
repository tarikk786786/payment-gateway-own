<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UpiGateway - Neomorphic Payment</title>
    <!-- Google Fonts: Poppins for modern, clean typography -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Root CSS Variables for theme customization */
        :root {
            --primary-color: #6366f1; /* Indigo primary color */
            --primary-hover: #4f46e5; /* Darker shade for hover */
            --secondary-color: #10b981; /* Emerald green for success */
            --danger-color: #ef4444; /* Red for errors/cancel */
            --warning-color: #f59e0b; /* Amber for warnings */
            --text-primary: #1f2937; /* Dark gray for primary text */
            --text-secondary: #6b7280; /* Medium gray for secondary text */
            --bg-primary: #f9fafb; /* Very light gray for background */
            --bg-card: #ffffff; /* White for card backgrounds */
            --border-color: #e5e7eb; /* Light gray for borders */
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --shadow-neo: 8px 8px 16px #d1d9e6, -8px -8px 16px #ffffff; /* Neomorphic shadow */
            --shadow-neo-inset: inset 8px 8px 16px #d1d9e6, inset -8px -8px 16px #ffffff; /* Inset neomorphic shadow */
            --radius-sm: 0.375rem;
            --radius-md: 0.5rem;
            --radius-lg: 0.75rem;
            --radius-full: 9999px;
            --transition: all 0.3s ease;
        }

        /* Dark mode variables */
        @media (prefers-color-scheme: dark) {
            :root {
                --primary-color: #818cf8; /* Lighter indigo for dark mode */
                --primary-hover: #6366f1; /* Original indigo for hover in dark mode */
                --secondary-color: #34d399; /* Lighter emerald for dark mode */
                --danger-color: #f87171; /* Lighter red for dark mode */
                --warning-color: #fbbf24; /* Lighter amber for dark mode */
                --text-primary: #f9fafb; /* Very light gray for primary text */
                --text-secondary: #d1d5db; /* Light gray for secondary text */
                --bg-primary: #111827; /* Very dark gray for background */
                --bg-card: #1f2937; /* Dark gray for card backgrounds */
                --border-color: #374151; /* Medium-dark gray for borders */
                --shadow-neo: 8px 8px 16px #0f1623, -8px -8px 16px #2f384b; /* Dark neomorphic shadow */
                --shadow-neo-inset: inset 8px 8px 16px #0f1623, inset -8px -8px 16px #2f384b; /* Dark inset neomorphic shadow */
            }
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--bg-primary);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            position: relative;
            overflow-x: hidden;
            transition: var(--transition);
        }

        /* Particles.js background */
        #particles-js {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
        }

        /* Main container */
        .container {
            width: 100%;
            max-width: 1000px;
            margin: 2rem auto;
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            z-index: 1;
        }

        /* Header */
        .header {
            background-color: var(--bg-card);
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: var(--shadow-neo);
            transition: var(--transition);
            <?php echo $gradientStyle; ?>
            <?php if (!empty($gradientStyle)): ?>
                color: white;
            <?php endif; ?>
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .header-logo {
            width: 3.5rem;
            height: 3.5rem;
            border-radius: var(--radius-full);
            object-fit: cover;
            box-shadow: var(--shadow-sm);
            border: 2px solid var(--border-color);
            <?php if (!empty($gradientStyle)): ?>
                border-color: rgba(255, 255, 255, 0.5);
            <?php endif; ?>
            transition: transform 0.3s ease;
        }

        .header-logo:hover {
            transform: scale(1.05);
        }

        .header-info h1 {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .header-info p {
            font-size: 0.875rem;
            color: var(--text-secondary);
            <?php if (!empty($gradientStyle)): ?>
                color: rgba(255, 255, 255, 0.8);
            <?php endif; ?>
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .verified-badge {
            display: inline-flex;
            align-items: center;
            background-color: var(--secondary-color);
            color: white;
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            border-radius: var(--radius-full);
            font-weight: 500;
        }

        .verified-badge i {
            margin-right: 0.25rem;
        }

        /* Main content */
        .content {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }

        @media (min-width: 768px) {
            .content {
                grid-template-columns: 1fr 1fr;
            }
        }

        /* Card component */
        .card {
            background-color: var(--bg-card);
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            box-shadow: var(--shadow-neo);
            transition: var(--transition);
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid var(--border-color);
        }

        .card-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .card-title i {
            color: var(--primary-color);
        }

        /* Payment summary */
        .payment-summary {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .summary-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.5rem 0;
        }

        .summary-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--text-secondary);
            font-size: 0.875rem;
        }

        .summary-value {
            font-weight: 500;
            color: var(--text-primary);
        }

        .amount-value {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--primary-color);
        }

        /* QR code section */
        .qr-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1rem;
            padding: 1rem 0;
        }

        .qr-code {
            width: 200px;
            height: 200px;
            background-color: white;
            padding: 0.75rem;
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-md);
            transition: transform 0.3s ease;
        }

        .qr-code:hover {
            transform: scale(1.05);
        }

        .qr-instruction {
            text-align: center;
            font-size: 0.875rem;
            color: var(--text-secondary);
            max-width: 250px;
            margin: 0 auto;
        }

        /* Payment methods */
        .payment-methods {
            display: flex;
            flex-wrap: wrap;
            gap: 0.10rem;
            justify-content: center;
            margin-top: 0.50rem;
        }

        .payment-app {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background-color: var(--bg-primary);
            border-radius: var(--radius-full);
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--text-primary);
            box-shadow: var(--shadow-neo);
            transition: var(--transition);
            cursor: pointer;
        }

        .payment-app:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
        }

        .payment-app img {
            width: 1.5rem;
            height: 1.5rem;
            border-radius: var(--radius-full);
            object-fit: contain;
        }

        /* UPI form */
        .upi-form {
            margin-top: 1rem;
        }

        .upi-form form {
            display: flex;
        }

        .upi-input {
            flex: 1;
            padding: 0.50rem 0.75rem;
            border-radius: var(--radius-md);
            border: 1px solid var(--border-color);
            background-color: var(--bg-primary);
            color: var(--text-primary);
            font-family: 'Poppins', sans-serif;
            font-size: 0.750rem;
            outline: none;
            max-width: 80%;
            transition: var(--transition);
            box-shadow: var(--shadow-neo-inset);
        }

        .upi-input:focus {
            border-color: var(--primary-color);
        }

        .upi-submit {
            padding: 0.75rem 1.25rem;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: var(--radius-md);
            font-family: 'Poppins', sans-serif;
            font-weight: 500;
            font-size: 0.875rem;
            cursor: pointer;
            transition: var(--transition);
            box-shadow: var(--shadow-md);
        }

        .upi-submit:hover {
            background-color: var(--primary-hover);
            transform: translateY(-2px);
        }

        /* Progress section */
        .progress-section {
            margin-top: 1rem;
        }

        .progress-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 0.75rem;
        }

        .progress-title {
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .timer {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--warning-color);
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .progress-bar {
            height: 0.5rem;
            background-color: var(--border-color);
            border-radius: var(--radius-full);
            overflow: hidden;
            margin-bottom: 0.5rem;
        }

        .progress-fill {
            height: 100%;
            width: 0%;
            background-color: var(--primary-color);
            border-radius: var(--radius-full);
            transition: width 0.3s ease;
        }

        .progress-status {
            font-size: 0.75rem;
            color: var(--text-secondary);
            text-align: center;
        }

        /* Action buttons */
        .action-buttons {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 0.75rem;
            margin-top: 1.5rem;
        }

        .btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.75rem 1rem;
            border-radius: var(--radius-md);
            font-family: 'Poppins', sans-serif;
            font-weight: 500;
            font-size: 0.875rem;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            box-shadow: var(--shadow-neo);
        }

        .btn:hover {
            transform: translateY(-3px);
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--primary-hover);
        }

        .btn-secondary {
            background-color: var(--bg-primary);
            color: var(--text-primary);
        }

        .btn-secondary:hover {
            background-color: var(--border-color);
        }

        .btn-danger {
            background-color: var(--danger-color);
            color: white;
        }

        .btn-danger:hover {
            background-color: #dc2626; /* Darker red */
        }

        .btn img, .btn i {
            width: 1.25rem;
            height: 1.25rem;
            object-fit: contain;
        }

        /* Help section */
        .help-section {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            margin-top: 1.5rem;
            padding-top: 1rem;
            border-top: 1px solid var(--border-color);
        }

        .help-link {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--primary-color);
            font-size: 0.875rem;
            font-weight: 500;
            text-decoration: none;
            transition: var(--transition);
        }

        .help-link:hover {
            color: var(--primary-hover);
            transform: translateY(-2px);
        }

        /* Footer */
        .footer {
            text-align: center;
            margin-top: 2rem;
            padding: 1rem;
            font-size: 0.75rem;
            color: var(--text-secondary);
        }

        .footer-brand {
            font-weight: 600;
            color: var(--primary-color);
        }

        /* Loader */
        .loader-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: var(--bg-primary);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            transition: opacity 0.5s ease, visibility 0.5s ease;
        }

        .loader-overlay.hidden {
            opacity: 0;
            visibility: hidden;
        }

        .loader {
            position: relative;
            width: 80px;
            height: 80px;
        }

        .loader-circle {
            position: absolute;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            border: 4px solid transparent;
            border-top-color: var(--primary-color);
            animation: spin 1s linear infinite;
        }

        .loader-circle:nth-child(2) {
            width: 70%;
            height: 70%;
            top: 15%;
            left: 15%;
            border-top-color: var(--secondary-color);
            animation-duration: 0.8s;
            animation-direction: reverse;
        }

        .loader-circle:nth-child(3) {
            width: 40%;
            height: 40%;
            top: 30%;
            left: 30%;
            border-top-color: var(--warning-color);
            animation-duration: 0.6s;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .loader-text {
            margin-top: 1.5rem;
            font-size: 1rem;
            font-weight: 500;
            color: var(--text-primary);
        }

        .loader-brand {
            margin-top: 0.5rem;
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--primary-color);
        }

        /* Banner notification */
        .banner {
            position: fixed;
            top: 1.5rem;
            left: 50%;
            transform: translateX(-50%) translateY(-100px);
            background-color: var(--bg-card);
            color: var(--text-primary);
            padding: 0.75rem 1.5rem;
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-lg);
            z-index: 1000;
            display: none;
            animation: slideDown 0.3s ease-out forwards;
            max-width: 90%;
        }

        @keyframes slideDown {
            to { transform: translateX(-50%) translateY(0); }
        }

        .slideUp {
            animation: slideUp 0.3s ease-in forwards;
        }

        @keyframes slideUp {
            to { transform: translateX(-50%) translateY(-100px); }
        }

        /* Back and close buttons */
        .back-btn, .close-btn {
            position: fixed;
            z-index: 100;
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: white;
            border: none;
            cursor: pointer;
            transition: var(--transition);
        }

        .back-btn {
            top: 0;
            left: 0;
            background-color: #1f2937;
            border-bottom-right-radius: var(--radius-md);
        }

        .back-btn:hover {
            background-color: #111827;
        }

        .close-btn {
            top: 0;
            right: 0;
            background-color: var(--danger-color);
            border-bottom-left-radius: var(--radius-md);
        }

        .close-btn:hover {
            background-color: #dc2626;
        }

        /* News ticker */
        .news-ticker-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            background-color: #1f2937;
            color: white;
            padding: 0.5rem;
            overflow: hidden;
            z-index: 50;
        }

        .news-ticker {
            white-space: nowrap;
            animation: ticker 20s linear infinite;
        }

        @keyframes ticker {
            0% { transform: translateX(100%); }
            100% { transform: translateX(-100%); }
        }

        /* Mobile optimizations */
        @media (max-width: 640px) {
            .container {
                margin: 1rem auto;
                gap: 1rem;
            }

            .header {
                padding: 1rem;
            }

            .header-logo {
                width: 2.5rem;
                height: 2.5rem;
            }

            .header-info h1 {
                font-size: 1rem;
            }

            .header-info p {
                font-size: 0.75rem;
            }

            .card {
                padding: 1rem;
            }

            .card-title {
                font-size: 1rem;
            }

            .qr-code {
                width: 180px;
                height: 180px;
            }

            .action-buttons {
                grid-template-columns: 1fr;
            }

            .payment-app {
                padding: 0.5rem 0.75rem;
                font-size: 0.75rem;
            }

            .payment-app img {
                width: 1.25rem;
                height: 1.25rem;
            }
        }

        /* Success/failure background styles */
        .success-bg {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
            color: white !important;
        }

        .failure-bg, .timeout-bg {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%) !important;
            color: white !important;
        }
    </style>
</head>
<body>
    <!-- Back button -->
    <button class="back-btn">
        <i class="fas fa-arrow-up"></i> Go Back
    </button>

    <!-- News ticker -->
    <div class="news-ticker-container">
        <div class="news-ticker">
            <?= $news ?>
        </div>
    </div>



    <!-- Particles.js background -->
    <div id="particles-js"></div>

    <!-- Banner notification -->
    <div id="banner" class="banner">
        <span id="bannerMessage"></span>
    </div>

    <?php if ($displayLoadingScreen): ?>
    <!-- Loader overlay -->
    <div id="loader" class="loader-overlay">
        <div class="loader">
            <div class="loader-circle"></div>
            <div class="loader-circle"></div>
            <div class="loader-circle"></div>
        </div>
        <div class="loader-text">Initializing Secure Payment...</div>
        <?php if (!$removeBranding): ?>
        <div class="loader-brand">UpiGateway<sup>™</sup></div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Main container -->
    <div class="container">
        <?php if ($displayHeaderFooter): ?>
        <!-- Header -->
        <header class="header <?php echo htmlspecialchars($defaultClass); ?>" style="<?php echo htmlspecialchars($gradientStyle); ?>">
            <div class="header-left">
                <img class="header-logo" src="https://<?php echo htmlspecialchars($_SERVER['SERVER_NAME'] . "/auth/" . $logo); ?>" alt="Merchant Logo">
                <div class="header-info">
                    <h1><?php echo htmlspecialchars($USERNAME); ?></h1>
                    <p>
                        <span class="verified-badge">
                            <i class="fas fa-check-circle"></i> Verified
                        </span>
                        Business
                    </p>
                </div>
            </div>
        </header>
        <?php endif; ?>

        <!-- Main content -->
        <div class="content" id="content">


            <!-- Right column -->
            <div class="right-column">
                <!-- QR code card -->
                <div class="card" style="<?php echo $showQr ? '' : 'display: none;'; ?>">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-qrcode"></i> Scan & Pay
                                        <?php if ($showPaymentLogos): ?>
                    <div class="payment-methods">
                        <div class="payment-app"><img src="https://w7.pngwing.com/pngs/173/994/png-transparent-paytm-social-icons-color-icon-thumbnail.png" alt="Paytm">
                        </div><div class="payment-app"><img src="https://img.icons8.com/color/48/google-pay-india.png" alt="GPay">
                        </div><div class="payment-app"><img src="https://img.icons8.com/?size=100&id=OYtBxIlJwMGA&format=png" alt="PhonePe">
                        </div><div class="payment-app"><img src="https://img.icons8.com/?size=100&id=BnFdXmRYms8Y&format=png" alt="BHIM">
                        </div>
                    </div>
                    <?php endif; ?>
                        </h2>
                    </div>
                    <div class="qr-container">
                        <?php if (isset($base64Image)): ?>
                        <img id="qrCode" class="qr-code" src="data:image/png;base64,<?php echo htmlspecialchars($base64Image); ?>" alt="Payment QR Code">
                        <?php elseif (isset($qrCodeBase64)): ?>
                        <img id="qrCode" class="qr-code" src="<?php echo htmlspecialchars($qrCodeBase64); ?>" alt="Payment QR Code">
                        <?php else: ?>
                        <p class="text-sm text-red-500 text-center">Unable to generate QR code</p>
                        <?php endif; ?>
                        <p class="qr-instruction">Scan this QR code with any UPI app to make payment</p>
                    </div>

        
                </div>

                <!-- UPI form -->
                <?php if ($showUpiRequest && $request_upi == "1"): ?>
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-paper-plane"></i> Send UPI Request
                        </h2>
                    </div>
                    <div class="upi-form">
                        <form action="https://<?php echo htmlspecialchars($_SERVER["SERVER_NAME"]); ?>/payment/instant-pay/hdfcupipay/<?php echo htmlspecialchars($link_token); ?>" method="post">
                            <input type="hidden" name="cxr_XsRFtoken" value="<?php echo htmlspecialchars($description); ?>">
                            <input type="text" name="upiId" placeholder="Enter UPI ID to send request" required class="upi-input">
                            <input type="hidden" name="TransactionId" value="<?php echo htmlspecialchars($cxrkalwaremark); ?>">
                            <button type="submit" name="subupireq" class="upi-submit">Pay ₹<?php echo number_format($amount, 2); ?></button>
                        </form>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Action buttons -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-credit-card"></i> Payment Options
                        </h2>
                    </div>
                    <div class="action-buttons">
                        <?php if ($showPaytmButton): ?>
                        <a href="<?php echo htmlspecialchars($paytmintent); ?>" class="btn btn-secondary">
                            <img src="https://w7.pngwing.com/pngs/173/994/png-transparent-paytm-social-icons-color-icon-thumbnail.png" alt="Paytm">
                            Paytm
                        </a>
                        <?php endif; ?>
                        
                        <?php if ($base64Image || $qrCodeBase64): ?>
                            <?php if ($showGpayButton): ?>
                            <button id="shareQr" class="btn btn-primary">
                                <img src="https://img.icons8.com/color/48/google-pay-india.png" alt="GPay">
                                GPay
                            </button>
                            <?php endif; ?>
                            
                            <?php if ($show_phonepe): ?>
                            <button id="showGuideButton" class="btn btn-primary">
                                <img src="https://img.icons8.com/?size=100&id=OYtBxIlJwMGA&format=png" alt="PhonePe">
                                PhonePe
                            </button>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <?php if ($showDownloadQr && (isset($base64Image) || isset($qrCodeBase64))): ?>
                        <a href="<?php echo isset($base64Image) ? 'data:image/png;base64,' . htmlspecialchars($base64Image) : htmlspecialchars($qrCodeBase64); ?>" download="payment_qr.png" class="btn btn-secondary">
                            <i class="fas fa-download"></i>
                            Download QR
                        </a>
                        <?php endif; ?>
                        
                        <?php if ($showIntentButton && isset($upi_id)): ?>
                        <a href="<?php echo htmlspecialchars($upi_base); ?>" class="btn btn-primary">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/f/ff/UPI_Logo_vector.svg" alt="UPI">
                            UPI App Intent
                        </a>
                        <?php endif; ?>
                        
                        <?php if ($method=="MANUAL" || $method=="MOBIKWIK" || $method=="Bharatpe"): ?>
                        <button id="updateBalanceBtn" class="btn btn-secondary">
                            <i class="fas fa-receipt"></i>
                            Submit UTR
                        </button>
                        <?php endif; ?>
                    </div>
                    
                    <button id="cancel-button" class="btn btn-danger cancel-trigger" data-order-id="<?php echo htmlspecialchars($order_id); ?>" style="margin-top: 1rem;">
                        <i class="fas fa-times-circle"></i>
                        Cancel Payment
                    </button>
                </div>
            </div>
                        <!-- Left column -->
            <div class="left-column">
                <!-- Payment summary card -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-receipt"></i> Payment Summary
                        </h2>
                    </div>
                    <div class="payment-summary">
                        <div class="summary-item">
                            <div class="summary-label">
                                <i class="fas fa-hashtag"></i> Order ID
                            </div>
                            <div class="summary-value" id="orderID"><?php echo htmlspecialchars($order_id); ?></div>
                        </div>
                        <div class="summary-item">
                            <div class="summary-label">
                                <i class="fas fa-rupee-sign"></i> Amount
                            </div>
                            <div class="summary-value amount-value">₹<?php echo number_format($amount, 2); ?></div>
                        </div>
                        <div class="summary-item">
                            <div class="summary-label">
                                <i class="fas fa-clock"></i> Status
                            </div>
                            <div class="summary-value" id="paymentStatus">Processing...</div>
                        </div>
                    </div>
                </div>

                <!-- Progress card -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-spinner"></i> Payment Progress
                        </h2>
                    </div>
                    <div class="progress-section">
                        <div class="progress-header">
                            <div class="progress-title" id="progressText">
                                <i class="fas fa-sync-alt fa-spin"></i> Checking Payment Status...
                            </div>
                            <div class="timer">
                                <i class="fas fa-hourglass-half"></i>
                                <span id="timeout">5:00</span>
                            </div>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" id="progressBar2"></div>
                        </div>
                        <div class="progress-bar" hidden>
                            <div class="progress-fill" id="progressBar"></div>
                        </div>
                        <p class="progress-status">Please do not close this page until payment is complete</p>
                    </div>
                </div>

                <?php if ($showHelp): ?>
                <div class="help-section">
                    <a href="<?php echo htmlspecialchars($whatsappUrl); ?>" target="_blank" class="help-link">
                        <i class="fab fa-whatsapp"></i> Need help? Chat with us
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!$removeBranding && $displayHeaderFooter): ?>
        <!-- Footer -->
        <div class="footer">
            Powered by <span class="footer-brand">UpiGateway<sup>™</sup></span>
        </div>
        <?php endif; ?>
    </div>

    <!-- Loader & Content Visibility Script -->
    <?php if ($displayLoadingScreen): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loaderElement = document.getElementById('loader');
            const contentElement = document.getElementById('content');

            setTimeout(() => {
                if (loaderElement) {
                    loaderElement.classList.add('hidden');
                }
            }, 1500);
        });
    </script>
    <?php endif; ?>

    <!-- Particles.js script -->
    <script>
        async function loadScript(src) {
            return new Promise((resolve, reject) => {
                const script = document.createElement('script');
                script.src = src;
                script.async = true;
                script.onload = resolve;
                script.onerror = reject;
                document.head.appendChild(script);
            });
        }

        window.addEventListener('load', async () => {
            try {
                await loadScript('https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js');
                particlesJS("particles-js", {
                    "particles": {
                        "number": {"value": 40, "density": {"enable": true, "value_area": 1200}},
                        "color": {"value": "#6366f1"},
                        "shape": {"type": "circle", "stroke": {"width": 0, "color": "#000000"}},
                        "opacity": {"value": 0.3, "random": true},
                        "size": {"value": 3, "random": true},
                        "line_linked": {"enable": true, "distance": 150, "color": "#6366f1", "opacity": 0.2, "width": 1},
                        "move": {"enable": true, "speed": 2, "direction": "none", "random": true, "straight": false, "out_mode": "out"}
                    },
                    "interactivity": {
                        "detect_on": "canvas",
                        "events": {"onhover": {"enable": true, "mode": "grab"}, "onclick": {"enable": true, "mode": "push"}, "resize": true},
                        "modes": {"grab": {"distance": 140, "line_linked": {"opacity": 0.8}}, "push": {"particles_nb": 3}}
                    },
                    "retina_detect": true
                });
            } catch (e) {
                console.error('Particles.js failed to load:', e);
            }
        });
    </script>
</body>
</html>