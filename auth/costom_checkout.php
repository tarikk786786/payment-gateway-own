<?php
include "header.php";

// Ensure database connection is available
// Assuming $conn is established in header.php. If not, include connection details here.
if (!isset($conn) || !$conn) {
    die("Database connection not established.");
}

// Ensure userdata is available and contains necessary keys
if (!isset($userdata['id']) || !isset($userdata['vip_expiry']) || !isset($userdata['role'])) {
    // Handle case where user data is incomplete, maybe redirect to login or show error
    die("User data not fully loaded. Please log in again.");
}

$user_id = $userdata["id"];
$vipExpiryDate = $userdata['vip_expiry'];
$currentDate = date('Y-m-d');

// VIP प्लान की वैधता की जांच करें
$vipActive = ($vipExpiryDate >= $currentDate);

// If VIP plan is expired, reset VIP-only settings
if (!$vipActive) {
    $query = "UPDATE user_checkout_settings
              SET remove_branding = 0, display_header_footer = 1, display_loading_screen = 1
              WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        if (!mysqli_stmt_execute($stmt)) {
            error_log("Error resetting VIP settings for user $user_id: " . mysqli_error($conn));
            // Consider user-friendly error message, but not die() for background operations
        }
        mysqli_stmt_close($stmt);
    } else {
        error_log("Error preparing VIP reset statement: " . mysqli_error($conn));
    }
}

