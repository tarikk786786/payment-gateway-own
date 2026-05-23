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
    $api_url = 'https://chickenpox.in/order/create';

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
        "redirect_url" => "https://chickenpox.in/ClientsPages/amiri/callback.php?order_id=$randomOrderId"
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
    <title>UpiGateway™ - Simplifying Digital Payments</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        body {
            animation: fadeIn 1s ease-in-out;
            background-image: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center px-4">
    <div class="bg-white rounded-lg shadow-xl p-8 max-w-md w-full">
        <h1 class="text-3xl font-bold text-center text-blue-600 mb-6">Welcome to AMIRI</h1>

        <?php if ($error_message): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                <p><?php echo $error_message; ?></p>
            </div>
        <?php endif; ?>

        <form method="post" action="" class="space-y-4">
            <div>
                <label for="txn_amount" class="block text-sm font-medium text-gray-700">Enter Amount:</label>
                <input type="number" id="txn_amount" name="txn_amount" min="100" placeholder="Minimum ₹100" required
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
            </div>
            <!--<div>-->
            <!--    <label for="customer_name" class="block text-sm font-medium text-gray-700">Customer Name:</label>-->
            <!--    <input type="text" id="customer_name" name="customer_name" placeholder="Your Full Name" required-->
            <!--           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">-->
            <!--</div>-->
            <div>
                <label for="customer_mobile" class="block text-sm font-medium text-gray-700">Active User ID:</label>
                <input type="text" id="userId" name="userId" placeholder="Enter User ID" required
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div hidden>
                <label for="customer_mobile" class="block text-sm font-medium text-gray-700">Mobile Number</label>
                <input type="text" id="customer_mobile" value="8375977385" name="customer_mobile" placeholder="Enter Mobile number" length="10" required
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
            </div>
            <button name="upiapi" type="submit" 
                    class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-150 ease-in-out">
                Pay Now
            </button>
        </form>
        
        <!--    <div class="bg-blue-100 text-blue-700 p-4 rounded-lg mb-6">-->
        <!--    <h2 class="font-bold text-lg mb-2">🚀 New Features:</h2>-->
        <!--    <ul class="list-disc list-inside space-y-1">-->
        <!--        <li>📃 Advanced Easy Documentation</li>-->
        <!--        <li>😎 3 Transaction Clear Options : <br> Status Check , Callback ,Make susses </li>-->
        <!--        <li>🆕 UPI QR Payment Gateway</li>-->
        <!--        <li>🔗 Instant Payment Links</li>-->
        <!--        <li>🏅 Advanced Reports Filters</li>-->
        <!--        <li>📊 Export Data (CSV, PDF, Excel)</li>-->
        <!--        <li>🔐 Secure Login & Data Protection</li>-->
        <!--        <li>⚡ GPay  & Paytm Intent Support</li>-->
        <!--    </ul>-->
        <!--    <p class="mt-2 font-semibold">🫰 Only ₹199/month | 7️⃣ Connect 7 Working Merchants!</p>-->
        <!--</div>-->

        <footer class="mt-8 text-center text-sm text-gray-500">
            Powered by <a href="https://chickenpox.in" class="text-blue-600 hover:underline">UpiGateway™</a> - Simplifying Digital Payments<br>
            
        </footer>
    </div>
</body>
</html>

