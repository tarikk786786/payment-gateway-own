<?php
// Modern Landing Page for UpiGateway
include "auth/config.php"; // to fetch brand logo if needed
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($site_settings['brand_name']) ? $site_settings['brand_name'] : 'UpiGateway'; ?> | Modern Payment Infrastructure</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Outfit:wght@400;700;900&display=swap" rel="stylesheet">
    
    <!-- Icons & Animations -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">

    <style>
        :root {
            --primary: #4F46E5;
            --secondary: #06B6D4;
        }
        body {
            font-family: 'Inter', sans-serif;
            background-color: #0f172a;
            color: #f8fafc;
            overflow-x: hidden;
        }
        h1, h2, h3, h4 {
            font-family: 'Outfit', sans-serif;
        }
        
        /* Modern Animated Background */
        .bg-gradient-animated {
            background: linear-gradient(-45deg, #0f172a, #1e1b4b, #0f172a, #082f49);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
        }
        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* Glassmorphism Classes */
        .glass-nav {
            background: rgba(15, 23, 42, 0.7);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }
        .glass-card {
            background: rgba(30, 41, 59, 0.4);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            transition: all 0.4s ease;
        }
        .glass-card:hover {
            transform: translateY(-5px);
            background: rgba(30, 41, 59, 0.6);
            border: 1px solid rgba(79, 70, 229, 0.3);
            box-shadow: 0 0 30px rgba(79, 70, 229, 0.2);
        }

        /* Glowing Text Utilities */
        .text-glow {
            text-shadow: 0 0 20px rgba(79, 70, 229, 0.5);
        }
        
        /* Floating Animation */
        .floating {
            animation: floating 3s ease-in-out infinite;
        }
        @keyframes floating {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-15px); }
            100% { transform: translateY(0px); }
        }
        
        /* Decorative Orbs */
        .orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            z-index: -1;
            opacity: 0.4;
        }
    </style>
