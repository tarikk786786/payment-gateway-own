<?php
include "header.php"; // Include your header file

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Plan expiry check
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

$messages = [];
$user_id = $userdata['id'];
$role = $userdata['role'];
$mobile = $userdata['mobile'];
$email = $userdata['email'];

// Pagination settings
$limit = 10; // IPs per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Website types array
$website_types = [
    'E-commerce' => 'E-commerce Website',
    'MultiRecharge' => 'Multi Recharge Website',
    'Gaming' => 'Gamming/Rummy Casino Website',
    'Business' => 'Business Website',
    'Blog' => 'Blog/News Website',
    'Portfolio' => 'Portfolio Website',
    'Educational' => 'Educational Website',
    'Healthcare' => 'Healthcare Website',
    'Real Estate' => 'Real Estate Website',
    'Restaurant' => 'Restaurant/Food Website',
    'Travel' => 'Travel Website',
    'Technology' => 'Technology IT company Website',
    'Entertainment' => 'Entertainment Website',
    'Social Media' => 'Social Media Platform',
    'Forum' => 'Community Forum',
    'Non-Profit' => 'Non-Profit Website',
    'Government' => 'Government Website',
    'localhiost'=>'Localhost Testing IP',
    'Other' => 'my website Type not in list'
];

// Handle IP Submission (OTP system removed)
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Plan expiry check again for form submission
    if ($expiry_timestamp < $today) {
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

    // Add new IP
    if (isset($_POST['ip'])) {
        $ip = trim($_POST['ip']);
        $domain = trim($_POST['domain']);
        $type = trim($_POST['type']);

        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            $messages[] = ["type" => "danger", "text" => "❌ Invalid IP address."];
        } else {
            // Check if IP already exists for this user
            $check = $conn->prepare("SELECT * FROM user_ips WHERE ip = ? AND user_id = ?");
            $check->bind_param("si", $ip, $user_id);
            $check->execute();

            if ($check->get_result()->num_rows > 0) {
                $messages[] = ["type" => "warning", "text" => "⚠️ This IP is already whitelisted."];
            } else {
                // Add IP directly without OTP verification
                $stmt = $conn->prepare("INSERT INTO user_ips (ip, domain, type, user_id, status) VALUES (?, ?, ?, ?, 0)");
                $stmt->bind_param("sssi", $ip, $domain, $type, $user_id);

                if ($stmt->execute()) {
                    $messages[] = ["type" => "success", "text" => "✅ IP added successfully. Waiting for admin approval."];

                    // Notify admin
                    $admin_msg = "New IP ($ip) of type ($type) added by user ID: $user_id. Please approve.";
                    sendWA('9876543210', $admin_msg);
                    
                    // Send confirmation to user
                    $user_msg = "Your IP ($ip) has been submitted for approval. You will be notified once approved.";
                    sendNotification($mobile, $email, $user_msg, "IP Whitelisting Request");
                } else {
                    $messages[] = ["type" => "danger", "text" => "❌ Failed to add IP. Please try again."];
                }
            }
        }
    }

    // Handle Delete IP (for users and admins)
    if (isset($_POST['delete_ip'])) {
        $ip_id = $_POST['ip_id'];
        
        // Get user mobile for notification
        $user_stmt = $conn->prepare("SELECT mobile FROM users WHERE id = (SELECT user_id FROM user_ips WHERE id = ?)");
        $user_stmt->bind_param("i", $ip_id);
        $user_stmt->execute();
        $user_result = $user_stmt->get_result();
        
        if ($user_result->num_rows > 0) {
            $user_mobile = $user_result->fetch_assoc()['mobile'];
        }

        $stmt = $conn->prepare("DELETE FROM user_ips WHERE id = ?");
        $stmt->bind_param("i", $ip_id);
        if ($stmt->execute()) {
            $messages[] = ["type" => "success", "text" => "✅ IP deleted successfully."];
            if (isset($user_mobile)) {
                sendNotification($user_mobile, $email, "Your IP has been deleted from whitelist.", "IP Deleted");
            }
        }
    }

    // Handle Admin Actions
    if ($role == 'Admin') {
        $user_stmt = $conn->prepare("SELECT mobile, email FROM users WHERE id = (SELECT user_id FROM user_ips WHERE id = ?)");

        if (isset($_POST['activate_ip'])) {
            $ip_id = $_POST['ip_id'];
            $stmt = $conn->prepare("UPDATE user_ips SET status = 1 WHERE id = ?");
            $stmt->bind_param("i", $ip_id);
            if ($stmt->execute()) {
                $messages[] = ["type" => "success", "text" => "✅ IP activated successfully."];
                
                $user_stmt->bind_param("i", $ip_id);
                $user_stmt->execute();
                $user_data = $user_stmt->get_result()->fetch_assoc();
                
                if ($user_data) {
                    sendNotification($user_data['mobile'], $user_data['email'], 
                        "Your IP has been activated by admin. You can now use it for transactions.", 
                        "IP Activated");
                }
            }
        }

        if (isset($_POST['deactivate_ip'])) {
            $ip_id = $_POST['ip_id'];
            $stmt = $conn->prepare("UPDATE user_ips SET status = 0 WHERE id = ?");
            $stmt->bind_param("i", $ip_id);
            if ($stmt->execute()) {
                $messages[] = ["type" => "success", "text" => "✅ IP deactivated successfully."];
                
                $user_stmt->bind_param("i", $ip_id);
                $user_stmt->execute();
                $user_data = $user_stmt->get_result()->fetch_assoc();
                
                if ($user_data) {
                    sendNotification($user_data['mobile'], $user_data['email'], 
                        "Your IP has been deactivated by admin. It can no longer be used for transactions.", 
                        "IP Deactivated");
                }
            }
        }
    }
}

