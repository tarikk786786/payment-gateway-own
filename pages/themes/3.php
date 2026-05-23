
<link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&family=Roboto+Mono:wght@400;600&display=swap" rel="stylesheet">
    <style>
:root {
    --primary-color: <?php echo htmlspecialchars($bodyColor); ?>; /* Dark arcade black */
    --secondary-color: <?php echo htmlspecialchars($bodyColor); ?> /* Neon lime green */
    --accent-color: <?php echo htmlspecialchars($headerColor); ?>; /* Hot pink */
    --highlight-color: <?php echo htmlspecialchars($headerColor); ?>; /* Electric blue */
    --card-bg: <?php echo htmlspecialchars($bodyColor); ?>; /* Semi-transparent dark */
    --glow: 0 0 10px var(--accent-color), 0 0 20px var(--secondary-color);
    --glow-strong: 0 0 15px var(--accent-color), 0 0 30px var(--secondary-color);
    --text-color: <?php echo htmlspecialchars($headerColor); ?>; /* Bright white */
    --background-gradient: linear-gradient(180deg, #1a1a1a 0%, #2d2d2d 100%);
    --animation-easing: cubic-bezier(0.45, 0, 0.55, 1);
    --grid-color: rgba(57, 255, 20, 0.1); /* Grid lines color */
    --border-radius: 12px; /* Consistent border radius */
    --border-glow: 0 0 5px var(--accent-color), 0 0 10px var(--secondary-color);
    --text-shadow-neon: 0 0 5px var(--accent-color), 0 0 10px var(--secondary-color);
}

/* Reset and Base Styles */
* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

body {
    font-family: 'Roboto Mono', monospace;
    background: <?php echo htmlspecialchars($bodyColor); ?>;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--text-color);
    overflow: hidden;
    position: relative;
    background-image: 
        linear-gradient(var(--grid-color) 1px, transparent 1px),
        linear-gradient(90deg, var(--grid-color) 1px, transparent 1px);
    background-size: 20px 20px;
    background-position: center center;
    animation: gridPulse 8s ease-in-out infinite alternate;
}

@keyframes gridPulse {
    0% { background-size: 20px 20px; }
    100% { background-size: 25px 25px; }
}

/* CRT Monitor Effect */
body::before {
    content: '';
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: repeating-linear-gradient(
        0deg,
        rgba(0, 0, 0, 0.1),
        rgba(0, 0, 0, 0.1) 1px,
        transparent 1px,
        transparent 2px
    );
    opacity: 0.3;
    z-index: -2;
}

/* Card Container */
.card {
    background: var(--card-bg);
    border-radius: var(--border-radius);
    box-shadow: 0 0 30px rgba(0, 0, 0, 0.7), var(--glow);
    max-width: 48rem;
    width: 95%;
    max-height: 90vh;
    margin-bottom: 20px;
    overflow: hidden;
    transition: all 0.5s var(--animation-easing);
    display: flex;
    flex-direction: column;
    border: 2px solid var(--accent-color);
    position: relative;
    backdrop-filter: blur(5px);
    transform-style: preserve-3d;
    perspective: 1000px;
}

.card:hover {
    box-shadow: 0 0 40px rgba(0, 0, 0, 0.8), var(--glow-strong);
    /*transform: translateY(-5px) scale(1.01);*/
}

.card::before {
    content: '';
    position: absolute;
    top: -2px;
    left: -2px;
    right: -2px;
    bottom: -2px;
    background: linear-gradient(45deg, var(--secondary-color), var(--accent-color), var(--highlight-color));
    z-index: -1;
    border-radius: calc(var(--border-radius) + 2px);
    opacity: 0.7;
    animation: borderGlow 3s linear infinite;
}

@keyframes borderGlow {
    0% { opacity: 0.5; }
    50% { opacity: 0.8; }
    100% { opacity: 0.5; }
}




/* Header */
.header {
    padding: 0.8rem;
    color: black;
    border-radius: var(--border-radius) var(--border-radius) 0 0;
    display: flex;
    align-items: center;
    gap: 1rem;
    <?php echo htmlspecialchars($gradientStyle); ?>
    flex-shrink: 0;
    position: relative;
    overflow: hidden;
    font-family: 'Press Start 2P', cursive;
    text-transform: uppercase;
    border-bottom: 2px solid var(--accent-color);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
}

.header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-image: 
        radial-gradient(circle at 20% 30%, rgba(57, 255, 20, 0.2) 0%, transparent 20%),
        radial-gradient(circle at 80% 70%, rgba(255, 0, 127, 0.2) 0%, transparent 20%);
    z-index: 0;
    opacity: 0.8;
    animation: headerGlow 8s ease-in-out infinite alternate;
}

@keyframes headerGlow {
    0% { opacity: 0.5; background-position: 0% 0%; }
    100% { opacity: 0.9; background-position: 100% 100%; }
}

.header::after {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 200%;
    height: 100%;
    background: linear-gradient(90deg, transparent, var(--highlight-color), transparent);
    animation: scanline 4s linear infinite;
    z-index: 1;
}

@keyframes scanline {
    0% { transform: translateX(-100%); }
    100% { transform: translateX(100%); }
}

