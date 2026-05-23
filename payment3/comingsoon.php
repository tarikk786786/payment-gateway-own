<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coming Soon</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Arial, sans-serif;
        }

        html, body {
            height: 100%;
            width: 100%;
            overflow: hidden;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            color: #ffffff;
        }

        .container {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            padding: 2rem;
            width: 90%;
            max-width: 700px;
            z-index: 2;
        }

        h1 {
            font-size: clamp(2.5em, 8vw, 4.5em);
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 0.5em;
            background: linear-gradient(to right, #00d4ff, #7b68ee);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            animation: fadeIn 1s ease-in;
        }

        p {
            font-size: clamp(1em, 3vw, 1.3em);
            line-height: 1.6;
            margin-bottom: 2em;
            color: #e0e0e0;
            animation: fadeIn 1s ease-in 0.3s backwards;
        }

        .animation-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 1;
        }

        .circle {
            position: absolute;
            background: radial-gradient(circle, rgba(0, 212, 255, 0.3), transparent);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
            pointer-events: none;
        }

        @keyframes float {
            0% {
                transform: translateY(100vh) scale(0.8);
                opacity: 0.7;
            }
            50% {
                opacity: 1;
            }
            100% {
                transform: translateY(-10vh) scale(1.2);
                opacity: 0;
            }
        }

        .btn-container {
            display: flex;
            justify-content: center;
            gap: 1.5rem;
            animation: fadeIn 1s ease-in 0.6s backwards;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-block;
            padding: 12px 30px;
            text-decoration: none;
            text-transform: uppercase;
            font-weight: 600;
            font-size: clamp(0.9em, 2vw, 1em);
            border-radius: 25px;
            transition: all 0.3s ease;
            letter-spacing: 1px;
            white-space: nowrap;
        }

        .btn-primary {
            background: #00d4ff;
            color: #1a1a2e;
        }

        .btn-primary:hover {
            background: #00b7dd;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 212, 255, 0.4);
        }

        .btn-secondary {
            background: transparent;
            color: #ffffff;
            border: 2px solid #7b68ee;
        }

        .btn-secondary:hover {
            background: #7b68ee;
            border-color: #7b68ee;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(123, 104, 238, 0.4);
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }
            .btn-container {
                flex-direction: column;
                gap: 1rem;
            }
        }

        @media (max-height: 500px) {
            .container {
                position: static;
                transform: none;
                padding: 1rem;
                height: 100%;
                display: flex;
                flex-direction: column;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="animation-container">
        <?php
        for ($i = 0; $i < 50; $i++) {
            $x = rand(0, 100);
            $y = rand(0, 100);
            $size = rand(20, 60);
            $delay = rand(0, 5);
            $duration = rand(4, 8);
            echo "<div class='circle' style='
                left: ${x}%;
                bottom: ${y}%;
                width: ${size}px;
                height: ${size}px;
                animation-delay: ${delay}s;
                animation-duration: ${duration}s;
            '></div>";
        }
        ?>
    </div>

    <div class="container">
        <h1>Coming Soon</h1>
        <p>This functionality is currently under development. Join our WhatsApp channel for real-time updates and be the first to know when it's ready!</p>
        <div class="btn-container">
            <a class="btn btn-primary" target="_blank" href="https://whatsapp.com/channel/0029Vas5C79Eawdhj46eCc1J">Join Channel</a><br>
            <a class="btn btn-secondary" href="index">Back to Login</a>
            <a class="btn btn-secondary" href="index">Go to Dashboard</a>
        </div>
    </div>

    <?php include "footer.php"; ?>
</body>
</html>