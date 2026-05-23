<style>
body {
  min-height: 100vh;
  height: 100vh;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  font-family: 'Arial', sans-serif;
  /* Background with a high-tech gradient */
  background: linear-gradient(-45deg, #00c6ff, #ac59ff, #ff66cc, #88f7b6);
  background-size: 400% 400%;
  /* Animation for smooth background gradient transition */
  animation: gradientAnimation 25s ease infinite, glowEffect 15s ease-in-out infinite;
  overflow: auto;
}
@keyframes gradientAnimation {
  0% {
    background-position: 0% 50%;
  }
  25% {
    background-position: 100% 50%;
  }
  50% {
    background-position: 0% 50%;
  }
  75% {
    background-position: 100% 50%;
  }
  100% {
    background-position: 0% 50%;
  }
}
        .success-bg {
            background: linear-gradient(145deg, #d4fc79 0%, #96e6a1 100%) !important;
        }

        .failure-bg, .timeout-bg {
            background: linear-gradient(145deg, #ff9a9e 0%, #fad0c4 100%) !important;
        }
        .content.open {
        background-color: <?=$bodyColor?>;
        }
        #particles-js {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            background: linear-gradient(45deg, #1a2a6c, #b21f1f, #fdbb2d);
            background-size: 400% 400%;
        }
<?php if ($displayLoadingScreen): ?>
.container {
    width: 850px;
    /*border-radius: 20px;*/
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    height: auto;
    max-width: 350px;
    max-height: 400px;
    justify-content: center;
    align-items: center;
    transition: max-height 0.8s ease-out;
    transition: max-width 0.8s ease-out, filter 0.2s ease-in-out;
}
.container.open{
    max-width: 100%;
    max-height: 100%;
}
.content {
    /*min-height: 40px;*/
    max-height: 5px;
    overflow: hidden;
    filter: blur(200px);
    transition: max-height 0.6s ease-out, filter 0.2s ease-in-out;
    display: block;
}

/* Expanded state */
.content.open {
    padding: 10px;
    max-height: 1000px;
    filter: blur(0);
    border-radius: 0px;
}



<?php else: ?>

.content {
    padding: 10px;
    max-height: 1000px;
    filter: blur(0);
    border-radius: 0px;
}

<?php endif; ?>
    .banner {
        position: fixed;
        top: 40px;
        left: 41%;
        transform: translateX(-50%);
        background-color: black;
        color: white;
        padding: 15px 30px;
        border-bottom-left-radius: 15px;
        border-bottom-right-radius: 15px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        z-index: 1000;
        display: none;
        animation: slideDown 0.3s ease;
    }
    

.upi-form form {
    display: flex;
    align-items: center;
    gap: 0rem; /* Fixed: was 'gap: -1px; 0.75rem;' */
}
.upi-form input {
    flex: 1;
    padding: 10px;
    font-size: 12px;
    border: 1px solid #ccc;
}
.upi-form button {
    padding: 10px 10px;
    font-size: 12px;
    border: none;
    background-color: #007bff;
    color: #fff;
    cursor: pointer;
    border: 1px solid #ccc;
}
.upi-form button:hover {
    background-color: #0056b3;
}

        @keyframes slideDown {
            from { top: -50px; opacity: 0; }
            to { top: 20px; opacity: 1; }
        }

        .slideUp {
            animation: slideUp 0.5s ease forwards;
        }

        @keyframes slideUp {
            from { top: 20px; opacity: 1; }
            to { top: -50px; opacity: 0; }
        }

        .loader-logo {
            font-size: 24px;
            font-weight: 700;
            color: #FF4500;
            letter-spacing: 1px;
        }
        .loader img {
            width: 100px;
            height: 100px;
            margin: 10px 0;
        }
        .loader-text {
            font-size: 16px;
            font-weight: 500;
            color: #333;
        }
        @media (min-width: 768px) {
            #paymentDetails {
                display: block !important;
            }
            #paymentDropdown {
                display: none !important;
            }
            .mobile-bar{
                display: none !important;
            }
        }
        /* Hide content-left on mobile */
        @media (max-width: 767px) {
            .content-left {
                display: none !important;
            }
        }
    </style>

<body class="h-screen flex justify-center items-center">
<button
    class="bg-black text-white font-semibold py-1 px-3 shadow-lg absolute top-0 left-0 transition-all duration-300 ease-in-out transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-opacity-75 rounded-br-lg z-10 flex items-center gap-1">
    🔙⤴️
</button>

