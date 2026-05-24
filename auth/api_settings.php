<?php include "header.php";

// API Key is handled by user_token from registration
// We just map it here for the UI
$userdata['api_key'] = $userdata['user_token'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['webhook_url'])) {
    $wh = $conn->real_escape_string($_POST['webhook_url']);
    // Webhooks in this system are stored in callback_url
    $conn->query("UPDATE users SET callback_url = '$wh' WHERE id = '{$userdata['id']}'");
    $userdata['callback_url'] = $wh;
    $success_msg = "Webhook URL updated successfully.";
}
?>

<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <h1 class="page-title fw-semibold fs-18 mb-0">API Credentials & Webhooks</h1>
        </div>

        <?php if(isset($success_msg)): ?>
            <div class="alert alert-success"><?php echo $success_msg; ?></div>
        <?php endif; ?>

        <div class="row">
            <div class="col-xl-12">
                <div class="card custom-card">
                    <div class="card-header">
                        <div class="card-title">Live API Credentials</div>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">Use these credentials to authenticate server-to-server API requests (e.g., creating orders). Do not share your Secret Key.</p>
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold">API Key (Public)</label>
                            <input type="text" class="form-control bg-light" value="<?php echo htmlspecialchars($userdata['api_key']); ?>" readonly>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-12 mt-4">
                <div class="card custom-card">
                    <div class="card-header">
                        <div class="card-title">Webhook Settings</div>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">DEZOPAY will send a POST request to this URL when a payment succeeds or fails. Ensure your server verifies the `x-razorpay-signature`.</p>
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Webhook URL</label>
                                <input type="url" name="webhook_url" class="form-control" placeholder="https://your-server.com/api/webhook" value="<?php echo htmlspecialchars($userdata['callback_url'] ?? ''); ?>" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Save Webhook URL</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include "footer.php"; ?>
