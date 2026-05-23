<?php

if (isset($_COOKIE['token'])) {
    $tokan = $_COOKIE['token'];
    $branch = $_COOKIE['branch'];
} else {
    echo "Token expired or not set!";
}
?>

<?php
$order_id = $_GET['order_id'] ?? '';
// Initialize cURL session
$ch = curl_init();

// Set the URL
curl_setopt($ch, CURLOPT_URL, 'https://' . $_SERVER['HTTP_HOST'] . '/api/check-order-status');

// Set the HTTP request method to POST
curl_setopt($ch, CURLOPT_POST, 1);

// Set the headers
$headers = [
    'Content-Type: application/x-www-form-urlencoded'
];
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

// Set the data to be sent in the request
$data = http_build_query([
    'user_token' => $tokan,
    'order_id' => $order_id
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

// Return the response instead of outputting it
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Execute the request
$response = curl_exec($ch);

// Close cURL session
curl_close($ch);

// Output the response
// echo $response;


$data = json_decode($response, true);


$transaction_status = $data['result']['txnStatus'] ?? 'Unknown';
$amount = $data['result']['amount'] ?? '0';
$transaction_id = $data['result']['orderId'] ?? 'N/A';

$status_color = ($transaction_status === 'SUCCESS') ? 'text-green-500' : 'text-red-500';


if ($transaction_status === 'SUCCESS') {

switch ($branch) {
    case 101:
        $numbers = ["9876543210"];
        break;
    case 105:
        $numbers = ["9876595210"];
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
$costmor_ID = $data['result']['customer_mobile'] ?? 'N/A';
$orderId = $data['result']['orderId'] ?? 'N/A';
// Encode the message to handle any special characters



if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $utr_number = $_POST['utr_number'];
    $message = urlencode("Payment Request from *$costmor_ID* 
💠 Rs $amount 
💠 UTR : $utr_number
💠 orderId : $orderId
💠 branch : $branch
Plese Verify & send Points...
");

    
    foreach ($numbers as $number) {
        $url = "https://wa.smsorg.in/sendMessage.php?appkey=73034a03-5e29-4b547-8022-5992c71d27c3&authkey=0RRlxndolyJjhLv0u7DdX4ohhorxg9aiX1ggE5RE957ttja0Gk&to=91$number&message=$message";
        
        // Fetch the content from the URL
        $content = file_get_contents($url);
        
        // echo "Message sent to $number: $content<br>";
    }
    
    // Redirect to a new URL
    header("Location: index.php?branch=$branch");
    exit(); // Ensures that no further code is executed after the redirect

}

$message = urlencode("New Payment Recived from *$costmor_ID* 
💠 Rs $amount 
💠 orderId : $orderId
💠 branch : $branch
");
    foreach ($numbers as $number) {
        $url = "https://wa.smsorg.in/sendMessage.php?appkey=730554a03-3e29-4b47-8022-5992c71d27c3&authkey=0RRlxndolyJjhLv0u7DdX4ohggorxg9aiX1xmE5RE957ttja0Gk&to=91$number&message=$message";
        
        // Fetch the content from the URL
        $content = file_get_contents($url);
        
        // echo "Message sent to $number: $content<br>";
    }



// Output the response
// echo $content;
}
?>


<?php






?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UpiGateway™ - Transaction Result</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-lg shadow-md max-w-md w-full">
        <h1 class="text-2xl font-bold text-center text-blue-600 mb-6">UpiGateway™ Transaction Result</h1>
        
        <div class="mb-6">
            <p class="text-xl font-semibold mb-2">Status: <span class="<?php echo $status_color; ?>"><?php echo $transaction_status; ?></span></p>
            <p class="text-lg">Amount: ₹<?php echo $amount; ?></p>
            <p class="text-sm text-gray-600">Transaction ID: <?php echo $transaction_id; ?></p>
        </div>
<?php if ($transaction_status === 'SUCCESS') { ?>
    <div class="bg-white p-6 rounded shadow-md w-full max-w-sm">
        <h2 class="text-2xl font-bold mb-4">UTR Number Form</h2>
        <form action="" method="POST">
            <div class="mb-4">
                <label for="utr_number" class="block text-sm font-medium text-gray-700">Enter UTR Number:</label>
                <input type="text" id="utr_number" name="utr_number" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            </div>
            <button type="submit" class="w-full py-2 px-4 bg-indigo-600 text-white font-semibold rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Submit</button>
        </form>
    </div>
    <?php }?>
        <!--<div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6">-->
        <!--    <h2 class="font-bold text-lg mb-2">Why Choose UpiGateway™?</h2>-->
        <!--    <ul class="list-disc list-inside space-y-1 text-sm">-->
        <!--        <li>Fast & Secure UPI Payments</li>-->
        <!--        <li>Instant Payment Links</li>-->
        <!--        <li>Advanced Reporting & Analytics</li>-->
        <!--        <li>24/7 Customer Support</li>-->
        <!--        <li>Competitive Pricing: Only ₹199/month!</li>-->
        <!--    </ul>-->
        <!--</div>-->

        <div class="text-center">
            <a href="https://" . $_SERVER["HTTP_HOST"] . "/ClientsPages/amiri/index.php?branch=<?=$branch?>" class="inline-block bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700 transition duration-300">
                Make New Payment
            </a>

        </div>
    </div>
</body>
</html>

