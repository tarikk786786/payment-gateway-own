<?php
// Timezone set करें ताकि सही टाइम मिले
date_default_timezone_set('Asia/Kolkata');

$logFile = 'data.log';

$input = [
    'datetime' => date('Y-m-d H:i:s'),
    'ip' => $_SERVER['REMOTE_ADDR'],
    // 'method' => $_SERVER['REQUEST_METHOD'],
    'get' => $_GET,
    'post' => $_POST,
    // 'raw_input' => file_get_contents('php://input')
];
$logEntry = json_encode($input, JSON_PRETTY_PRINT);
file_put_contents($logFile, $logEntry . "\n\n", FILE_APPEND);

// ऑप्शनल - कंफर्मेशन मैसेज दिखाएं
echo "Data logged successfully.";

$msgFrom = $_GET['api_key'];
$massage = $_GET['massage'];
$token = $_GET['massage'];
?>