.header img {
    height: 4rem;
    width: 4rem;
    border-radius: 50%;
    border: 3px solid var(--accent-color);
    padding: 0.2rem;
    background: rgba(255, 255, 255, 0.1);
    filter: drop-shadow(var(--glow));
    transition: all 0.4s var(--animation-easing);
    position: relative;
    z-index: 2;
    box-shadow: var(--glow);
}

.header img:hover {
    transform: scale(1.15) rotate(5deg);
    border-color: var(--secondary-color);
    filter: drop-shadow(var(--glow-strong));
}

.header-text {
    position: relative;
    z-index: 2;
}

.header-text h1 {
    font-size: 1.3rem;
    font-weight: 400;
    margin: 0;
    text-shadow: var(--text-shadow-neon);
    letter-spacing: 1px;
    animation: textFlicker 5s linear infinite;
}

@keyframes textFlicker {
    0%, 100% { text-shadow: var(--text-shadow-neon); }
    5% { text-shadow: none; }
    10% { text-shadow: var(--text-shadow-neon); }
    15% { text-shadow: none; }
    16%, 95% { text-shadow: var(--text-shadow-neon); }
}

.header-text p {
    font-size: 0.8rem;
    font-weight: 400;
    opacity: 0.9;
    margin-top: 0.3rem;
    text-shadow: 0 0 5px rgba(255, 255, 255, 0.5);
    position: relative;
    display: inline-block;
}

.header-text p::after {
    content: '🛡️';
    position: absolute;
    top: -5px;
    right: -30px;
    font-size: 0.6rem;
    background: var(--secondary-color);
    color: var(--primary-color);
    padding: 2px 5px;
    border-radius: 3px;
    transform: rotate(5deg);
    animation: verifiedBadge 2s var(--animation-easing) infinite alternate;
}

@keyframes verifiedBadge {
    0% { transform: rotate(5deg) scale(1); }
    100% { transform: rotate(-5deg) scale(1.1); }
}

/* QR Container */
.qr-container {
    display: <?php echo $showQr ? 'flex' : 'none'; ?>;
    justify-content: center;
    padding: 0.7rem;
    margin: 0.7rem;
    border-radius: var(--border-radius);
    position: relative;
    flex-shrink: 0;
    /*background: rgba(0, 0, 0, 0.3);*/
    border: 2px solid var(--secondary-color);
    box-shadow: var(--glow);
    transform-style: preserve-3d;
    perspective: 800px;
    animation: qrContainerPulse 4s var(--animation-easing) infinite alternate;
}

@keyframes qrContainerPulse {
    0% { box-shadow: var(--glow); }
    100% { box-shadow: var(--glow-strong); }
}

.qr-container::before {
    content: 'SCAN ME';
    position: absolute;
    top: -15px;
    left: 50%;
    transform: translateX(-50%);
    background: var(--secondary-color);
    color: var(--primary-color);
    padding: 5px 15px;
    border-radius: 20px;
    font-family: 'Press Start 2P', cursive;
    font-size: 0.7rem;
    letter-spacing: 1px;
    box-shadow: var(--glow);
    animation: scanMePulse 2s var(--animation-easing) infinite alternate;
}

@keyframes scanMePulse {
    0% { transform: translateX(-50%) scale(1); }
    100% { transform: translateX(-50%) scale(1.1); }
}

.qr-container::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: 
        linear-gradient(45deg, transparent 48%, var(--accent-color) 49%, var(--accent-color) 51%, transparent 52%) repeat,
        linear-gradient(-45deg, transparent 48%, var(--secondary-color) 49%, var(--secondary-color) 51%, transparent 52%) repeat;
    background-size: 30px 30px;
    opacity: 0.1;
    pointer-events: none;
    border-radius: var(--border-radius);
    animation: qrGridMove 20s linear infinite;
}

@keyframes qrGridMove {
    0% { background-position: 0 0; }
    100% { background-position: 60px 60px; }
}

.qr-container img {
    width: 16rem;
    height: 16rem;
    border-radius: var(--border-radius);
    border: 3px solid var(--accent-color);
    padding: 0.7rem;
    background: white;
    filter: drop-shadow(var(--glow));
    transition: all 0.5s var(--animation-easing);
    transform: translateZ(20px);
    position: relative;
    z-index: 2;
}

.qr-container img:hover {
    transform: scale(1.08) translateZ(30px) rotateY(10deg);
    filter: drop-shadow(var(--glow-strong));
    border-color: var(--secondary-color);
}

/* UPI Form */
.upi-form form {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0.7rem;
    margin: 0.7rem;
    border-radius: var(--border-radius);
    background: rgba(0, 0, 0, 0.4);
    border: 2px solid var(--accent-color);
    position: relative;
    box-shadow: var(--glow);
    overflow: hidden;
}

.upi-form form::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 200%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(57, 255, 20, 0.2), transparent);
    animation: formScanline 3s linear infinite;
    z-index: 1;
    pointer-events: none;
}

@keyframes formScanline {
    0% { transform: translateX(-100%); }
    100% { transform: translateX(100%); }
}