</head>
<body class="bg-gradient-animated min-h-screen flex flex-col relative">

    <!-- Background Orbs -->
    <div class="orb bg-indigo-600 w-96 h-96 top-0 left-0 -translate-x-1/2 -translate-y-1/2"></div>
    <div class="orb bg-cyan-500 w-96 h-96 bottom-0 right-0 translate-x-1/3 translate-y-1/3"></div>
    <div class="orb bg-purple-600 w-80 h-80 top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2"></div>

    <!-- Navigation -->
    <nav class="fixed w-full z-50 glass-nav transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <div class="flex items-center gap-3">
                    <div class="bg-gradient-to-tr from-indigo-500 to-cyan-400 p-2 rounded-xl text-white">
                        <i class="fas fa-wallet text-2xl"></i>
                    </div>
                    <span class="font-outfit font-black text-2xl tracking-tight text-transparent bg-clip-text bg-gradient-to-r from-white to-gray-400">
                        <?php echo isset($site_settings['brand_name']) ? $site_settings['brand_name'] : 'UpiGateway'; ?>
                    </span>
                </div>
                
                <div class="hidden md:flex items-center space-x-8">
                    <a href="#features" class="text-sm font-medium text-gray-300 hover:text-white transition-colors">Features</a>
                    <a href="#security" class="text-sm font-medium text-gray-300 hover:text-white transition-colors">Security</a>
                    
                    <div class="flex items-center gap-4 border-l border-gray-700 pl-8">
                        <a href="auth/index.php" class="text-sm font-semibold text-white hover:text-indigo-300 transition-colors">
                            Sign In
                        </a>
                        <a href="Register.php" class="group relative px-6 py-2.5 font-semibold text-white text-sm rounded-full overflow-hidden bg-indigo-600 hover:bg-indigo-500 transition-all shadow-[0_0_20px_rgba(79,70,229,0.4)] hover:shadow-[0_0_30px_rgba(79,70,229,0.6)]">
                            <span class="relative z-10">Create Account</span>
                            <div class="absolute inset-0 h-full w-full bg-gradient-to-r from-indigo-600 to-cyan-500 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                        </a>
                    </div>
                </div>

                <!-- Mobile menu button -->
                <div class="md:hidden flex items-center">
                    <button class="text-gray-300 hover:text-white focus:outline-none">
                        <i class="fas fa-bars text-2xl"></i>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <main class="flex-grow pt-32 pb-20 px-4 sm:px-6 lg:px-8 relative z-10 flex flex-col justify-center items-center text-center min-h-[90vh]">
        <div class="max-w-4xl mx-auto animate__animated animate__fadeInUp">
            
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full glass-card border-indigo-500/30 mb-8">
                <span class="flex h-2 w-2 relative">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                </span>
                <span class="text-sm font-medium text-indigo-200">Gateway Systems 100% Operational</span>
            </div>

            <h1 class="text-5xl md:text-7xl font-black mb-6 leading-tight">
                Accept UPI Payments <br/>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 via-cyan-400 to-emerald-400 text-glow">
                    Without Limits.
                </span>
            </h1>
            
            <p class="text-lg md:text-xl text-gray-300 mb-10 max-w-2xl mx-auto font-light leading-relaxed">
                Experience the next generation of payment infrastructure. Instant settlements, zero hidden fees, and seamless developer integration for modern businesses.
            </p>

            <div class="flex flex-col sm:flex-row gap-5 justify-center items-center">
                <a href="Register.php" class="w-full sm:w-auto px-8 py-4 rounded-xl bg-white text-indigo-900 font-bold text-lg hover:bg-gray-100 transition-colors shadow-[0_0_20px_rgba(255,255,255,0.3)] hover:scale-105 transform duration-200 flex items-center justify-center gap-2">
                    Get Started Now <i class="fas fa-arrow-right"></i>
                </a>
                <a href="auth/index.php" class="w-full sm:w-auto px-8 py-4 rounded-xl glass-card font-semibold text-white text-lg hover:bg-white/10 transition-colors flex items-center justify-center gap-2">
                    <i class="fas fa-lock text-indigo-400"></i> Merchant Login
                </a>
            </div>

            <!-- Dashboard Preview Graphic -->
            <div class="mt-20 relative floating">
                <div class="absolute inset-0 bg-gradient-to-t from-[#0f172a] via-transparent to-transparent z-10"></div>
                <img src="https://ui-avatars.com/api/?name=Dashboard&background=1e293b&color=4f46e5&size=800&font-size=0.1&length=1" alt="Dashboard Preview" class="rounded-t-2xl shadow-[0_-20px_50px_rgba(79,70,229,0.15)] border-t border-l border-r border-indigo-500/20 opacity-80 mix-blend-screen hidden md:block w-full max-w-4xl mx-auto object-cover h-[300px]" style="background-image: linear-gradient(rgba(30, 41, 59, 0.9), rgba(30, 41, 59, 0.9)), repeating-linear-gradient(45deg, #4f46e5 0, #4f46e5 1px, transparent 0, transparent 50%); background-size: cover, 20px 20px;">
                
                <!-- Overlay Elements on Graphic -->
                <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 glass-card p-6 rounded-2xl flex items-center gap-4 w-72 md:flex hidden z-20">
                    <div class="w-12 h-12 rounded-full bg-green-500/20 flex items-center justify-center text-green-400 text-xl">
                        <i class="fas fa-check"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-400">Payment Received</p>
                        <p class="text-xl font-bold text-white">₹ 2,499.00</p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Features Section -->
    <section id="features" class="py-24 relative z-10 bg-[#0f172a]/50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-5xl font-black mb-4">Everything You Need.</h2>
                <p class="text-gray-400 max-w-2xl mx-auto">Built from the ground up to provide the fastest, most reliable payment experience for your business.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="glass-card p-8 rounded-3xl group">
                    <div class="w-14 h-14 rounded-2xl bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center text-indigo-400 text-2xl mb-6 group-hover:scale-110 transition-transform">
                        <i class="fas fa-bolt"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-3 text-white">Instant Settlements</h3>
                    <p class="text-gray-400 text-sm leading-relaxed">Say goodbye to waiting. Your funds are settled directly into your bank account instantly, keeping your cash flow positive.</p>
                </div>

                <!-- Feature 2 -->
                <div class="glass-card p-8 rounded-3xl group">
                    <div class="w-14 h-14 rounded-2xl bg-cyan-500/10 border border-cyan-500/20 flex items-center justify-center text-cyan-400 text-2xl mb-6 group-hover:scale-110 transition-transform">
                        <i class="fas fa-code"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-3 text-white">Developer Friendly</h3>
                    <p class="text-gray-400 text-sm leading-relaxed">Robust REST APIs and webhooks make integration a breeze. Start accepting payments in minutes, not days.</p>
                </div>

                <!-- Feature 3 -->
                <div class="glass-card p-8 rounded-3xl group">
                    <div class="w-14 h-14 rounded-2xl bg-purple-500/10 border border-purple-500/20 flex items-center justify-center text-purple-400 text-2xl mb-6 group-hover:scale-110 transition-transform">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-3 text-white">Bank-Grade Security</h3>
                    <p class="text-gray-400 text-sm leading-relaxed">Your transactions are protected by end-to-end 256-bit encryption and advanced fraud detection systems.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="border-t border-gray-800 bg-[#0b1121] py-12 relative z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row justify-between items-center gap-6">
            <div class="flex items-center gap-2">
                <i class="fas fa-wallet text-indigo-500 text-xl"></i>
                <span class="font-outfit font-bold text-xl text-white">UpiGateway</span>
            </div>
            <div class="text-gray-500 text-sm">
                &copy; <?php echo date('Y'); ?> UpiGateway Infrastructure. All rights reserved.
            </div>
            <div class="flex gap-4 text-gray-500">
                <a href="#" class="hover:text-white transition-colors"><i class="fab fa-twitter text-xl"></i></a>
                <a href="#" class="hover:text-white transition-colors"><i class="fab fa-github text-xl"></i></a>
                <a href="#" class="hover:text-white transition-colors"><i class="fab fa-discord text-xl"></i></a>
            </div>
        </div>
    </footer>

</body>
</html>
