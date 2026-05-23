<?php include "header.php"; 
    //   include 'function.php';

// Pagination settings
$records_per_page = 25;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;

// Search functionality
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_condition = '';
if (!empty($search)) {
    $search_condition = " AND (name LIKE '%$search%' OR mobile LIKE '%$search%' OR company LIKE '%$search%')";
}

// Get summary statistics
$today = date('Y-m-d');
$summary_query = "SELECT 
    COUNT(*) as total_users,
    SUM(CASE WHEN expiry >= '$today' THEN 1 ELSE 0 END) as active_users,
    SUM(CASE WHEN expiry < '$today' THEN 1 ELSE 0 END) as expired_users,
    SUM(CASE WHEN vip_expiry >= '$today' THEN 1 ELSE 0 END) as vip_users,
    SUM(CASE WHEN kycstatus = 'Verified' THEN 1 ELSE 0 END) as kyc_verified,
    SUM(balance) as total_balance,
    AVG(balance) as avg_balance
    FROM users WHERE role != 'Admin'";
    
$summary_result = mysqli_query($conn, $summary_query);
$summary = mysqli_fetch_assoc($summary_result);

// Get total count for pagination
$count_query = "SELECT COUNT(*) as total FROM users WHERE role != 'Admin' $search_condition";
$count_result = mysqli_query($conn, $count_query);
$total_records = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_records / $records_per_page);
?>

<!-- Summary Dashboard -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h3><?= number_format($summary['total_users']) ?></h3>
                        <p class="mb-0">Total Users</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fa fa-users fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h3><?= number_format($summary['active_users']) ?> Active Users</h3>
                        <small><?= $summary['total_users'] > 0 ? round(($summary['active_users']/$summary['total_users'])*100, 1) : 0 ?>%</small>
                    </div>
                    <div class="align-self-center">
                        <i class="fa fa-check-circle fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h3><?= number_format($summary['vip_users']) ?> VIP Users</h3>
                        <small><?= $summary['total_users'] > 0 ? round(($summary['vip_users']/$summary['total_users'])*100, 1) : 0 ?>%</small>
                    </div>
                    <div class="align-self-center">
                        <i class="fa fa-star fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h3><?= number_format($summary['kyc_verified']) ?> KYC Verified</h3>
                        <small><?= $summary['total_users'] > 0 ? round(($summary['kyc_verified']/$summary['total_users'])*100, 1) : 0 ?>%</small>
                    </div>
                    <div class="align-self-center">
                        <i class="fa fa-shield fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Additional Summary Row -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h3><?= number_format($summary['expired_users']) ?> Expired Users</h3>

                        <small><?= $summary['total_users'] > 0 ? round(($summary['expired_users']/$summary['total_users'])*100, 1) : 0 ?>%</small>
                    </div>
                    <div class="align-self-center">
                        <i class="fa fa-exclamation-triangle fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card bg-dark text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h3>₹<?= number_format($summary['total_balance'], 2) ?> Total Balance</h3>
                        <small>Avg: ₹<?= number_format($summary['avg_balance'], 2) ?></small>
                    </div>
                    <div class="align-self-center">
                        <i class="fa fa-wallet fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card bg-secondary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h3><?= number_format($summary['kyc_verified'] > 0 ? ($summary['total_balance'] / $summary['kyc_verified']) : 0, 0) ?> Avg KYC Balance</h3>
                        <small>Per verified user</small>
                    </div>
                    <div class="align-self-center">
                        <i class="fa fa-chart-line fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Expiring Users Section -->