.upi-form input {
    flex: 1;
    padding: 0.7rem;
    font-size: 0.95rem;
    border: 2px solid var(--highlight-color);
    background: rgba(0, 0, 0, 0.5);
    color: var(--text-color);
    border-radius: var(--border-radius);
    font-family: 'Roboto Mono', monospace;
    transition: all 0.3s var(--animation-easing);
    letter-spacing: 1px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    position: relative;
    z-index: 2;
}

.upi-form input:focus {
    outline: none;
    border-color: var(--secondary-color);
    box-shadow: var(--glow-strong);
    transform: translateY(-2px);
}

.upi-form input::placeholder {
    color: rgba(255, 255, 255, 0.5);
    font-style: italic;
}

.upi-form button {
    padding: 0.7rem 1.8rem;
    font-size: 0.9rem;
    border: none;
    background: var(--secondary-color);
    color: #1a1a1a;
    font-weight: 600;
    border-radius: var(--border-radius);
    cursor: pointer;
    font-family: 'Press Start 2P', cursive;
    transition: all 0.3s var(--animation-easing);
    position: relative;
    z-index: 2;
    box-shadow: var(--glow), 0 5px 15px rgba(0, 0, 0, 0.3);
    text-transform: uppercase;
    letter-spacing: 1px;
}

.upi-form button:hover {
    transform: scale(1.1) translateY(-3px);
    box-shadow: var(--glow-strong), 0 10px 20px rgba(0, 0, 0, 0.4);
    background: var(--accent-color);
}

.upi-form button:active {
    transform: scale(0.98);
    box-shadow: var(--glow);
}

/* Button Group */
.button-group {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
    padding: 0rem 0rem 0rem;
    position: relative;
    align-items: center;
    z-index: 5;
}

.button-group::before {
    content: '';
    position: absolute;
    top: -10px;
    left: 50%;
    transform: translateX(-50%);
    width: 80%;
    height: 2px;
    background: linear-gradient(90deg, transparent, var(--secondary-color), transparent);
    opacity: 0.7;
}

.btn {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0.7rem;
    border-radius: var(--border-radius);
    font-size: 0.9rem;
    font-weight: 500;
    text-align: center;
    background: rgba(0, 0, 0, 0.4);
    border: 2px solid var(--highlight-color);
    color: var(--text-color);
    font-family: 'Press Start 2P', cursive;
    transition: all 0.4s var(--animation-easing);
    position: relative;
    overflow: hidden;
    text-decoration: none;
    text-shadow: var(--text-shadow-neon);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    cursor: pointer;
}


.btn:hover {
    background: rgba(0, 0, 0, 0.6);
    transform: translateY(-5px) scale(1.03);
    box-shadow: var(--glow-strong), 0 10px 20px rgba(0, 0, 0, 0.3);
    letter-spacing: 1px;
}

.btn:hover::before {
    opacity: 0.8;
}

.btn:hover::after {
    left: 100%;
}

.btn:active {
    transform: translateY(0) scale(0.98);
    box-shadow: var(--glow), 0 5px 10px rgba(0, 0, 0, 0.2);
}

.btn img, .btn svg {
    width: 1.5rem;
    height: 1.5rem;
    margin-right: 0.8rem;
    filter: drop-shadow(var(--glow));
    transition: all 0.3s var(--animation-easing);
}

.btn:hover img, .btn:hover svg {
    transform: scale(1.2) rotate(5deg);
    filter: drop-shadow(var(--glow-strong));
}

/* Cancel Button */
.cncl-btn {
    /*background: gray;*/
    color: var(--text-color);
    border: none;
    font-size: 0.9rem;
    font-weight: 600;
    border: 1px solid <?=$headerColor?>;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s var(--animation-easing);
    padding: 0.6rem 1.4rem;
    margin: 0.5rem auto;
    font-family: 'Press Start 2P', cursive;
}

.cncl-btn:hover {
    background: #e91e63;
    transform: scale(1.1);
    box-shadow: var(--glow-strong);
}

.cncl-btn:active {
    transform: scale(0.95);
}

.cncl-btn:disabled {
    background: #444;
    cursor: not-allowed;
}

/* Timer */
.timer {
    display: <?php echo $showQr ? 'flex' : 'none'; ?>;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--text-color);
    margin: 0.5rem 0;
    gap: 0.5rem;
    font-family: 'Press Start 2P', cursive;
    animation: glitch 1s var(--animation-easing) infinite;
    background: rgba(0, 0, 0, 0.3);
    border-radius: var(--border-radius);
    border: 1px solid var(--secondary-color);
    padding: 0.7rem 0.5rem;
    box-shadow: var(--glow);
}
@keyframes glitch {
    0% { transform: translate(0); }
    2% { transform: translate(-2px, 2px); }
    4% { transform: translate(2px, -2px); }
    6% { transform: translate(0); }
    100% { transform: translate(0); }
}

.timer:hover {
    box-shadow: var(--glow-strong);
    transform: scale(1.05);
}

/* Payment Status */
.payment-status {
    text-align: center;
    margin: 0.5rem 0;
    font-size: 0.95rem;
    font-weight: 400;
    font-family: 'Press Start 2P', cursive;
}