<!-- News Ticker -->
<div class="absolute top-0 left-0 w-full text-center bg-gradient-to-r from-gray-900 to-gray-800 text-white py-1 px-2 shadow-lg overflow-hidden flex items-center justify-center h-8 z-0">
    <div class="news-ticker text-sm md:text-base animate-pulse">
        <?php echo $news ;?>
    </div>
</div>

<!-- Close Button -->
<div id="security-banner"></div>
    <div id="banner" class="banner">
        <span id="bannerMessage"></span>
    </div>
    <!-- Rest of your existing HTML -->
    <div id="particles-js"></div>

    <?php if ($displayLoadingScreen): ?>
<script>
    setTimeout(() => {
        // Hide loader
        document.getElementById("loader").style.display = "none";

        // Show content and trigger expansion
        document.querySelector('.content').style.display = 'block';
        document.getElementById("content").classList.add("open");
        document.getElementById("mainContainer").classList.add("open");
    }, 1500); // Trigger everything after 1.5 seconds
</script>
    <?php endif; ?>
    <!-- Header Section -->
    <div id="mainContainer" class="container">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl h-[90vh] flex flex-col overflow-hidden backdrop-filter backdrop-blur-sm bg-opacity-95 border border-gray-200">
<?php if ($displayHeaderFooter): ?>
<header class="<?= $defaultClass ?> p-4 flex items-center justify-between rounded-t-2xl" style="<?= $gradientStyle ?>">
    <img src="https://<?=$server."/auth/". $logo ?>" alt="Company Logo"
        class="w-16 rounded-full border-2 border-white transition-transform duration-300 hover:scale-110">
    
    <div class="company-info text-right">
        <h6 class="text-sm text-white font-light">Paying To...</h6>
        <div class="trusted-business flex items-center justify-end">
            <h1 class="text-xl font-semibold text-white"><?= htmlspecialchars($USERNAME) ?></h1>
            <img src="https://<?= $server ?>/assets/img/verify.png" alt="Trusted Badge" class="w-6 ml-2">
        </div>
        <h5 class="text-xs text-green-400">Verified Business</h5>
    </div>
</header>
<?php endif; ?>
            <?php if ($displayLoadingScreen): ?>
            <!-- Loader Section -->
            <?php
            $icons = [
                "https://$server/pages/tgloader.gif",
                "https://$server/pages/2.gif", 
                "https://$server/pages/3.gif", 
                "https://$server/pages/4.gif"
            ];
            $random_icon = $icons[array_rand($icons)];
            ?>
            
            <div id="loader" class="loader-container flex flex-col items-center justify-center h-full">
                <?php if (!$removeBranding): ?>
                <div class="loader-logo text-3xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-orange-500 to-red-600 animate-pulse">UpiGateway<sup style="font-size: 0.8em; color: #FF4500;">™</sup></div>
                <?php endif; ?>
                <div class="loader relative">
                    <div class="absolute inset-0 bg-gradient-to-r from-blue-400 to-purple-500 opacity-20 rounded-full animate-pulse"></div>
                    <img src="<?php echo $random_icon; ?>" alt="Loading..." class="relative z-10">
                </div>
                <div class="loader-text font-medium text-gray-700 mt-3 animate-pulse">Securing Your Connection<span class="dots">...</span></div>
                <style>
                    @keyframes dots {
                        0%, 20% { content: '.'; }
                        40% { content: '..'; }
                        60%, 100% { content: '...'; }
                    }
                    .dots::after {
                        content: '';
                        animation: dots 1.5s infinite;
                    }
                </style>
            </div>
            <?php endif; ?>
            <div id="content" class="content">
                <main class="flex-1 p-4 overflow-y-auto flex flex-col md:flex-row gap-4">
<div class="content-left flex-1 flex flex-col gap-4">
    <!-- Payment Summary Card -->
<!-- Payment Status Section -->
<div class="payment-summary bg-gray-50 p-4 rounded-lg shadow-md border border-gray-200 hover:shadow-lg transition-all duration-300 transform hover:scale-102">
    <h2 class="text-lg font-semibold text-gray-800 mb-2">Payment Summary</h2>
    
    <div class="flex items-center gap-2 mb-2">
        <i class="fas fa-receipt text-blue-500"></i>
        <p class="text-sm text-gray-700"><strong>Order ID:</strong> 
            <span class="font-medium text-gray-900" id="orderID"><?php echo $order_id; ?></span>
        </p>
    </div>

    <div class="flex items-center gap-2 mb-2">
        <i class="fas fa-rupee-sign text-green-500"></i>
        <p class="text-sm text-gray-700"><strong>Amount:</strong> 
            <span class="font-medium text-gray-900">₹<?php echo number_format($amount, 2); ?></span>
        </p>
    </div>

    <div class="flex items-center gap-2">
        <i class="fas fa-clock text-yellow-500"></i>
        <p class="text-sm text-gray-700"><strong>Status:</strong> 
            <span id="paymentStatus" class="font-medium text-gray-900">Processing...</span>
        </p>
    </div>
