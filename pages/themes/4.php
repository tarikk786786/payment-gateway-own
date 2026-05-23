<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Gateway - Elegant Minimalist</title>
    <!-- Google Fonts: Lato for clean, modern look -->
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <!-- Tailwind CSS (replace with your actual Tailwind setup or use a CDN if not compiling) -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Root CSS Variables for easy theme customization */
        :root {
            --primary-bg: #fdfdfd; /* Off-white background */
            --content-bg: #ffffff; /* Pure white for content blocks */
            --text-dark: #333d47; /* Dark charcoal for primary text */
            --text-light: #6a7480; /* Muted gray for secondary text */
            --accent-blue: #007bff; /* Bright blue for accents */
            --accent-green: #28a745; /* Standard green for success */
            --accent-red: #dc3545; /* Standard red for warning/cancel */
            --border-soft: #e9ecef; /* Soft border color */
            --shadow-subtle: 0 4px 20px rgba(0, 0, 0, 0.05); /* Very light shadow */
            --font-main: 'Lato', sans-serif;
        }

        body {
            font-family: var(--font-main);
            background-color: var(--primary-bg);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: flex-start; /* Align to top, main container will center with margin auto */
            color: var(--text-dark);
            overflow-y: auto; /* Allow scrolling if content overflows */
            position: relative;
        }

        /* Particles.js background - very subtle */
        #particles-js {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            opacity: 0.7; /* Make them lighter */
        }

        /* Main Container - A wrapper for all content blocks */
        #mainContainer {
            width: 100%;
            max-width: 900px; /* Wider for 2-column desktop layout */
            margin: 1rem auto; /* Top margin and auto horizontal for centering */
            display: flex;
            flex-direction: column; /* Default to column for mobile */
            gap: 0.5rem; /* Spacing between main sections */
            position: relative;
            z-index: 1; /* Above particles */
        }

        /* Header section */
        .page-header {
            background-color: var(--content-bg);
            border-radius: 0.75rem;
            padding: 1.5rem;
            box-shadow: var(--shadow-subtle);
            display: flex;
            align-items: center;
            justify-content: space-between;
            color: var(--text-dark);
            /* Apply gradient if provided by PHP */
            <?php echo $gradientStyle; ?>
            <?php if (!empty($gradientStyle)): /* If gradient, text color might need to be white */ ?>
                color: white;
            <?php endif; ?>
        }

        .page-header img {
            width: 4rem; /* Logo size */
            height: 4rem;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--border-soft);
            <?php if (!empty($gradientStyle)): /* If gradient, white border */ ?>
                border-color: rgba(255, 255, 255, 0.6);
            <?php endif; ?>
            transition: transform 0.2s ease-out;
        }

        .page-header img:hover {
            transform: scale(1.05);
        }

        .page-header-info h1 {
            font-size: 1.4rem;
            font-weight: 700;
            line-height: 1.2;
        }

        .page-header-info p {
            font-size: 0.85rem;
            font-weight: 400;
            opacity: 0.9;
        }

        /* Main Content Section - handles 2 columns on desktop */
        #content {
            display: flex;
            flex-direction: column; /* Default to column for mobile */
            gap: 1.5rem; /* Spacing between content blocks */
            opacity: 1; /* Default visible */
            transition: opacity 0.6s ease-in-out;
        }
        <?php if ($displayLoadingScreen): ?>
        /* Content hidden for loader */
        #content {
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.8s ease-in-out;
        }
        #content.open {
            opacity: 1;
            pointer-events: auto;
        }
        <?php endif; ?>

        /* General content block styling */
        .content-block {
            background-color: var(--content-bg);
            border-radius: 0.75rem;
            padding: 1rem;
            box-shadow: var(--shadow-subtle);
            border: 1px solid var(--border-soft);
        }

        /* Collapsible Section Styles */
        .block-header {
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: 600;
            color: var(--text-dark);
            font-size: 1.1rem;
            margin-bottom: 0.75rem; /* Space below header */
        }
        .block-header i {
            transition: transform 0.3s ease-out;
        }
        .block-header.active i {
            transform: rotate(180deg);
        }
        .collapsible-content {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.4s ease-out;
        }
        .collapsible-content.open {
            max-height: 500px; /* Sufficient height to show content */
        }

        /* Payment Summary */
        .summary-details p {
            font-size: 0.9rem;
            color: var(--text-light);
            display: flex;
            align-items: center;
            margin-bottom: 0.6rem;
        }
        .summary-details p:last-child {
            margin-bottom: 0;
        }
        .summary-details p strong {
            color: var(--text-dark);
            margin-right: 0.5rem;
            min-width: 80px; /* Align content */
        }
        .summary-details i {
            margin-right: 0.6rem;
            font-size: 1rem;
            color: var(--accent-blue); /* Consistent icon color */
        }
        .summary-details #paymentStatus {
            font-weight: 600;
            color: var(--text-dark);
        }

        /* Progress bars & status */
        .progress-indicator h3 {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 0.8rem;
            color: var(--text-dark);
        }
        .progress-bar-line {
            background-color: var(--border-soft);
            border-radius: 9999px;
            height: 0.5rem;
            overflow: hidden;
            margin-bottom: 0.4rem;
        }
        .progress-bar-fill {
            height: 100%;
            background-color: var(--accent-blue);
            width: 0%; /* Controlled by JS */
            transition: width 0.3s ease-out;
        }
        .animate-progress { /* Simple animation for progress bar */
            animation: progressPulse 2s infinite linear;
        }
        @keyframes progressPulse {
            0% { width: 10%; background-color: var(--accent-blue); }
            50% { width: 80%; background-color: #00bcd4; } /* Slightly different shade at peak */
            100% { width: 10%; background-color: var(--accent-blue); }
        }

        /* QR Section */
        .qr-display {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1rem;
            text-align: center;
        }
        .qr-display img {
            width: 12rem;
            height: 12rem;
            border: 1px solid var(--border-soft);
            border-radius: 0.75rem;
            padding: 0.5rem;
            background-color: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            transition: transform 0.2s ease-out, box-shadow 0.2s ease-out;
        }
        .qr-display img:hover {
            transform: scale(1.03);
            box-shadow: 0 6px 20px rgba(0,0,0,0.12);
        }

        /* UPI Logos */
        .supported-apps {
            display: flex;
            justify-content: center;
            gap: 0.8rem;
            margin-top: 0.5rem;
        }
        .supported-apps img {
            width: 2rem;
            height: 2rem;
            border-radius: 50%;
            object-fit: contain;
            box-shadow: 0 1px 4px rgba(0,0,0,0.08);
            transition: transform 0.2s ease-out;
        }
        .supported-apps img:hover {
            transform: scale(1.15);
        }

        /* Buttons */
        .action-buttons {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 0.75rem;
            margin-top: 1rem;
        }
        .btn {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0.8rem 1rem;
            border-radius: 0.5rem;
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.2s ease-out;
            text-decoration: none;
            color: white;
            border: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .btn img, .btn svg {
            width: 1.2rem;
            height: 1.2rem;
            margin-right: 0.5rem;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .btn-primary { background-color: var(--accent-blue); }
        .btn-primary:hover { background-color: #0056b3; }
        .btn-success { background-color: var(--accent-green); }
        .btn-success:hover { background-color: #218838; }
        .btn-neutral { background-color: #6c757d; }
        .btn-neutral:hover { background-color: #5a6268; }

        /* Cancel Button */
        .cncl-btn {
            background-color: var(--accent-red);
            color: white;
            padding: 0.7rem 1.4rem;
            border-radius: 0.5rem;
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
            border: none;
            transition: background-color 0.2s ease-out, transform 0.2s ease-out;
            display: block; /* Make it a block for centering */
            margin: 1.5rem auto 0.5rem auto; /* Top margin for separation */
        }
        .cncl-btn:hover {
            background-color: #c82333;
            transform: scale(1.02);
        }
        .cncl-btn:disabled {
            background-color: #d1d5db;
            cursor: not-allowed;
            transform: none;
        }

        /* UPI Form for manual/request */
        .upi-form-section {
            padding-top: 1rem; /* Spacing from above elements */
        }
        .upi-form-section form {
            display: flex;
            gap: 0.5rem;
        }
        .upi-form-section input {
            flex-grow: 1;
            padding: 0.75rem;
            border: 1px solid var(--border-soft);
            border-radius: 0.5rem;
            font-size: 0.9rem;
            color: var(--text-dark);
            outline: none;
            transition: border-color 0.2s ease-out;
        }
        .upi-form-section input:focus {
            border-color: var(--accent-blue);
        }
        .upi-form-section button {
            padding: 0.75rem 1rem;
            background-color: var(--accent-blue);
            color: white;
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 500;
            transition: background-color 0.2s ease-out;
        }
        .upi-form-section button:hover {
            background-color: #0056b3;
        }

        /* Footer */
        .page-footer {
            background-color: var(--content-bg);
            border-radius: 0.75rem;
            padding: 1rem;
            text-align: center;
            font-size: 0.8rem;
            color: var(--text-light);
            border-top: 1px solid var(--border-soft);
            box-shadow: var(--shadow-subtle);
        }
        .page-footer .powered-by span {
            font-weight: 600;
            color: var(--accent-blue);
        }
        .page-footer .powered-by sup {
            font-size: 0.6em;
            color: var(--accent-blue);
            vertical-align: super;
        }

        /* Loader specific styles */
        .loader-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: var(--primary-bg);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 2000;
            opacity: 1;
            transition: opacity 0.8s ease-out, visibility 0s 0.8s;
            visibility: visible;
        }
        .loader-overlay.hidden {
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
        }

        .loader-dot-pulse {
            width: 50px;
            height: 50px;
            position: relative;
            margin-bottom: 1.5rem;
        }
        .loader-dot {
            width: 12px;
            height: 12px;
            background-color: var(--accent-blue);
            border-radius: 50%;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            animation: dotPulse 1.2s cubic-bezier(0.45, 0.05, 0.55, 0.95) infinite;
        }
        .loader-dot:nth-child(1) { animation-delay: 0s; }
        .loader-dot:nth-child(2) { animation-delay: 0.2s; background-color: #00a0ff; }
        .loader-dot:nth-child(3) { animation-delay: 0.4s; background-color: #33bbff; }

        @keyframes dotPulse {
            0% { transform: translate(-50%, -50%) scale(0.8); opacity: 0.6; }
            50% { transform: translate(-50%, -50%) scale(1.2); opacity: 1; }
            100% { transform: translate(-50%, -50%) scale(0.8); opacity: 0.6; }
        }

        .loader-brand {
            font-family: var(--font-main);
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-dark);
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
        }
        .loader-brand sup {
            font-size: 0.7em;
            color: var(--accent-blue);
            vertical-align: super;
        }
        .loader-status-text {
            font-family: var(--font-main);
            font-size: 1rem;
            color: var(--text-light);
        }

        /* Banner styles */
        .banner {
            position: fixed;
            top: 1.5rem;
            left: 50%;
            transform: translateX(-50%) translateY(-50px);
            background-color: #333d47;
            color: white;
            padding: 0.8rem 1.5rem;
            border-radius: 0.5rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
            z-index: 1000;
            display: none;
            animation: slideDown 0.3s ease-out forwards;
        }
        @keyframes slideDown {
            from { transform: translateX(-50%) translateY(-50px); opacity: 0; }
            to { transform: translateX(-50%) translateY(0); opacity: 1; }
        }
        .slideUp {
            animation: slideUp 0.3s ease-in forwards;
        }
        @keyframes slideUp {
            from { transform: translateX(-50%) translateY(0); opacity: 1; }
            to { transform: translateX(-50%) translateY(-50px); opacity: 0; }
        }
        
        /* Desktop specific styles (2-column layout) */
        @media (min-width: 768px) {
            #content {
                flex-direction: row; /* Make content a row for columns */
                flex-wrap: wrap; /* Allow columns to wrap if needed */
                justify-content: center; /* Center columns horizontally */
                align-items: flex-start; /* Align columns at the top */
                gap: 1rem; /* Gap between columns */
            }
            .content-column {
                flex: 1; /* Each column takes equal width */
                min-width: 350px; /* Minimum width for each column */
                display: flex;
                flex-direction: column;
                gap: 1rem; /* Gap between blocks within a column */
            }
            /* Collapsible sections are always open on desktop */
            .collapsible-content {
                max-height: none !important;
                overflow: visible !important;
            }
            .block-header i {
                display: none !important; /* Hide collapse/expand arrow on desktop */
            }
            .block-header {
                cursor: default; /* No pointer cursor on desktop */
                margin-bottom: 0.75rem; /* Reset margin if needed */
            }
            /* Hide the `paymentDropdown` select element entirely if it's there */
            #paymentDropdown {
                display: none !important;
            }
            /* Progress bars: only show progressBar2 */
            #progressBar { /* Target the old progressBar directly */
                display: none !important;
            }
        }
        /* Mobile specific adjustments (max-width: 767px) */
        @media (max-width: 767px) {
            #mainContainer {
                margin: 1rem auto;
                padding: 0 0.5rem; /* Add some horizontal padding */
                gap: 1rem;
            }
            .page-header, .content-block, .page-footer {
                padding: 1rem;
                border-radius: 0.5rem; /* Smaller radius for mobile */
            }
            .page-header img {
                width: 3rem;
                height: 3rem;
            }
            .page-header-info h1 {
                font-size: 1.2rem;
            }
            .page-header-info p {
                font-size: 0.75rem;
            }
            .summary-details p {
                font-size: 0.85rem;
            }
            .qr-display img {
                width: 15rem;
                height: 15rem;
            }
            .supported-apps img {
                width: 1.7rem;
                height: 1.7rem;
            }
            .btn {
                padding: 0.7rem 0.8rem;
                font-size: 0.9rem;
            }
            .btn img, .btn svg {
                width: 1rem;
                height: 1rem;
            }
            .cncl-btn {
                padding: 0.6rem 1.2rem;
                font-size: 0.85rem;
            }
            .upi-form-section input, .upi-form-section button {
                padding: 0.6rem;
                font-size: 0.85rem;
            }
            .loader-brand {
                font-size: 1.7rem;
            }
            .loader-dot-pulse {
                width: 40px;
                height: 40px;
            }
            .loader-dot {
                width: 10px;
                height: 10px;
            }
            .loader-status-text {
                font-size: 0.9rem;
            }

            /* Mobile specific: Hide Payment Details section */
            .payment-details-section-mobile-hide {
                display: none !important;
            }
            /* Progress bars: only show progressBar2 */
            #progressBar { /* Target the old progressBar directly */
                display: none !important;
            }
        }
    </style>
</head>
<body>
<button
    class="bg-black-500 hover:bg-black-600 text-white font-semibold py-0.5 px-1 shadow-lg absolute top-0 left-0 transition-all duration-300 ease-in-out transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-opacity-75 rounded-br-lg z-10">
    ⬆️Go Back
</button>

<!-- News Ticker -->
<div class="absolute top-0 left-0 w-full text-center bg-gray-800 text-white py-1 px-2 shadow-lg overflow-hidden flex items-center justify-center h-8 z-0">
    <div class="news-ticker text-sm md:text-base">
        <?=$news?>
    </div>
</div>


    <div id="security-banner"></div>
    <div id="banner" class="banner">
        <span id="bannerMessage"></span>
    </div>

    <!-- Particles.js Background -->
    <div id="particles-js"></div>

    <?php if ($displayLoadingScreen): ?>
    <!-- Loader Overlay -->
    <div id="loader" class="loader-overlay">
        <div class="loader-dot-pulse">
            <div class="loader-dot"></div>
            <div class="loader-dot"></div>
            <div class="loader-dot"></div>
        </div>
        <?php if (!$removeBranding): ?>
        <div class="loader-brand">UpiGateway<sup>™</sup></div>
        <?php endif; ?>
        <div class="loader-status-text">Initializing Secure Connection...</div>
    </div>
    <?php endif; ?>

    <!-- Loader & Content Visibility Script -->
    <?php if ($displayLoadingScreen): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loaderElement = document.getElementById("loader");
            const contentElement = document.getElementById("content");

            setTimeout(() => {
                if (loaderElement) {
                    loaderElement.classList.add("hidden"); // Hide loader with fade-out
                }
                if (contentElement) {
                    contentElement.classList.add("open"); // Show content with fade-in
                }
            }, 1500); // Loader visible for 1.5 seconds
        });
    </script>
    <?php endif; ?>

    <!-- Main Payment Container -->
    <div id="mainContainer">
        <?php if ($displayHeaderFooter): ?>
        <header class="<?= htmlspecialchars($defaultClass); ?> page-header" style="<?= htmlspecialchars($gradientStyle); ?>">
            <img src="https://<?= htmlspecialchars($server . "/auth/" . $logo); ?>" alt="Company Logo">
            <div class="page-header-info text-right">
                <h6 class="text-sm font-light opacity-90">Paying To...</h6>
                <div class="flex items-center justify-end">
                    <h1><?= htmlspecialchars($USERNAME); ?></h1>
                    <img src="https://<?= htmlspecialchars($server); ?>/assets/img/verify.png" alt="Verified Badge" class="w-5 h-5 ml-2">
                </div>
                <p class="text-xs text-green-300">Verified Business</p>
            </div>
        </header>
        <?php endif; ?>

        <div id="content">
            <!-- Left Column for Desktop -->
            <div class="content-column">
                <!-- Payment Summary Block - Collapsible on mobile -->
                <div class="content-block">
                    <div class="block-header clickable-header" data-target="paymentSummaryContent">
                        <h2>Payment Summary</h2>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div id="paymentSummaryContent" class="collapsible-content">
                        <p><i class="fas fa-receipt text-blue-500"></i><strong>Order ID:</strong> <span id="orderID"><?php echo htmlspecialchars($order_id); ?></span></p>
                        <p><i class="fas fa-rupee-sign text-green-500"></i><strong>Amount:</strong> ₹<?php echo number_format($amount, 2); ?></p>
                        <p><i class="fas fa-clock text-yellow-500"></i><strong>Status:</strong> <span id="paymentStatus">Processing...</span></p>
                    </div>
                </div>

                <!-- Progress & Timer Block -->


                <!-- Payment Details Block - Hidden on mobile, collapsible on desktop (but effectively always open) -->
                <!-- The class `payment-details-section-mobile-hide` makes it `display: none` on mobile -->
                <div class="content-block payment-details-section-mobile-hide">
                    <div class="block-header clickable-header" data-target="paymentDetailsContent">
                        <h2>Payment Details</h2>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div id="paymentDetailsContent" class="collapsible-content">
                        <p class="text-sm text-gray-700"><strong>Order ID:</strong> <span class="font-medium"><?php echo htmlspecialchars($order_id); ?></span></p>
                        <p class="text-sm text-gray-700"><strong>Amount:</strong> <span class="font-medium">₹<?php echo number_format($amount, 2); ?></span></p>
                        <p class="text-sm text-gray-700"><strong>Transaction ID:</strong> <span class="font-medium"><?php echo htmlspecialchars($description); ?></span></p>
                    </div>
                </div>
                <!-- Original paymentDropdown and paymentDetails from reference themes, kept hidden for ID compliance -->
                <select id="paymentDropdown" class="hidden"></select>
                <div id="paymentDetails" class="hidden"></div>
                <div class="content-block progress-indicator">
                    <h3 id="progressText">⌛ Waiting For Payment...</h3>
                    <div class="progress-bar-line">
                        <div id="progressBar2" class="progress-bar-fill animate-progress"></div>
                    </div>
                    <p class="text-xs text-gray-600 mt-1">🔄 Checking Payment Status...</p>
                    <p class="text-sm text-gray-700 mt-3 text-center">Time Left: <strong><span id="timeout">5:00:00</span></strong></p>
                    <!-- progressBar (the second one) is hidden via CSS for all screens -->
                    <div id="progressBar" class="hidden"></div> 
                </div>
                               <p class="instruction text-xs text-red-500 text-center flex items-center justify-center gap-1 mt-4">
                        <i class="fas fa-volume-up text-blue-500"></i> "पेज बंद न करें। भुगतान के बाद इसी पेज पर रहें ताकि लेनदेन तुरंत प्रोसेस हो।"
                    </p>

                    <?php if ($showHelp): ?>
                    <a class="text-sm text-blue-600 hover:underline text-center mt-3 block" target="_blank" href="<?php echo htmlspecialchars($whatsappUrl);?>">Need help? Click me💬</a>
                    <?php endif; ?>
            </div> <!-- End Left Column -->

            <!-- Right Column for Desktop -->
            <div class="content-column">
                <!-- QR & UPI Options Block -->
                <div class="content-block qr-display" style="<?php echo $showQr ? '' : 'display: none;'; ?>">
                    <h3 class="text-lg font-semibold mb-3">Scan & Pay</h3>
                    <?php if ($base64Image): ?>
                        <img id="qrCode" src="data:image/png;base64,<?php echo htmlspecialchars($base64Image); ?>" alt="QR Code">
                    <?php elseif ($qrCodeBase64): ?>
                        <img id="qrCode" src="<?php echo htmlspecialchars($qrCodeBase64); ?>" alt="QR Code">
                    <?php else: ?>
                        <p class="text-sm text-red-500 text-center">Error generating QR code.</p>
                    <?php endif; ?>
                    
                    <?php if ($showPaymentLogos): ?>
                    <!--<span class="text-sm font-medium text-gray-600">PAY WITH ANY APP</span>-->
                    <div class="supported-apps">
                        <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQyVO9LUWF81Ov6LZR50eDNu5rNFCpkn0LwYQ&s" alt="Google Pay">
                        <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTo4x8kSTmPUq4PFzl4HNT0gObFuEhivHOFYg&s" alt="PhonePe">
                        <img src="https://w7.pngwing.com/pngs/173/994/png-transparent-paytm-social-icons-color-icon-thumbnail.png" alt="PayTM">
                        <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRSouM4icV33KEDtJakZiySZN3HH2LPfv3-BA&s" alt="BHIM">
                    </div>
                    <?php endif; ?>
                    <?php if ($hdfc_seassion || $method=="MANUAL" || $method=="MOBIKWIK" || $method=="Bharatpe"): ?>
                    <button type="button" id="updateBalanceBtn" class="btn btn-neutral w-full mt-4">
                        🔄 Not Updating? <span class="underline ml-1">Check Status!</span>
                    </button>
                    <?php endif; ?>
                </div>

                <!-- UPI ID Form (if applicable) -->
                <?php if ($showUpiRequest && $request_upi == "1"): ?>
                <div class="content-block upi-form-section">
                    <h3 class="text-md font-semibold mb-3">Send UPI Request</h3>
                    <form action="https://<?= htmlspecialchars($_SERVER["SERVER_NAME"]) ?>/payment/instant-pay/hdfcupipay/<?php echo htmlspecialchars($link_token); ?>" method="post">
                        <input type="hidden" name="cxr_XsRFtoken" value="<?php echo htmlspecialchars($description); ?>">
                        <input type="text" name="upiId" placeholder="Enter UPI ID to Send request" required>
                        <input type="hidden" name="TransactionId" value="<?php echo htmlspecialchars($cxrkalwaremark); ?>">
                        <button type="submit" name="subupireq" class="btn btn-primary">Pay ₹<?php echo number_format($amount, 2); ?></button>
                    </form>
                </div>
                <?php endif; ?>

                <!-- Action Buttons Block -->
                <div class="content-block">
                    <div class="action-buttons">
                        <?php if ($showPaytmButton): ?>
                        <a href="<?php echo htmlspecialchars($paytmintent); ?>" class="btn btn-neutral">
                            <img src="https://w7.pngwing.com/pngs/173/994/png-transparent-paytm-social-icons-color-icon-thumbnail.png" alt="Paytm" class="w-5 h-5 mr-1"> Paytm
                        </a>
                        <?php endif; ?>
                        <?php if ($base64Image || $qrCodeBase64): ?>
                            <?php if ($showGpayButton): ?>
                            <button id="shareQr" class="btn btn-success">
                                <img src="https://img.icons8.com/color/48/google-pay-india.png" alt="Gpay" class="w-5 h-5 mr-1"> GPay
                            </button>
                            <?php endif; ?>
                            <?php if ($show_phonepe): ?>
                            <button id="showGuideButton" class="btn btn-success">
                                <img src="https://img.icons8.com/?size=100&id=OYtBxIlJwMGA&format=png" alt="Gpay" class="w-5 h-5 mr-1"> PhonePe
                            </button>
                            <?php endif; ?>
                        <?php endif; ?>
                        <?php if ($showDownloadQr && ($base64Image || $qrCodeBase64)): ?>
                        <a id="downloadQr" href="<?php echo isset($base64Image) ? 'data:image/png;base64,' . htmlspecialchars($base64Image) : htmlspecialchars($qrCodeBase64); ?>" download="qr.png" class="btn btn-neutral">
                            <i class="fas fa-download mr-2"></i> Download QR
                        </a>
                        <?php endif; ?>
                        <?php if ($showIntentButton && isset($upi_id)): ?>
                        <a href="<?php echo htmlspecialchars($upi_base); ?>" class="btn btn-primary">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/f/ff/UPI_Logo_vector.svg" alt="UPI" class="w-5 h-5 mr-1"> Pay with UPI App
                        </a>
                        <?php endif; ?>
                    </div>

 
                    <button id="cancel-button" class="cncl-btn cancel-trigger" data-order-id="<?php echo htmlspecialchars($order_id); ?>">Cancel Payment</button>
                </div>
            </div> <!-- End Right Column -->

        </div> <!-- End of #content -->

        <?php if ($displayHeaderFooter): ?>
        <footer class="<?= htmlspecialchars($defaultClass); ?> page-footer" style="<?= htmlspecialchars($gradientStyle); ?>">
            <?php if (!$removeBranding): ?>
                <div class="powered-by">
                    Powered by 
                    <span class="font-semibold">UpiGateway<sup>™</sup></span>
                </div>
            <?php endif; ?>
        </footer>
        <?php endif; ?>
    </div> <!-- End of #mainContainer -->
    
    <!-- Particles.js script loading -->
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
                        "number": {"value": 30, "density": {"enable": true, "value_area": 1200}},
                        "color": {"value": "#e9ecef"}, /* Very light, subtle particles */
                        "shape": {"type": "circle", "stroke": {"width": 0, "color": "#000000"}},
                        "opacity": {"value": 0.3, "random": false},
                        "size": {"value": 1.5, "random": true},
                        "line_linked": {"enable": true, "distance": 180, "color": "#e9ecef", "opacity": 0.3, "width": 1},
                        "move": {"enable": true, "speed": 2, "direction": "none", "random": false, "straight": false, "out_mode": "out", "bounce": false}
                    },
                    "interactivity": {
                        "detect_on": "canvas",
                        "events": {"onhover": {"enable": true, "mode": "grab"}, "onclick": {"enable": true, "mode": "push"}, "resize": true},
                        "modes": {"grab": {"distance": 160, "line_linked": {"opacity": 1}}, "bubble": {"distance": 200, "size": 0, "duration": 2, "opacity": 0, "speed": 3}, "repulse": {"distance": 200, "duration": 0.4}, "push": {"particles_nb": 4}, "remove": {"particles_nb": 2}}
                    },
                    "retina_detect": true
                });
            } catch (e) {
                console.error('Particles.js failed to load:', e);
            }
        });

        // JavaScript for collapsible sections
        document.addEventListener('DOMContentLoaded', function() {
            const clickableHeaders = document.querySelectorAll('.clickable-header');
            const mobileBreakpoint = 767; // Matches CSS media query for mobile

            function initializeCollapsible(header, targetContent, arrowIcon) {
                // Determine if we are on desktop or mobile
                if (window.innerWidth <= mobileBreakpoint) { // Mobile: Start minimized
                    header.classList.remove('active');
                    targetContent.classList.remove('open');
                    targetContent.style.maxHeight = '0px';
                    if (arrowIcon) arrowIcon.style.display = 'inline-block'; // Show arrow
                    header.style.cursor = 'pointer';
                } else { // Desktop: Start fully open
                    header.classList.add('active');
                    targetContent.classList.add('open');
                    targetContent.style.maxHeight = 'none'; // Let content define height
                    if (arrowIcon) arrowIcon.style.display = 'none'; // Hide arrow
                    header.style.cursor = 'default';
                }
            }

            clickableHeaders.forEach(header => {
                const targetId = header.dataset.target;
                const targetContent = document.getElementById(targetId);
                const arrowIcon = header.querySelector('i'); // Get the arrow icon

                // Initialize state on load
                initializeCollapsible(header, targetContent, arrowIcon);

                header.addEventListener('click', () => {
                    if (window.innerWidth <= mobileBreakpoint) { // Only toggle on mobile
                        header.classList.toggle('active');
                        targetContent.classList.toggle('open');

                        if (targetContent.classList.contains('open')) {
                            targetContent.style.maxHeight = targetContent.scrollHeight + 'px';
                        } else {
                            targetContent.style.maxHeight = '0px';
                        }
                    }
                });

                // Handle window resize for desktop/mobile toggle
                let resizeTimeout;
                window.addEventListener('resize', () => {
                    clearTimeout(resizeTimeout);
                    resizeTimeout = setTimeout(() => {
                        clickableHeaders.forEach(reHeader => { // Re-evaluate all headers on resize
                            const reTargetId = reHeader.dataset.target;
                            const reTargetContent = document.getElementById(reTargetId);
                            const reArrowIcon = reHeader.querySelector('i');
                            initializeCollapsible(reHeader, reTargetContent, reArrowIcon);
                        });
                    }, 200); // Debounce resize event
                });
            });
        });

        // Dummy function for paymentDropdown click (from original theme, kept for ID compliance)
        // This function will not be called in this theme's UI
        function showDetails() {
            console.log("showDetails function from original theme was called, but is not active in this UI.");
            // No functional implementation needed as per new theme design
        }
    </script>
</body>
</html>