// Handle Admin Bulk Update
if ($userdata['role'] == 'Admin' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_update'])) {
    $fields = [];
    $values = [];
    $types = "";

    $field_mappings = [
        'show_qr' => ['name' => 'admin_toggleQr', 'type' => 'i', 'value' => isset($_POST['admin_toggleQr']) ? 1 : 0],
        'show_paytmButton' => ['name' => 'admin_togglePaytmButtons', 'type' => 'i', 'value' => isset($_POST['admin_togglePaytmButtons']) ? 1 : 0],
        'show_help' => ['name' => 'admin_toggleHelp', 'type' => 'i', 'value' => isset($_POST['admin_toggleHelp']) ? 1 : 0],
        'Show_GpayButton' => ['name' => 'admin_toggleGpay', 'type' => 'i', 'value' => isset($_POST['admin_toggleGpay']) ? 1 : 0],
        'show_phonepe' => ['name' => 'admin_phonepe', 'type' => 'i', 'value' => isset($_POST['admin_phonepe']) ? 1 : 0], // Corrected name for PhonePe
        'show_payment_logos' => ['name' => 'admin_showPaymentLogos', 'type' => 'i', 'value' => isset($_POST['admin_showPaymentLogos']) ? 1 : 0],
        'Show_IntentButton' => ['name' => 'admin_toggleIntent', 'type' => 'i', 'value' => isset($_POST['admin_toggleIntent']) ? 1 : 0],
        'show_upiRequest' => ['name' => 'admin_toggleUpiRequest', 'type' => 'i', 'value' => isset($_POST['admin_toggleUpiRequest']) ? 1 : 0],
        'show_download_qr' => ['name' => 'admin_toggleDownload', 'type' => 'i', 'value' => isset($_POST['admin_toggleDownload']) ? 1 : 0],
        'headerColor' => ['name' => 'admin_headerColor', 'type' => 's', 'value' => isset($_POST['admin_headerColor']) && preg_match('/^#[0-9A-Fa-f]{6}$/', $_POST['admin_headerColor']) ? $_POST['admin_headerColor'] : '#ffffff'],
        'bodyColor' => ['name' => 'admin_bodyColor', 'type' => 's', 'value' => isset($_POST['admin_bodyColor']) && preg_match('/^#[0-9A-Fa-f]{6}$/', $_POST['admin_bodyColor']) ? $_POST['admin_bodyColor'] : '#ffffff'],
        'remove_branding' => ['name' => 'admin_removeBranding', 'type' => 'i', 'value' => isset($_POST['admin_removeBranding']) ? 1 : 0],
        'display_header_footer' => ['name' => 'admin_displayHeaderFooter', 'type' => 'i', 'value' => isset($_POST['admin_displayHeaderFooter']) ? 1 : 0],
        'display_loading_screen' => ['name' => 'admin_displayLoadingScreen', 'type' => 'i', 'value' => isset($_POST['admin_displayLoadingScreen']) ? 1 : 0],
        'news' => ['name' => 'admin_news', 'type' => 's', 'value' => isset($_POST['admin_news']) ? $_POST['admin_news'] : ''],
    ];

    foreach ($field_mappings as $db_field => &$field) { // Use & to modify array in place
        if (isset($_POST['update_field']) && in_array($db_field, $_POST['update_field'])) {
            // Apply sanitization for 'news' field
if ($db_field === 'news') {
    $sanitized_news = $field['value'];
    // 2. Remove all HTML and PHP tags. This is generally the safest first step for plain text.
    // strip_tags() is UTF-8 aware if your PHP internal encoding is set correctly or multibyte string functions are enabled.
    $sanitized_news = strip_tags($sanitized_news);

    // 3. Remove script tags (redundant after strip_tags, but good for explicit safety)
    $sanitized_news = preg_replace('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/i', '', $sanitized_news);

    // 4. Remove 'on' event attributes (e.g., onclick, onerror).
    $sanitized_news = preg_replace('/on\w+="[^"]*"/i', '', $sanitized_news);
    // 5. Sanitize specific problematic characters or control characters that aren't visible
    // and might interfere with storage or display.
    // It's generally safe and won't affect visible characters like Hindi.
    $sanitized_news = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/u', '', $sanitized_news);

    $field['value'] = $sanitized_news;
}

echo $sanitized_news;
            $fields[] = "$db_field = ?";
            $values[] = $field['value'];
            $types .= $field['type'];
        }
    }
    unset($field); // Unset the reference after the loop

    if (!empty($fields)) {
        $query = "UPDATE user_checkout_settings SET " . implode(", ", $fields);
        $stmt = mysqli_prepare($conn, $query);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, $types, ...$values);
            if (!mysqli_stmt_execute($stmt)) {
                die("Error updating all users' settings: " . mysqli_error($conn));
            }
            mysqli_stmt_close($stmt);
        } else {
            die("Error preparing statement: " . mysqli_error($conn));
        }

        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Selected Settings Updated!',
                text: 'Selected checkout page settings for all users have been saved successfully.',
                confirmButtonText: 'OK'
            }).then(() => { window.location.reload(); });
        </script>";
    } else {
        echo "<script>
            Swal.fire({
                icon: 'warning',
                title: 'No Fields Selected!',
                text: 'Please select at least one field to update.',
                confirmButtonText: 'OK'
            });
        </script>";
    }
}

// Fetch existing settings for the current user
$query = "SELECT * FROM user_checkout_settings WHERE user_id = ?";
$stmt = mysqli_prepare($conn, $query);
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $settings = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
} else {
    die("Error preparing statement to fetch user settings: " . mysqli_error($conn));
}


if (!$settings) {
    // Insert default settings if none exist
    $query = "INSERT INTO user_checkout_settings (
        user_id, theme, show_qr, show_paytmButton, show_help, remove_branding,
        show_payment_logos, Show_IntentButton, show_upiRequest, show_download_qr,
        Show_GpayButton, show_phonepe, headerColor, bodyColor, display_header_footer, display_loading_screen, news
    ) VALUES (?, 1, 1, 1, 1, 0, 1, 0, 1, 1, 1, 1, '#c800b2', '#ffffff', 1, 1, '')";
    $stmt = mysqli_prepare($conn, $query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    } else {
        die("Error preparing insert statement for default settings: " . mysqli_error($conn));
    }

    $settings = [
        'theme' => 1,
        'show_qr' => 1,
        'show_paytmButton' => 1,
        'show_help' => 1,
        'remove_branding' => 0,
        'show_payment_logos' => 1,
        'Show_IntentButton' => 0,
        'show_upiRequest' => 1,
        'show_download_qr' => 1,
        'Show_GpayButton' => 1,
        'show_phonepe' => 1,
        'headerColor' => '#c800b2',
        'bodyColor' => '#ffffff',
        'display_header_footer' => 1,
        'display_loading_screen' => 1,
        'news' => ''
    ];
}

