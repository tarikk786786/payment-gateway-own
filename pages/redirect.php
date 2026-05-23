<?php
// Default fallback company name
$company = isset($_GET['company']) ? htmlspecialchars($_GET['company']) : "Our Partner"; 

// Set the redirect delay in seconds
$redirectDelay = 2; // Increased for a better look at the animations

// Get the redirect URL from cookie, with a fallback
$redirectUrl = isset($_COOKIE['redirect']) ? $_COOKIE['redirect'] : "https://example.com";

// Unset the cookie after reading it
if (isset($_COOKIE['redirect'])) {
    setcookie("redirect", "", time() - 3600, "/");
    unset($_COOKIE['redirect']);
}

// Array of available themes
$themes = ['cosmic-warp', 'matrix-rainfall', 'quantum-core', 'aurora-borealis'];
// Select a random theme for this page load
$selectedTheme = $themes[array_rand($themes)];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UpiGateway™ - Securely Redirecting...</title>
    <style>
        :root {
            /* General Variables */
            --dark-bg: #0d1b2a;
            --light-text: #e0e7ff;
            --font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            
            /* Theme-specific variables will be defined under theme classes */
        }

        /* --- Basic Setup --- */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: var(--font-family);
            height: 100vh;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--light-text);
            transition: background-color 1s ease;
        }

        .container {
            width: 100%;
            max-width: 800px;
            padding: 2rem;
            text-align: center;
            position: relative;
            z-index: 10;
        }
        
        /* --- Shared Animated Elements --- */
        .logo {
            margin-bottom: 2.5rem;
            animation: fadeInDown 1s ease-out 0.2s forwards;
            opacity: 0;
        }

        .logo-text {
            font-size: 3.5rem;
            font-weight: 700;
            letter-spacing: 2px;
            background-size: 200% auto;
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            animation: colorShift 4s linear infinite;
        }
        
        .logo-text sup {
            font-size: 1.2rem;
            vertical-align: super;
            opacity: 0.8;
        }

        .message {
            animation: fadeInUp 1s ease-out 0.5s forwards;
            opacity: 0;
        }

        .message h4 {
            font-size: 0.9rem;
            margin-bottom: 2.5rem;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            opacity: 0.7;
        }

        .message p {
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
            color: rgba(255, 255, 255, 0.8);
        }

        .company-name {
            font-family: Arial, sans-serif;
            font-size: 1.75rem;
            font-weight: bold;
            text-shadow: 0 0 15px var(--accent-color, transparent);
            color: var(--highlight-color, #FFF);
            transition: all 0.5s ease;
        }
        
        .loader {
            width: 80px;
            height: 80px;
            margin: 2rem auto;
            position: relative;
        }
        
        .background-effects {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
        }
        
        /* --- Keyframe Animations --- */
        @keyframes fadeInDown {
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes colorShift {
            0% { background-position: 200% center; }
            100% { background-position: 0% center; }
        }

        /* ==================================== */
        /*           THEME STYLES             */
        /* ==================================== */

        /* --- Theme 1: Cosmic Warp --- */
        body.cosmic-warp {
            --primary-color: #4f46e5;
            --secondary-color: #ec4899;
            --accent-color: #f59e0b;
            --bg-color: #111827;
            --highlight-color: #f3f4f6;
            background-color: var(--bg-color);
            background-image: radial-gradient(circle at 1px 1px, rgba(255,255,255,0.1) 1px, transparent 0);
            background-size: 2rem 2rem;
        }
        .cosmic-warp .logo-text {
            background-image: linear-gradient(to right, var(--primary-color), var(--secondary-color), var(--accent-color));
        }
        .cosmic-warp .loader {
            border: 4px solid transparent;
            border-top-color: var(--primary-color);
            border-radius: 50%;
            animation: spin 1.5s ease-in-out infinite;
        }
        .cosmic-warp .loader::before {
            content: '';
            position: absolute;
            inset: 6px;
            border: 4px solid transparent;
            border-top-color: var(--secondary-color);
            border-radius: 50%;
            animation: spin 3s linear reverse infinite;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        /* --- Theme 2: Matrix Rainfall --- */
        body.matrix-rainfall {
            --primary-color: #00ff00;
            --secondary-color: #39ff14;
            --accent-color: #ccff00;
            --bg-color: #000;
            --highlight-color: #ccff00;
            background-color: var(--bg-color);
        }
        .matrix-rainfall .logo-text {
             background-image: linear-gradient(to right, var(--primary-color), var(--secondary-color));
        }
        .matrix-rainfall .company-name { color: var(--primary-color); }
        .matrix-rainfall .loader {
            animation: flicker 1.5s infinite;
        }
        .matrix-rainfall .loader::before, .matrix-rainfall .loader::after {
            content: "{}";
            font-size: 4rem;
            color: var(--primary-color);
            position: absolute;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            text-shadow: 0 0 10px var(--primary-color);
        }
        .matrix-rainfall .loader::after { content: "/>"; animation: flicker 2s infinite reverse; }
        @keyframes flicker {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.4; }
        }
        .matrix-rainfall .matrix-char {
            position: absolute;
            font-family: 'Courier New', Courier, monospace;
            color: var(--primary-color);
            opacity: 0;
            animation: matrix-fall 10s linear infinite;
            user-select: none;
        }
        @keyframes matrix-fall {
            0% { transform: translateY(-10vh); opacity: 1; }
            100% { transform: translateY(110vh); opacity: 1; }
        }

        /* --- Theme 3: Quantum Core --- */
        body.quantum-core {
            --primary-color: #00bfff;
            --secondary-color: #1e90ff;
            --accent-color: #ffffff;
            --bg-color: #0a0f1d;
            --highlight-color: #00bfff;
            background-color: var(--bg-color);
        }
        .quantum-core .logo-text {
            background-image: linear-gradient(to right, var(--primary-color), var(--accent-color));
        }
        .quantum-core .loader {
            border: 2px solid var(--secondary-color);
            border-radius: 50%;
            perspective: 800px;
        }
        .quantum-core .loader::before, .quantum-core .loader::after {
            content: '';
            position: absolute;
            inset: -2px;
            border-radius: 50%;
            border: 2px solid transparent;
        }
        .quantum-core .loader::before {
            border-left-color: var(--primary-color);
            border-right-color: var(--primary-color);
            animation: rotate-3d 2s linear infinite;
        }
        .quantum-core .loader::after {
            border-top-color: var(--accent-color);
            border-bottom-color: var(--accent-color);
            animation: rotate-3d 3s ease-in-out infinite reverse;
        }
        @keyframes rotate-3d {
            from { transform: rotate3d(1, 1, 0, 0deg); }
            to { transform: rotate3d(1, 1, 0, 360deg); }
        }
        
        /* --- Theme 4: Aurora Borealis --- */
        body.aurora-borealis {
            --primary-color: #38bdf8;
            --secondary-color: #4ade80;
            --accent-color: #a78bfa;
            --bg-color: #0c0a1a;
            --highlight-color: #ecfccb;
             background: var(--bg-color);
        }
        .aurora-borealis .logo-text {
            background-image: linear-gradient(45deg, var(--primary-color), var(--secondary-color), var(--accent-color));
        }
        .aurora-borealis .loader {
            display: flex;
            justify-content: space-around;
            align-items: flex-end;
            width: 100px;
        }
        .aurora-borealis .loader .bar {
            width: 10px;
            height: 50px;
            background: linear-gradient(to top, var(--primary-color), var(--secondary-color));
            animation: wave 1.2s ease-in-out infinite;
        }
        .aurora-borealis .loader .bar:nth-child(2) { animation-delay: 0.1s; }
        .aurora-borealis .loader .bar:nth-child(3) { animation-delay: 0.2s; }
        .aurora-borealis .loader .bar:nth-child(4) { animation-delay: 0.3s; }
        @keyframes wave {
            0%, 100% { transform: scaleY(0.2); }
            50% { transform: scaleY(1); }
        }
        .aurora-borealis #aurora {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            filter: blur(40px);
            opacity: 0.6;
        }
        .aurora-borealis .aurora-shape {
            position: absolute;
            border-radius: 50%;
            animation: aurora-move 20s infinite alternate;
        }
        .aurora-borealis .aurora-shape:nth-child(1) {
            width: 400px; height: 400px; background: var(--primary-color); left: 10%; top: 10%;
        }
        .aurora-borealis .aurora-shape:nth-child(2) {
            width: 300px; height: 300px; background: var(--secondary-color); right: 10%; bottom: 20%; animation-duration: 25s;
        }
        .aurora-borealis .aurora-shape:nth-child(3) {
            width: 250px; height: 250px; background: var(--accent-color); left: 30%; bottom: 5%; animation-duration: 18s;
        }
        @keyframes aurora-move {
            from { transform: rotate(0deg) translateX(50px) scale(1); }
            to { transform: rotate(360deg) translateX(-50px) scale(1.2); }
        }


        /* --- Responsive Design --- */
        @media (max-width: 768px) {
            .logo-text { font-size: 2.8rem; }
            .company-name { font-size: 1.5rem; }
            .loader { width: 70px; height: 70px; }
        }
        @media (max-width: 480px) {
            .logo-text { font-size: 2.2rem; }
            .company-name { font-size: 1.3rem; }
            .loader { width: 60px; height: 60px; }
        }
    </style>
</head>
<body class="<?php echo $selectedTheme; ?>">

    <!-- Background effects container -->
    <div class="background-effects">
        <?php if ($selectedTheme === 'aurora-borealis'): ?>
            <div id="aurora">
                <div class="aurora-shape"></div>
                <div class="aurora-shape"></div>
                <div class="aurora-shape"></div>
            </div>
        <?php endif; ?>
    </div>

    <div class="container">
        <div class="logo">
            <span class="logo-text">UpiGateway<sup>™</sup></span>
        </div>
        
        <div class="loader">
            <?php if ($selectedTheme === 'aurora-borealis'): ?>
                <div class="bar"></div>
                <div class="bar"></div>
                <div class="bar"></div>
                <div class="bar"></div>
            <?php endif; ?>
        </div>
        
        <div class="message">
            <h4>Simplify Payments, Amplify Success!</h4>
            <p>Securely redirecting to</p> 
            <h5 class="company-name">
                <?php echo $company; ?>
            </h5>
        </div>
    </div>
    
    <script>
        // --- Redirect Logic ---
        const redirectUrl = "<?php echo $redirectUrl; ?>";
        const delay = <?php echo $redirectDelay * 1000; ?>;
        
        setTimeout(() => {
            window.location.href = redirectUrl;
        }, delay);

        // --- Theme-specific Scripts ---
        const currentTheme = "<?php echo $selectedTheme; ?>";

        if (currentTheme === 'matrix-rainfall') {
            const container = document.querySelector('.background-effects');
            const chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789<>{}[]|';
            const streamCount = Math.floor(window.innerWidth / 20);

            for (let i = 0; i < streamCount; i++) {
                const charElement = document.createElement('div');
                charElement.className = 'matrix-char';
                charElement.textContent = chars[Math.floor(Math.random() * chars.length)];
                charElement.style.left = `${i * 20}px`;
                charElement.style.animationDelay = `${Math.random() * 10}s`;
                charElement.style.fontSize = `${Math.random() * 1.5 + 0.8}rem`;
                container.appendChild(charElement);
            }
        }
    </script>
</body>
</html>