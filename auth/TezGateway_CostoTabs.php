<?php include "header.php"; 
    $expiry = $userdata['expiry']; // डेटाबेस से एक्सपायरी डेट लें
    $expiry_timestamp = strtotime($expiry); // Expiry date को timestamp में बदलें
    $today = strtotime(date('Y-m-d')); // आज की तारीख का timestamp

    // **Check limits**
    if ($expiry_timestamp < $today) { // यदि expiry date आज से पहले की है तो प्लान एक्सपायर है
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Plan Expired!',
                text: 'Your plan expired on: " . date('d M, Y', $expiry_timestamp) . ". Please renew your plan to Access.',
                confirmButtonText: 'OK'
            }).then(() => { window.location.href='subscription'; });
        </script>";
        exit;
    }

?>

    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --accent-color-success: #28a745;
            --accent-color-danger: #dc3545;
            --text-color: #1f1d1d;
            --bg-gradient-start: #667eea;
            --bg-gradient-end: #764ba2;
            --card-bg: rgba(255, 255, 255, 0.95);
            --code-bg: gray; /* Darker background for code blocks */
            --code-text: #2b1c1c; 
            --border-radius-lg: 20px;
            --border-radius-md: 10px;
            --border-radius-sm: 5px;
            --box-shadow-light: 0 5px 15px rgba(0, 0, 0, 0.1);
            --box-shadow-medium: 0 10px 30px rgba(0, 0, 0, 0.1);
            --transition-speed: 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
  
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            /*color: var(--text-color);*/
            background: linear-gradient(135deg, var(--bg-gradient-start) 0%, var(--bg-gradient-end) 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        li {
            color: black;
        }
        p {
            color: black;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            flex-grow: 1;
        }

        .header1 {
            text-align: center;
            padding: 40px 0;
            color: white;
            margin-bottom: 30px;
        }

        .header1 h1 {
            font-size: 3.5rem;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
            animation: fadeInDown 1s ease-out;
        }

        .header1 p {
            font-size: 1.3rem;
            opacity: 0.9;
            animation: fadeInUp 1s ease-out 0.3s both;
        }

        .platform-selector {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-bottom: 40px;
            flex-wrap: wrap;
        }

        .platform-btn {
            padding: 12px 24px;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 25px;
            cursor: pointer;
            transition: all var(--transition-speed);
            font-weight: 600;
            backdrop-filter: blur(10px);
        }

        .platform-btn:hover, .platform-btn.active {
            background: var(--card-bg);
            color: var(--text-color);
            transform: translateY(-2px);
            box-shadow: var(--box-shadow-light);
        }

        .content-card {
            background: var(--card-bg);
            border-radius: var(--border-radius-lg);
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: var(--box-shadow-medium);
            backdrop-filter: blur(10px);
            transform: translateY(20px);
            opacity: 0;
            animation: slideUp 0.6s ease-out forwards;
        }

        .platform-content {
            display: none;
        }

        .platform-content.active {
            display: block;
            animation: fadeIn 0.5s ease-out;
        }
                .overview-section {
            background: rgba(255,255,255,0.95);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            backdrop-filter: blur(10px);
        }

        .flow-diagram {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 30px 0;
            flex-wrap: wrap;
            gap: 20px;
        }

        .flow-step {
            flex: 1;
            min-width: 200px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 20px;
            border-radius: 15px;
            text-align: center;
            position: relative;
        }

        .flow-step::after {
            content: '→';
            position: absolute;
            right: -15px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 2rem;
            color: #667eea;
        }

        .flow-step:last-child::after {
            display: none;
        }

        .step {
            margin-bottom: 30px;
            padding: 20px;
            border-left: 4px solid var(--primary-color);
            background: linear-gradient(90deg, rgba(102, 126, 234, 0.1) 0%, transparent 100%);
            border-radius: 0 var(--border-radius-md) var(--border-radius-md) 0;
            position: relative;
            overflow: hidden;
        }

        .step::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            transform: translateX(-100%);
            transition: transform 0.6s;
        }

        .step:hover::before {
            transform: translateX(100%);
        }

        .step-number {
            display: inline-block;
            width: 40px;
            height: 40px;
            background: var(--primary-color);
            color: white;
            border-radius: 50%;
            text-align: center;
            line-height: 40px;
            font-weight: bold;
            margin-right: 15px;
            animation: pulse 2s infinite;
        }

        .step h3 {
            color: var(--text-color);
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }

        .code-block {
            background: var(--code-bg);
            color: var(--code-text);
            padding: 20px;
            border-radius: var(--border-radius-md);
            margin: 15px 0;
            overflow-x: auto;
            position: relative;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
            line-height: 1.5;
        }
        
        .code-block pre {
            margin: 0;
            white-space: pre-wrap; /* Ensures long lines wrap */
            word-break: break-all; /* Breaks words if necessary */
        }

        .code-block::before {
            content: attr(data-lang);
            position: absolute;
            top: 10px;
            right: 10px;
            background: var(--primary-color);
            color: white;
            padding: 2px 8px;
            border-radius: var(--border-radius-sm);
            font-size: 0.8rem;
            text-transform: uppercase;
        }

        .copy-btn {
            position: absolute;
            top: 10px;
            right: 90px;
            background: var(--accent-color-success);
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: var(--border-radius-sm);
            cursor: pointer;
            font-size: 0.8rem;
            opacity: 0;
            transition: opacity var(--transition-speed);
        }

        .code-block:hover .copy-btn {
            opacity: 1;
        }

        .payment-buttons {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin: 10px 0;
            flex-wrap: wrap;
        }

        .payment-btn {
            padding: 12px 24px;
            border: none;
            border-radius: 10px;
            color: white;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            min-width: 180px;
        }

        .qr-btn { background: #1a1a1a; }
        .gpay-btn { background: #1a73e8; }
        .paytm-btn { background: #00BAF2; }

        .payment-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .demo-section {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            padding: 30px;
            border-radius: 20px;
            margin: 30px 0;
            text-align: center;
            color: white;
        }

        .phone-mockup {
            width: 280px;
            height: 550px;
            background: #333;
            border-radius: 25px;
            margin: 20px auto;
            padding: 20px;
            position: relative;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        
        .phone-screen {
            width: 100%;
            height: 100%;
            background: white;
            border-radius: 15px;
            overflow: hidden;
            position: relative;
        }
        
        .app-bar {
            height: 60px;
            background: #667eea;
            display: flex;
            align-items: center;
            padding: 0 15px;
            color: white;
            font-weight: 600;
        }

        .app-content {
            padding: 30px 20px;
            text-align: center;
        }

        .amount-input {
            width: 200px;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            margin-bottom: 30px;
        }

        .custom-tab {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: white;
            transform: translateX(100%);
            transition: transform 0.5s ease;
            border-radius: 15px;
            overflow: hidden;
        }
        
        .custom-tab.active {
            transform: translateX(0);
        }
        
        .tab-toolbar {
            height: 50px;
            background: #667eea;
            display: flex;
            align-items: center;
            padding: 0 15px;
            color: white;
            font-size: 0.9rem;
        }

        .tab-content {
            padding: 20px;
            text-align: center;
        }

        .qr-placeholder {
            width: 200px;
            height: 200px;
            background: #f0f0f0;
            border: 2px dashed #ccc;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 20px auto;
            font-size: 14px;
            color: #666;
        }

        .alert {
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
        }

        .alert-info {
            background: #e1f5fe;
            border-left: 4px solid #0288d1;
            color: #01579b;
        }

        .alert-warning {
            background: #fff3e0;
            border-left: 4px solid #f57c00;
            color: #e65100;
        }

        .alert-success {
            background: #e8f5e8;
            border-left: 4px solid #4caf50;
            color: #2e7d32;
        }

        .tab-toolbar span:first-child {
            cursor: pointer;
            margin-right: 15px;
            font-size: 1.2rem;
            font-weight: bold;
        }

        .tab-content img {
            width: 100%;
            height: auto;
            display: block;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }

        .feature-card {
            background: white;
            padding: 25px;
            border-radius: var(--border-radius-md);
            box-shadow: var(--box-shadow-light);
            transition: transform var(--transition-speed);
        }

        .feature-card:hover {
            transform: translateY(-5px);
        }

        .feature-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            color: white;
            margin-bottom: 15px;
        }

        .pros-cons {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin: 20px 0;
        }

        .pros, .cons {
            padding: 20px;
            border-radius: var(--border-radius-md);
        }

        .pros {
            background: linear-gradient(135deg, #d4edda, #c3e6cb);
            border-left: 4px solid var(--accent-color-success);
        }

        .cons {
            background: linear-gradient(135deg, #f8d7da, #f1c0c4);
            border-left: 4px solid var(--accent-color-danger);
        }

        .pros ul, .cons ul {
            list-style: none;
            padding-left: 0;
        }

        .pros ul li::before {
            content: '✔️';
            margin-right: 10px;
            color: var(--accent-color-success);
        }

        .cons ul li::before {
            content: '❌';
            margin-right: 10px;
            color: var(--accent-color-danger);
        }

        .tooltip {
            position: relative;
            cursor: help;
            border-bottom: 1px dotted var(--primary-color);
        }

        .tooltip::after {
            content: attr(data-tooltip);
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            background: #333;
            color: white;
            padding: 8px 12px;
            border-radius: var(--border-radius-sm);
            white-space: nowrap;
            opacity: 0;
            pointer-events: none;
            transition: opacity var(--transition-speed);
            font-size: 0.8rem;
            z-index: 1000;
        }

        .tooltip:hover::after {
            opacity: 1;
        }

        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-50px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(50px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes slideUp {
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); } /* Slightly smaller pulse for subtlety */
        }

        @media (max-width: 768px) {
            .header1 h1 {
                font-size: 2.5rem;
            }
            .header1 p {
                font-size: 1rem;
            }
            .pros-cons {
                grid-template-columns: 1fr;
            }
            .phone-mockup {
                width: 250px;
                height: 500px;
                padding: 20px;
            }
            .phone-screen {
                border-radius: 15px;
            }
            .custom-tab {
                border-radius: 15px;
            }
            .platform-selector {
                justify-content: center;
                gap: 10px;
            }
            .platform-btn {
                font-size: 0.9rem;
                padding: 10px 18px;
            }
            .code-block {
                font-size: 0.8rem;
                padding: 15px;
            }
            .copy-btn {
                right: 10px;
                top: 5px;
            }
            .code-block::before {
                top: 5px;
                right: 50px; /* Adjust position to not overlap copy button */
                font-size: 0.7rem;
            }
        }

        @media (max-width: 480px) {
            .header1 h1 {
                font-size: 2rem;
            }
            .phone-mockup {
                width: 90%;
                height: 450px;
            }
            .phone-buttons button {
                width: 95%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header1">
            <h1>🚀 UpiGateway Mobile Integration Steps</h1>
            <p>A comprehensive guide to integrating UpiGateway into your mobile applications.</p>
        </div>
                <!-- Overview Section -->
        <div class="overview-section">
            <h2>📋 Integration Overview</h2>
            <p>UpiGateway provides a seamless UPI payment integration for mobile applications. Follow these steps to implement secure payment processing in your app.</p>
            
            <div class="flow-diagram">
                <div class="flow-step">
                    <h4>1. Create Order</h4>
                    <p>Send payment request to API</p>
                </div>
                <div class="flow-step">
                    <h4>2. Get Response</h4>
                    <p>Receive payment URLs & QR code</p>
                </div>
                <div class="flow-step">
                    <h4>3. Show UI</h4>
                    <p>Display payment options to user</p>
                </div>
                <div class="flow-step">
                    <h4>4. Check Status</h4>
                    <p>Monitor payment completion</p>
                </div>
                </div>
                </div>
                
         <!-- UI Implementation -->
        <div class="demo-section">
            <h2>🎯 User Interface Implementation</h2>
            <p>Create three attractive payment buttons in your checkout screen:</p>

            <div class="phone-mockup" id="phoneMockup">
                <div class="phone-screen">
                    <div class="app-bar">
                        <span>💰 My Payment App</span>
                    </div>
                    <div class="app-content">
                        <h3 style="margin-bottom: 20px;">Add Balance</h3>
                        <input type="number" class="amount-input" placeholder="Enter Amount" value="₹100">
                        
                        <div class="payment-buttons">
                            <button class="payment-btn qr-btn" onclick="showDemo()">📱 Show QR</button>
                            <button class="payment-btn gpay-btn">💰 Google Pay</button>
                            <button class="payment-btn paytm-btn">💳 Paytm</button>
                        </div>
                    </div>
                    
                    <div class="custom-tab" id="customTab">
                        <div class="tab-toolbar">
                            <span onclick="hideDemo()" style="cursor: pointer; margin-right: 15px;">←</span>
                            <span>🌐 chickenpox.in</span>
                        </div>
                        <img src="assets/img/checkout.jpg"
                    </div>
                </div>
            </div>
        </div>
        <div class="content-card">
            <h2>Payment Order Flow</h2>
            <div class="step">
                <h3><span class="step-number">1</span>Create Order of Payment & Get Response</h3>
                <p>Upon successful creation of a payment order, you will receive the following JSON response:</p>
                <div class="code-block" data-lang="JSON">
                    <button class="copy-btn" onclick="copyCode(this)">Copy</button>
                    <pre>{
    "status": true,
    "message": "Order Created Successfully",
    "result": {
        "method": "Bharatpe",
        "orderId": "123456789125994465655859",
        "payment_url": "https://chickenpox.in/payment4/instant-pay/98a186be1ba109aa4b19ef7f1d499d3251e319ef43b8b94eb9fd17939c130a21",
        "data": {
            "bhim_link": "upi://pay?pa=...",
            "phonepe_link": "phonepe://pay?pa=...",
            "paytm_link": "paytmmp://pay?pa=...",
            "gpay_link": "tez://upi/pay?pa=...",
            "amazonpay_link": "amazonpay://upi/pay?pa=...",
            "cred_link": "cred://upi/pay?pa=...",
            "qr_image": "data:image/png;base64,(imagedata_base64)"
        }
    }
}</pre>
                </div>
            </div>

            <div class="step">
                <h3><span class="step-number">2</span>Implement Checkout Buttons</h3>
                <p>On your checkout page, implement three distinct buttons for user payment options:</p>
                <div class="code-block" data-lang="UI Suggestion">
                    <button class="copy-btn" onclick="copyCode(this)">Copy</button>
                    <pre>1. Show QR
2. GPay
3. Paytm</pre>
                </div>
                <ul>
                    <li>For the "Show QR" button, pass the `payment_url` received in the response to the Custom Tabs for display.</li>
                    <li>For the "GPay" button, share the `qr_image` (Base64 encoded) to the GPay application.</li>
                    <li>For the "PayTm" button, use the `paytm_link` received in the response to launch the Paytm application.</li>
                </ul>
            </div>

            <div class="step">
                <h3><span class="step-number">3</span>Initiate Payment Status Check</h3>
                <p>After the user clicks on any payment button, start a payment status check after 15 seconds. Continue monitoring until the user returns to your application after completing the payment.</p>
            </div>
        </div>
        
 
        <h1 class="payment-btn">📋 Costom Tabs Guide</h1>
        <div class="platform-selector">
            <button class="platform-btn active" data-platform="android">📱 Android Native</button>
            <button class="platform-btn" data-platform="flutter">🦋 Flutter</button>
            <button class="platform-btn" data-platform="react-native">⚛️ React Native</button>
            <button class="platform-btn" data-platform="xamarin">🔷 Xamarin</button>
            <button class="platform-btn" data-platform="cordova">📱 Cordova</button>
        </div>
        
        <div class="platform-content active" id="android">
            <div class="content-card">
                <h2>📱 Android Native Implementation</h2>
                <p>Integrate UpiGateway seamlessly into your native Android applications using Custom Tabs.</p>
                
                <div class="step">
                    <h3><span class="step-number">1</span>Add Dependencies</h3>
                    <p>First, add the Custom Tabs library to your app's <span class="tooltip" data-tooltip="Application-level Gradle file: app/build.gradle">build.gradle</span> file:</p>
                    <div class="code-block" data-lang="Gradle">
                        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
<pre>dependencies {
    implementation 'androidx.browser:browser:1.7.0'
}</pre>
                    </div>
                </div>
                
                <div class="step">
                    <h3><span class="step-number">2</span>Check Custom Tabs Support</h3>
                    <p>It's crucial to verify if Custom Tabs are supported on the user's device before attempting to launch them:</p>
                    <div class="code-block" data-lang="Kotlin">
                        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
<pre>private fun isCustomTabsSupported(): Boolean {
    val packageManager = packageManager
    val intent = Intent(Intent.ACTION_VIEW, Uri.parse("http://www.example.com"))
    val resolveInfos = packageManager.queryIntentActivities(intent, PackageManager.MATCH_ALL)
    
    for (resolveInfo in resolveInfos) {
        val serviceIntent = Intent()
        serviceIntent.action = "android.support.customtabs.action.CustomTabsService"
        serviceIntent.setPackage(resolveInfo.activityInfo.packageName)
        
        if (packageManager.resolveService(serviceIntent, 0) != null) {
            return true
        }
    }
    return false
}</pre>
                    </div>
                </div>
                
                <div class="step">
                    <h3><span class="step-number">3</span>Build and Launch Custom Tab</h3>
                    <p>Configure the appearance and behavior of your Custom Tab, then launch it with the payment URL:</p>
                    <div class="code-block" data-lang="Kotlin">
                        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
<pre>import androidx.browser.customtabs.CustomTabsIntent
import androidx.core.content.ContextCompat
import android.net.Uri
import android.content.Intent
import android.content.pm.PackageManager

private fun openCustomTab(url: String) {
    val builder = CustomTabsIntent.Builder()
    
    // 🎨 Customize appearance
    builder.setToolbarColor(ContextCompat.getColor(this, R.color.primary_color))
    builder.setSecondaryToolbarColor(ContextCompat.getColor(this, R.color.secondary_color))
    
    // ✨ Add animations for a smoother user experience
    builder.setStartAnimations(this, R.anim.slide_in_right, R.anim.slide_out_left)
    builder.setExitAnimations(this, R.anim.slide_in_left, R.anim.slide_out_right)
    
    // 📋 Add a custom menu item (e.g., Share option)
    val menuItemPendingIntent = createSharePendingIntent() // You'll need to implement this
    builder.addMenuItem("Share", menuItemPendingIntent)
    
    // 🚀 Build and launch the Custom Tab
    val customTabsIntent = builder.build()
    customTabsIntent.launchUrl(this, Uri.parse(url))
}</pre>
                    </div>
                </div>
                
                <div class="pros-cons">
                    <div class="pros">
                        <h4>✅ Pros</h4>
                        <ul>
                            <li>Native performance and responsiveness.</li>
                            <li>Full customization control over the browser UI.</li>
                            <li>Seamless integration with your app's theme.</li>
                            <li>Enhanced security features from the browser.</li>
                        </ul>
                    </div>
                    <div class="cons">
                        <h4>❌ Cons</h4>
                        <ul>
                            <li>Android-only solution (requires separate iOS implementation).</li>
                            <li>Requires Chrome or another Custom Tabs-supporting browser installed on the device.</li>
                            <li>More setup code compared to cross-platform solutions.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="platform-content" id="flutter">
            <div class="content-card">
                <h2>🦋 Flutter Implementation</h2>
                <p>Integrate UpiGateway into your Flutter applications for a consistent cross-platform payment experience.</p>
                
                <div class="step">
                    <h3><span class="step-number">1</span>Add Package</h3>
                    <p>Add the `flutter_custom_tabs` package to your <span class="tooltip" data-tooltip="Flutter project's dependency file: pubspec.yaml">pubspec.yaml</span> file:</p>
                    <div class="code-block" data-lang="YAML">
                        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
<pre>dependencies:
  flutter_custom_tabs: ^1.0.4</pre>
                    </div>
                </div>
                
                <div class="step">
                    <h3><span class="step-number">2</span>Import and Use</h3>
                    <p>Utilize the simple API to launch Custom Tabs with various customization options:</p>
                    <div class="code-block" data-lang="Dart">
                        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
<pre>import 'package:flutter_custom_tabs/flutter_custom_tabs.dart';
import 'package:flutter/material.dart';

void launchURL(BuildContext context, String url) async {
  try {
    await launch(
      url,
      customTabsOption: CustomTabsOption(
        toolbarColor: Theme.of(context).primaryColor,
        enableDefaultShare: true,
        enableUrlBarHiding: true,
        showPageTitle: true,
        animation: CustomTabsSystemAnimation.slideIn(),
        // Specify preferred browsers if needed
        extraCustomTabs: <String>[
          'org.mozilla.firefox',
          'com.microsoft.emmx',
        ],
      ),
      safariVCOption: SafariViewControllerOption(
        preferredBarTintColor: Theme.of(context).primaryColor,
        preferredControlTintColor: Colors.white,
        barCollapsingEnabled: true,
        entersReaderIfAvailable: false,
      ),
    );
  } catch (e) {
    // 🔄 Fallback to launching in the default browser if Custom Tabs fail
    debugPrint('Could not launch $url: $e');
    // Consider using url_launcher for a robust fallback:
    // if (await canLaunch(url)) {
    //   await launch(url);
    // }
  }
}</pre>
                    </div>
                </div>
                
                <div class="step">
                    <h3><span class="step-number">3</span>Widget Integration</h3>
                    <p>Easily integrate the URL launching functionality into your Flutter widgets:</p>
                    <div class="code-block" data-lang="Dart">
                        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
<pre>import 'package:flutter/material.dart';

// Assuming launchURL is defined in the same scope or imported
// Example usage within a StatelessWidget or StatefulWidget
class MyPaymentButton extends StatelessWidget {
  final String paymentUrl = 'https://chickenpox.in/payment_page'; // Replace with actual URL

  @override
  Widget build(BuildContext context) {
    return ElevatedButton(
      onPressed: () => launchURL(context, paymentUrl),
      child: Text('Open UpiGateway'),
      style: ElevatedButton.styleFrom(
        primary: Theme.of(context).primaryColor,
        onPrimary: Colors.white,
        padding: EdgeInsets.symmetric(horizontal: 30, vertical: 15),
        textStyle: TextStyle(fontSize: 18),
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(10),
        ),
      ),
    );
  }
}</pre>
                    </div>
                </div>
                
                <div class="pros-cons">
                    <div class="pros">
                        <h4>✅ Pros</h4>
                        <ul>
                            <li>Cross-platform support for both iOS (Safari View Controller) and Android (Custom Tabs).</li>
                            <li>Simple and intuitive API for quick integration.</li>
                            <li>Excellent integration within the Flutter ecosystem.</li>
                            <li>Automatic fallback to Safari View Controller on iOS.</li>
                        </ul>
                    </div>
                    <div class="cons">
                        <h4>❌ Cons</h4>
                        <ul>
                            <li>Relies on a third-party package; maintainer dependency.</li>
                            <li>Customization options might be slightly less extensive than native implementations.</li>
                            <li>Potential for package version compatibility issues with Flutter updates.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="platform-content" id="react-native">
            <div class="content-card">
                <h2>⚛️ React Native Implementation</h2>
                <p>Integrate UpiGateway into your React Native applications for a consistent cross-platform payment experience.</p>
                
                <div class="step">
                    <h3><span class="step-number">1</span>Install Package</h3>
                    <p>Install the `react-native-custom-tabs` package and link native modules:</p>
                    <div class="code-block" data-lang="Bash">
                        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
<pre>npm install react-native-custom-tabs
# For iOS (React Native 0.60+ and above):
cd ios && pod install</pre>
                    </div>
                </div>
                
                <div class="step">
                    <h3><span class="step-number">2</span>Implementation</h3>
                    <p>Create a reusable JavaScript function to open Custom Tabs, including a fallback mechanism:</p>
                    <div class="code-block" data-lang="JavaScript">
                        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
<pre>import { CustomTabs } from 'react-native-custom-tabs';
import { Linking, Alert } from 'react-native';

const openUpiGatewayTab = async (url) => {
  try {
    await CustomTabs.openURL(url, {
      toolbarColor: '#667eea', // Use your primary color
      enableUrlBarHiding: true,
      showPageTitle: true,
      enableDefaultShare: true,
      animations: {
        startEnter: 'slide_in_right',
        startExit: 'slide_out_left',
        endEnter: 'slide_in_left',
        endExit: 'slide_out_right',
      },
      headers: {
        'X-App-Name': 'UpiGatewayApp', // Optional: Custom header for tracking
      },
      forceCloseOnRedirection: false, // Keep the tab open on redirects
    });
  } catch (error) {
    // 🔄 Fallback to the system browser if Custom Tabs are not available
    console.warn('Custom Tabs not available, attempting to open with system browser:', error);
    try {
      await Linking.openURL(url);
    } catch (linkingError) {
      Alert.alert('Error', 'Failed to open link in browser.');
      console.error('Failed to open link with Linking:', linkingError);
    }
  }
};</pre>
                    </div>
                </div>
                
                <div class="step">
                    <h3><span class="step-number">3</span>Component Usage</h3>
                    <p>Integrate the `openUpiGatewayTab` function with your React Native UI components:</p>
                    <div class="code-block" data-lang="JavaScript">
                        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
<pre>import React from 'react';
import { TouchableOpacity, Text, StyleSheet, View } from 'react-native';

// Assuming openUpiGatewayTab is imported or defined in the same file
const PaymentButton = ({ url, title }) => (
  <TouchableOpacity 
    style={styles.button}
    onPress={() => openUpiGatewayTab(url)}
  >
    <Text style={styles.buttonText}>{title}</Text>
  </TouchableOpacity>
);

const styles = StyleSheet.create({
  button: {
    backgroundColor: '#667eea', // Your app's primary color
    paddingVertical: 15,
    paddingHorizontal: 25,
    borderRadius: 8,
    alignItems: 'center',
    marginVertical: 10,
    shadowColor: "#000",
    shadowOffset: {
      width: 0,
      height: 2,
    },
    shadowOpacity: 0.25,
    shadowRadius: 3.84,
    elevation: 5,
  },
  buttonText: {
    color: 'white',
    fontWeight: 'bold',
    fontSize: 16,
  },
});

export default () => (
  <View style={{ flex: 1, justifyContent: 'center', alignItems: 'center' }}>
    <PaymentButton url="https://chickenpox.in/demo-payment" title="Pay with UpiGateway" />
  </View>
);</pre>
                    </div>
                </div>
                
                <div class="pros-cons">
                    <div class="pros">
                        <h4>✅ Pros</h4>
                        <ul>
                            <li>Leverages JavaScript/TypeScript, familiar to web developers.</li>
                            <li>Good customization options for both Android and iOS.</li>
                            <li>Strong cross-platform support with a single codebase.</li>
                            <li>Large and active community for support and resources.</li>
                        </ul>
                    </div>
                    <div class="cons">
                        <h4>❌ Cons</h4>
                        <ul>
                            <li>Requires additional native dependencies and linking steps.</li>
                            <li>Platform-specific setup might still be necessary for some configurations.</li>
                            <li>Potential for version compatibility issues between React Native and the plugin.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="platform-content" id="xamarin">
            <div class="content-card">
                <h2>🔷 Xamarin Implementation</h2>
                <p>Integrate UpiGateway into your Xamarin.Android applications using Custom Tabs for an enhanced payment experience.</p>
                
                <div class="step">
                    <h3><span class="step-number">1</span>NuGet Package Installation</h3>
                    <p>Install the `Xamarin.AndroidX.Browser` NuGet package into your Android project:</p>
                    <div class="code-block" data-lang="XML">
                        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
<pre>&lt;PackageReference Include="Xamarin.AndroidX.Browser" Version="1.4.0" /&gt;</pre>
                    </div>
                </div>
                
                <div class="step">
                    <h3><span class="step-number">2</span>C# Implementation (Helper Class)</h3>
                    <p>Create a helper class to encapsulate the Custom Tabs logic, making it reusable across your application:</p>
                    <div class="code-block" data-lang="C#">
                        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
<pre>using AndroidX.Browser.CustomTabs;
using Android.Content;
using AndroidX.Core.Content;
using Android.App; // For Activity
using Android.Net; // For Uri
using Android.Content.PM; // For PackageInfoFlags

public static class CustomTabsHelper
{
    public static void OpenCustomTab(Activity activity, string url)
    {
        var builder = new CustomTabsIntent.Builder();
        
        // 🎨 Customize appearance to match your app's branding
        builder.SetToolbarColor(ContextCompat.GetColor(activity, Resource.Color.primary_color)); // Define in colors.xml
        builder.SetSecondaryToolbarColor(ContextCompat.GetColor(activity, Resource.Color.secondary_color));
        
        // ✨ Add entry and exit animations for a smooth transition
        builder.SetStartAnimations(activity, Resource.Animation.slide_in_right, Resource.Animation.slide_out_left);
        builder.SetExitAnimations(activity, Resource.Animation.slide_in_left, Resource.Animation.slide_out_right);
        
        // 🚀 Build the intent and launch the Custom Tab
        var customTabsIntent = builder.Build();
        customTabsIntent.LaunchUrl(activity, Uri.Parse(url));
    }
    
    public static bool IsCustomTabsSupported(Context context)
    {
        var packageManager = context.PackageManager;
        var intent = new Intent(Intent.ActionView, Uri.Parse("http://www.example.com"));
        var resolveInfos = packageManager.QueryIntentActivities(intent, PackageInfoFlags.MatchAll);
        
        foreach (var resolveInfo in resolveInfos)
        {
            var serviceIntent = new Intent();
            serviceIntent.SetAction("android.support.customtabs.action.CustomTabsService");
            serviceIntent.SetPackage(resolveInfo.ActivityInfo.PackageName);
            
            if (packageManager.ResolveService(serviceIntent, 0) != null)
                return true;
        }
        return false;
    }
}</pre>
                    </div>
                </div>
                
                <div class="step">
                    <h3><span class="step-number">3</span>Usage in Activity/Fragment</h3>
                    <p>Call the `OpenCustomTab` helper method from your Android activities or fragments, including a fallback:</p>
                    <div class="code-block" data-lang="C#">
                        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
<pre>// In your Android Activity (e.g., MainActivity.cs)
using Android.App;
using Android.OS;
using Android.Widget;
using Android.Content;

[Activity(Label = "MyPaymentApp", Theme = "@style/AppTheme", MainLauncher = true)]
public class MainActivity : AppCompatActivity
{
    protected override void OnCreate(Bundle savedInstanceState)
    {
        base.OnCreate(savedInstanceState);
        SetContentView(Resource.Layout.activity_main);

        Button payButton = FindViewById<Button>(Resource.Id.payButton);
        payButton.Click += (sender, e) => {
            string paymentUrl = "https://chickenpox.in/your-payment-link"; // Replace with actual URL
            OpenWebLink(paymentUrl);
        };
    }

    private void OpenWebLink(string url)
    {
        if (CustomTabsHelper.IsCustomTabsSupported(this))
        {
            CustomTabsHelper.OpenCustomTab(this, url);
        }
        else
        {
            // Fallback to default system browser
            var intent = new Intent(Intent.ActionView, Android.Net.Uri.Parse(url));
            StartActivity(intent);
            Toast.MakeText(this, "Opening link in default browser...", ToastLength.Short).Show();
        }
    }
}</pre>
                    </div>
                </div>
                
                <div class="pros-cons">
                    <div class="pros">
                        <h4>✅ Pros</h4>
                        <ul>
                            <li>Leverages the .NET/C# ecosystem for mobile development.</li>
                            <li>Benefits from strong typing and object-oriented programming.</li>
                            <li>Official support from Microsoft.</li>
                            <li>Ability to share business logic across iOS and Android (Xamarin.Forms).</li>
                        </ul>
                    </div>
                    <div class="cons">
                        <h4>❌ Cons</h4>
                        <ul>
                            <li>Dependency on the Xamarin platform and its specific tooling.</li>
                            <li>Can introduce additional complexity compared to native Android/iOS development.</li>
                            <li>Requires platform-specific implementations for UI and certain features.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="platform-content" id="cordova">
            <div class="content-card">
                <h2>📱 Cordova Implementation</h2>
                <p>Integrate UpiGateway into your Cordova applications using the `cordova-plugin-browsertab` plugin to open Custom Tabs.</p>
                
                <div class="step">
                    <h3><span class="step-number">1</span>Install Plugin</h3>
                    <p>Add the `cordova-plugin-browsertab` plugin to your Cordova project:</p>
                    <div class="code-block" data-lang="Bash">
                        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
<pre>cordova plugin add cordova-plugin-browsertab</pre>
                    </div>
                </div>
                
                <div class="step">
                    <h3><span class="step-number">2</span>JavaScript Implementation</h3>
                    <p>Use the plugin's JavaScript API to open Custom Tabs, including a robust fallback for unsupported environments:</p>
                    <div class="code-block" data-lang="JavaScript">
                        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
<pre>function openCustomTab(url) {
    // Check if Cordova and the browsertab plugin are available
    if (window.cordova && window.cordova.plugins && window.cordova.plugins.browsertab) {
        cordova.plugins.browsertab.isAvailable(function(result) {
            if (result) {
                // Custom Tabs are available, open the URL
                cordova.plugins.browsertab.openUrl(
                    url,
                    {
                        // Optional customization options
                        toolbarColor: '#667eea', // Your app's primary color
                        showTitle: true,
                        // You can add more options as supported by the plugin
                    },
                    function(success) {
                        console.log('Custom Tab opened successfully');
                    },
                    function(error) {
                        console.error('Error opening Custom Tab:', error);
                        // Fallback to InAppBrowser or default system browser on error
                        window.open(url, '_system');
                    }
                );
            } else {
                console.log('Custom Tabs not available. Falling back to system browser.');
                // Fallback to InAppBrowser or default system browser
                window.open(url, '_system');
            }
        });
    } else {
        console.warn('Cordova or browsertab plugin not found. Opening in a new browser tab.');
        // Fallback for non-Cordova environments (e.g., direct browser access)
        window.open(url, '_blank');
    }
}</pre>
                    </div>
                </div>
                
                <div class="step">
                    <h3><span class="step-number">3</span>HTML Integration</h3>
                    <p>Add a button or link in your HTML to trigger the `openCustomTab` function:</p>
                    <div class="code-block" data-lang="HTML">
                        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
                      <pre>&lt;button onclick="openCustomTab('https://chickenpox.in/demo-payment')"&gt;Open UpiGateway Payment&lt;/button&gt;

&lt;!-- Or for a link --&gt;
&lt;a href="#" onclick="openCustomTab('https://chickenpox.in/demo-payment'); return false;"&gt;Click to Pay&lt;/a&gt;</pre>
                    </div>
                </div>
                
                <div class="pros-cons">
                    <div class="pros">
                        <h4>✅ Pros</h4>
                        <ul>
                            <li>Enables web-based development using HTML, CSS, and JavaScript.</li>
                            <li>Provides cross-platform support with a single codebase.</li>
                            <li>Easy integration of features via Cordova plugins.</li>
                            <li>Leverages existing web development skills and resources.</li>
                        </ul>
                    </div>
                    <div class="cons">
                        <h4>❌ Cons</h4>
                        <ul>
                            <li>Limited customization options for native UI elements.</li>
                            <li>Can incur performance overhead compared to fully native apps.</li>
                            <li>Reliance on third-party plugins for native functionalities; plugin dependency risks.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Platform Selector Functionality
        document.querySelectorAll('.platform-btn').forEach(button => {
            button.addEventListener('click', () => {
                // Remove active class from all buttons and content
                document.querySelectorAll('.platform-btn').forEach(btn => btn.classList.remove('active'));
                document.querySelectorAll('.platform-content').forEach(content => content.classList.remove('active'));

                // Add active class to clicked button and corresponding content
                button.classList.add('active');
                const platform = button.getAttribute('data-platform');
                document.getElementById(platform).classList.add('active');
            });
        });

        // Copy Code Functionality
        function copyCode(button) {
            const code = button.nextElementSibling.textContent;
            navigator.clipboard.writeText(code).then(() => {
                button.textContent = 'Copied!';
                setTimeout(() => {
                    button.textContent = 'Copy';
                }, 2000);
            }).catch(err => {
                console.error('Failed to copy: ', err);
                button.textContent = 'Error';
                setTimeout(() => {
                    button.textContent = 'Copy';
                }, 2000);
            });
        }

        // Demo Functionality
        function showDemo() {
            const customTab = document.getElementById('customTab');
            customTab.classList.add('active');
        }

        function hideDemo() {
            const customTab = document.getElementById('customTab');
            customTab.classList.remove('active');
        }

        // Initialize the page
        window.addEventListener('DOMContentLoaded', () => {
            // Ensure Android is active by default on load
            document.getElementById('android').classList.add('active');
            document.querySelector('.platform-btn[data-platform="android"]').classList.add('active');
        });
    </script>
</body>