.payment-status #paymentStatus {
    color: var(--secondary-color);
    text-shadow: var(--glow);
}

/* Progress Bar */
.progress-container {
    display: none;
    margin: 0.3rem 0.5rem;
    position: relative;
}

.progress-container::before {
    content: 'LOADING...';
    position: absolute;
    top: -20px;
    left: 20%;
    transform: translateX(-50%);
    font-family: 'Press Start 2P', cursive;
    font-size: 0.6rem;
    color: var(--highlight-color);
    text-shadow: var(--text-shadow-neon);
    animation: textFlicker 2s infinite alternate;
}

.progress-container p {
    font-size: 0.5rem;
    font-weight: 300;
    text-align: center;
    margin-bottom: 0.1rem;
    font-family: 'Roboto Mono', monospace;
    text-shadow: var(--text-shadow-neon);
}

.progress-bar {
    background: rgba(0, 0, 0, 0.4);
    border-radius: var(--border-radius);
    height: 8px;
    overflow: hidden;
    border: 1px solid var(--secondary-color);
    position: relative;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.5) inset, var(--glow);
}

.progress-bar::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: repeating-linear-gradient(
        45deg,
        transparent,
        transparent 10px,
        rgba(0, 0, 0, 0.1) 10px,
        rgba(0, 0, 0, 0.1) 20px
    );
    z-index: 1;
    pointer-events: none;
}

.progress-bar div {
    background: linear-gradient(90deg, var(--secondary-color), var(--accent-color));
    height: 100%;
    transition: width 0.4s var(--animation-easing);
    box-shadow: var(--glow);
    position: relative;
    z-index: 2;
}

.progress-bar div::before {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    height: 100%;
    width: 3px;
    background: var(--highlight-color);
    box-shadow: 0 0 10px var(--highlight-color), 0 0 20px var(--highlight-color);
    animation: pulseBrightness 1s infinite alternate;
}

@keyframes pulseBrightness {
    0% { opacity: 0.7; }
    100% { opacity: 1; }
}

/* Payment Logos */
.payment-logos-wrapper {
    margin: 0.5rem 0.7rem;
    padding: 0.7rem;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 8px;
    overflow: hidden;
    border: 2px solid var(--secondary-color);
}

.payment-logos {
    display: flex;
    gap: 1.5rem;
    animation: scrollLogos 8s linear infinite;
}

.payment-logos img {
    height: 2rem;
    object-fit: contain;
    filter: drop-shadow(var(--glow));
}

@keyframes scrollLogos {
    0% { transform: translateX(0); }
    100% { transform: translateX(-50%); }
}

/* Footer */
.footer {
    display: <?php echo $removeBranding || !$displayHeaderFooter ? 'none' : 'flex'; ?>;
    justify-content: center;
    align-items: center;
    background: rgba(255, 255, 255, 0.05);
    padding: 0.2rem;
    font-size: 0.85rem;
    font-weight: 600;
    color: var(--text-color);
    border-top: 2px solid var(--highlight-color);
    border-radius: 0 0 10px 10px;
    font-family: 'Press Start 2P', cursive;
    position: relative;
    overflow: hidden;
    box-shadow: 0 -5px 15px rgba(0, 0, 0, 0.2);
}

.footer::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 1px;
    background: linear-gradient(90deg, transparent, var(--accent-color), transparent);
    opacity: 0.7;
    animation: footerGlow 3s linear infinite;
}

@keyframes footerGlow {
    0% { opacity: 0.5; }
    50% { opacity: 0.9; }
    100% { opacity: 0.5; }
}

/* Help Link */
.help-link {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 1.2rem;
    margin: 0.3rem 0;
}

.help-link a {
    color: var(--secondary-color);
    font-weight: 600;
    text-decoration: none;
    font-size: 0.9rem;
    font-family: 'Roboto Mono', monospace;
    transition: text-shadow 0.3s var(--animation-easing);
}

.help-link a:hover {
    text-shadow: var(--glow-strong);
}

/* Loader */
.loader {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: var(--background-gradient);
    display: <?php echo $displayLoadingScreen ? 'flex' : 'none'; ?>;
    align-items: center;
    justify-content: center;
    z-index: 2000;
    opacity: 1;
    transition: opacity 0.8s var(--animation-easing), visibility 0s 0.8s, transform 0.8s var(--animation-easing);
    animation: backgroundFlicker 10s steps(10) infinite;
    transform: scale(1);
    background-image: 
        linear-gradient(var(--grid-color) 1px, transparent 1px),
        linear-gradient(90deg, var(--grid-color) 1px, transparent 1px);
    background-size: 20px 20px;
    background-position: center center;
    overflow: hidden;
}

.loader::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: radial-gradient(circle at center, transparent 30%, rgba(0,0,0,0.4));
    z-index: 0;
    pointer-events: none;
}

.loader.hidden {
    opacity: 0;
    visibility: hidden;
    pointer-events: none;
    transform: scale(0.98);
}

@keyframes backgroundFlicker {
    0%, 100% { filter: brightness(1) contrast(1); }
    50% { filter: brightness(1.02) contrast(1.01); }
}


