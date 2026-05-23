
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction Details</title>
    <style>
        table {
            width: 50%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 10px;
            text-align: left;
        }
    </style>
</head>
<body>

<h2>Transaction Details</h2>

<?php
require_once("auth/components/main.components.php");
// API endpoint URL
$url = "https://" . $_SERVER["HTTP_HOST"] . "/order/status";

// JSON payload
$data = json_encode([
    "token" => "b7b04fd33fdb54a4ae727e72039c08a3",
    "order_id" => "ORD1704180733951325"
]);

// Initialize cURL session
$ch = curl_init($url);

// Set cURL options
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Content-Length: ' . strlen($data)
]);

// Execute cURL session
$response = curl_exec($ch);

// Check for cURL errors
if (curl_errno($ch)) {
    echo 'Curl error: ' . curl_error($ch);
}

// Close cURL session
curl_close($ch);

// Decode JSON response
$data = json_decode($response, true);

// Check if status is true
if ($data['status']) {
    // Display transaction details in a table
    echo '<table>';
    foreach ($data['results'] as $key => $value) {
        echo '<tr>';
        echo '<td>' . ucwords(str_replace('_', ' ', $key)) . '</td>';
        echo '<td>' . $value . '</td>';
        echo '</tr>';
    }
    echo '</table>';
} else {
    // Display an error message if status is false
    echo '<p>Error retrieving transaction details.</p>';
}
?>

</body>
</html>