// Initialize variables with database values, ensuring they are always set
$themeSelect = $settings['theme'] ?? 1;
$showQr = $settings['show_qr'] ?? 1;
$showPaytmButton = $settings['show_paytmButton'] ?? 1;
$showHelp = $settings['show_help'] ?? 1;
// VIP-only settings, initialized based on DB and then potentially overridden if VIP active
$removeBranding = $settings['remove_branding'] ?? 0;
$displayHeaderFooter = $settings['display_header_footer'] ?? 1;
$displayLoadingScreen = $settings['display_loading_screen'] ?? 1;

$showPaymentLogos = $settings['show_payment_logos'] ?? 1;
$showIntentButton = $settings['Show_IntentButton'] ?? 0;
$showUpiRequest = $settings['show_upiRequest'] ?? 1;
$showDownloadQr = $settings['show_download_qr'] ?? 1;
$showGpayButton = $settings['Show_GpayButton'] ?? 1;
$show_phonepe = $settings['show_phonepe'] ?? 1;
$headerColor = $settings['headerColor'] ?? '#c800b2';
$bodyColor = $settings['bodyColor'] ?? '#ffffff';
$news = $settings['news'] ?? ''; // Initialize 'news' from DB

// Process individual user form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['admin_update'])) {
    $expiry = $userdata['expiry']; // डेटाबेस से एक्सपायरी डेट लें
    $expiry_timestamp = strtotime($expiry); // Expiry date को timestamp में बदलें
    $today = strtotime(date('Y-m-d')); // आज की तारीख का timestamp

    // **Check limits**
    if ($expiry_timestamp < $today) { // यदि expiry date आज से पहले की है तो प्लान एक्सपायर है
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Plan Expired!',
                text: 'Your plan expired on: " . date('d M, Y', $expiry_timestamp) . ". Please renew your plan to Access.',
                confirmButtonText: 'OK'
            }).then(() => { window.location.href='subscription'; });
        </script>";
        exit;
    }

    // Process form inputs
    $themeSelect = $_POST['themeSelect'] ?? 1; // Default to 1 if not set
    $showQr = isset($_POST['toggleQr']) ? 1 : 0;
    $showPaytmButton = isset($_POST['togglePaytmButtons']) ? 1 : 0;
    $showHelp = isset($_POST['toggleHelp']) ? 1 : 0;
    $showGpayButton = isset($_POST['toggleGpay']) ? 1 : 0;
    $show_phonepe = isset($_POST['togglePhonepe']) ? 1 : 0;
    $showPaymentLogos = isset($_POST['showPaymentLogos']) ? 1 : 0;
    $showIntentButton = isset($_POST['toggleIntent']) ? 1 : 0;
    $showUpiRequest = isset($_POST['toggleUpiRequest']) ? 1 : 0;
    $showDownloadQr = isset($_POST['toggleDownload']) ? 1 : 0;
    $headerColor = isset($_POST['headerColor']) && preg_match('/^#[0-9A-Fa-f]{6}$/', $_POST['headerColor']) ? $_POST['headerColor'] : '#c800b2'; // Default to original if invalid
    $bodyColor = isset($_POST['bodyColor']) && preg_match('/^#[0-9A-Fa-f]{6}$/', $_POST['bodyColor']) ? $_POST['bodyColor'] : '#ffffff'; // Default to original if invalid

// --- Start Backend Sanitization for 'news' field (User Submission) ---

$news = $_POST['news'] ?? ''; // Get news input. Using null coalescing operator for safety.

// 1. Remove HTML tags. This is generally the first line of defense against XSS.
// strip_tags is safe for UTF-8 if your PHP environment is configured correctly.
$news = strip_tags($news);

