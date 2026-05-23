<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Page</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Roboto', sans-serif;
        }

        body {
            background-color: #f3f4f7;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .container {
            width: 95%;
            max-width: 400px;
            margin: 15px auto;
            background-color: #ffffff;
            border-radius: 14px;
            box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.1);
            padding-bottom: 20px;
        }

        .header {
            background-color: #2C3E88;
            padding: 15px;
            color: #fff;
            display: flex;
            align-items: center;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
        }

        .header img {
            width: 50px;
            border-radius: 20%;
            margin-right: 10px;
        }

        .header .company-info {
            display: flex;
            flex-direction: column;
        }

        .company-info h1 {
            font-size: 16px;
            font-weight: 700;
        }

        .trusted-business {
            display: flex;
            
            align-items: center;
            margin-top: 5px;
        }

        .trusted-business img {
            width: 20px;
            margin-left: 5px;
        }

        .price-summary {
            background-color: #f2f3f7;
            padding: 15px;
            font-size: 14px;
            text-align: center;
            font-weight: bold;
        }

        /* QR Code Section */
        .qr-section {
            background-color: #f7f9fc;
            padding: 15px;
            border-radius: 10px;
            display: flex;
            align-items: center;
           justify-content: space-around;
            margin-bottom: 15px;
        }

        .qr-section img {
            width: 125px;
            height: 125px;
            border-radius: 10px;
        }

        .qr-section .center {
            text-align: center;
        }

        .qr-section .center span {
            display: block;
            font-size: 14px;
            font-weight: 400;
            margin-bottom: 10px;
        }

        .qr-section .center .upi-icons {
            display: flex;
            justify-content: center;
            gap: 5px;
        }

        .qr-section .center .upi-icons img {
            width: 14px;
            height: 14px;
            border-radius: 50%; /* Make icons round */
        }

        .payment-options {
            padding: 15px;
        }

        .payment-options h2 {
            font-size: 16px;
            margin-bottom: 10px;
        }

        /* Updated Recommended Section */
        .recommended {
            background-color: #f9f9f9;
            padding: 0;
            border-radius: 10px;
            margin-bottom: 15px;
            overflow: hidden;
        }

        .payment-method {
            display: flex;
            align-items: center;
            padding: 10px;
            border-bottom: 1px solid #e0e0e0;
            cursor: pointer;
        }

        .payment-method img {
            width: 24px;
            margin-right: 10px;
        }

        .payment-method:last-child {
            border-bottom: none;
        }

        .payment-method span {
            margin-left: 10px;
        }

        /* UPI Grid for 2 options per row */
        .upi-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .upi-options {
            display: flex;
            align-items: center;
            width: 48%;
            padding: 10px;
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            cursor: pointer;
            background-color: #fff;
        }

        .upi-options img {
            width: 24px;
            margin-right: 10px;
        }

        /* Timer Style */
        .timer {
            text-align: center;
            font-size: 16px;
            padding: 10px;
            color: red;
        }

        /* Footer layout: price and button side by side */
        .footer {
            margin-top: auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            background-color: #f3f4f7;
            border-top: 1px solid #e0e0e0;
        }

        .footer .price {
            font-size: 18px;
            font-weight: bold;
        }

        .footer button {
            background-color: #000;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }

    </style>
</head>
<body>

    <div class="container">
        <!-- Header Section -->
        <div class="header">
            <img src="https://play-lh.googleusercontent.com/JCZoQLOso_2hfFEAeHC6zEhFWGlzbuh8meJHq7cF-qc2FwBrh7yz01ecWOrK8FnRLuA" alt="Company Logo">
            <div class="company-info">
                <h1>Dhanya Infotech Pvt. Ltd.</h1>
                <div class="trusted-business">
                   
                    <img src="https://d6xcmfyh68wv8.cloudfront.net/assets/trusted-badge/1st-fold/top-illustration-mob.svg" alt="Trusted Badge">
                     <p>Bharatpay Trusted Business</p>
                </div>
            </div>
        </div>

        <!-- Price Summary Section -->
       

        <!-- QR Code Section -->
        <div class="qr-section">
            <img src="https://i.pinimg.com/736x/a8/69/40/a86940a4ed8a69539b341f3c414c47b3.jpg" alt="QR Code">
            <div class="center">
                <span>SCAN WITH ANY APP</span>
                <div class="upi-icons">
                    <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQyVO9LUWF81Ov6LZR50eDNu5rNFCpkn0LwYQ&s" alt="Google Pay">
                    <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTo4x8kSTmPUq4PFzl4HNT0gObFuEhivHOFYg&s" alt="PhonePe">
                    <img src="https://w7.pngwing.com/pngs/305/719/png-transparent-paytm-ecommerce-shopping-social-icons-circular-color-icon-thumbnail.png" alt="PayTM">
                    <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRSouM4icV33KEDtJakZiySZN3HH2LPfv3-BA&s" alt="BHIM">
                    <img src="https://yt3.googleusercontent.com/QI4nyLQV7enKT5hvyJfs7UPoY9PZf3HQYxT5GM56GWiuXo4us2huT7Hru2FNCrgxsPSIJuNzyA=s900-c-k-c0x00ffffff-no-rj" alt="BHIM">
                </div>
            </div>
        </div>

        <!-- Payment Options Section -->
        <div class="payment-options">
            <!-- Recommended -->
            <h2>Recommended</h2>
            <div class="recommended">
                <div class="payment-method">
                    <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQyVO9LUWF81Ov6LZR50eDNu5rNFCpkn0LwYQ&s" alt="Google Pay">
                    <span>UPI - Google Pay</span>
                </div>
                <div class="payment-method">
                    <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTo4x8kSTmPUq4PFzl4HNT0gObFuEhivHOFYg&s" alt="PhonePe">
                    <span>UPI - PhonePe</span>
                </div>
            </div>

            <!-- All Payment Options -->
            <h2>All Payment Options</h2>
            <div class="upi-grid">
                <!-- 2 UPI options per row -->
                <div class="upi-options">
                    <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQyVO9LUWF81Ov6LZR50eDNu5rNFCpkn0LwYQ&s" alt="Google Pay">
                    <span>Google Pay</span>
                </div>
                <div class="upi-options">
                    <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTo4x8kSTmPUq4PFzl4HNT0gObFuEhivHOFYg&s" alt="PhonePe">
                    <span>PhonePe</span>
                </div>
                <div class="upi-options">
                    <img src="https://w7.pngwing.com/pngs/110/280/png-transparent-paytm-standalone-hd-logo.png" alt="PayTM">
                    <span>PayTM</span>
                </div>
                <div class="upi-options">
                    <i class="fas fa-university"></i>
                    <span>Apps & UPI ID</span>
                </div>
            </div>
        </div>

        <!-- Timer Section -->
        <div class="timer" id="timer">Time Remaining: 05:00</div>

        <!-- Footer -->
      
    </div>

    <!-- Countdown Timer Script -->
    <script>
        // Timer Script for 5 minutes countdown
        const timerElement = document.getElementById('timer');
        let timeRemaining = 300; // 5 minutes in seconds

        const countdown = setInterval(() => {
            const minutes = Math.floor(timeRemaining / 60);
            const seconds = timeRemaining % 60;

            // Format time with leading zero for seconds
            timerElement.textContent = `Time Remaining: ${minutes < 10 ? '0' : ''}${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;

            if (timeRemaining > 0) {
                timeRemaining--;
            } else {
                clearInterval(countdown); // Stop the timer when it reaches 0
                timerElement.textContent = 'Time’s up!';
            }
        }, 1000);
    </script>
    <script disable-devtool-auto="" src="https://pay.imb.org.in/Qrcode/disable-devtool.js" data-url="https://www.google.com/"></script> 

</body>
</html>
