<style>
:root {
    --primary-color: #4b0082;
    --secondary-color: #9b00ff;
    --card-bg: #ffffff;
    --shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
    --shadow-hover: 0 12px 32px rgba(0, 0, 0, 0.12);
    --accent-glow-color: rgba(0, 174, 255, 0.7); /* Adjusted for secondary */
    --accent-glow-strong: rgba(0, 200, 255, 1);
    --text-color: #696969; /* Defined for consistent usage */
    --background-gradient: linear-gradient(160deg, var(--primary-color) 0%, #040a14 100%);
    --animation-timing-function: cubic-bezier(0.65, 0, 0.35, 1);
    --animation-timing-function-alt: cubic-bezier(0.25, 0.1, 0.25, 1);
}

/* Universal box-sizing and reset margin/padding for consistency */
* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

/* Base body styles, optimized and consolidated */
body {
    font-family: 'Roboto Mono', monospace;
    background: var(--background-gradient); /* Uses root variable for consistency */
    min-height: 100vh;
    max-height: 100vh;
    overflow: hidden; /* Critical to prevent body scrolling */
    display: flex;
    align-items: center;
    justify-content: center;
    /*padding: 0.5rem;*/
    color: var(--text-color);
    margin: 0; /* Ensures no default browser margin */
}

/* Card container styles */
.card {
    background: <?php echo htmlspecialchars($bodyColor); ?>;
    border-radius: 1rem;
    box-shadow: var(--shadow);
    max-height: 90vh;
    max-width: 36rem;
    width: 100%;
    max-height: 100vh;
    overflow-y: auto; /* Allows internal scrolling if content overflows */
    overflow-x: HIDDEN;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    display: flex;
    margin-bottom: 20px;
    flex-direction: column;
}

.card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-hover);
}

/* Header styles */
.header {
    padding: 1rem;
    text-align: left;
    color: white;
    border-radius: 1rem 1rem 0 0;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    <?php echo htmlspecialchars($gradientStyle); ?>
    flex-shrink: 0; /* Prevents header from shrinking */
}

.header img {
    height: 3rem;
    width: 3rem;
    object-fit: contain;
    border-radius: 0.5rem;
    background: rgba(255, 255, 255, 0.1);
    padding: 0.3rem;
}

.header-text h1 {
    font-size: 1.25rem;
    font-weight: 700;
    margin: 0;
    line-height: 1.2;
}

.header-text p {
    font-size: 0.9rem;
    font-weight: 500;
    margin: 0.2rem 0 0;
    opacity: 0.9;
}

/* QR container styles */
.qr-container {
    display: <?php echo $showQr ? 'flex' : 'none'; ?>;
    justify-content: center;
    padding: 0.75rem;
    background: #fafafa;
    border-radius: 0.75rem;
    margin: 0.75rem;
    flex-shrink: 0; /* Prevents QR container from shrinking */
}

.qr-container img {
    width: 16rem;
    height: 16rem;
    border-radius: 0.5rem;
    border: 2px solid #e5e7eb;
    margin: 0.5rem;
    padding: 0.3rem;
    background: white;
    transition: transform 0.3s ease;
}

.qr-container img:hover {
    transform: scale(1.01);
}

/* UPI form styles, fixed syntax error in gap property */
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

/* Button group styles */
.button-group {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    padding: 0 0.75rem 0.75rem;
    gap: 0.5rem;
    flex-grow: 1; /* Allows buttons to take available space */
}

.btn {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0.75rem;
    border-radius: 0.5rem;
    font-size: 0.9rem;
    font-weight: 600;
    text-align: center;
    transition: all 0.3s ease;
    box-shadow: 0 3px 8px rgba(0, 0, 0, 0.1);
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
}

.btn img, .btn svg {
    width: 1.25rem;
    height: 1.25rem;
    margin-right: 0.5rem;
}

