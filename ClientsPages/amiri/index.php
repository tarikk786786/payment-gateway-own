<?php
$error_message = '';
$success_message = '';

$branch = $_GET['branch'];

switch ($branch) {
    case 101:
        $token = "b7b04fd33fdb54a4ae727e72039c08a3";
        break;
    case 105:
        $token = "8a5df7ea32509af794c91363e4d090f3";
        break;
    // case 103:
    //     $token = "token_for_103";
    //     break;
    // case 104:
    //     $token = "token_for_104";
    //     break;
    // case 105:
    //     $token = "token_for_105";
    //     break;
    // case 106:
    //     $token = "token_for_106";
    //     break;
    // case 107:
    //     $token = "token_for_107";
    //     break;
    // case 108:
    //     $token = "token_for_108";
    //     break;
    // case 109:
    //     $token = "token_for_109";
    //     break;
    // case 110:
    //     $token = "token_for_110";
    //     break;
    default:
        $token = "invalid_branch";
    echo("invalid Branch");
    exit;
}


setcookie("token", $token, time() + 300, "/"); // 300 सेकंड = 5 मिनट
setcookie("branch", $branch, time() + 300, "/");
// echo "Token stored in session: " . $_SESSION['token'];



if (isset($_POST['upiapi'])) {
    $api_url = 'https://' . $_SERVER['HTTP_HOST'] . '/order/create';

    $txn_amount = $_POST['txn_amount'];
    $userId = $_POST['userId'];
    $txn_note = $branch;
    $customer_name = $_POST['customer_name'];
    $customer_mobile = $_POST['customer_mobile'];
    $customer_email = "demo@chickenpox.in";
    $randomOrderId = time().'XXXX'.$userId;

    $data = [
        "token" => $token, // Replace with your API TOKEN
        "order_id" => $randomOrderId,
        "txn_amount" => $txn_amount,
        "txn_note" => $txn_note,
        "product_name" => "UpiGateway Subscription",
        "customer_name" => $customer_name,
        "customer_mobile" => $customer_mobile,
        "customer_email" => $customer_email,
        "redirect_url" => "https://" . $_SERVER["HTTP_HOST"] . "/ClientsPages/amiri/callback.php?order_id=$randomOrderId"
    ];

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $api_url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
        ],
    ]);

    $response = curl_exec($curl);
    $error = curl_error($curl);
    curl_close($curl);

    if ($error) {
        $error_message = 'Unable to connect to UpiGateway. Please try again later.';
    } else {
        $result = json_decode($response, true);
        if ($result['status'] == "true") {
            header("Location: " . $result['results']['payment_url']);
            exit();
        } else {
            $error_message = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UpiGateway | Secure Checkout</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%);
            min-height: 100vh;
            color: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .glass-card {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            border-radius: 1.5rem;
        }
    </style>
</head>
<body class="p-4">
    <div class="glass-card w-full max-w-md p-8 relative overflow-hidden">
        <!-- Decorative Glow -->
        <div class="absolute top-0 right-0 -mt-10 -mr-10 w-40 h-40 bg-blue-500 opacity-20 rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 left-0 -mb-10 -ml-10 w-40 h-40 bg-indigo-500 opacity-20 rounded-full blur-3xl"></div>

        <div class="text-center mb-8 relative z-10">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gradient-to-r from-blue-600 to-indigo-600 mb-4 shadow-lg">
                <i class="fas fa-lock text-2xl text-white"></i>
            </div>
            <h1 class="text-3xl font-extrabold text-white">Secure Checkout</h1>
            <p class="text-gray-400 mt-2">Instant UPI Payments Powered by UpiGateway</p>
        </div>

        <?php if ($error_message): ?>
            <div class="bg-red-500 bg-opacity-20 border border-red-500 text-red-100 px-4 py-3 rounded-xl mb-6 relative z-10 flex items-center">
                <i class="fas fa-exclamation-circle mr-2 text-red-400"></i>
                <p><?php echo $error_message; ?></p>
            </div>
        <?php endif; ?>

        <form method="post" action="" class="space-y-6 relative z-10">
            <div>
                <label for="txn_amount" class="block text-sm font-medium text-gray-300 mb-1">Enter Amount (₹)</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <span class="text-gray-400">₹</span>
                    </div>
                    <input type="number" id="txn_amount" name="txn_amount" min="100" placeholder="Minimum 100" required
                           class="w-full bg-gray-800 bg-opacity-50 text-white border border-gray-600 rounded-xl pl-8 pr-4 py-3 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors">
                </div>
            </div>

            <div>
                <label for="userId" class="block text-sm font-medium text-gray-300 mb-1">Active User ID</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <i class="fas fa-user text-gray-400"></i>
                    </div>
                    <input type="text" id="userId" name="userId" placeholder="Enter User ID" required
                           class="w-full bg-gray-800 bg-opacity-50 text-white border border-gray-600 rounded-xl pl-10 pr-4 py-3 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors">
                </div>
            </div>

            <div hidden>
                <input type="text" id="customer_mobile" value="8375977385" name="customer_mobile" length="10" required>
            </div>

            <button name="upiapi" type="submit" id="payBtn"
                    class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-bold py-4 rounded-xl shadow-lg transition-all transform hover:-translate-y-1 mt-4">
                Pay Securely
            </button>
        </form>

        <div class="mt-8 text-center border-t border-gray-700 pt-6 relative z-10">
            <div class="flex justify-center space-x-4 mb-4 opacity-70">
                <i class="fab fa-cc-visa text-2xl text-gray-400"></i>
                <i class="fab fa-cc-mastercard text-2xl text-gray-400"></i>
                <i class="fas fa-rupee-sign text-2xl text-gray-400 border border-gray-400 rounded-full w-8 h-8 flex items-center justify-center"></i>
            </div>
            <p class="text-sm text-gray-500">
                <i class="fas fa-shield-alt mr-1"></i> 256-bit Encrypted Checkout
            </p>
        </div>
    </div>

    <script>
        document.querySelector('form').addEventListener('submit', function() {
            if(this.checkValidity()) {
                const btn = document.getElementById('payBtn');
                btn.innerHTML = '<i class="fas fa-circle-notch fa-spin mr-2"></i> Initializing Gateway...';
                btn.style.opacity = '0.8';
                btn.style.pointerEvents = 'none';
            }
        });
    </script>
</body>
</html>

