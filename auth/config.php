<?php
if (!defined('CONFIG_INCLUDED')) {
    define('CONFIG_INCLUDED', true);

    // Security Headers (Helmet Equivalent)
    if (!headers_sent()) {
        header("X-Frame-Options: SAMEORIGIN");
        header("X-XSS-Protection: 1; mode=block");
        header("X-Content-Type-Options: nosniff");
        header("Referrer-Policy: strict-origin-when-cross-origin");
        header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
    }

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

    error_reporting(E_ALL);
    ini_set("display_errors", true);

    $db_host = getenv('DB_HOST') ?: '127.0.0.1';
    $db_user = getenv('DB_USER') ?: 'Tarik7-353033376eab';
    $db_pass = getenv('DB_PASS') ?: 'Tarik@786';
    $db_name = getenv('DB_NAME') ?: 'Tarik7-353033376eab';

    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    $server = $_SERVER["SERVER_NAME"];

    // Auto-migrate missing columns to prevent fatal errors on fresh deployments
    if ($conn && !$conn->connect_error) {
        $migrations = [
            "ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `kycstatus` enum('Verified','Pending','Rejected') NOT NULL DEFAULT 'Pending'",
            "ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `login_token` varchar(255) DEFAULT NULL",
            "ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `create_date` datetime DEFAULT current_timestamp()",
            "ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `referred_by` int(11) DEFAULT NULL",
            "ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `Intent_unable` tinyint(1) DEFAULT 1",
            "ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `merchantRouting` tinyint(1) DEFAULT 0",
            "ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `telegram_subscribed` varchar(3) DEFAULT 'off'",
            "ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `telegram_chat_id` varchar(25) DEFAULT NULL",
            "ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `telegram_username` varchar(25) DEFAULT NULL",
            "ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `instance_id` varchar(255) DEFAULT NULL",
            "ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `instance_secret` varchar(255) DEFAULT NULL",
            "ALTER TABLE `user_checkout_settings` ADD COLUMN IF NOT EXISTS `news` text DEFAULT ''",
            // Performance indexes - run IF NOT EXISTS to be safe on existing DBs
            "ALTER TABLE `orders` ADD INDEX IF NOT EXISTS `idx_orders_user_id` (`user_id`)",
            "ALTER TABLE `orders` ADD INDEX IF NOT EXISTS `idx_orders_status` (`status`)",
            "ALTER TABLE `orders` ADD INDEX IF NOT EXISTS `idx_orders_user_token` (`user_token`(64))",
            "ALTER TABLE `users` ADD INDEX IF NOT EXISTS `idx_users_mobile` (`mobile`)",
            "ALTER TABLE `users` ADD INDEX IF NOT EXISTS `idx_users_user_token` (`user_token`(32))",
            "ALTER TABLE `user_ips` ADD INDEX IF NOT EXISTS `idx_user_ips_user_id` (`user_id`)",
        ];
        foreach ($migrations as $sql) {
            @$conn->query($sql);
        }
    }


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
            'whatsapp_number' => '9114411026',
            'copyright_text' => '© Default Copyright'
        ];
    }
}
?>