/* Cancel button styles */
.cncl-btn {
    background: #fc9292;
    color: white;
    border: none;
    font-size: 0.9rem;
    font-weight: 450;
    border-radius: 0.5rem;
    cursor: pointer;
    transition: all 0.3s ease;
    width: 30%;
    /*max-width: 180px;*/
    margin: 0.3rem auto;
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

/* Timer styles */
.timer {
    display: <?php echo $showQr ? 'flex' : 'none'; ?>;
    align-items: center;
    justify-content: center;
    font-size: 0.9rem;
    font-weight: 500;
    color: var(--text-color);
    margin: 0.75rem 0;
    gap: 0.5rem;
    flex-shrink: 0;
}

/* Footer styles */
.footer {
    display: <?php echo $removeBranding || !$displayHeaderFooter ? 'none' : 'flex'; ?>;
    justify-content: center;
    align-items: center;
    background: #f9fafb;
    /*padding: 0.75rem;*/
    text-align: center;
    font-size: 0.8rem;
    font-weight: 500;
    color: #6b7280;
    border-top: 1px solid #e5e7eb;
    border-radius: 0 0 1rem 1rem;
    flex-shrink: 0;
}

.payment-logos-wrapper {
    overflow: hidden;
    background: #f9fafb;
    border-radius: 0.5rem;
    margin: 0.5rem 0.75rem;
    padding: 0.5rem 0;
    position: relative;
}

.payment-logos {
    display: flex;
    gap: 1.5rem;
    animation: push-effect 20s linear infinite;
    width: max-content;
}

.payment-logos img {
    height: 2rem;
    object-fit: contain;
    flex-shrink: 0;
}

@keyframes push-effect {
    0% {
        transform: translateX(40%);
        box-shadow: 0 0 5px rgba(255, 255, 255, 0.5);
    }
    25% {
        transform: translateX(20%);
        box-shadow: 0 0 15px rgba(255, 255, 0, 1);
    }
    50% {
        transform: translateX(0%);
        box-shadow: 0 0 25px rgba(255, 0, 0, 1);
    }
    75% {
        transform: translateX(20%);
        box-shadow: 0 0 15px rgba(0, 255, 0, 1);
    }
    100% {
        transform: translateX(40%);
        box-shadow: 0 0 5px rgba(255, 255, 255, 0.5);
    }
}

.moving-element {
    animation: push-effect 5s infinite ease-in-out;
    background: linear-gradient(45deg, #ff0, #f00);
    width: 100px;
    height: 50px;
    border-radius: 10px;
    text-align: center;
    line-height: 50px;
    font-weight: bold;
    color: white;
}


/* Help link styles */
.help-link {
    display: <?php echo $showHelp ? 'block' : 'none'; ?>;
    text-align: center;
    margin: 0.20rem 0;
}

.help-link a {
    color: #2563eb;
    font-weight: 600;
    text-decoration: none;
    transition: color 0.3s ease;
    font-size: 0.9rem;
}

.help-link a:hover {
    color: #1e40af;
    text-decoration: underline;
}

/* Import fonts for the loader */
@import url('https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Roboto+Mono:wght@300;400&display=swap');

/* Loader container styles */
.loader {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: var(--background-gradient); /* Consistent with body */
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 2000;
    opacity: 1;
    transition: opacity 1s ease-out;
    perspective: 1200px;
}

/* Loader visibility states */
.loader.initially-hidden {
    display: none !important;
    opacity: 0;
}

.loader.hidden {
    opacity: 0;
    pointer-events: none;
}

/* Loader text elements */
.loader-text-container {
    position: absolute;
    bottom: 8%;
    left: 50%;
    transform: translateX(-50%);
    text-align: center;
    z-index: 100; /* Above most loader elements */
    width: 90%;
}

.logo-text {
    font-family: 'Orbitron', sans-serif;
    font-size: clamp(1.8rem, 5vw, 2.8rem);
    font-weight: 700;
    color: #F6BFFF;
    text-transform: uppercase;
    letter-spacing: 3px;
    margin-bottom: 0.75rem;
    display: inline-block;
}
.logo-text.hidden { display: none; }
.logo-text span {
    display: inline-block;
    opacity: 0;
    transform: translateY(20px);
    animation: letterReveal 0.8s var(--animation-timing-function) forwards;
    text-shadow: 0 0 8px var(--accent-glow-color),
                 0 0 15px var(--accent-glow-color),
                 0 0 25px var(--primary-color);
}
.logo-text span:nth-child(1) { animation-delay: 0.5s; } .logo-text span:nth-child(2) { animation-delay: 0.55s; }
.logo-text span:nth-child(3) { animation-delay: 0.6s; } .logo-text span:nth-child(4) { animation-delay: 0.65s; }
.logo-text span:nth-child(5) { animation-delay: 0.7s; } .logo-text span:nth-child(6) { animation-delay: 0.75s; }
.logo-text span:nth-child(7) { animation-delay: 0.8s; } .logo-text span:nth-child(8) { animation-delay: 0.85s; }
.logo-text span:nth-child(9) { animation-delay: 0.9s; } .logo-text span:nth-child(10){ animation-delay: 0.95s; }

@keyframes letterReveal { to { opacity: 1; transform: translateY(0); } }

.status-text {
    color: white;
    font-size: clamp(0.8rem, 2.5vw, 1rem);
    font-weight: 300;
    text-transform: uppercase;
    letter-spacing: 2.5px;
    opacity: 0;
    animation: fadeInTextStatus 1.5s var(--animation-timing-function) forwards 1.2s,
               pulseTextStatus 2.5s ease-in-out infinite alternate 2.7s;
}
@keyframes fadeInTextStatus { to { opacity: 0.8; } }
@keyframes pulseTextStatus {
    from { opacity: 0.6; letter-spacing: 2.5px; }
    to { opacity: 0.9; letter-spacing: 3px; }
}

/* Loader variant specific styles */
.loader-variant {
    display: none; /* JS will set this to flex/grid/block based on selection */
    width: 100%;
    height: 100%;
    align-items: center;
    justify-content: center;
    position: relative;
    overflow: hidden;
}

/* --- Architectural Genesis Styles --- */
.architectural-genesis-loader {}
.ag-scene { width: 300px; height: 300px; position: relative; transform-style: preserve-3d; animation: sceneRotate 25s linear infinite 1s; }
@keyframes sceneRotate { 0% { transform: rotateX(20deg) rotateY(30deg) rotateZ(0deg); } 100% { transform: rotateX(20deg) rotateY(390deg) rotateZ(0deg); } }
.ag-structure { width: 100%; height: 100%; position: absolute; transform-style: preserve-3d; transform: translateZ(-50px); }
.ag-cuboid { --cuboid-size: 40px; --cuboid-half-size: calc(var(--cuboid-size) / 2); width: var(--cuboid-size); height: var(--cuboid-size); position: absolute; top: 50%; left: 50%; margin-left: calc(-1 * var(--cuboid-half-size)); margin-top: calc(-1 * var(--cuboid-half-size)); transform-style: preserve-3d; opacity: 0; animation: assembleCuboid 2s var(--animation-timing-function) forwards; animation-delay: calc(var(--i) * 0.15s + 0.5s); }
.ag-cuboid:nth-child(1) { --tx: 0px; --ty: -60px; --tz: 0px; --rx: 0deg; --ry: 0deg; } .ag-cuboid:nth-child(2) { --tx: 60px; --ty: 0px; --tz: 0px; --rx: 0deg; --ry: 0deg; } .ag-cuboid:nth-child(3) { --tx: 0px; --ty: 60px; --tz: 0px; --rx: 0deg; --ry: 0deg; } .ag-cuboid:nth-child(4) { --tx: -60px; --ty: 0px; --tz: 0px; --rx: 0deg; --ry: 0deg; } .ag-cuboid:nth-child(5) { --tx: 30px; --ty: -30px; --tz: 50px; --rx: 45deg; --ry: 45deg; } .ag-cuboid:nth-child(6) { --tx: -30px; --ty: 30px; --tz: 50px; --rx: -45deg; --ry: 45deg; } .ag-cuboid:nth-child(7) { --tx: 30px; --ty: 30px; --tz: -50px; --rx: 45deg; --ry: -45deg; } .ag-cuboid:nth-child(8) { --tx: -30px; --ty: -30px; --tz: -50px; --rx: -45deg; --ry: -45deg; }
@keyframes assembleCuboid { 0% { opacity: 0; transform: rotateX(calc(var(--i) * 25deg + 70deg)) rotateY(calc(var(--i) * 15deg + 60deg)) translateZ(150px) scale(0.5); } 60% { opacity: 0.8; transform: rotateX(var(--rx, 0deg)) rotateY(var(--ry, 0deg)) translateX(var(--tx, 0px)) translateY(var(--ty, 0px)) translateZ(var(--tz, 0px)) scale(1.1); } 100% { opacity: 1; transform: rotateX(var(--rx, 0deg)) rotateY(var(--ry, 0deg)) translateX(var(--tx, 0px)) translateY(var(--ty, 0px)) translateZ(var(--tz, 0px)) scale(1); } }
.ag-face { position: absolute; width: var(--cuboid-size); height: var(--cuboid-size); background: radial-gradient(circle at center, rgba(0,174,255,0.05) 0%, rgba(0,174,255,0.15) 70%), repeating-linear-gradient(0deg, transparent, transparent 4px, rgba(0,174,255,0.2) 5px, rgba(0,174,255,0.2) 6px), repeating-linear-gradient(90deg, transparent, transparent 4px, rgba(0,174,255,0.2) 5px, rgba(0,174,255,0.2) 6px); border: 1px solid var(--accent-glow-color); box-shadow: inset 0 0 8px rgba(0,174,255,0.3), 0 0 5px var(--accent-glow-color); opacity: 0.6; }
.ag-front  { transform: translateZ(var(--cuboid-half-size)); } .ag-back   { transform: rotateY(180deg) translateZ(var(--cuboid-half-size)); } .ag-right  { transform: rotateY(90deg) translateZ(var(--cuboid-half-size)); } .ag-left   { transform: rotateY(-90deg) translateZ(var(--cuboid-half-size)); } .ag-top    { transform: rotateX(90deg) translateZ(var(--cuboid-half-size)); } .ag-bottom { transform: rotateX(-90deg) translateZ(var(--cuboid-half-size)); }
.ag-scanline-system { position: absolute; top: -50%; left: -50%; width: 200%; height: 200%; transform-style: preserve-3d; pointer-events: none; animation: scanPulse 6s ease-in-out infinite 2s; }
.ag-scanline { position: absolute; top: 0; left: 50%; width: 2px; height: 100%; background: var(--accent-glow-strong); box-shadow: 0 0 10px var(--accent-glow-strong), 0 0 20px var(--accent-glow-strong); opacity: 0; transform-origin: center; }
@keyframes scanPulse { 0%, 100% { opacity: 0; transform: scaleY(0.1) rotateY(0deg); } 10%, 90% { opacity: 0.6; transform: scaleY(1) rotateY(0deg); } 50% { opacity: 0.3; transform: scaleY(1) rotateY(180deg); } }
.ag-grid-emitter { position: absolute; width: 400px; height: 400px; top: 50%; left: 50%; transform: translate(-50%, -50%) rotateX(70deg) translateZ(-200px); background-image: linear-gradient(var(--accent-glow-color) 1px, transparent 1px), linear-gradient(to right, var(--accent-glow-color) 1px, transparent 1px); background-size: 30px 30px; opacity: 0; animation: fadeInGrid 3s ease-out forwards 1s, pulseGrid 8s ease-in-out infinite 4s; }
@keyframes fadeInGrid { to { opacity: 0.08; } }
@keyframes pulseGrid { 0%, 100% { opacity: 0.08; transform: translate(-50%, -50%) rotateX(70deg) translateZ(-200px) scale(1); } 50% { opacity: 0.15; transform: translate(-50%, -50%) rotateX(70deg) translateZ(-190px) scale(1.05); } }

/* --- Quantum Entanglement Styles --- */
.quantum-entanglement-loader {}
.qe-scene {
    width: 300px;
    height: 250px;
    position: relative;
    transform-style: preserve-3d;
    animation: qeSceneRotate 30s linear infinite 0.5s;
}
@keyframes qeSceneRotate {
    to { transform: rotateY(360deg) rotateX(10deg); }
}

.qe-core {
    position: absolute;
    width: 80px;
    height: 80px;
    transform-style: preserve-3d;
    animation: qeCoreOrbit 10s ease-in-out infinite alternate;
}
.qe-core1 { top: 30%; left: 20%; animation-delay: 0s; }
.qe-core2 { top: 30%; left: calc(80% - 80px); animation-delay: -5s; } /* Offset animation */

@keyframes qeCoreOrbit {
    0% { transform: translateZ(0px) rotateY(0deg); }
    50% { transform: translateZ(30px) rotateY(20deg); }
    100% { transform: translateZ(0px) rotateY(-20deg); }
}

.qe-core-inner {
    width: 100%; height: 100%;
    border-radius: 50%;
    background: radial-gradient(circle, var(--accent-glow-strong) 0%, var(--primary-color) 70%);
    box-shadow: 0 0 15px var(--accent-glow-strong), 0 0 30px var(--secondary-color), inset 0 0 10px rgba(255,255,255,0.3);
    animation: qeCorePulse 2s ease-in-out infinite;
    position: absolute;
}
@keyframes qeCorePulse {
    0%, 100% { transform: scale(0.95); opacity: 0.8; }
    50% { transform: scale(1.05); opacity: 1; }
}

.qe-core-rings div {
    position: absolute;
    top: 50%; left: 50%;
    border: 1px solid var(--accent-glow-color);
    border-radius: 50%;
    opacity: 0.5;
    animation: qeRingRotate 4s linear infinite;
}
.qe-core-rings div:nth-child(1) { width: 100px; height: 100px; transform: translate(-50%, -50%) rotateX(70deg); animation-duration: 3.5s;}
.qe-core-rings div:nth-child(2) { width: 120px; height: 120px; transform: translate(-50%, -50%) rotateX(70deg) rotateY(60deg); animation-duration: 4s;}
.qe-core-rings div:nth-child(3) { width: 140px; height: 140px; transform: translate(-50%, -50%) rotateX(70deg) rotateY(120deg); animation-duration: 4.5s;}

@keyframes qeRingRotate {
    to { transform: translate(-50%,-50%) rotateX(70deg) rotateZ(360deg); }
}

.qe-connections {
    position: absolute;
    top: calc(30% + 40px); left: calc(20% + 40px); /* Align between cores */
    width: calc(40%); height: 1px;
    transform-style: preserve-3d;
}
.qe-beam {
    position: absolute;
    width: 100%; height: 2px;
    background: var(--accent-glow-strong);
    border-radius: 1px;
    opacity: 0;
    transform-origin: left;
    animation: qeBeamFire 3s var(--animation-timing-function-alt) infinite;
    animation-delay: calc(var(--i) * 0.5s);
    box-shadow: 0 0 5px var(--accent-glow-strong), 0 0 10px var(--secondary-color);
}
@keyframes qeBeamFire {
    0% { transform: scaleX(0) translateX(0); opacity: 0; }
    20% { transform: scaleX(1) translateX(0); opacity: 0.7; }
    80% { transform: scaleX(1) translateX(0); opacity: 0.7; }
    100% { transform: scaleX(0) translateX(100%); opacity: 0; } /* Move origin for shrink */
}

.qe-particle-field {
    position: absolute;
    top: 0; left: 0; width: 100%; height: 100%;
    transform-style: preserve-3d;
    pointer-events: none;
}
.qe-particle {
    position: absolute;
    width: 3px; height: 3px;
    background: var(--accent-glow-color);
    border-radius: 50%;
    opacity: 0;
    /* CSS random() is experimental, fallback with nth-child rules below */
    --rx: calc(random() * 360deg);
    --ry: calc(random() * 360deg);
    --dist: calc(100px + random() * 100px);
    transform: rotateX(var(--rx)) rotateY(var(--ry)) translateZ(var(--dist));
    animation: qeParticleFloat 6s ease-in-out infinite;
    animation-delay: calc(var(--i) * 0.1s);
}
/* Fallback if CSS random() not supported (set with JS or more nth-child) */
.qe-particle:nth-child(odd) { --rx: 45deg; --dist: 120px; }
.qe-particle:nth-child(even) { --ry: 70deg; --dist: 150px; }


@keyframes qeParticleFloat {
    0%, 100% { opacity: 0; transform: rotateX(var(--rx)) rotateY(var(--ry)) translateZ(var(--dist)) scale(0.5); }
    50% { opacity: 0.8; transform: rotateX(var(--rx)) rotateY(var(--ry)) translateZ(calc(var(--dist) + 20px)) scale(1); }
}

/* --- Bio-Digital Synapse Styles --- */
.bio-digital-synapse-loader { }
.bds-svg {
    width: 280px;
    height: 280px;
    overflow: visible;
}
.bds-node {
    fill: var(--primary-color);
    stroke: var(--accent-glow-strong);
    stroke-width: 1.5;
    r: 0; /* Start small */
    animation: bdsNodeGrow 1.5s var(--animation-timing-function) forwards,
               bdsNodePulse 2.5s ease-in-out infinite alternate;
}
.bds-node-central {
    stroke-width: 2.5;
    animation-delay: 0s, 1.5s; /* Pulse after grow */
    animation: bdsNodeGrowCentral 1.5s var(--animation-timing-function) forwards,
               bdsNodePulseCentral 2.5s ease-in-out infinite alternate;
}
/* Stagger node growth */
.bds-svg #n1 { animation-delay: 0.2s, 1.7s; } .bds-svg #n2 { animation-delay: 0.4s, 1.9s; }
.bds-svg #n3 { animation-delay: 0.6s, 2.1s; } .bds-svg #n4 { animation-delay: 0.8s, 2.3s; }
.bds-svg #n5 { animation-delay: 1.0s, 2.5s; } .bds-svg #n6 { animation-delay: 0.0s, 1.5s; } /* Original delay for central node */


@keyframes bdsNodeGrow {
    to { r: 8; }
}
@keyframes bdsNodeGrowCentral { /* Specific keyframe for central node's grow */
    to { r: 12; }
}

@keyframes bdsNodePulse {
    from { stroke-width: 1.5; filter: brightness(0.9); }
    to { stroke-width: 2.5; filter: brightness(1.2); }
}
@keyframes bdsNodePulseCentral { /* Specific keyframe for central node's pulse */
    from { stroke-width: 2.5; }
    to { stroke-width: 3.5; }
}

.bds-connection {
    stroke: var(--accent-glow-color);
    stroke-width: 1;
    stroke-linecap: round;
    stroke-dasharray: 100; /* A bit longer than expected max length */
    stroke-dashoffset: 100;
    opacity: 0.6;
    animation: bdsDrawLine 1s var(--animation-timing-function-alt) forwards;
    animation-delay: calc(var(--li) * 0.1s + 1s); /* Start after nodes grow */
}
@keyframes bdsDrawLine {
    to { stroke-dashoffset: 0; }
}

/* --- Cryptext Flux Styles --- */
.cryptext-flux-loader {
    background: #000; /* Darker specific background for this loader */
}
.cf-grid {
    display: flex;
    width: 80%;
    max-width: 400px;
    height: 250px;
    position: relative;
    perspective: 500px; /* For column depth */
    overflow: hidden;
}
.cf-column {
    flex: 1;
    display: flex;
    flex-direction: column;
    margin: 0 1px;
    transform-style: preserve-3d; /* If adding 3D rotation to columns */
    animation: cfColumnFall 5s linear infinite;
    animation-delay: calc(var(--ci) * -0.3s); /* Stagger fall start */
    opacity: calc(0.5 + (var(--ci) % 5) * 0.1); /* Vary opacity slightly */
    transform: translateZ(calc((var(--ci) % 3 - 1) * -20px)); /* Slight depth */
}
@keyframes cfColumnFall {
    0% { transform: translateY(-100%) translateZ(calc((var(--ci) % 3 - 1) * -20px)); }
    100% { transform: translateY(100%) translateZ(calc((var(--ci) % 3 - 1) * -20px)); }
}

.cf-char {
    font-family: 'Orbitron', 'Roboto Mono', monospace;
    font-size: clamp(0.8rem, 3vw, 1.1rem);
    line-height: 1.3;
    color: var(--accent-glow-color);
    text-shadow: 0 0 3px var(--accent-glow-color);
    opacity: calc(0.3 + (var(--cj) % 10) * 0.05); /* Vary char opacity */
    animation: cfCharFlicker 0.2s linear infinite;
    animation-delay: calc(random() * 0.2s); /* CSS random is experimental */
}
/* A few chars brighter */
.cf-column:nth-child(3n) .cf-char:nth-child(5n) {
    color: var(--text-color);
    opacity: 1;
    text-shadow: 0 0 5px var(--text-color), 0 0 10px var(--accent-glow-strong);
}
@keyframes cfCharFlicker {
    0%, 100% { opacity: currentcolor; } /* Use existing opacity */
    50% { opacity: calc(currentcolor * 0.5); } /* Halve its current opacity */
}

.cf-scanbar {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 3px;
    background: linear-gradient(90deg, transparent, var(--accent-glow-strong), transparent);
    animation: cfScanBarMove 3s ease-in-out infinite;
    opacity: 0.7;
    box-shadow: 0 0 10px var(--accent-glow-strong);
}
@keyframes cfScanBarMove {
    0% { transform: translateY(0px); }
    50% { transform: translateY(247px); } /* grid height - bar height */
    100% { transform: translateY(0px); }
}

.cf-focus-glitch {
    position: absolute;
    top: 30%; left: 30%;
    width: 40%; height: 20%;
    background: var(--primary-color);
    opacity: 0;
    animation: cfGlitchEffect 4s steps(4, jump-none) infinite 1s;
    mix-blend-mode: difference; /* Interesting visual effect */
}
@keyframes cfGlitchEffect {
    0%, 100% { opacity: 0; transform: translate(0,0) skew(0,0); }
    5% { opacity: 0.3; transform: translate(-5px, 2px) skew(5deg, 2deg); }
    10% { opacity: 0; }
    15% { opacity: 0.4; transform: translate(3px, -3px) skew(-3deg, -4deg); }
    20% { opacity: 0; }
}

/* Banner styles */
.banner {
    position: fixed;
    top: 0.5rem;
    left: 50%;
    transform: translateX(-50%);
    background: #1f2937;
    color: white;
    padding: 0.5rem 1.5rem;
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

/* Background gradient utility classes */
.success-bg {
    background: linear-gradient(145deg, #d4fc79 0%, #96e6a1 100%) !important;
}

.failure-bg, .timeout-bg {
    background: linear-gradient(145deg, #ff9a9e 0%, #fad0c4 100%) !important;
}

/* Card body padding */
.card-body{
    align-items: center;
    padding: 10px;
    padding-top: 5PX;
}

/* Media Queries for responsiveness, consolidated for brevity */
@media (max-width: 640px) {
    .logo-text { font-size: clamp(1.5rem, 4vw, 2rem); }
    .status-text { font-size: clamp(0.7rem, 2vw, 0.9rem); }

    .card {
        max-width: 95%;
        max-height: 95vh; /* More compact for smaller screens */
    }

    .header {
        padding: 1.5rem; /* Larger padding */
        gap: 1rem; /* Larger gap */
        border-radius: 1.25rem 1.25rem 0 0; /* Larger radius */
    }

    .header img {
        height: 3.5rem; /* Larger icon size */
        width: 3.5rem;
    }

    .header-text h1 {
        font-size: 1.5rem; /* Larger text size */
    }

    .qr-container img {
        width: 14rem; /* Smaller QR code */
        height: 14rem;
    }

    .button-group {
        grid-template-columns: 1fr; /* Single column layout */
        padding: 0 0.5rem 0.5rem; /* Adjusted padding */
    }

    .btn {
        padding: 0.6rem; /* Smaller padding */
        font-size: 0.85rem; /* Smaller font size */
    }

    .btn img, .btn svg {
        width: 1.1rem; /* Smaller icon size */
        height: 1.1rem;
    }

    .cncl-btn {
        padding: 0.3rem 0.5rem; /* Adjusted padding */
        font-size: 0.85rem; /* Smaller font size */
        /*max-width: 240px; */
    }

    .timer {
        font-size: 0.8rem; /* Smaller font size */
        margin: 0.5rem 0; /* Adjusted margin */
    }

    .footer {
        padding: 0.5rem; /* Smaller padding */
        font-size: 0.75rem; /* Smaller font size */
    }

    .payment-logos {
        gap: 0.75rem; /* Smaller gap */
        margin: 0.5rem; /* Adjusted margin */
    }

    .payment-logos img {
        height: 1.75rem; /* Smaller logo size */
    }

    .help-link a {
        font-size: 0.8rem; /* Smaller font size */
    }

    .payment-status {
        font-size: 0.8rem; /* Smaller font size */
        color: BLACK;
    }
    .progress-bar{
        display: none !important; /* Retains original rule */
    }

    .upi-form{max-width:AUTO;} /* Retains original rule */

    /* Loader variant specific media queries */
    .ag-scene { width: 220px; height: 220px; }
    .ag-cuboid { --cuboid-size: 30px; }
    .ag-grid-emitter { width: 300px; height: 300px; background-size: 20px 20px; }

    .qe-scene { width: 240px; height: 200px; }
    .qe-core { width: 60px; height: 60px; }
    .qe-core-rings div:nth-child(1) { width: 80px; height: 80px; }
    .qe-core-rings div:nth-child(2) { width: 100px; height: 100px; }
    .qe-core-rings div:nth-child(3) { width: 120px; height: 120px; }
    .qe-connections { top: calc(30% + 30px); left: calc(20% + 30px); }

    .bds-svg { width: 220px; height: 220px; }

    .cf-grid { width: 90%; height: 200px; }
    @keyframes cfScanBarMove { /* Adjusted for smaller grid height */
        0% { transform: translateY(0px); }
        50% { transform: translateY(197px); } /* grid height (200px) - bar height (3px) */
        100% { transform: translateY(0px); }
    }
}
</style>

<!--<body>-->
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

    <?php if ($displayLoadingScreen): ?>

        <!-- Loader Variant 1: Architectural Genesis (Enhanced Holographic Blueprint) -->
    <div id="loader" class="loader <?php echo $displayLoadingScreen ? '' : 'initially-hidden'; ?>" role="status" aria-live="polite">

        <!-- Loader Variant 1: Architectural Genesis -->
        <div class="loader-variant architectural-genesis-loader">
            <div class="ag-scene">
                <div class="ag-structure">
                    <?php for ($i = 0; $i < 8; $i++): ?>
                    <div class="ag-cuboid" style="--i: <?php echo $i; ?>;">
                        <div class="ag-face ag-front"></div><div class="ag-face ag-back"></div>
                        <div class="ag-face ag-right"></div><div class="ag-face ag-left"></div>
                        <div class="ag-face ag-top"></div><div class="ag-face ag-bottom"></div>
                    </div>
                    <?php endfor; ?>
                </div>
                <div class="ag-scanline-system"><div class="ag-scanline"></div></div>
                <div class="ag-grid-emitter"></div>
            </div>
        </div>

        <!-- Loader Variant 2: Quantum Entanglement -->
        <div class="loader-variant quantum-entanglement-loader">
            <div class="qe-scene">
                <div class="qe-core qe-core1">
                    <div class="qe-core-inner"></div>
                    <div class="qe-core-rings"><div></div><div></div><div></div></div>
                </div>
                <div class="qe-core qe-core2">
                    <div class="qe-core-inner"></div>
                    <div class="qe-core-rings"><div></div><div></div><div></div></div>
                </div>
                <div class="qe-connections">
                    <?php for ($i = 0; $i < 5; $i++): /* Energy beams */ ?>
                    <div class="qe-beam" style="--i: <?php echo $i; ?>"></div>
                    <?php endfor; ?>
                </div>
                <div class="qe-particle-field">
                    <?php for ($i = 0; $i < 50; $i++): /* More particles */ ?>
                    <div class="qe-particle" style="--i: <?php echo $i; ?>"></div>
                    <?php endfor; ?>
                </div>
            </div>
        </div>

        <!-- Loader Variant 3: Bio-Digital Synapse -->
        <div class="loader-variant bio-digital-synapse-loader">
            <svg class="bds-svg" viewBox="0 0 300 300">
                <defs>
                    <filter id="bds-glow" x="-50%" y="-50%" width="200%" height="200%">
                        <feGaussianBlur stdDeviation="3.5" result="coloredBlur"/>
                        <feMerge>
                            <feMergeNode in="coloredBlur"/>
                            <feMergeNode in="SourceGraphic"/>
                        </feMerge>
                    </filter>
                </defs>
                <g class="bds-network" filter="url(#bds-glow)">
                    <!-- Nodes (circles) -->
                    <?php
                    $nodes = [
                        ['id' => 'n1', 'cx' => 150, 'cy' => 50], ['id' => 'n2', 'cx' => 250, 'cy' => 120],
                        ['id' => 'n3', 'cx' => 200, 'cy' => 250], ['id' => 'n4', 'cx' => 100, 'cy' => 250],
                        ['id' => 'n5', 'cx' => 50, 'cy' => 120], ['id' => 'n6', 'cx' => 150, 'cy' => 150, 'central' => true] // Central node
                    ];
                    foreach ($nodes as $node) {
                        echo '<circle class="bds-node' . (isset($node['central']) ? ' bds-node-central' : '') . '" id="' . $node['id'] . '" cx="' . $node['cx'] . '" cy="' . $node['cy'] . '" r="0"/>';
                    }
                    ?>
                    <!-- Connections (lines) -->
                    <?php
                    $connections = [
                        ['from' => 'n1', 'to' => 'n6'], ['from' => 'n2', 'to' => 'n6'], ['from' => 'n3', 'to' => 'n6'],
                        ['from' => 'n4', 'to' => 'n6'], ['from' => 'n5', 'to' => 'n6'],
                        ['from' => 'n1', 'to' => 'n2'], ['from' => 'n2', 'to' => 'n3'],
                        ['from' => 'n3', 'to' => 'n4'], ['from' => 'n4', 'to' => 'n5'], ['from' => 'n5', 'to' => 'n1']
                    ];
                    $line_index = 0;
                    foreach ($connections as $conn) {
                        $from_node = current(array_filter($nodes, function($n) use ($conn) { return $n['id'] == $conn['from']; }));
                        $to_node = current(array_filter($nodes, function($n) use ($conn) { return $n['id'] == $conn['to']; }));
                        echo '<line class="bds-connection" style="--li: '.$line_index++.';" x1="'.$from_node['cx'].'" y1="'.$from_node['cy'].'" x2="'.$to_node['cx'].'" y2="'.$to_node['cy'].'"/>';
                    }
                    ?>
                </g>
            </svg>
        </div>

        <!-- Loader Variant 4: Cryptext Flux -->
        <div class="loader-variant cryptext-flux-loader">
            <div class="cf-grid">
                <?php for ($i = 0; $i < 10; $i++): // 10 columns ?>
                <div class="cf-column" style="--ci: <?php echo $i; ?>">
                    <?php for ($j = 0; $j < 20; $j++): // Chars per column
                        $char_options = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789#@$%&*<>()[]{}/|\\?!^~';
                        // $char_options = '░▒▓█▄▀▌▐─│┌┐└┘├┤┬┴┼═║╔╗╚╝'; // Alternative block/symbol characters
                        $char = $char_options[rand(0, strlen($char_options) - 1)];
                    ?>
                    <span class="cf-char" style="--cj: <?php echo $j; ?>"><?php echo htmlspecialchars($char); ?></span>
                    <?php endfor; ?>
                </div>
                <?php endfor; ?>
            </div>
            <div class="cf-scanbar"></div>
            <div class="cf-focus-glitch"></div>
        </div>

        <!-- Common Text Elements - Refined -->
        <div class="loader-text-container">
            <div class="logo-text <?php echo $removeBranding ? 'hidden' : ''; ?>">
                <span>T</span><span>e</span><span>z</span><span>G</span><span>a</span><span>t</span><span>e</span><span>w</span><span>a</span><span>y</span>
            </div>
            <p class="status-text">Initializing Secure Connection...</p>
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
  <div class="payment-logos">
    <!-- Original Set -->
    <img src="https://w7.pngwing.com/pngs/173/994/png-transparent-paytm-social-icons-color-icon-thumbnail.png" alt="Paytm">
    <img src="https://img.icons8.com/color/48/google-pay-india.png" alt="GPay">
    <img src="https://img.icons8.com/?size=100&id=112309&format=png" alt="UPI">
    <img src="https://img.icons8.com/?size=100&id=OYtBxIlJwMGA&format=png" alt="PhonePe">
    <img src="https://img.icons8.com/?size=100&id=BnFdXmRYms8Y&format=png" alt="BHIM">
    <img src="https://img.icons8.com/?size=100&id=8P8YogTOtnau&format=png" alt="Amazon Pay">
    <img src="https://img.icons8.com/?size=100&id=EHDKPGIcsGMt&format=png" alt="QR">


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
                <?php if ($show_phonepe): ?>
                <button id="showGuideButton" class="flex-1 flex items-center justify-center bg-gradient-to-r from-purple-800 to-purple-700 text-white px-4 py-3 rounded-lg font-medium text-m shadow-md hover:shadow-xl hover:from-purple-800 hover:to-purple-700 transition-all duration-300 transform hover:scale-105">
                    <img src="https://img.icons8.com/?size=100&id=OYtBxIlJwMGA&format=png" alt="Gpay" class="w-5 h-5 mr-1"> Phonepe
                </button>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        <?php if ($showDownloadQr && (isset($base64Image) || isset($qrCodeBase64))): ?>
            <a href="<?php echo isset($base64Image) ? 'data:image/png;base64,' . htmlspecialchars($base64Image) : htmlspecialchars($qrCodeBase64); ?>" download="qr.png" class="btn bg-gradient-to-r from-gray-600 to-gray-500 text-white">
                <svg class="h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg> Download QR
            </a>
        <?php endif; ?>
        <?php if ($showIntentButton && isset($upi_id)): ?>
            <a href="<?php echo htmlspecialchars($upi_base); ?>"  class="btn bg-gradient-to-r from-purple-600 to-purple-400 text-white">
                <img src="https://upload.wikimedia.org/wikipedia/commons/f/ff/UPI_Logo_vector.svg" alt="UPI Intent" class="h-6 mr-2"> Pay via UPI App Intent
            </a>
        <?php endif; ?>
    </div>

    <?php if ($showQr): ?>

        <!-- New Elements for Payment Status and Progress Bars -->
        <div class="payment-status text-center my-2">
            <span id="paymentStatus" class="font-bold"></span>
       
            <span class="mr-2"> Time Left: <strong><span id="timeout">5:00</span></strong>

        </div>
        <div class="progress-container mx-4 my-2">
            <p id="progressText" class="text-center font-medium mb-1"></p>
            <div class="progress-bar bg-gray-200 rounded-full h-2 overflow-hidden">
                <div id="progressBar" class="bg-yellow-500 h-full transition-all duration-300" style="width: 0%"></div>
            </div>
            <div class="progress-bar bg-gray-200 rounded-full h-2 overflow-hidden mt-2">
                <div id="progressBar2" class="bg-yellow-500 h-full transition-all duration-300" style="width: 0%"></div>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($method=="MANUAL"||$method=="MOBIKWIK"||$method=="Bharatpe"): ?>
        <button id="updateBalanceBtn" class="btn bg-gradient-to-r from-indigo-600 to-indigo-400 text-white w-full mt-4">Submit UTR</button>
    <?php endif; ?>

    
<div class="help-link" style="display: flex; align-items: center; gap: 15px;">
    <?php if ($showHelp): ?>
        <a href="<?php echo $whatsappUrl;?>" target="_blank">Need help? Click me💬</a>
    <?php endif; ?>
    <button id="cancel-button" class="cncl-btn cancel-trigger" data-order-id="<?php echo htmlspecialchars($order_id); ?>">Cancel</button>
</div>


    
</div>



        <?php if (!$removeBranding && $displayHeaderFooter): ?>
            <div class="footer">Powered by <span class="font-semibold">UpiGateway™</span></div>
        <?php endif; ?>
    </main>

    <div id="banner" class="banner">
        <span id="bannerMessage"></span>
    </div>

      <script>
        function selectRandomLoader() {
            const loaderElement = document.getElementById('loader');
            const variants = loaderElement.querySelectorAll('.loader-variant');
            variants.forEach(v => v.style.display = 'none');

            if (variants.length > 0) {
                const randomIndex = Math.floor(Math.random() * variants.length);
                // Ensure the chosen variant is actually visible and not empty
                if (variants[randomIndex] && variants[randomIndex].innerHTML.trim() !== '') {
                    variants[randomIndex].style.display = 'flex'; // Or 'grid', 'block' etc. as appropriate
                } else { // Fallback if a randomly chosen one is empty, pick the first non-empty
                    const firstValid = Array.from(variants).find(v => v.innerHTML.trim() !== '');
                    if (firstValid) firstValid.style.display = 'flex';
                }
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const loaderElement = document.getElementById('loader');
            if (loaderElement && !loaderElement.classList.contains('initially-hidden')) {
                selectRandomLoader();
            }

            window.addEventListener('load', () => {
                setTimeout(() => {
                    if (loaderElement) {
                        loaderElement.classList.add('hidden');
                         // Optional: fully hide after transition
                        setTimeout(() => {
                            if (loaderElement.style.opacity === '0') loaderElement.style.display = 'none';
                        }, 1000); // Match opacity transition duration
                    }
                }, 2500); // Allow more time for animations
            });
        });

    // Initialize particles.js after page load
window.addEventListener('load', async () => {
    try {
        await loadScript('https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js');
particlesJS("particles-js", {"particles":{"number":{"value":160,"density":{"enable":true,"value_area":800}},"color":{"value":"#ffffff"},"shape":{"type":"circle","stroke":{"width":0,"color":"#000000"},"polygon":{"nb_sides":5},"image":{"src":"img/github.svg","width":100,"height":100}},"opacity":{"value":1,"random":true,"anim":{"enable":true,"speed":1,"opacity_min":0,"sync":false}},"size":{"value":3,"random":true,"anim":{"enable":false,"speed":4,"size_min":0.3,"sync":false}},"line_linked":{"enable":false,"distance":150,"color":"#ffffff","opacity":0.4,"width":1},"move":{"enable":true,"speed":1,"direction":"none","random":true,"straight":false,"out_mode":"out","bounce":false,"attract":{"enable":false,"rotateX":600,"rotateY":600}}},"interactivity":{"detect_on":"canvas","events":{"onhover":{"enable":true,"mode":"bubble"},"onclick":{"enable":true,"mode":"repulse"},"resize":true},"modes":{"grab":{"distance":400,"line_linked":{"opacity":1}},"bubble":{"distance":250,"size":0,"duration":2,"opacity":0,"speed":3},"repulse":{"distance":400,"duration":0.4},"push":{"particles_nb":4},"remove":{"particles_nb":2}}},"retina_detect":true});var count_particles, stats, update; stats = new Stats; stats.setMode(0); stats.domElement.style.position = 'absolute'; stats.domElement.style.left = '0px'; stats.domElement.style.top = '0px'; document.body.appendChild(stats.domElement); count_particles = document.querySelector('.js-count-particles'); update = function() { stats.begin(); stats.end(); if (window.pJSDom[0].pJS.particles && window.pJSDom[0].pJS.particles.array) { count_particles.innerText = window.pJSDom[0].pJS.particles.array.length; } requestAnimationFrame(update); }; requestAnimationFrame(update);;
    } catch (e) {
        console.error('Particles.js failed to load:', e);
    }
});
    </script>