</div>

<!-- Animated Progress Bar -->
    <h3 class="text-sm font-medium text-gray-800 mb-2" id="progressText">⌛ Waiting For Payment...</h3>
    
    <div class="relative w-full bg-gray-300 rounded-full h-2.5 overflow-hidden shadow-inner">
        <div id="progressBar2" class="absolute left-0 h-full bg-gradient-to-r from-yellow-400 to-yellow-600 animate-progress w-1/4"></div>
    </div>

    <p class="text-xs text-gray-600 mt-1">🔄 Checking Payment Status...</p>


<!-- Tailwind Animation -->
<style>
    @keyframes progressMove {
        0% { width: 10%; }
        50% { width: 80%; }
        100% { width: 10%; }
    }
    .animate-progress {
        animation: progressMove 2s infinite linear;
    }
    
    .glow-on-hover {
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
    }
    
    .glow-on-hover:hover {
        box-shadow: 0 0 20px rgba(59, 130, 246, 0.5);
    }
    
    .payment-summary:hover, .qr-section:hover {
        transform: translateY(-2px);
    }
    
    .button-section button, .button-section a {
        transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }
    
    .button-section button:hover, .button-section a:hover {
        transform: translateY(-3px) scale(1.05);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
</style>
    <!-- Help/Support Section -->
    <?php if ($showHelp): ?>
    <div class="help-section mt-4">
        <a id="helpButton" href="<?php echo $whatsappUrl;?>" class="text-sm bg-blue-50 text-blue-600 hover:text-blue-800 px-3 py-1.5 rounded-full shadow-sm flex items-center gap-1 transition-all duration-300 hover:bg-blue-100 border border-blue-200">
            <i class="fas fa-question-circle animate-pulse"></i> Need Help?
        </a>
        <div id="helpContent" class="hidden mt-2 text-sm text-gray-700 bg-gray-50 p-3 rounded-lg shadow-md">
            <p>Contact us at <a href="mailto:support@chickenpox.in" class="text-blue-500 hover:underline">support@chickenpox.in</a> or call +91-123-456-7890.</p>
        </div>
    </div>
    <?php endif; ?>
    <!-- Existing Payment Dropdown/Details -->
    <select id="paymentDropdown" onclick="showDetails()" class="w-full p-2 text-sm border border-gray-300 bg-gray-100 rounded-lg cursor-pointer focus:outline-none focus:ring-2 focus:ring-blue-500">
        <option value="">Payment Details of ₹ <?php echo number_format($amount, 2); ?></option>
    </select>
    <div id="paymentDetails" class="payment-details bg-gray-100 p-3 rounded-lg hidden">
        <p class="text-sm text-gray-700"><strong>Order ID:</strong> <span class="font-medium"><?php echo $order_id; ?></span></p>
        <p class="text-sm text-gray-700"><strong>Amount:</strong> <span class="font-medium">₹<?php echo number_format($amount, 2); ?></span></p>
        <p class="text-sm text-gray-700"><strong>Transaction ID:</strong> <span class="font-medium"><?php echo $description; ?></span></p>
    </div>
</div>

                    <div class="content-right flex-1 flex flex-col gap-4">
<div class="qr-section bg-gray-50 p-3 rounded-lg shadow-md">
  <div class="flex flex-wrap md:flex-nowrap items-center justify-center gap-3">
    
    <!-- QR Code Wrapper -->
    <div id="qrWrapper" style="<?php echo $showQr ? '' : 'display: none;'; ?>">
      <?php if ($base64Image): ?>
        <img id="qrCode" src="data:image/png;base64,<?php echo $base64Image; ?>" alt="QR Code"
          class="w-35 h-35 rounded-lg border border-gray-300 transition-transform duration-300 hover:scale-105 hover:shadow-lg">
      <?php elseif ($qrCodeBase64): ?>
        <img id="qrCode" src="<?php echo $qrCodeBase64; ?>" alt="QR Code"
          class="w-40 h-40 rounded-lg border border-gray-300 transition-transform duration-300 hover:scale-105 hover:shadow-lg">
      <?php else: ?>
        <p class="text-sm text-red-500 text-center">Error generating QR code.</p>
      <?php endif; ?>
    </div>

    <!-- Buttons & Payment Logos -->
    <div class="flex flex-col items-center justify-center gap-1">
      <?php if ($showPaymentLogos): ?>
        <span class="text-sm font-medium text-gray-600">PAY ON ANY APP</span>
        <div class="upi-icons flex justify-center gap-1">
          <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQyVO9LUWF81Ov6LZR50eDNu5rNFCpkn0LwYQ&s" alt="Google Pay" class="w-6 h-6 rounded-full transition-transform duration-300 hover:scale-110">
          <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTo4x8kSTmPUq4PFzl4HNT0gObFuEhivHOFYg&s" alt="PhonePe" class="w-6 h-6 rounded-full transition-transform duration-300 hover:scale-110">
          <img src="https://w7.pngwing.com/pngs/173/994/png-transparent-paytm-social-icons-color-icon-thumbnail.png" alt="PayTM" class="w-6 h-6 rounded-full transition-transform duration-300 hover:scale-110">
          <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRSouM4icV33KEDtJakZiySZN3HH2LPfv3-BA&s" alt="BHIM" class="w-6 h-6 rounded-full transition-transform duration-300 hover:scale-110">
        </div>
      <?php endif; ?>

      <?php if ($hdfc_seassion): ?>
        <form action="#" method="post">
          <button type="button" id="updateBalanceBtn"
            class="bg-gray-800 text-white py-1 px-2 border border-gray-600 rounded-sm font-bold cursor-pointer text-sm">
            🔄 <span class="text-yellow-400 underline text-xs">Check Status!</span>
          </button>
        </form>
      <?php endif; ?>

      <?php if ($method == "MANUAL" || $method == "MOBIKWIK" || $method == "Bharatpe"): ?>
        <button class="bg-gray-800 text-white py-1 px-2 border border-gray-600 rounded-sm font-bold cursor-pointer text-sm"
          onclick="payViaUPI()">Submit UTR</button>
      <?php endif; ?>
    </div>

  </div>
</div>

                        <div class="instruction text-xs text-red-500 text-center flex items-center justify-center gap-1">
                            <i class="fas fa-volume-up text-green-500 cursor-pointer"></i> <?php echo $infoMassage; ?>
                        </div>
                        <!--Only HDFC SmartHub SECTION-->
<?php if ($showUpiRequest && $request_upi == "1"): ?>
    <div class="upi-form">
        <form action="https://<?= $_SERVER["SERVER_NAME"] ?>/payment/instant-pay/hdfcupipay/<?php echo $link_token; ?>" method="post">
            <input type="hidden" name="cxr_XsRFtoken" value="<?php echo $description; ?>">
            <input type="text" name="upiId" placeholder="Enter UPI ID to Send request on Upi App" required>
            <input type="hidden" name="TransactionId" value="<?php echo $cxrkalwaremark; ?>">
            <button type="submit" name="subupireq">Pay ₹<?php echo number_format($amount, 2); ?></button>
        </form>
    </div>
<?php endif; ?>

<div class="button-section flex justify-center gap-1 flex-wrap">
<?php if ($showPaytmButton || $showGpayButton): ?>
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
        <?php if ($show_phonepe): ?>
        <button id="showGuideButton" class="flex-1 flex items-center justify-center bg-gradient-to-r from-purple-800 to-purple-700 text-white px-4 py-3 rounded-lg font-medium text-m shadow-md hover:shadow-xl hover:from-purple-800 hover:to-purple-700 transition-all duration-300 transform hover:scale-105"><strong>PhonePe</strong></button>
        <?php endif; ?>
    <?php endif; ?>
</div>

                                <?php if ($showDownloadQr): ?>
                                <button id="downloadQr" class="download-button bg-gradient-to-r from-gray-600 to-gray-500 text-white px-4 py-2 rounded-lg flex items-center gap-2 shadow-md hover:shadow-lg hover:scale-105 transition-all duration-300">
                                    <img src="https://img.icons8.com/ios-filled/50/ffffff/download--v1.png" alt="Download Icon" class="w-6 h-6"> Download QR
                                </button>
                                <?php endif; ?>
                            <?php endif; ?>
                            <?php if ($showIntentButton && isset($upi_id)): ?>
                                <button onclick="window.location.href='<?php echo $upi_base; ?>'" class="download-button bg-gradient-to-r from-green-600 to-green-500 text-white px-4 py-2 rounded-lg flex items-center gap-2 shadow-md hover:shadow-lg hover:scale-105 transition-all duration-300">
                                    <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn=CCRSouM4icV33KEDtJakZiySZN3HH2LPfv3-BA&s" alt="Google Pay" class="w-6 h-6"> Pay with UPI
                                </button>
                            <?php endif; ?>
                        </div>

                        <!-- Timer & Footer Section -->
<div class="timeout-section">
    <p>⌛ Waiting For Payment: <span id="timeout">5:00:00</span></p>
    <div class="relative w-full bg-gray-300 rounded-full h-2.5 overflow-hidden mobile-bar shadow-inner">
        <div id="progressBar" class="absolute left-0 h-full bg-gradient-to-r from-yellow-400 to-yellow-600 animate-progress w-1/4"></div>
    </div>
    <p class="text-xs text-gray-600 mt-1" id="progressText">🔄 Checking Live Payment Status...</p>
<?php if ($showHelp || isset($order_id)): ?>
<div class="flex justify-center items-center gap-3 mt-2 text-center">
    <?php if ($showHelp): ?>
    <a class="bg-transparent hover:bg-blue-500 text-blue-700 font-semibold hover:text-white border border-blue-500 hover:border-transparent rounded-lg px-1 py-1 flex items-center justify-center gap-2 transition-all duration-300 shadow-sm hover:shadow-md"
       target="_blank" href="<?php echo $whatsappUrl;?>">
       <i class="fa-brands fa-whatsapp"></i>
        Need help? Click me
    </a>
    <?php endif; ?>

    <button id="cancel-button"
            class="cncl-btn cancel-trigger bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded-md shadow-md transition-all duration-300 flex items-center gap-1"
            data-order-id="<?php echo $order_id; ?>">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
        Cancel
    </button>
</div>
<?php endif; ?>

    <div id="paymentStatus"></div>
</div>
                    </main>
                </div>


<?php if ($displayHeaderFooter): ?>
<footer class="<?= $defaultClass ?> p-3 text-center text-white rounded-b-2xl" style="<?= $gradientStyle ?>">
    <?php if (!$removeBranding): ?>
        <div class="powered-by text-sm">
            Powered by 
            <span class="text-orange-500 font-semibold">
                UpiGateway<sup class="text-xs">™</sup>
            </span>
        </div>
    <?php endif; ?>
</footer>
<?php endif; ?>
        </div>
    </div>
    </div>
    
    <script>
    // Initialize particles.js after page load
window.addEventListener('load', async () => {
    try {
        await loadScript('https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js');
        particlesJS("particles-js", {
            "particles": {
                "number": {"value": 100, "density": {"enable": true, "value_area": 800}},
                "color": {"value": ["#ffffff", "#fd8a8a", "#a8e6cf", "#fdffab"]},
                "shape": {"type": ["circle", "triangle", "star"]},
                "opacity": {"value": 0.6, "random": true, "anim": {"enable": true, "speed": 1, "opacity_min": 0.1, "sync": false}},
                "size": {"value": 4, "random": true, "anim": {"enable": true, "speed": 2, "size_min": 0.1, "sync": false}},
                "line_linked": {"enable": true, "distance": 150, "color": "#ffffff", "opacity": 0.3, "width": 1},
                "move": {"enable": true, "speed": 3, "direction": "none", "random": true, "straight": false, "out_mode": "out", "bounce": false, "attract": {"enable": true, "rotateX": 600, "rotateY": 1200}}
            },
            "interactivity": {
                "detect_on": "canvas",
                "events": {
                    "onhover": {"enable": true, "mode": "bubble"},
                    "onclick": {"enable": true, "mode": "push"},
                    "resize": true
                },
                "modes": {
                    "bubble": {"distance": 200, "size": 6, "duration": 0.2, "opacity": 0.8, "speed": 3},
                    "push": {"particles_nb": 4},
                    "remove": {"particles_nb": 2}
                }
            },
            "retina_detect": true
        });
    
        
        // Add keyframes for fadeIn animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes fadeIn {
                from { opacity: 0; transform: translateY(10px); }
                to { opacity: 1; transform: translateY(0); }
            }
        `;
        document.head.appendChild(style);
        
    } catch (e) {
        console.error('Particles.js failed to load:', e);
    }
});
    </script>
    </body>