<?php
// Get the current URL base dynamically
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$base_url = $protocol . $_SERVER['HTTP_HOST'];

// List of URLs with dynamic base URL
$urls = [
    $base_url . "/crons/cron.php",
    $base_url . "/crons/cron2.php",
    $base_url . "/crons/cron3.php",
    $base_url . "/crons/cron4.php",
    // $base_url . "/crons/cron5.php",
    $base_url . "/crons/cron6.php",
    $base_url . "/crons/amazonsession.php",
];

// Function to access a URL
function accessUrl($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($response === false || $http_code != 200) {
        echo "<div style='margin-bottom: 20px;'>";
        echo "<strong>Failed to access $url.</strong><br>";
        echo "HTTP code: $http_code.<br>";
        echo "Error: " . curl_error($ch) . "<br>";
        echo "</div>";
    } else {
        echo "<div style='margin-bottom: 20px;'>";
        echo "<strong>Successfully accessed $url.</strong><br>";
        echo "HTTP code: $http_code.<br>";
        echo "Response:<br><pre>" . json_encode(json_decode($response), JSON_PRETTY_PRINT) . "</pre><br>";
        echo "</div>";
    }

    curl_close($ch);
}

// Access each URL
foreach ($urls as $url) {
    accessUrl($url);
}
?>
