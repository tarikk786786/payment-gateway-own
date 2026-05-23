<?php
// AndroidDOcs.php
// Version: 1.3 | Last Updated: May 23, 2025
// A comprehensive guide for integrating the UpiGateway payment page into an Android app using Chrome Custom Tabs across Flutter, Java, Kotlin, and Cordova

include 'header.php'; // Ensure header.php exists and has no syntax errors
?>

<head>
    <title>UpiGateway Integration Documentation (v1.3)</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 2rem;
        }
        .section {
            background: #ffffff;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
            transition: transform 0.2s;
        }
        .section:hover {
            transform: translateY(-5px);
        }
        .section h2 {
            color: #1a73e8;
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 1rem;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 0.5rem;
        }
        .section h3 {
            color: #2d3748;
            font-size: 1.25rem;
            font-weight: 600;
            margin-top: 1.5rem;
            margin-bottom: 0.75rem;
        }
        .code-block {
            background: #1a202c;
            color: #e2e8f0;
            padding: 1rem;
            border-radius: 8px;
            overflow-x: auto;
            font-family: 'Courier New', Courier, monospace;
            font-size: 0.9rem;
            line-height: 1.5;
        }
        .preview-img {
            max-width: 300px;
            height: auto;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            margin: 1rem auto;
            display: block;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        .highlight {
            background: #edf2ff;
            padding: 1rem;
            border-radius: 8px;
            margin-top: 1rem;
            border-left: 4px solid #1a73e8;
        }
        .toc {
            background: #edf2ff;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 2rem;
        }
        .toc ul {
            list-style: none;
            padding-left: 0;
        }
        .toc a {
            color: #1a73e8;
            text-decoration: none;
            font-weight: 500;
        }
        .toc a:hover {
            text-decoration: underline;
        }
        .footer {
            background: #1a202c;
            color: #e2e8f0;
            padding: 1.5rem;
            text-align: center;
            border-radius: 12px;
            margin-top: 2rem;
        }
        .version-changes {
            background: #fff3cd;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            border-left: 4px solid #f6c107;
        }
    </style>
</head>


        <!-- Header Section -->
        <header class="text-center mb-10">
            <h1 class="text-4xl font-bold text-blue-900">UpiGateway Payment Integration Guide</h1>
            <p class="text-lg text-gray-600 mt-3">Version 1.3 | Last Updated: May 23, 2025</p>
        </header>

        <!-- Table of Contents -->
        <div class="toc" role="navigation" aria-label="Table of Contents">
            <h2 class="text-xl font-semibold text-gray-800">Table of Contents</h2>
            <ul>
                <li><a href="#objective">Objective</a></li>
                <li><a href="#preview">Preview of the Payment Page</a></li>
                <li><a href="#requirements">Requirements</a></li>
                <li><a href="#integration">Step-by-Step Integration</a></li>
                <li><a href="#javascript-interaction">JavaScript Interaction</a></li>
                <li><a href="#security">Security Considerations</a></li>
                <li><a href="#testing">Testing and Debugging</a></li>
            </ul>
        </div>

        <!-- Objective Section -->
        <div class="section" id="objective">
            <h2>Objective</h2>
            <p>This guide provides a comprehensive walkthrough for integrating the UpiGateway UPI-based payment page into an Android application using Chrome Custom Tabs across Flutter, Java, Kotlin, and Cordova platforms. The goal is to ensure seamless functionality for all interactive elements, including QR code scanning, UPI app payments, payment status updates, file downloads, and redirects, with minimal configuration.</p>
            <div class="highlight">
                <p><strong>Target Page:</strong> The payment page is a UPI-based gateway by UpiGateway, accessible at a unique URL (e.g., <code>https://chickenpox.in/payment/instant-pay/...</code>), supporting payments via QR code, UPI apps, or Paytm.</p>
            </div>
        </div>

        <!-- Preview Section -->
        <div class="section" id="preview">
            <h2>Preview of the Payment Page</h2>
            <p>The UpiGateway payment page is a mobile-friendly interface designed for UPI payments. Below is a visual representation of the page to be integrated into your Android app via Chrome Custom Tabs.</p>
            <img src="/assets/img/page.png" alt="UpiGateway Payment Page Preview" class="preview-img">
            <p class="text-sm text-gray-600 text-center mt-2">Figure 1: UpiGateway Payment Page with QR Code, UPI Options, and Payment Status</p>
            <p><strong>Key Elements:</strong></p>
            <ul class="list-disc pl-5 text-gray-700">
                <li><strong>Header:</strong> "Paying To UpiGateway" with UPI logo.</li>
                <li><strong>QR Code:</strong> Scannable via UPI apps (Google Pay, PhonePe, Paytm, BHIM).</li>
                <li><strong>Payment Amount:</strong> Displays the amount to be paid (e.g., ₹1.00).</li>
                <li><strong>Buttons:</strong>
                    <ul class="list-circle pl-5">
                        <li>"Pay ₹1.00": Initiates payment process.</li>
                        <li>"Paytm": Redirects to Paytm app or website.</li>
                        <li>"Pay on UPI APP": Opens a UPI app with pre-filled details.</li>
                        <li>"Download QR": Downloads the QR code as an image.</li>
                        <li>"Submit UTR": Allows manual entry of a Unique Transaction Reference number.</li>
                    </ul>
                </li>
                <li><strong>Progress Bar:</strong> Countdown timer (e.g., "Waiting For Payment: 04:59") with live status updates via JavaScript.</li>
                <li><strong>Cancel Button:</strong> Redirects to a cancellation URL.</li>
                <li><strong>Footer:</strong> "Powered by UpiGateway™".</li>
            </ul>
            <div class="highlight">
                <p><strong>Note:</strong> Chrome Custom Tabs leverage Chrome's rendering engine, ensuring consistent JavaScript execution and automatic handling of UPI deep links and file downloads across all platforms.</p>
            </div>
        </div>

        <!-- Requirements Section -->
        <div class="section" id="requirements">
            <h2>Requirements</h2>
            <h3>1. Development Environment</h3>
            <ul class="list-disc pl-5 text-gray-700">
                <li>Android Studio (latest stable version recommended).</li>
                <li>Flutter (for Flutter apps), Java, Kotlin, or Cordova CLI (depending on platform).</li>
                <li>Android SDK with minimum SDK 21 (Android 5.0) and target SDK 35 (Android 15) for compatibility.</li>
                <li>Stable internet connection for loading the payment page.</li>
            </ul>

            <h3>2. Permissions</h3>
            <p>Add the following permission to <code>AndroidManifest.xml</code> (all platforms):</p>
            <pre class="code-block"><?php echo htmlspecialchars('<uses-permission android:name="android.permission.INTERNET" />'); ?></pre>
            <p><strong>Note:</strong> Storage permissions are not required, as file downloads are handled by Chrome.</p>

            <h3>3. Dependencies</h3>
            <p>Platform-specific dependencies:</p>
            <ul class="list-disc pl-5 text-gray-700">
                <li><strong>Flutter:</strong> <code>flutter_custom_tabs: ^2.0.0</code> (add to <code>pubspec.yaml</code>).</li>
                <li><strong>Java/Kotlin:</strong> <code>implementation 'androidx.browser:browser:1.8.0'</code> (add to <code>build.gradle</code>).</li>
                <li><strong>Cordova:</strong> <code>cordova-plugin-inappbrowser</code> (install via CLI).</li>
            </ul>
            <p>Add to <code>pubspec.yaml</code> (Flutter):</p>
            <pre class="code-block"><?php echo htmlspecialchars('
dependencies:
  flutter_custom_tabs: ^2.0.0
'); ?></pre>
            <p>Add to <code>build.gradle</code> (Java/Kotlin):</p>
            <pre class="code-block"><?php echo htmlspecialchars('
dependencies {
    implementation \'androidx.browser:browser:1.8.0\'
}
'); ?></pre>

            <h3>4. Assets</h3>
            <ul class="list-disc pl-5 text-gray-700">
                <li>The payment page URL (e.g., <code>https://chickenpox.in/payment/instant-pay/abd297add8520dab8c334597865f4267df0dce980e82c9ceb0b49950eeadad18</code>).</li>
                <li>Ensure the server uses HTTPS for secure loading in Chrome Custom Tabs.</li>
            </ul>
        </div>

        <!-- Step-by-Step Integration Section -->
<div class="section" id="integration">
    <button class="relative inline-flex items-center justify-center p-0.5 mb-4 me-2 overflow-hidden text-sm font-medium text-gray-900 rounded-lg group bg-gradient-to-br from-purple-500 to-pink-500 group-hover:from-purple-500 group-hover:to-pink-500 hover:text-black dark:text-white focus:ring-4 focus:outline-none focus:ring-purple-200 dark:focus:ring-purple-800">
        <span class="relative px-5 py-2.5 transition-all ease-in duration-75 bg-white dark:bg-gray-900 rounded-md group-hover:bg-transparent group-hover:dark:bg-transparent">
            UpiGateway Custom Tabs Integration Guide
        </span>
    </button>
    <div id="iframe-container" class="hidden mt-4">
        <iframe id="UpiGateway-iframe" class="w-full h-[850px] border-2 border-gray-200 rounded-lg shadow-lg" src="" title="UpiGateway Custom Tabs"></iframe>
    </div>
</div>

        <!-- JavaScript Interaction Section -->
        <div class="section" id="javascript-interaction">
            <h2>JavaScript Interaction</h2>
            <p>Chrome Custom Tabs use Chrome’s rendering engine, ensuring seamless execution of the payment page’s JavaScript (e.g., payment status polling, progress bar updates) across all platforms without additional configuration. Direct JavaScript injection is not supported due to security restrictions.</p>
            <div class="highlight">
                <p><strong>Note:</strong> To capture payment status (e.g., "SUCCESS", "FAILED"), configure the payment page to redirect to a custom URL (e.g., <code>https://yourapp.com/success?status=SUCCESS</code>) and handle it via deep links as shown in the platform-specific sections.</p>
            </div>
        </div>

        <!-- Security Considerations Section -->
        <div class="section" id="security">
            <h2>Security Considerations</h2>
            <p>Chrome Custom Tabs provide robust security features:</p>
            <ul class="list-disc pl-5 text-gray-700">
                <li><strong>HTTPS Enforcement:</strong> Custom Tabs only load HTTPS URLs, ensuring secure communication.</li>
                <li><strong>Session Isolation:</strong> Each Custom Tabs session is isolated, preventing cross-site attacks.</li>
                <li><strong>UPI Deep Link Handling:</strong> Chrome validates and securely redirects to UPI apps.</li>
                <li><strong>No JavaScript Injection:</strong> Eliminates risks associated with WebView JavaScript interfaces.</li>
            </ul>
            <p><strong>Best Practice:</strong> Verify the payment URL is from the trusted <code>chickenpox.in</code> domain before launching Chrome Custom Tabs.</p>
        </div>

        <!-- Testing and Debugging Section -->
        <div class="section" id="testing">
            <h2>Testing and Debugging</h2>
            <p>Test the app on an emulator and physical devices with Chrome installed. Verify all elements from the preview image:</p>
            <ul class="list-disc pl-5 text-gray-700">
                <li>QR code opens UPI apps via deep links.</li>
                <li>Buttons ("Paytm", "Pay on UPI APP", "Download QR", "Submit UTR") function correctly.</li>
                <li>Payment status updates dynamically via JavaScript.</li>
                <li>"Cancel" redirects to the correct URL.</li>
                <li>QR code downloads to the device’s Downloads folder via Chrome.</li>
            </ul>
            <p><strong>Test Scenarios:</strong></p>
            <ul class="list-disc pl-5 text-gray-700">
                <li><strong>Offline Mode:</strong> Verify error handling when internet is unavailable.</li>
                <li><strong>No Chrome Installed:</strong> Test fallback to default browser.</li>
                <li><strong>UPI App Absence:</strong> Ensure graceful error messages when no UPI app is installed.</li>
                <li><strong>Payment Timeout:</strong> Confirm the countdown timer behaves as expected.</li>
                <li><strong>Invalid UTR:</strong> Test error messages for incorrect UTR submissions.</li>
                <li><strong>Platform-Specific:</strong> Test deep link handling in Flutter (URL launcher fallback), Cordova (InAppBrowser), and native apps (Intent resolution).</li>
            </ul>
            <p>Use Android Studio’s Logcat (Java/Kotlin), Flutter DevTools (Flutter), or Chrome DevTools (<code>chrome://inspect</code>) for debugging. For Cordova, use browser developer tools via remote debugging.</p>
        </div>

        <!-- Footer Section -->
        <footer class="footer" role="contentinfo">
            <p>Powered by <span class="text-orange-500 font-semibold">UpiGateway<sup class="text-xs">™</sup></span></p>
            <p>© <?php echo date("Y"); ?> UpiGateway. All rights reserved.</p>
        </footer>
        <a href="/auth/apidetails" class="fixed top-1/2 right-4 transform -translate-y-1/2 px-6 py-3 text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition duration-300 shadow-lg">
            <span class="relative z-10">Back To Api Details</span>
            <span class="absolute -top-2 -right-3 px-2 py-1 text-xs font-bold text-white bg-red-500 rounded-full animate-ping">NEW</span>
            <span class="absolute -top-2 -right-3 px-2 py-1 text-xs font-bold text-white bg-red-500 rounded-full">NEW</span>
        </a>
        
<script>
    // Function to load the iframe content
    function loadUpiGatewayIframe() {
        const iframeContainer = document.getElementById('iframe-container');
        const iframe = document.getElementById('UpiGateway-iframe');
        const button = document.querySelector('#integration button');

        // URL of the UpiGateway_CostoTabs page (update with the actual URL)
        const UpiGatewayUrl = 'UpiGateway_CostoTabs'; // Replace with actual URL or local path

        button.addEventListener('click', () => {
            // Set the iframe source and show the container
            iframe.src = UpiGatewayUrl;
            iframeContainer.classList.remove('hidden');

            // Optional: Scroll to the iframe smoothly
            iframeContainer.scrollIntoView({ behavior: 'smooth' });
        });

        // Optional: Handle iframe loading errors
        iframe.addEventListener('error', () => {
            iframeContainer.innerHTML = '<p class="text-red-600 text-center">Failed to load UpiGateway Custom Tabs. Please try again later.</p>';
        });
    }

    // Initialize the iframe loading functionality
    window.addEventListener('DOMContentLoaded', loadUpiGatewayIframe);
</script>

<?php
// Ensure no trailing code or whitespace after closing tag
?>