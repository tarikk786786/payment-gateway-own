<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
$db_host = getenv('DB_HOST') ?: '127.0.0.1';
$db_user = getenv('DB_USER') ?: 'Tarik7-353033376eab';
$db_pass = getenv('DB_PASS') ?: 'Tarik@786';
$db_name = getenv('DB_NAME') ?: 'Tarik7-353033376eab';

echo "Attempting to connect to \$db_host with user \$db_user...<br>";

mysqli_report(MYSQLI_REPORT_STRICT | MYSQLI_REPORT_ALL);

try {
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    echo "Connected successfully!";
} catch (Exception $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>
