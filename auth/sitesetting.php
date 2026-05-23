<?php
// Enable error reporting for debugging
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

// Start output buffering to prevent premature output
ob_start();

include "header.php";
include "config.php"; // Assuming config.php contains the database connection

// Initialize message variable
$message = "";

// Check if user is Admin
if($userdata["role"] != 'Admin'){
    echo '<script>
        window.location.href = "dashboard";
    </script>';
    exit;
}

// Fetch existing settings from the database
$query = "SELECT * FROM site_settings LIMIT 1";
$result = mysqli_query($conn, $query);
$settings = mysqli_fetch_assoc($result);

// Fetch referral slabs
$slabs = [];
$slab_result = mysqli_query($conn, "SELECT * FROM refer_income_slabs ORDER BY required_referrals ASC");
if ($slab_result && mysqli_num_rows($slab_result) > 0) {
    while ($row = mysqli_fetch_assoc($slab_result)) {
        $slabs[] = $row;
    }
}

// Handle Site Settings form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['brand_name'])) {
    $brand_name = mysqli_real_escape_string($conn, $_POST['brand_name']);
    $logo_url = mysqli_real_escape_string($conn, $_POST['logo_url']);
    $site_link = mysqli_real_escape_string($conn, $_POST['site_link']);
    $whatsapp_number = mysqli_real_escape_string($conn, $_POST['whatsapp_number']);
    $copyright_text = mysqli_real_escape_string($conn, $_POST['copyright_text']);
    $bonus_refer = mysqli_real_escape_string($conn, $_POST['bonus_refer']);
    $income_sponser = mysqli_real_escape_string($conn, $_POST['income_sponser']);
    $vip_plan = mysqli_real_escape_string($conn, $_POST['vip_plan']);

    if ($settings) {
        // Update existing record
        $update_query = "UPDATE site_settings SET 
            brand_name='$brand_name', 
            logo_url='$logo_url', 
            site_link='$site_link', 
            whatsapp_number='$whatsapp_number', 
            copyright_text='$copyright_text', 
            bonus_refer='$bonus_refer', 
            income_sponser='$income_sponser', 
            vip_plan='$vip_plan' 
            WHERE id=".$settings['id'];
        
        if (mysqli_query($conn, $update_query)) {
            $message = "Settings updated successfully.";
        } else {
            $message = "Error updating settings: " . mysqli_error($conn);
        }
    } else {
        // Insert new record
        $insert_query = "INSERT INTO site_settings 
            (brand_name, logo_url, site_link, whatsapp_number, copyright_text, bonus_refer, income_sponser, vip_plan) 
            VALUES ('$brand_name', '$logo_url', '$site_link', '$whatsapp_number', '$copyright_text', '$bonus_refer', '$income_sponser', '$vip_plan')";
        
        if (mysqli_query($conn, $insert_query)) {
            $message = "Settings saved successfully.";
        } else {
            $message = "Error saving settings: " . mysqli_error($conn);
        }
    }
}

// Handle Slab Updates form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['slab_ids'])) {
    foreach ($_POST['slab_ids'] as $index => $id) {
        $title = mysqli_real_escape_string($conn, $_POST['slab_titles'][$index]);
        $referrals = intval($_POST['slab_referrals'][$index]);
        $commission = floatval($_POST['slab_commissions'][$index]);
        $color = mysqli_real_escape_string($conn, $_POST['slab_colors'][$index]);

        $updateSlab = "UPDATE refer_income_slabs 
            SET title='$title', 
                required_referrals=$referrals, 
                commission_percent=$commission, 
                color_class='$color' 
            WHERE id=$id";
        
        if (mysqli_query($conn, $updateSlab)) {
            $message = "Slabs updated successfully.";
        } else {
            $message = "Error updating slabs: " . mysqli_error($conn);
        }
    }
}

// Redirect after form submission
if (!empty($message)) {
    header("Location: sitesetting.php?message=" . urlencode($message));
    exit();
}

// End output buffering and flush output
ob_end_flush();
?>