// 2. Remove script tags (redundant after strip_tags, but good for explicit safety,
// though a robust strip_tags should handle this).
$news = preg_replace('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/i', '', $news);

// 3. Remove 'on' event attributes (e.g., onclick, onerror) which are common XSS vectors.
$news = preg_replace('/on\w+="[^"]*"/i', '', $news);

// 4. Sanitize to remove *only* problematic non-printable control characters.
// This new regex removes ASCII control characters (0x00-0x1F, excluding common whitespace like tab, newline, carriage return)
// that can interfere with storage or display, but it *will not touch* visible characters like Hindi,
// other Unicode letters, numbers, punctuation, or most emojis. The 'u' modifier ensures UTF-8 handling.
$news = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/u', '', $news);

// --- End Backend Sanitization for 'news' field ---

// Now, $news contains the sanitized value ready for use or database insertion.
// echo $news; // For debugging, you can echo it here

    
    // --- End Backend Sanitization for 'news' field ---

    // VIP-only fields (only update if user is VIP)
    $removeBranding = $vipActive && isset($_POST['removeBranding']) ? 1 : 0;
    $displayHeaderFooter = $vipActive && isset($_POST['displayHeaderFooter']) ? 1 : 0;
    $displayLoadingScreen = $vipActive && isset($_POST['displayLoadingScreen']) ? 1 : 0;

    // Update existing record
    if ($settings) {
        $query = "UPDATE user_checkout_settings
                    SET theme = ?, show_qr = ?, show_paytmButton = ?, show_help = ?, remove_branding = ?,
                        show_payment_logos = ?, Show_IntentButton = ?, show_upiRequest = ?,
                        show_download_qr = ?, Show_GpayButton = ?, show_phonepe = ?, headerColor = ?, bodyColor = ?,
                        display_header_footer = ?, display_loading_screen = ?, news = ?
                    WHERE user_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "iiiiiiiiiiisssisi", $themeSelect,
                $showQr, $showPaytmButton, $showHelp, $removeBranding,
                $showPaymentLogos, $showIntentButton, $showUpiRequest,
                $showDownloadQr, $showGpayButton, $show_phonepe, $headerColor, $bodyColor,
                $displayHeaderFooter, $displayLoadingScreen, $news, $user_id
            );
            if (!mysqli_stmt_execute($stmt)) {
                die("Error updating settings: " . mysqli_error($conn));
            }
            mysqli_stmt_close($stmt);
        } else {
            die("Error preparing statement for user update: " . mysqli_error($conn));
        }
    }

    echo "<script>
        Swal.fire({
            icon: 'success',
            title: 'Settings Updated!',
            text: 'Your checkout page settings have been saved successfully.',
            confirmButtonText: 'OK'
        }).then(() => { window.location.reload(); });
    </script>";
}

// Close the connection (Moved to the very end of the script if no more database operations are expected)
// mysqli_close($conn); // Keep this commented if more PHP code follows that needs $conn

?>