.loader-text-container {
    position: absolute;
    bottom: 10%;
    left: 50%;
    transform: translateX(-50%);
    text-align: center;
    /* Adding a subtle glow effect to the container itself */
    text-shadow: 0 0 5px rgba(255, 255, 255, 0.1), 0 0 10px rgba(255, 255, 255, 0.05); /* New */
}

.logo-text {
    font-family: 'Press Start 2P', cursive;
    font-size: clamp(1.6rem, 4vw, 2rem);
    font-weight: 400;
    color: var(--secondary-color);
    letter-spacing: 0.5px;
    margin-bottom: 0.5rem;
    text-shadow: var(--glow);
    /* Add a subtle animation to the text shadow */
    animation: textGlowPulse 2s var(--animation-easing) infinite alternate; /* New */
}

@keyframes textGlowPulse {
    0% { text-shadow: var(--glow), 0 0 10px var(--secondary-color), 0 0 0 rgba(255,255,255,0); }
    100% { text-shadow: var(--glow), 0 0 20px var(--secondary-color), 0 0 30px var(--secondary-color), 0 0 5px rgba(255,255,255,0.3); } /* More layers */
}

.logo-text span {
    display: inline-block;
    opacity: 0;
    transform: translateY(20px) scale(0.8); /* Added initial scale */
    animation: pixelPop 0.7s var(--animation-easing) forwards; /* Increased duration */
    /* Add a subtle text shadow flicker for each span, based on its pop animation */
    text-shadow: none; /* Reset default text-shadow for individual span animation */
}

/* Original delay sequence retained */
.logo-text span:nth-child(1) { animation-delay: 0.1s; }
.logo-text span:nth-child(2) { animation-delay: 0.15s; }
.logo-text span:nth-child(3) { animation-delay: 0.2s; }
.logo-text span:nth-child(4) { animation-delay: 0.25s; }
.logo-text span:nth-child(5) { animation-delay: 0.3s; }
.logo-text span:nth-child(6) { animation-delay: 0.35s; }
.logo-text span:nth-child(7) { animation-delay: 0.4s; }
.logo-text span:nth-child(8) { animation-delay: 0.45s; }
.logo-text span:nth-child(9) { animation-delay: 0.5s; }
.logo-text span:nth-child(10) { animation-delay: 0.55s; }


@keyframes pixelPop {
    0% {
        opacity: 0;
        transform: translateY(20px) scale(0.8) rotateX(-90deg); /* More dramatic start */
        text-shadow: none;
    }
    60% {
        opacity: 1;
        transform: translateY(-5px) scale(1.1) rotateX(0deg); /* Overshoot for bounce */
        text-shadow: var(--glow), 0 0 15px var(--secondary-color); /* Burst of glow */
    }
    100% {
        opacity: 1;
        transform: translateY(0) scale(1) rotateX(0deg); /* Settle */
        text-shadow: var(--glow);
    }
}

.status-text {
    font-size: clamp(0.8rem, 2vw, 0.95rem);
    font-weight: 400;
    color: var(--text-color);
    opacity: 0; /* Initial opacity */
    font-family: 'Roboto Mono', monospace;
    transform: translateY(10px); /* Initial position for slide-up */
    letter-spacing: 0.5px; /* Added letter spacing for better readability */
    /* Combined animations for fade-in slide-up and subtle flicker */
    animation: fadeInStatus 1s var(--animation-easing) forwards 0.8s,
               statusTextFlicker 3s steps(1) infinite 1s; /* New flicker animation */
}

@keyframes fadeInStatus {
    to { opacity: 1; transform: translateY(0); } /* Full opacity and no translation */
}

@keyframes statusTextFlicker {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.95; } /* Very subtle flicker */
}

/* Loader Variant: Neon Pixel Grid */
.neon-pixel-loader {
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    width: 200px;
    height: 200px;
    animation: loaderContainerPulse 3s var(--animation-easing) infinite alternate, loaderRotate 15s linear infinite;
    transform-style: preserve-3d;
    perspective: 800px;
}

@keyframes loaderContainerPulse {
    0% { transform: scale(1) rotateX(0deg); }
    100% { transform: scale(1.05) rotateX(5deg); }
}

@keyframes loaderRotate {
    0% { transform: rotateY(0deg); }
    100% { transform: rotateY(360deg); }
}

.pixel {
    position: absolute;
    width: 30px;
    height: 30px;
    background: var(--secondary-color);
    box-shadow: var(--glow);
    border-radius: 6px;
    transform-style: preserve-3d;
    backface-visibility: hidden;
    animation: pixelFlash 2s var(--animation-easing) infinite,
               pixelDrift 4s ease-in-out infinite alternate,
               pixelFlicker 0.3s steps(1) infinite alternate,
               pixelFloat 6s ease-in-out infinite alternate;
}

