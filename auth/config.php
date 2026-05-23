<?php
// Security Headers (Helmet Equivalent)
header("X-Frame-Options: SAMEORIGIN");
header("X-XSS-Protection: 1; mode=block");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Strict-Transport-Security: max-age=31536000; includeSubDomains");

// Simple .env loader
$env_path = __DIR__ . '/../.env';
if (file_exists($env_path)) {
    $lines = file($env_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
                putenv(sprintf('%s=%s', $name, $value));
                $_ENV[$name] = $value;
                $_SERVER[$name] = $value;
            }
        }
    }
}

// error_reporting(E_ALL);
// ini_set("display_errors", true);

$db_host = getenv('DB_HOST') ?: 'localhost';
$db_user = getenv('DB_USER') ?: 'Tarik7-353033376eab';
$db_pass = getenv('DB_PASS') ?: 'Tarik@786';
$db_name = getenv('DB_NAME') ?: 'Tarik7-353033376eab';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
$server = $_SERVER["SERVER_NAME"];

// Fetch site settings from the database
$query = "SELECT * FROM site_settings LIMIT 1";
$result = mysqli_query($conn, $query);


if ($result && mysqli_num_rows($result) > 0) {
    $site_settings = mysqli_fetch_assoc($result);
} else {
    // Default values in case settings are not found
    $site_settings = [
        'brand_name' => 'Default Brand Name',
        'logo_url' => 'default_logo.png',
        'site_link' => 'https://chickenpox.in/',
        'whatsapp_number' => '9219565158',
        'copyright_text' => '© Default Copyright'
    ];
}
?>