<head>
    <style>
        .container { max-width: 100%; margin: 40px 0; }
        .card { border: none; border-radius: 15px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); overflow: hidden; }
        .card-header { background: linear-gradient(135deg, #007bff, #00b4db); padding: 20px; border-radius: 15px 15px 0 0; }
        .card-title { margin: 0; font-size: 24px; font-weight: 600; color: #fff; }
        .card-body { padding: 30px; background: #fff; }
        .form-section { background: #f8f9fa; padding: 20px; border-radius: 10px; height: 100%; }
        .form-check { margin-bottom: 15px; }
        .form-check-input { margin-top: 2px; }
        .form-check-label { font-size: 16px; color: #333; }
        .form-group input[type="color"] { width: 60px; height: 40px; border: 1px solid #ccc; border-radius: 5px; cursor: pointer; margin: 5px auto; display: block; }
        .form-group input[type="color"]::-webkit-color-swatch { border: none; border-radius: 5px; }
        .btn-group-custom, .btn-group-custom-2 { margin-top: 20px; display: flex; gap: 10px; }
        .btn-custom { padding: 10px 25px; border-radius: 25px; font-weight: 500; transition: all 0.3s ease; }
        .btn-primary { background: #007bff; border: none; }
        .btn-primary:hover { background: #0056b3; }
        .btn-outline-primary { border-color: #007bff; color: #007bff; }
        .btn-outline-primary:hover { background: #007bff; color: #fff; }
        .btn-active { background: #007bff; color: #fff; border-color: #007bff; }
        .alert { border-radius: 10px; margin-top: 10px; }
        .preview-container {
            border-radius: 15px; overflow: hidden; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            background: #fff; transition: all 0.3s ease; height: 800px !important; width: 100% !important;
        }
        .preview-iframe { width: 100%; height: 100%; border: none; }
        .mobile-preview { max-width: 450px; margin: 0 auto; }
        .desktop-preview { max-width: 100%; }
        .row { align-items: stretch; }
        .col-md-4, .col-md-8 { display: flex; flex-direction: column; }
        .admin-form-container {
            display: flex; overflow-x: auto; gap: 15px; padding: 10px;
            border: 1px solid #ddd; border-radius: 5px; background: #fff; flex-wrap: wrap;
        }
        .admin-field { flex: 0 0 auto; text-align: center; min-width: 120px; }
        .admin-field label { font-size: 14px; display: block; margin-bottom: 5px; }
        .admin-field input[type="checkbox"] { margin: 0 auto; display: block; }
        .theme-selection { padding: 15px; background: #f8f9fa; border-radius: 8px; }
        .form-label { font-size: 16px; font-weight: 500; color: #333; margin-bottom: 10px; display: block; }
        .theme-radio-group { display: flex; gap: 20px; flex-wrap: wrap; }
        .form-check-inline { display: flex; align-items: center; gap: 8px; }
        .form-check-input[type="radio"] {
            width: 18px; height: 18px; cursor: pointer; border: 2px solid #007bff; transition: all 0.2s ease;
        }
        .form-check-input[type="radio"]:checked { background-color: #007bff; border-color: #0056b3; }
        .form-check-label { font-size: 15px; color: #333; cursor: pointer; }

        @media (max-width: 991px) {
            .preview-container { height: 400px !important; }
            .mobile-preview, .desktop-preview { max-width: 100%; }
            .btn-custom { width: 100%; margin-bottom: 10px; }
            .btn-group-custom { flex-direction: column; }
            .admin-form-container { gap: 10px; padding: 5px; }
            .admin-field { min-width: 100px; }
            .admin-field label { font-size: 12px; }
            .form-group input[type="color"] { width: 50px; height: 30px; }
            .row.mt-3 .col-md-2 { flex: 0 0 50%; max-width: 50%; align-items: center; }
            .theme-radio-group { flex-direction: column; gap: 10px; }
            .form-check-inline { gap: 6px; }
            .form-check-input[type="radio"] { width: 16px; height: 16px; }
            .form-check-label { font-size: 14px; }
        }
    </style>
    <div class="container">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h3 class="card-title">Customize Checkout Page</h3>
            </div>
            <div class="card-body">
                <?php if ($userdata['role'] == 'Admin'): ?>
                    <div class="form-section mb-4">
                        <h4 class="mb-3">Admin: Bulk Update All Users</h4>
                        <form method="POST" action="">
                            <input type="hidden" name="admin_update" value="1">
                            <div class="admin-form-container">
                                <div class="admin-field">
                                    <label for="admin_toggleQr"> QR Code</label>
                                    <input class="form-check-input" type="checkbox" id="admin_toggleQr" name="admin_toggleQr">
                                    <label>Update</label>
                                    <input class="form-check-input" type="checkbox" name="update_field[]" value="show_qr">
                                </div>
                                <div class="admin-field">
                                    <label for="admin_toggleHelp"> Help</label>
                                    <input class="form-check-input" type="checkbox" id="admin_toggleHelp" name="admin_toggleHelp">
                                    <label>Update</label>
                                    <input class="form-check-input" type="checkbox" name="update_field[]" value="show_help">
                                </div>
                                <div class="admin-field">
                                    <label for="admin_togglePaytmButtons"> Paytm Button</label>
                                    <input class="form-check-input" type="checkbox" id="admin_togglePaytmButtons" name="admin_togglePaytmButtons">
                                    <label>Update</label>
                                    <input class="form-check-input" type="checkbox" name="update_field[]" value="show_paytmButton">
                                </div>
                                <div class="admin-field">
                                    <label for="admin_toggleGpay"> Gpay</label>
                                    <input class="form-check-input" type="checkbox" id="admin_toggleGpay" name="admin_toggleGpay">
                                    <label>Update</label>
                                    <input class="form-check-input" type="checkbox" name="update_field[]" value="Show_GpayButton">
                                </div>
                                <div class="admin-field">
                                    <label for="admin_phonepe"> Phonepe</label>
                                    <input class="form-check-input" type="checkbox" id="admin_phonepe" name="admin_phonepe">
                                    <label>Update</label>
                                    <input class="form-check-input" type="checkbox" name="update_field[]" value="show_phonepe"> </div>
                                <div class="admin-field">
                                    <label for="admin_toggleDownload"> Download QR</label>
                                    <input class="form-check-input" type="checkbox" id="admin_toggleDownload" name="admin_toggleDownload">
                                    <label for="update_showDownloadQR">Update</label>
                                    <input class="form-check-input" type="checkbox" id="update_showDownloadQR" name="update_field[]" value="show_download_qr">
                                </div>
                                <div class="admin-field">
                                    <label for="admin_toggleUpiRequest"> UPI Request </label>
                                    <input class="form-check-input" type="checkbox" id="admin_toggleUpiRequest" name="admin_toggleUpiRequest">
                                    <label>Update</label>
                                    <input class="form-check-input" type="checkbox" name="update_field[]" value="show_upiRequest">
                                </div>
                                <div class="admin-field">
                                    <label for="admin_toggleIntent"> UPI Intent</label>
                                    <input class="form-check-input" type="checkbox" id="admin_toggleIntent" name="admin_toggleIntent">
                                    <label>Update</label>
                                    <input class="form-check-input" type="checkbox" name="update_field[]" value="Show_IntentButton">
                                </div>
                                <div class="admin-field">
                                    <label for="admin_removeBranding">Remove Branding</label>
                                    <input class="form-check-input" type="checkbox" id="admin_removeBranding" name="admin_removeBranding">
                                    <label>Update</label>
                                    <input class="form-check-input" type="checkbox" name="update_field[]" value="remove_branding">
                                </div>
                                <div class="admin-field" hidden>
                                    <label for="admin_displayHeaderFooter">Header Footer</label>
                                    <input class="form-check-input" type="checkbox" id="admin_displayHeaderFooter" name="admin_displayHeaderFooter">
                                    <label>Update</label>
                                    <input class="form-check-input" type="checkbox" name="update_field[]" value="display_header_footer">
                                </div>
                                <div class="admin-field" hidden>
                                    <label for="admin_displayLoadingScreen">Loading Screen</label>
                                    <input class="form-check-input" type="checkbox" id="admin_displayLoadingScreen" name="admin_displayLoadingScreen">
                                    <label>Update</label>
                                    <input class="form-check-input" type="checkbox" name="update_field[]" value="display_loading_screen">
                                </div>
                                <div class="admin-field">
                                    <label for="admin_news">News Content</label>
                                    <input class="form-control" type="text" id="admin_news" name="admin_news" value="<?php echo htmlspecialchars($_POST['admin_news'] ?? ''); ?>">
                                    <label>Update</label>
                                    <input class="form-check-input" type="checkbox" name="update_field[]" value="news">
                                </div>
                            </div>
                            <div class="btn-group-custom-2 mt-3">
                                <button class="btn btn-danger btn-custom" type="submit">Update Selected Fields</button>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>
                <div class="row">
                    <div class="col-md-4">
                        <a href="profile">
                            <button class="btn btn-info btn-custom mb-3">Change Logo</button>
                        </a>
                        <div class="form-section">
                            <form method="POST" action="">
                                <div class="theme-selection form-group mb-3">
                                    <label class="form-label">Select Theme</label>
                                    <div class="theme-radio-group">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" id="themeSelect1" name="themeSelect" value="1" <?php echo ($themeSelect == 1) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="themeSelect1">Theme 1</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" id="themeSelect2" name="themeSelect" value="2" <?php echo ($themeSelect == 2) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="themeSelect2">Theme 2</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" id="themeSelect3" name="themeSelect" value="3" <?php echo ($themeSelect == 3) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="themeSelect3">Theme 3</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" id="themeSelect4" name="themeSelect" value="4" <?php echo ($themeSelect == 4) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="themeSelect4">Theme 4</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" id="themeSelect5" name="themeSelect" value="5" <?php echo ($themeSelect == 5) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="themeSelect5">Theme 5</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" id="themeSelect6" name="themeSelect" value="6" <?php echo ($themeSelect == 6) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="themeSelect5">Theme 6</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 col-6 form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="toggleQr" name="toggleQr" <?php echo ($showQr) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="toggleQr">Show QR Code</label>
                                    </div>
                                    <div class="col-md-6 col-6 form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="toggleHelp" name="toggleHelp" <?php echo ($showHelp) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="toggleHelp">Show Help</label>
                                    </div>
                                    <div class="col-md-6 col-6 form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="togglePaytmButtons" name="togglePaytmButtons" <?php echo ($showPaytmButton) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="togglePaytmButtons">Show Paytm Button</label>
                                    </div>
                                    <div class="col-md-6 col-6 form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="toggleGpay" name="toggleGpay" <?php echo ($showGpayButton) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="toggleGpay">Show Gpay Button</label>
                                    </div>
                                    <div class="col-md-6 col-6 form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="togglePhonepe" name="togglePhonepe" <?php echo ($show_phonepe) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="togglePhonepe">Show PhonePe Button</label>
                                    </div>
                                    <div class="col-md-6 col-6 form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="toggleDownload" name="toggleDownload" <?php echo ($showDownloadQr) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="toggleDownload">Show Download QR Button</label>
                                    </div>
                                    <div class="col-md-6 col-6 form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="toggleUpiRequest" name="toggleUpiRequest" <?php echo ($showUpiRequest) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="toggleUpiRequest">Show UPI ID Request Option</label>
                                    </div>
                                </div>

                                <div class="row mt-3">
                                    <div class="col-md-6 col-6 form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="showPaymentLogos" name="showPaymentLogos" <?php echo ($showPaymentLogos) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="showPaymentLogos">Show Payment Apps Logos</label>
                                    </div>


                                    <div class="form-group mt-3">
                                        <label for="headerColor">Header Color</label>
                                        <label for="bodyColor" class="ms-4">Body Color</label><br>
                                        <input type="color" class="form-control d-inline-block" id="headerColor" name="headerColor" value="<?php echo htmlspecialchars($headerColor); ?>" style="width: 100px;">
                                        <input type="color" class="form-control d-inline-block ms-3" id="bodyColor" name="bodyColor" value="<?php echo htmlspecialchars($bodyColor); ?>" style="width: 100px;">
                                    </div>

                                    <?php if (!$vipActive): ?>
                                        <div class="alert alert-warning mt-3">
                                            VIP Required For Below Features | <a href="subscription" class="btn btn-primary btn-sm">Buy VIP Plan</a>
                                        </div>
                                    <?php endif; ?>
                                    <div class="col-md-6 col-6 form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="toggleIntent" name="toggleIntent"<?php echo ($vipActive) ? '' : 'disabled'; ?> <?php echo ($showIntentButton) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="toggleIntent">Show Pay On UPI Intent Button</label>
                                    </div>
                                    <div class="col-md-6 col-6 form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="removeBranding" name="removeBranding" <?php echo ($vipActive) ? '' : 'disabled'; ?> <?php echo ($removeBranding) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="removeBranding">Remove UpiGateway Branding</label>
                                    </div>
                                    <div class="col-md-6 col-6 form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="displayHeaderFooter" name="displayHeaderFooter" <?php echo ($vipActive) ? '' : 'disabled'; ?> <?php echo ($displayHeaderFooter) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="displayHeaderFooter">Display Header Footer</label>
                                    </div>
                                    <div class="col-md-6 col-6 form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="displayLoadingScreen" name="displayLoadingScreen" <?php echo ($vipActive) ? '' : 'disabled'; ?> <?php echo ($displayLoadingScreen) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="displayLoadingScreen">Display Loading Screen</label>
                                    </div>
                                    <div class="col-md-12 col-12 form-check mb-2">
                                        <label class="form-label" for="news">News Content</label>
                                        <input class="form-control" type="text" id="news" name="news" value="<?php echo htmlspecialchars($news); ?>" <?php echo ($vipActive) ? '' : 'readonly'; ?>>
                                    </div>
                                </div>

                                <div class="btn-group-custom-2 mt-3">
                                    <button class="btn btn-info btn-custom" type="submit">Save Changes</button>
                                </div>

                                <div class="btn-group-custom mt-3">
                                    <button class="btn btn-outline-primary btn-custom mobile-btn" type="button" onclick="setPreview('mobile', this)">Mobile Preview</button>
                                    <button class="btn btn-outline-primary btn-custom desktop-btn" type="button" onclick="setPreview('desktop', this)">Desktop Preview</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div id="previewContainer" class="preview-container mobile-preview">
                            <iframe class="preview-iframe" src="https://chickenpox.in/payment91/instant-pay/<?=$userdata['user_token']?>?id=<?=$user_id?>"></iframe>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initial setup: activate mobile button if not already, and set preview size
            const mobileBtn = document.querySelector('.mobile-btn');
            const desktopBtn = document.querySelector('.desktop-btn');
            const previewContainer = document.getElementById('previewContainer');

            // Set initial state for preview and button
            if (mobileBtn) {
                mobileBtn.classList.add('btn-active'); // Default to mobile active
            }
            if (previewContainer) {
                previewContainer.classList.add('mobile-preview');
                previewContainer.style.maxWidth = '450px';
                previewContainer.style.height = window.innerWidth <= 991 ? '400px' : '600px';
            }

            function setPreview(type, button) {
                try {
                    const buttons = document.querySelectorAll('.btn-group-custom button');
                    
                    // Remove active class from all buttons
                    buttons.forEach(btn => btn.classList.remove('btn-active'));
                    // Add active class to clicked button
                    if (button) { // Check if button exists before adding class
                        button.classList.add('btn-active');
                    }

                    // Remove both classes first
                    previewContainer.classList.remove('mobile-preview', 'desktop-preview');
                    
                    if (type === 'mobile') {
                        previewContainer.classList.add('mobile-preview');
                        previewContainer.style.maxWidth = '450px';
                        previewContainer.style.height = window.innerWidth <= 991 ? '400px' : '600px';
                    } else {
                        previewContainer.classList.add('desktop-preview');
                        previewContainer.style.maxWidth = '100%';
                        previewContainer.style.height = window.innerWidth <= 991 ? '400px' : '600px';
                    }
                } catch (error) {
                    console.error('Error in setPreview:', error);
                    alert('An error occurred while switching preview mode. Please try again.');
                }
            }

            // Attach event listeners to buttons
            if (mobileBtn) {
                mobileBtn.addEventListener('click', () => setPreview('mobile', mobileBtn));
            }
            if (desktopBtn) {
                desktopBtn.addEventListener('click', () => setPreview('desktop', desktopBtn));
            }
        });
    </script>
</head>
<?php
// Close the database connection here, after all operations that need it
if (isset($conn) && $conn) {
    mysqli_close($conn);
}
?>