// Handle Search
$search_ip = isset($_POST['search_ip']) ? trim($_POST['search_ip']) : '';
$search_user_id = ($role == 'Admin' && isset($_POST['search_user_id'])) ? trim($_POST['search_user_id']) : '';

if ($role == 'Admin') {
    // Count total IPs for pagination
    if ($search_ip || $search_user_id) {
        $count_stmt = $conn->prepare("SELECT COUNT(*) as total FROM user_ips WHERE ip LIKE ? AND user_id LIKE ?");
        $search_ip_param = "%$search_ip%";
        $search_user_id_param = "%$search_user_id%";
        $count_stmt->bind_param("ss", $search_ip_param, $search_user_id_param);
        $count_stmt->execute();
        $total_ips = $count_stmt->get_result()->fetch_assoc()['total'];
    } else {
        $total_ips = $conn->query("SELECT COUNT(*) as total FROM user_ips")->fetch_assoc()['total'];
    }

    // Fetch IPs
    if ($search_ip || $search_user_id) {
        $stmt = $conn->prepare("SELECT * FROM user_ips WHERE ip LIKE ? AND user_id LIKE ? ORDER BY created_at DESC LIMIT ? OFFSET ?");
        $search_ip_param = "%$search_ip%";
        $search_user_id_param = "%$search_user_id%";
        $stmt->bind_param("ssii", $search_ip_param, $search_user_id_param, $limit, $offset);
        $stmt->execute();
        $ip_list = $stmt->get_result();
    } else {
        $stmt = $conn->prepare("SELECT * FROM user_ips ORDER BY created_at DESC LIMIT ? OFFSET ?");
        $stmt->bind_param("ii", $limit, $offset);
        $stmt->execute();
        $ip_list = $stmt->get_result();
    }
} else {
    // Count total IPs for pagination
    if ($search_ip) {
        $count_stmt = $conn->prepare("SELECT COUNT(*) as total FROM user_ips WHERE user_id = ? AND ip LIKE ?");
        $search_ip_param = "%$search_ip%";
        $count_stmt->bind_param("is", $user_id, $search_ip_param);
        $count_stmt->execute();
        $total_ips = $count_stmt->get_result()->fetch_assoc()['total'];
    } else {
        $total_ips = $conn->query("SELECT COUNT(*) as total FROM user_ips WHERE user_id = $user_id")->fetch_assoc()['total'];
    }

    // Fetch IPs
    if ($search_ip) {
        $stmt = $conn->prepare("SELECT * FROM user_ips WHERE user_id = ? AND ip LIKE ? ORDER BY created_at DESC LIMIT ? OFFSET ?");
        $search_ip_param = "%$search_ip%";
        $stmt->bind_param("isii", $user_id, $search_ip_param, $limit, $offset);
        $stmt->execute();
        $ip_list = $stmt->get_result();
    } else {
        $stmt = $conn->prepare("SELECT * FROM user_ips WHERE user_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?");
        $stmt->bind_param("iii", $user_id, $limit, $offset);
        $stmt->execute();
        $ip_list = $stmt->get_result();
    }
}

$total_pages = ceil($total_ips / $limit);
?>

