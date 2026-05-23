<?php include "header.php";

// Initialize keys if missing
if (empty($userdata['api_key'])) {
    $new_key = 'dz_live_' . bin2hex(random_bytes(12));
    $new_secret = 'dz_secret_' . bin2hex(random_bytes(24));
    $conn->query("UPDATE users SET api_key = '$new_key', api_secret = '$new_secret' WHERE id = '{$userdata['id']}'");
    $userdata['api_key'] = $new_key;
    $userdata['api_secret'] = $new_secret;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['webhook_url'])) {
    $wh = $conn->real_escape_string($_POST['webhook_url']);
    $conn->query("UPDATE users SET webhook_url = '$wh' WHERE id = '{$userdata['id']}'");
    $userdata['webhook_url'] = $wh;
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

                        <div class="mb-4">
                            <label class="form-label fw-bold">API Secret (Private)</label>
                            <div class="input-group">
                                <input type="password" class="form-control bg-light" id="apiSecret" value="<?php echo htmlspecialchars($userdata['api_secret']); ?>" readonly>
                                <button class="btn btn-outline-secondary" type="button" onclick="document.getElementById('apiSecret').type='text'">Show</button>
                            </div>
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
                                <input type="url" name="webhook_url" class="form-control" placeholder="https://your-server.com/api/webhook" value="<?php echo htmlspecialchars($userdata['webhook_url'] ?? ''); ?>" required>
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