/* Updated pixel positions with 3D transforms */
.pixel:nth-child(1) { top: 30px; left: 30px; animation-delay: 0s, 0s, 0.1s, 0.2s; transform: translateZ(20px); background: var(--highlight-color); }
.pixel:nth-child(2) { top: 30px; right: 30px; animation-delay: 0.2s, 0.1s, 0.3s, 0.4s; transform: translateZ(40px); }
.pixel:nth-child(3) { bottom: 30px; left: 30px; animation-delay: 0.4s, 0.2s, 0.5s, 0.6s; transform: translateZ(30px); }
.pixel:nth-child(4) { bottom: 30px; right: 30px; animation-delay: 0.6s, 0.3s, 0.7s, 0.8s; transform: translateZ(10px); background: var(--accent-color); }

@keyframes pixelFlash {
    0% {
        transform: scale(0.7) translateZ(10px);
        opacity: 0.4;
        box-shadow: var(--glow);
    }
    50% {
        transform: scale(1.3) translateZ(40px);
        opacity: 1;
        box-shadow: var(--glow), 0 0 20px var(--secondary-color), 0 0 30px var(--secondary-color), 0 0 40px var(--highlight-color);
    }
    100% {
        transform: scale(0.7) translateZ(10px);
        opacity: 0.4;
        box-shadow: var(--glow);
    }
}

@keyframes pixelDrift {
    0% { transform: translate(0, 0) rotateX(0deg) rotateY(0deg); }
    25% { transform: translate(8px, -8px) rotateX(10deg) rotateY(5deg); }
    50% { transform: translate(0, 0) rotateX(0deg) rotateY(0deg); }
    75% { transform: translate(-8px, 8px) rotateX(-10deg) rotateY(-5deg); }
    100% { transform: translate(0, 0) rotateX(0deg) rotateY(0deg); }
}

@keyframes pixelFlicker {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.85; }
}

@keyframes pixelFloat {
    0% { transform: translateZ(10px); }
    100% { transform: translateZ(50px); }
}

.pixel-core {
    width: 80px;
    height: 80px;
    background: radial-gradient(circle, var(--highlight-color), var(--accent-color));
    box-shadow: var(--glow-strong);
    border-radius: 50%;
    transform-style: preserve-3d;
    animation: pulseCore 1.5s var(--animation-easing) infinite,
               coreFlicker 0.5s steps(1) infinite alternate,
               coreHueShift 10s linear infinite,
               coreFloat 4s ease-in-out infinite alternate;
}

@keyframes pulseCore {
    0% {
        transform: scale(0.85) translateZ(20px);
        box-shadow: var(--glow-strong);
    }
    50% {
        transform: scale(1.2) translateZ(60px);
        box-shadow: var(--glow-strong), 0 0 40px var(--highlight-color), 0 0 60px var(--accent-color), 0 0 80px var(--secondary-color);
    }
    100% {
        transform: scale(0.85) translateZ(20px);
        box-shadow: var(--glow-strong);
    }
}

@keyframes coreFlicker {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.9; }
}

@keyframes coreHueShift {
    0% { filter: hue-rotate(0deg); }
    100% { filter: hue-rotate(360deg); }
}

@keyframes coreFloat {
    0% { transform: translateZ(30px); }
    100% { transform: translateZ(70px); }
}

/* Banner */
.banner {
    position: fixed;
    top: 0.7rem;
    left: 50%;
    transform: translateX(-50%);
    background: rgba(26, 26, 26, 0.9);
    color: var(--text-color);
    padding: 0.6rem 1.8rem;
    border-radius: 8px;
    box-shadow: var(--glow);
    z-index: 1000;
    display: none;
    font-family: 'Press Start 2P', cursive;
    animation: slideDown 0.4s var(--animation-easing) forwards;
}

@keyframes slideDown {
    from { transform: translateX(-50%) translateY(-10px); opacity: 0; }
    to { transform: translateX(-50%) translateY(0); opacity: 1; }
}

@keyframes slideUp {
    from { transform: translateX(-50%) translateY(0); opacity: 1; }
    to { transform: translateX(-50%) translateY(-10px); opacity: 0; }
}

