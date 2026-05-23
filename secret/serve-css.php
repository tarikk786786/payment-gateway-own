<?php
// Token ko URL se nikaalein
$token = $_GET['token'] ?? '';

// Apna secret token define karein
$secret_token = 'window.history.replaceState';

// Token ko check karein
if ($token !== $secret_token) {
    header('HTTP/1.0 403 Forbidden');
    echo 'Aapko is file ko access karne ki anumati nahi hai.';
    exit;
}

// File ka naam URL se nikaalein (e.g., ?file=main ya ?file=bootstrap)
$file_param = $_GET['file'] ?? '';

// CSS files ka mapping ya condition banaayein
if ($file_param === 'main') {
    $file = realpath('../../Youtube/Dashboard/main.min.css');
} elseif ($file_param === 'second') {
    $file = realpath('../../Youtube/Dashboard/main.css');
} elseif ($file_param === 'bootstrap') {
    $file = realpath('../../Youtube/Dashboard/bootstrap.min.css');
} else {
    header('HTTP/1.0 404 Not Found');
    echo 'CSS file nahi mili.';
    exit;
}

// Debugging ke liye print karein $file ka value
var_dump($file);

// Check karein ki file exist karti hai ya nahi
if (!$file || !file_exists($file)) {
    header('HTTP/1.0 404 Not Found');
    echo 'CSS file nahi mil rahi hai.';
    exit;
}

// Correct content type set karein CSS ke liye
header('Content-Type: text/css');

// CSS file ko output karein
readfile($file);
exit;
?>