<div class="ibox">
    <div class="ibox-head">
        <div class="ibox-title">Expiring Users (Next 5 Days)</div>
    </div>
    <div class="ibox-body">
        <form action="" method="POST">
            <table class="table table-striped table-bordered table-hover" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>User Name</th>
                        <th>Mobile No</th>
                        <th>Shop Name</th>
                        <th>Expire Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $today = date('Y-m-d'); 
                    $startDate = date('Y-m-d', strtotime($today . ' -4 days'));
                    $endDate = date('Y-m-d', strtotime($today . ' +1 days'));

                    $query = "SELECT `id`, `name`, `mobile`, `company`, `expiry` FROM `users` WHERE `expiry` BETWEEN '$startDate' AND '$endDate' LIMIT 10";
                    $query_run = mysqli_query($conn, $query);
 
                    if ($query_run && mysqli_num_rows($query_run) > 0) {
                        while ($row = mysqli_fetch_assoc($query_run)) {
                        $expiryDate = htmlspecialchars($row['expiry']);
                        $today = date('Y-m-d');
                        if (strtotime($expiryDate) >= strtotime($today)) {
                            $xstatus = "<span class='badge bg-warning'>Will Expire</span>";
                        } else {
                            $xstatus = "<span class='badge bg-danger'>Expired</span>";
                        }                            
                            
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8') . "</td>";
                            echo "<td>" . htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8') . "</td>";
                            echo "<td>" . htmlspecialchars($row['mobile'], ENT_QUOTES, 'UTF-8') . "</td>";
                            echo "<td>" . htmlspecialchars($row['company'], ENT_QUOTES, 'UTF-8') . "</td>";
                            echo "<td>" . htmlspecialchars($row['expiry'], ENT_QUOTES, 'UTF-8') . "</td>";
                            echo "<td>" . $xstatus . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6'>No expiring users found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
            <button type="submit" class="btn btn-warning" name="notify_all">Notify All</button>
        </form>
    </div>
</div>

<?php
if (isset($_POST['notify_all'])) {
    // URL जिसे हिट करना है
    $url = "https://chickenpox.in/crons/CronExpiryAlert.php";
    // cURL सेशन प्रारंभ करें
    $ch = curl_init();
    // cURL के विकल्प सेट करें
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    // HTTP अनुरोध भेजें और प्रतिक्रिया प्राप्त करें
    $response = curl_exec($ch);
    // cURL से कोई त्रुटि आई है या नहीं, यह जांचें
    if (curl_errno($ch)) {
        echo "<div class='alert alert-danger'>cURL Error: " . curl_error($ch) . "</div>";
    } else {
        echo "<div class='alert alert-success'>Notification sent successfully!</div>";
    }
    // cURL सेशन बंद करें
    curl_close($ch);
}
?>

<!-- Main User List -->
<div class="page-heading">
    <h1 class="page-title">Merchant List</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="index.html"><i class="la la-home font-20"></i></a>
        </li>
    </ol>
</div>

<div class="page-content fade-in-up">
    <div class="ibox">
        <div class="ibox-head">
            <div class="ibox-title">
                Users (Showing <?= $offset + 1 ?> - <?= min($offset + $records_per_page, $total_records) ?> of <?= $total_records ?>)
            </div>
            <div class="ibox-tools">
                <!-- Search Form -->
                <form method="GET" class="d-inline-flex">
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" 
                           placeholder="Search users..." class="form-control form-control-sm me-2" style="width: 200px;">
                    <button type="submit" class="btn btn-sm btn-primary">Search</button>
                    <?php if (!empty($search)): ?>
                        <a href="?" class="btn btn-sm btn-secondary ms-1">Clear</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
        <div class="ibox-body">
            <div class="table-responsive">
                <table class="table table-striped table-bordered table-hover" cellspacing="0" width="100%">
                    <thead class="thead-dark">
                        <tr>
                            <th>#</th>
                            <th>User Type</th>
                            <th>User Name</th>
                            <th>Shop Name</th>
                            <th>Mobile No</th>
                            <th>Balance</th>
                            <th>Expiry Date</th>
                            <th>Status</th>
                            <th>Transactions</th>
                            <th>Plan ID</th>
                            <th>Referred By</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $userid = $userdata['id'];

                        $query = "SELECT `id`, `balance`, `name`, `mobile`, `role`, `company`, `expiry`, `vip_expiry`, `kycstatus`, `tranjection_Count`, `planId`, `referred_by` 
                                  FROM `users` 
                                  WHERE role != 'Admin' $search_condition 
                                  ORDER BY id DESC 
                                  LIMIT $records_per_page OFFSET $offset";
                        $query_run = mysqli_query($conn, $query);

                        if ($query_run && mysqli_num_rows($query_run) > 0) {
                            $counter = $offset + 1;
                            while ($row = mysqli_fetch_assoc($query_run)) {
                                $expiryDate = $row['expiry'];
                                $vip_expiry = $row['vip_expiry'];
                                $today = date('Y-m-d');
                                $kycStatus = $row['kycstatus'];

                                // Determine subscription status
                                if (strtotime($expiryDate) >= strtotime($today)) {
                                    $subscriptionStatus = "<span class='badge bg-success'>Active</span>";
                                } else {
                                    $subscriptionStatus = "<span class='badge bg-danger'>Expired</span>";
                                }

                                // Determine VIP status
                                if (strtotime($vip_expiry) >= strtotime($today)) {
                                    $vipStatus = "<span class='badge bg-warning'>VIP Active</span>";
                                } else {
                                    $vipStatus = "<span class='badge bg-secondary'>VIP Expired</span>";
                                }

                                // Determine KYC status
                                if ($kycStatus == 'Verified') {
                                    $kycBadge = "<span class='badge bg-info'>KYC Verified</span>";
                                } else {
                                    $kycBadge = "<span class='badge bg-light text-dark'>KYC Pending</span>";
                                }

                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['role']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['company']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['mobile']) . "</td>";
                                echo "<td>₹ " . htmlspecialchars(number_format($row['balance'], 2)) . "</td>";
                                
                                $date = new DateTime($row['expiry']);
                                $formattedDate = $date->format('d/m/Y');
                                echo "<td>" . htmlspecialchars($formattedDate) . "</td>";
                                
                                // Combined status badges
                                echo "<td>$subscriptionStatus $vipStatus $kycBadge</td>";
                                
                                echo "<td>" . htmlspecialchars($row['tranjection_Count']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['planId']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['referred_by']) . "</td>";

                                echo "<td>";
                                echo "<div class='d-flex gap-2'>";
                                echo "<form action='edituser.php' method='post'>";
                                echo "<input type='hidden' name='csrf_token' value='" . $_SESSION['csrf_token'] . "'>";
                                echo "<input type='hidden' name='mobileno' value='" . $row['mobile'] . "'>";
                                echo "<button type='submit' class='btn btn-sm btn-primary' name='edituser'>Edit</button>";
                                echo "</form>";

                                echo "<form action='" . htmlspecialchars($_SERVER["PHP_SELF"]) . "' method='POST'>";
                                echo "<input type='hidden' name='csrf_token' value='" . $_SESSION['csrf_token'] . "'>";
                                echo "<input type='hidden' name='mobileno' value='" . $row['mobile'] . "'>";
                                echo "<button type='submit' class='btn btn-sm btn-danger' name='delete' onclick='return confirm(\"Are you sure you want to delete this user?\")'>Delete</button>";
                                echo "</form>";
                                echo "</div>";
                                echo "</td>";

                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='12'>No records found.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    <p class="text-muted mb-0">
                        Showing <?= $offset + 1 ?> to <?= min($offset + $records_per_page, $total_records) ?> 
                        of <?= $total_records ?> entries
                    </p>
                </div>
                <nav>
                    <ul class="pagination pagination-sm mb-0">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $page - 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">
                                    <i class="fa fa-chevron-left"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <?php
                        $start_page = max(1, $page - 2);
                        $end_page = min($total_pages, $page + 2);
                        
                        if ($start_page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=1<?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">1</a>
                            </li>
                            <?php if ($start_page > 2): ?>
                                <li class="page-item disabled"><span class="page-link">...</span></li>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($end_page < $total_pages): ?>
                            <?php if ($end_page < $total_pages - 1): ?>
                                <li class="page-item disabled"><span class="page-link">...</span></li>
                            <?php endif; ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $total_pages ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>"><?= $total_pages ?></a>
                            </li>
                        <?php endif; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $page + 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">
                                    <i class="fa fa-chevron-right"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.card {
    border: none;
    border-radius: 10px;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
}

.card:hover {
    transform: translateY(-2px);
}

.opacity-50 {
    opacity: 0.5;
}

.table-responsive {
    border-radius: 8px;
}

.pagination .page-link {
    border-radius: 5px;
    margin: 0 2px;
}

.badge {
    font-size: 0.8em;
    margin: 1px;
}

@media (max-width: 768px) {
    .d-flex.gap-2 {
        flex-direction: column;
        gap: 0.5rem !important;
    }
    
    .btn-sm {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
    }
}
</style>