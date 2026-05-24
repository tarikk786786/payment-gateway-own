<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
$db_host = getenv('DB_HOST') ?: '127.0.0.1';
$db_user = getenv('DB_USER') ?: 'Tarik7-353033376eab';
$db_pass = getenv('DB_PASS') ?: 'Tarik@786';
$db_name = getenv('DB_NAME') ?: 'Tarik7-353033376eab';

echo "Attempting to connect to \$db_host with user \$db_user...<br>";

echo password_hash('Tarik@786', PASSWORD_BCRYPT);
?>