<?php if($userdata["role"] == 'Admin'){ ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/line-awesome/1.3.0/line-awesome/css/line-awesome.min.css" rel="stylesheet">
    <style>
        .card { margin-bottom: 2rem; }
        .page-title { font-size: 1.8rem; margin-bottom: 1rem; }
        @media (max-width: 768px) {
            .card { margin-left: 0 !important; }
        }
    </style>
</head>
<body>
<div class="container-fluid mt-5">
    <h1 class="page-title">Manage Site Settings</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item">
            <a href="dashboard"><i class="la la-home font-20"></i></a>
        </li>
        <li class="breadcrumb-item active">Site Settings</li>
    </ol>

    <!-- Display success/error message -->
    <?php if (isset($_GET['message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($_GET['message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <script>
            setTimeout(function() {
                window.location = "sitesetting.php";
            }, 2000);
        </script>
    <?php endif; ?>

    <div class="row">
        <!-- Site Settings Form -->
        <div class="col-md-6">
            <div class="card shadow-lg p-4">
                <h4 class="mb-4 text-primary">Site Settings</h4>
                <form method="post" action="sitesetting.php">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Brand Name:</label>
                            <input class="form-control" type="text" name="brand_name" value="<?php echo htmlspecialchars($settings['brand_name'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Logo URL:</label>
                            <input class="form-control" type="text" name="logo_url" value="<?php echo htmlspecialchars($settings['logo_url'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Site Link:</label>
                            <input class="form-control" type="text" name="site_link" value="<?php echo htmlspecialchars($settings['site_link'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">WhatsApp Number:</label>
                            <input class="form-control" type="text" name="whatsapp_number" value="<?php echo htmlspecialchars($settings['whatsapp_number'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Copyright Text:</label>
                            <input class="form-control" type="text" name="copyright_text" value="<?php echo htmlspecialchars($settings['copyright_text'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Refer Join Bonus:</label>
                            <input class="form-control" type="text" name="bonus_refer" value="<?php echo htmlspecialchars($settings['bonus_refer'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Referrer Sponsor Income:</label>
                            <input class="form-control" type="text" name="income_sponser" value="<?php echo htmlspecialchars($settings['income_sponser'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">VIP Plan:</label>
                            <input class="form-control" type="text" name="vip_plan" value="<?php echo htmlspecialchars($settings['vip_plan'] ?? ''); ?>">
                        </div>
                    </div>
                    <button class="btn btn-primary w-100 mt-3" type="submit">Save Settings</button>
                </form>
            </div>
        </div>

        <!-- Referral Income Slabs Form -->
        <div class="col-md-6">
            <div class="card shadow-lg p-4">
                <h4 class="mb-4 text-primary">Referral Income Slabs</h4>
                <?php if (!empty($slabs)): ?>
                    <form method="post" action="sitesetting.php">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Title</th>
                                        <th>Required Referrals</th>
                                        <th>Commission (%)</th>
                                        <th>Color Class</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($slabs as $index => $slab): ?>
                                        <tr>
                                            <td>
                                                <input type="hidden" name="slab_ids[]" value="<?php echo $slab['id']; ?>">
                                                <input class="form-control" type="text" name="slab_titles[]" value="<?php echo htmlspecialchars($slab['title']); ?>">
                                            </td>
                                            <td>
                                                <input class="form-control" type="number" name="slab_referrals[]" value="<?php echo intval($slab['required_referrals']); ?>">
                                            </td>
                                            <td>
                                                <input class="form-control" type="number" step="0.01" name="slab_commissions[]" value="<?php echo floatval($slab['commission_percent']); ?>">
                                            </td>
                                            <td>
                                                <input class="form-control" type="text" name="slab_colors[]" value="<?php echo htmlspecialchars($slab['color_class']); ?>" readonly>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <button class="btn btn-success w-100 mt-3" type="submit">Update Slabs</button>
                    </form>
                <?php else: ?>
                    <p>No referral slabs found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php } ?>