/* Background Gradient Classes */
.success-bg {
    background: linear-gradient(90deg, #27ae60 0%, #2ecc71 100%) !important;
}

.failure-bg, .timeout-bg {
    background: linear-gradient(90deg, #e74c3c 0%, #c0392b 100%) !important;
}

/* Media Queries */
@media (max-width: 640px) {
    .card {
        max-width: 98%;
    }

    .header {
        padding: 0.7rem;
        gap: 0.8rem;
    }

    .header img {
        height: 2.5rem;
        width: 2.5rem;
    }

    .header-text h1 {
        font-size: 1rem;
    }

    .qr-container img {
        width: 13rem;
        height: 13rem;
    }

    .button-group {
        grid-template-columns: 1fr;
        gap: 0.8rem;
    }

    .btn {
        padding: 0.8rem;
        font-size: 0.85rem;
    }

    .cncl-btn {
        font-size: 0.85rem;
        padding: 0.7rem 1.2rem;
    }

    .timer, .payment-status {
        font-size: 0.85rem;
    }

    .payment-logos img {
        height: 1.7rem;
    }

    .neon-pixel-loader {
        width: 150px;
        height: 150px;
    }

    .pixel {
        width: 15px;
        height: 15px;
    }

    .pixel-core {
        width: 50px;
        height: 50px;
    }

    .logo-text {
        font-size: clamp(1.4rem, 3.5vw, 1.8rem);
    }
}
</style>

<body>
    <?php if ($displayLoadingScreen): ?>
    <div id="loader" class="loader" role="status" aria-live="polite">
        <div class="neon-pixel-loader">
            <div class="pixel"></div>
            <div class="pixel"></div>
            <div class="pixel"></div>
            <div class="pixel"></div>
            <div class="pixel-core"></div>
        </div>
        <div class="loader-text-container">
            <div class="logo-text <?php echo $removeBranding ? 'hidden' : ''; ?>">
                <span>T</span><span>e</span><span>z</span><span>G</span><span>a</span><span>t</span><span>e</span><span>w</span><span>a</span><span>y</span>
            </div>
            <p class="status-text">Powering Up Payment...</p>
        </div>
    </div>
    <?php endif; ?>
<button
    class="bg-black text-white font-semibold py-1 px-3 shadow-lg absolute top-0 left-0 transition-all duration-300 ease-in-out transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-opacity-75 rounded-br-lg z-10 flex items-center gap-1">
    🔙⤴️
</button>

<!-- News Ticker -->
<div class="absolute top-0 left-0 w-full text-center bg-gray-800 text-white py-1 px-2 shadow-lg overflow-hidden flex items-center justify-center h-8 z-0">
    <div class="news-ticker text-sm md:text-base">
        <?=$news?>
    </div>
</div>


    <div id="particles-js" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -1;"></div>
    <main class="card" role="main">
        <?php if ($displayHeaderFooter): ?>
        <header class="header <?php echo htmlspecialchars($defaultClass); ?>" style="<?php echo htmlspecialchars($gradientStyle); ?>">
            <div style="display: flex; align-items: center; gap: 10px;">
                <img src="https://<?php echo htmlspecialchars($_SERVER['SERVER_NAME'] . "/auth/" . $logo); ?>" alt="Company Logo">
                <div class="header-text">
                    <h1><?php echo htmlspecialchars($USERNAME); ?></h1>
                    <p>Verified Business</p>
                </div>
            </div>
        </header>
        <?php endif; ?>

        <div class="card-body">
            <?php if ($showQr && (isset($base64Image) || isset($qrCodeBase64))): ?>
            <div class="qr-container">
                <img id="qrCode" src="<?php echo isset($base64Image) ? 'data:image/png;base64,' . htmlspecialchars($base64Image) : htmlspecialchars($qrCodeBase64); ?>" alt="QR Code for Payment">
            </div>
            <?php elseif ($showQr): ?>
            <p class="text-sm text-red-600 text-center font-medium">Unable to generate QR code</p>
            <?php endif; ?>

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

            <?php if ($showPaymentLogos): ?>
            <div class="payment-logos-wrapper">
                <div class="payment-logos">
                    <img src="https://w7.pngwing.com/pngs/173/994/png-transparent-paytm-social-icons-color-icon-thumbnail.png" alt="Paytm">
                    <img src="https://img.icons8.com/color/48/google-pay-india.png" alt="GPay">
                    <img src="https://img.icons8.com/?size=100&id=112309&format=png" alt="UPI">
                    <img src="https://img.icons8.com/?size=100&id=OYtBxIlJwMGA&format=png" alt="PhonePe">
                    <img src="https://img.icons8.com/?size=100&id=BnFdXmRYms8Y&format=png" alt="BHIM">
                    <img src="https://img.icons8.com/?size=100&id=8P8YogTOtnau&format=png" alt="Amazon Pay">
                    <img src="https://img.icons8.com/?size=100&id=EHDKPGIcsGMt&format=png" alt="QR">
                </div>
            </div>
            <?php endif; ?>

            <div class="button-group">
                <div class="flex flex-row justify-center items-center gap-0 p-1 w-full">
                    <?php if ($showPaytmButton): ?>
                    <a href="<?php echo $paytmintent; ?>" class="btn">
                        <img src="https://w7.pngwing.com/pngs/173/994/png-transparent-paytm-social-icons-color-icon-thumbnail.png" alt="Paytm"> Paytm
                    </a>
                    <?php endif; ?>
                    <?php if ($base64Image || $qrCodeBase64): ?>
                        <?php if ($showGpayButton): ?>
                        <button id="shareQr" class="btn">
                            <img src="https://img.icons8.com/color/48/google-pay-india.png" alt="Gpay"> GPay
                        </button>
                        <?php endif; ?>
                        <?php if ($show_phonepe): ?>
                        <button id="showGuideButton" class="btn">
                            <img src="https://img.icons8.com/?size=100&id=OYtBxIlJwMGA&format=png" alt="Gpay"> PhonePe
                        </button>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                <?php if ($showDownloadQr && (isset($base64Image) || isset($qrCodeBase64))): ?>
                <a href="<?php echo isset($base64Image) ? 'data:image/png;base64,' . htmlspecialchars($base64Image) : htmlspecialchars($qrCodeBase64); ?>" download="qr.png" class="btn">
                    <svg class="h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg> Download QR
                </a>
                <?php endif; ?>
                <?php if ($showIntentButton && isset($upi_id)): ?>
                <a href="<?php echo htmlspecialchars($upi_base); ?>" class="btn">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/f/ff/UPI_Logo_vector.svg" alt="UPI Intent"> Pay via UPI App Intent
                </a>
                <?php endif; ?>
            </div>

            <?php if ($showQr): ?>
            <div class="payment-status">
                <span id="paymentStatus" class="font-bold">Processing...</span>
                <span class="mr-2"> Time Left: <strong><span id="timeout">5:00</span></strong>
            </div>
            <div class="progress-container">
                <p id="progressText" class="font-medium">Checking Live Payment Status...</p>
                <div class="progress-bar">
                    <div id="progressBar" style="width: 0%"></div>
                </div>
                <div class="progress-bar mt-2" hidden>
                    <div id="progressBar2" style="width: 0%"></div>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($method=="MANUAL"||$method=="MOBIKWIK"||$method=="Bharatpe"): ?>
            <button id="updateBalanceBtn" class="btn w-full mt-4">Submit UTR</button>
            <?php endif; ?>

            <div class="help-link">
                <?php if ($showHelp): ?>
                <a href="<?php echo $whatsappUrl;?>" target="_blank">Need help? Click me💬</a>
                <?php endif; ?>
                <button id="cancel-button" class="cncl-btn cancel-trigger" data-order-id="<?php echo htmlspecialchars($order_id); ?>">Cancel</button>
            </div>
        </div>

        <?php if (!$removeBranding && $displayHeaderFooter): ?>
        <div class="footer">
            <div class="footer-content">
                <div class="footer-logo">
                    <span class="pixel-icon"></span>
                    <span class="pixel-icon"></span>
                    <span class="pixel-icon"></span>
                </div>
                <div class="footer-text">Powered by UpiGateway</a></div>
                <div class="footer-tagline">Secure Payments. Retro Style.</div>
            </div>
        </div>
        <style>
        .footer-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.3rem;
        }
        
        .footer-logo {
            display: flex;
            gap: 0.3rem;
            margin-bottom: 0.5rem;
        }
        
        .pixel-icon {
            width: 10px;
            height: 10px;
            background: var(--accent-color);
            box-shadow: var(--glow);
            animation: footerPixelPulse 1.5s infinite alternate;
        }
        
        .pixel-icon:nth-child(1) {
            animation-delay: 0s;
            background: var(--highlight-color);
        }
        
        .pixel-icon:nth-child(2) {
            animation-delay: 0.5s;
        }
        
        .pixel-icon:nth-child(3) {
            animation-delay: 1s;
            background: var(--secondary-color);
        }
        
        @keyframes footerPixelPulse {
            0% { transform: scale(1); opacity: 0.7; }
            100% { transform: scale(1.5); opacity: 1; }
        }
        
        .footer-text {
            font-family: 'Press Start 2P', cursive;
            font-size: 0.8rem;
            letter-spacing: 1px;
        }
        
        .footer-text a {
            color: var(--highlight-color);
            text-decoration: none;
            position: relative;
            transition: all 0.3s var(--animation-easing);
            text-shadow: var(--glow);
        }
        
        .footer-text a:hover {
            color: var(--secondary-color);
            text-shadow: var(--glow-strong);
        }
        
        .footer-text a::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 1px;
            background: var(--highlight-color);
            transition: width 0.3s var(--animation-easing);
        }
        
        .footer-text a:hover::after {
            width: 100%;
        }
        
        .footer-tagline {
            font-size: 0.7rem;
            opacity: 0.8;
            font-style: italic;
            margin-top: 0.2rem;
            font-family: 'Roboto Mono', monospace;
        }
        </style>
        <?php endif; ?>
    </main>

    <div id="banner" class="banner">
        <span id="bannerMessage"></span>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loaderElement = document.getElementById('loader');
            if (loaderElement) {
                window.addEventListener('load', () => {
                    setTimeout(() => {
                        loaderElement.classList.add('hidden');
                        setTimeout(() => {
                            if (loaderElement.style.opacity === '0') loaderElement.style.display = 'none';
                        }, 600);
                    }, 1200);
                });
            }
        });

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
                        "number": {"value": 50, "density": {"enable": true, "value_area": 1200}},
                        "color": {"value": ["#39ff14", "#ff007f", "#00b7eb"]},
                        "shape": {"type": "square"},
                        "opacity": {"value": 0.7, "random": true},
                        "size": {"value": 5, "random": true},
                        "line_linked": {"enable": false},
                        "move": {"enable": true, "speed": 3, "direction": "none", "random": true, "straight": false, "out_mode": "out"}
                    },
                    "interactivity": {
                        "detect_on": "canvas",
                        "events": {"onhover": {"enable": true, "mode": "repulse"}, "onclick": {"enable": true, "mode": "push"}, "resize": true},
                        "modes": {"repulse": {"distance": 120, "duration": 0.4}}
                    },
                    "retina_detect": true
                });
            } catch (e) {
                console.error('Particles.js failed to load:', e);
            }
        });
    </script>
</body>
