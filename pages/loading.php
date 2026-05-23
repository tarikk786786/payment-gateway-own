<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Processing Payment</title>
    <style>
        /* General body styling for a clean, centered layout */
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f4f6f9;
            color: #495057;
            text-align: center;
        }

        /* Main container for the loading screen */
        .loading-container {
            padding: 3rem;
            border-radius: 1rem;
            background-color: #fff;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            display: flex;
            flex-direction: column;
            align-items: center;
            transition: opacity 0.5s ease-in-out;
        }

        /* Styles for the animated spinner */
        .spinner {
            width: 60px;
            height: 60px;
            border: 6px solid #e0e0e0;
            border-top: 6px solid #4a90e2;
            border-radius: 50%;
            animation: spin 1.2s linear infinite;
        }

        /* Keyframe animation for the spin effect */
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Styles for the payment status text */
        .status-message {
            margin-top: 2rem;
            font-size: 1.25rem;
            font-weight: 500;
        }

        /* A simple transition class to hide the screen */
        .hidden {
            opacity: 0;
            pointer-events: none;
        }
    </style>
</head>
<body>

<div id="loading-screen" class="loading-container">
    <div class="spinner"></div>
    <p class="status-message">Redirecting to Payment Gateway...</p>
</div>

<!-- Simple JavaScript to simulate the loading process and hide the screen -->
<script>
    const loadingScreen = document.getElementById('loading-screen');

    // Simulate a loading delay of 3 seconds
    setTimeout(() => {
        // You would replace this with your actual logic
        // For now, let's just update the message and hide the screen
        const statusMessage = loadingScreen.querySelector('.status-message');
        const spinner = loadingScreen.querySelector('.spinner');

        // Update the message for demonstration
        statusMessage.textContent = "Payment Successful!";
        spinner.style.borderTop = "6px solid #28a745"; // Change color for success

        // Add a class to hide the screen with a fade-out effect
        setTimeout(() => {
            loadingScreen.classList.add('hidden');
        }, 1000); // 1-second delay to show the success message
    }, 3000); // Main 3-second loading delay
</script>

</body>
</html>
