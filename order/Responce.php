<?php
// response.php
date_default_timezone_set("Asia/Kolkata");
// Log function
function logToFile($message) {
    $logFile = 'debug.log'; // File ka naam
    $currentDate = date('Y-m-d H:i:s'); // Current date and time
    $messageToLog = "[$currentDate] $message\n"; // Format message
    file_put_contents($logFile, $messageToLog, FILE_APPEND); // Append message to log file
}

// Initialize an array to hold all received data
$dataReceived = [];

// Check if there is any input (GET or POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Store POST data
    $dataReceived = $_POST;
    // Log POST data
    logToFile("Received POST data: " . json_encode($dataReceived));
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Store GET data
    $dataReceived = $_GET;
    // Log GET data
    logToFile("Received GET data: " . json_encode($dataReceived));
}

// Output the received data as JSON
header('Content-Type: application/json');
echo json_encode($dataReceived);
?>