<div class="container mt-5">
    <h3 class="mb-4">⚙️ IP Settings</h3>

    <!-- Security Explanation -->
    <div class="alert alert-info" role="alert">
        <strong>IP Security:</strong> Add and manage your whitelisted IP addresses here. Only IPs with "Active" status can be used to place new orders or process transactions.
    </div>

    <!-- Flash Messages -->
    <?php foreach ($messages as $msg): ?>
        <div class="alert alert-<?= $msg['type']; ?> alert-dismissible fade show" role="alert">
            <?= $msg['text']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endforeach; ?>

    <!-- Add IP Form (Simplified - No OTP) -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="POST">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="ip" class="form-label">Enter IP Address</label>
                        <input type="text" class="form-control" id="ip" name="ip" placeholder="e.g. 192.168.0.1" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="domain" class="form-label">Enter Website Domain</label>
                        <input type="text" class="form-control" id="domain" name="domain" placeholder="e.g. https://yourdomain.com" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="type" class="form-label">Select Website Type</label>
                        <select class="form-select" id="type" name="type" required>
                            <option value="">Choose website type...</option>
                            <?php foreach ($website_types as $key => $value): ?>
                                <option value="<?= htmlspecialchars($key); ?>"><?= htmlspecialchars($value); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Add IP for Approval</button>
                <small class="text-muted d-block mt-2">IP will be submitted for admin approval. You will be notified once approved.</small>
            </form>
        </div>
    </div>

    <!-- Whitelisted IPs Table with Search -->
    <div class="card shadow-sm">
        <div class="card-body">
            <h5 class="card-title mb-3">✅ Your Whitelisted IPs</h5>

            <!-- Search Form -->
            <form method="POST" class="mb-3">
                <div class="input-group">
                    <input type="text" class="form-control" name="search_ip" placeholder="Search by IP address" value="<?= htmlspecialchars($search_ip); ?>">
                    <?php if ($role == 'Admin'): ?>
                        <input type="text" class="form-control" name="search_user_id" placeholder="Search by User ID" value="<?= htmlspecialchars($search_user_id); ?>">
                    <?php endif; ?>
                    <button type="submit" class="btn btn-primary">Search</button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <?php if ($role == 'Admin'): ?>
                                <th>User ID</th>
                            <?php endif; ?>
                            <th>IP Address</th>
                            <th>Domain</th>
                            <th>Website Type</th>
                            <th>Status</th>
                            <th>Added On</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = $offset + 1; while($row = $ip_list->fetch_assoc()): ?>
                            <tr>
                                <td><?= $i++; ?></td>
                                <?php if ($role == 'Admin'): ?>
                                    <td><?= htmlspecialchars($row['user_id']); ?></td>
                                <?php endif; ?>
                                <td><?= htmlspecialchars($row['ip']); ?></td>
                                <td>
                                    <?php if (!empty($row['domain'])): ?>
                                        <a href="<?= htmlspecialchars($row['domain']); ?>" target="_blank" class="text-decoration-none">
                                            <?= htmlspecialchars($row['domain']); ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">Not specified</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">
                                        <?= htmlspecialchars($row['type'] ?? 'Not Set'); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $row['status'] ? 'success' : 'warning'; ?>">
                                        <?= $row['status'] ? 'Active' : 'Pending Approval'; ?>
                                    </span>
                                </td>
                                <td><?= date('d M, Y h:i A', strtotime($row['created_at'])); ?></td>
                                <td>
                                    <!-- User and Admin can delete -->
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="ip_id" value="<?= $row['id']; ?>">
                                        <button type="submit" name="delete_ip" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this IP?');">Delete</button>
                                    </form>
                                    <!-- Admin-specific actions -->
                                    <?php if ($role == 'Admin'): ?>
                                        <?php if ($row['status']): ?>
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="ip_id" value="<?= $row['id']; ?>">
                                                <button type="submit" name="deactivate_ip" class="btn btn-sm btn-warning">Deactivate</button>
                                            </form>
                                        <?php else: ?>
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="ip_id" value="<?= $row['id']; ?>">
                                                <button type="submit" name="activate_ip" class="btn btn-sm btn-success">Activate</button>
                                            </form>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        <?php if ($ip_list->num_rows == 0): ?>
                            <tr>
                                <td colspan="<?= $role == 'Admin' ? '8' : '7'; ?>" class="text-center">No IPs found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <nav aria-label="Page navigation">
                    <ul class="pagination">
                        <li class="page-item <?= $page <= 1 ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?= $page - 1; ?>" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                        <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                            <li class="page-item <?= $p == $page ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?= $p; ?>"><?= $p; ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?= $page >= $total_pages ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?= $page + 1; ?>" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
            <p class="text-muted">Showing <?= $ip_list->num_rows; ?> of <?= $total_ips; ?> IPs.</p>
        </div>
    </div>
</div>