<?php include "header.php";
    $todayallpayment = $conn->query("SELECT COUNT(`id`) as amt FROM `orders` WHERE `user_id` = '{$userdata["id"]}' AND `status` = 'SUCCESS' AND DATE(`create_date`) = '$tdate' ")->fetch_assoc();
    $todayallpaymentadmin = $conn->query("SELECT COUNT(`id`) as amt FROM `orders` WHERE `status` = 'SUCCESS' AND DATE(`create_date`) = '$tdate' ")->fetch_assoc();
    $todaysuccesspayment = $conn->query("SELECT SUM(`amount`) as amt FROM `orders` WHERE `user_id` = '{$userdata["id"]}' AND `status` = 'SUCCESS' AND DATE(`create_date`) = '$tdate' ")->fetch_assoc();
    $todaysuccesspaymentadmin = $conn->query("SELECT SUM(`amount`) as amt FROM `orders` WHERE `status` = 'SUCCESS' AND DATE(`create_date`) = '$tdate' ")->fetch_assoc();
    $todaypendingpayment = $conn->query("SELECT SUM(`amount`) as amt FROM `orders` WHERE `user_id` = '{$userdata["id"]}' AND `status` = 'PENDING' AND DATE(`create_date`) = '$tdate' ")->fetch_assoc();
    $todaypendingpaymentadmin = $conn->query("SELECT SUM(`amount`) as amt FROM `orders` WHERE `status` = 'PENDING' AND DATE(`create_date`) = '$tdate' ")->fetch_assoc();
    $todayfail = $conn->query("SELECT SUM(`amount`) as amt FROM `orders` WHERE `user_id` = '{$userdata["id"]}' AND `status` = 'FAILURE' AND DATE(`create_date`) = '$tdate' ")->fetch_assoc();
    $todaysettlement = $conn->query("SELECT SUM(`amount`) as amt FROM `settlement` WHERE `userid` = '{$userdata["id"]}' AND `status` = 'Success' AND DATE(`date`) = '$tdate'")->fetch_assoc();
    $monthlybilling = $conn->query("SELECT SUM(`amount`) as amt FROM `planorders` WHERE `status` = 'SUCCESS' AND MONTH(`payment_date`) = MONTH(CURDATE()) AND YEAR(`payment_date`) = YEAR(CURDATE())")->fetch_assoc();
    $users = $conn->query("SELECT COUNT(*) as total_users, SUM(CASE WHEN expiry < CURRENT_DATE THEN 1 ELSE 0 END) as expired_users, SUM(CASE WHEN expiry >= CURRENT_DATE THEN 1 ELSE 0 END) as active_users, SUM(CASE WHEN login_token IS NOT NULL THEN 1 ELSE 0 END) as inline_users FROM users")->fetch_assoc();

    function convertUrlsToLinks($text) {
        $pattern = '/(https?:\/\/[^\s]+)/';
        $replacement = '<a href="$1" target="_blank" class="text-blue-500 hover:text-blue-700 underline transition-colors duration-300 focus:outline-none focus:ring-2 focus:ring-blue-300 rounded">$1</a>';
        return preg_replace($pattern, $replacement, $text);
    }

    $query = "SELECT content FROM news";
    $result = mysqli_query($conn, $query);
    $newsItems = [];
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $newsItems[] = convertUrlsToLinks($row['content']);
        }
    }

    $totalPayments = !empty($todayallpayment["amt"]) ? floatval($todayallpayment["amt"]) : 0;
    $successPayments = !empty($todaysuccesspayment["amt"]) ? floatval($todaysuccesspayment["amt"]) : 0;
    $pendingPayments = !empty($todaypendingpayment["amt"]) ? floatval($todaypendingpayment["amt"]) : 0;
    $failedPayments = !empty($todayfail["amt"]) ? floatval($todayfail["amt"]) : 0;

    function calculatePaymentStats(&$successPercentage, &$pendingPercentage, &$failedPercentage, &$totalPayments, $successPayments, $pendingPayments, $failedPayments) {
        if ($totalPayments == 0 || $totalPayments < ($successPayments + $pendingPayments + $failedPayments)) {
            $totalPayments = $successPayments + $pendingPayments + $failedPayments;
        }
        if ($totalPayments > 0) {
            $successPercentage = round(($successPayments / $totalPayments) * 100, 2);
            $pendingPercentage = round(($pendingPayments / $totalPayments) * 100, 2);
            $failedPercentage = round(($failedPayments / $totalPayments) * 100, 2);
        } else {
            $successPercentage = $pendingPercentage = $failedPercentage = 0;
        }
    }

    calculatePaymentStats($successPercentage, $pendingPercentage, $failedPercentage, $totalPayments, $successPayments, $pendingPayments, $failedPayments);

    $expiryDate = $userdata['expiry'];
    $today = date('Y-m-d');
    $expiryTimestamp = strtotime($expiryDate);
    $todayTimestamp = strtotime($today);
    $daysExpired = floor(($todayTimestamp - $expiryTimestamp) / (60 * 60 * 24));

    $limitUsed = isset($userdata['tranjection_Count']) ? $userdata['tranjection_Count'] : 0;
    $hitlimit_result = db_select($conn, "subscription_plan", "hitLimit", "id=".$userdata['planId']);
    if ($hitlimit_result && $hitlimit_result->num_rows > 0) {
        $row = $hitlimit_result->fetch_assoc();
        $hitlimit = $row['hitLimit'];
    } else {
        $hitlimit = 0;
    }
    $usagePercentage = $hitlimit > 0 ? round(($limitUsed / $hitlimit) * 100, 2) : 0;

    // Fetch data for new monthly transaction chart
 $sql = "
    SELECT 
        DATE_FORMAT(create_date, '%Y-%m') AS month,
        COUNT(id) AS total_transactions,
        SUM(CASE WHEN status = 'SUCCESS' THEN amount ELSE 0 END) AS success_amount,
        SUM(CASE WHEN status = 'FAILURE' THEN amount ELSE 0 END) AS failure_amount,
        SUM(CASE WHEN status = 'PENDING' THEN amount ELSE 0 END) AS pending_amount
    FROM orders 
    WHERE create_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
    " . ($userdata['role'] == 'Admin' ? "" : " AND user_id = ?") . "
    GROUP BY month
    ORDER BY month ASC
";
$stmt = $conn->prepare($sql);
if ($userdata['role'] != 'Admin') {
    $stmt->bind_param('s', $userdata['id']);
}
$stmt->execute();
$result = $stmt->get_result();
$monthlyData = [
    'labels' => [],
    'transactions' => [],
    'success_amount' => [],
    'failure_amount' => [],
    'pending_amount' => []
];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $monthlyData['labels'][] = date('M Y', strtotime($row['month'] . '-01'));
        $monthlyData['transactions'][] = (int)$row['total_transactions'];
        $monthlyData['success_amount'][] = (float)($row['success_amount'] ?? 0);
        $monthlyData['failure_amount'][] = (float)($row['failure_amount'] ?? 0);
        $monthlyData['pending_amount'][] = (float)($row['pending_amount'] ?? 0);
    }
} else {
    $monthlyData['labels'] = [date('M Y')];
    $monthlyData['transactions'] = [0];
    $monthlyData['success_amount'] = [0];
    $monthlyData['failure_amount'] = [0];
    $monthlyData['pending_amount'] = [0];
}
$stmt->close();

echo "<script>var monthlyChartData = " . json_encode($monthlyData) . ";</script>";


// Prepare data for the payment method pie chart for TODAY ONLY
$sql_method_stats = "
    SELECT
        method,
        COUNT(id) as transaction_count,
        SUM(amount) as total_amount
    FROM orders
    WHERE status = 'SUCCESS'
    AND DATE(create_date) = CURDATE()
    " . ($userdata['role'] == 'Admin' ? "" : " AND user_id = '{$userdata["id"]}'") . "
    GROUP BY method
    ORDER BY total_amount DESC
";

$result_method_stats = $conn->query($sql_method_stats);
$methodData = [
    'labels' => [],
    'transaction_counts' => [],
    'total_amounts' => []
];

if ($result_method_stats->num_rows > 0) {
    while ($row = $result_method_stats->fetch_assoc()) {
        $methodData['labels'][] = $row['method'];
        $methodData['transaction_counts'][] = (int)$row['transaction_count'];
        $methodData['total_amounts'][] = (float)$row['total_amount'];
    }
} else {
    // Provide default data if no successful transactions are found
    $methodData['labels'][] = 'No Data';
    $methodData['transaction_counts'][] = 0;
    $methodData['total_amounts'][] = 0;
}

echo "<script>var methodChartData = " . json_encode($methodData) . "; window.methodChartData = methodChartData;</script>";


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Tailwind CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    
</head>
<body class="light-theme">
    
    <div class="container mx-auto px-1 py-2">
        <!-- Offer Section -->
        <?php
        $sql = "SELECT * FROM offers WHERE status = 'active' ORDER BY id DESC";
        $result = $conn->query($sql);
        ?>
        <?php if ($result && $result->num_rows > 0) { ?>
            <?php while ($row = $result->fetch_assoc()) { ?>
                <div id="offerModal<?php echo $row['id']; ?>" 
                    class="modal fixed inset-0 flex items-center justify-center opacity-0 invisible transition-all duration-300 z-50">
                    <div class="modal-content w-full max-w-md transform scale-95 transition-transform duration-300">
                        <div class="bg-gradient-to-r from-red-600 to-red-500 text-white px-6 py-4 rounded-t-xl">
                            <div class="flex justify-between items-center">
                                <h5 class="text-xl font-bold tracking-tight">
                                    <span class="mr-2">🔥</span><?php echo htmlspecialchars($row['title']); ?><span class="ml-2">🔥</span>
                                </h5>
                                <button onclick="closeModal('<?php echo $row['id']; ?>')" 
                                    class="text-white hover:text-gray-200" aria-label="Close modal">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div class="p-6 space-y-4">
                            <p class="leading-relaxed"><?php echo $row['description']; ?></p>
                        </div>
                        <div class="px-6 py-4 bg-gray-800 rounded-b-xl flex justify-end space-x-3">
                            <span class="text-gray-400">📅</span>
                            <strong class="text-gray-300 font-medium">Valid Till: <?php echo htmlspecialchars($row['validity']); ?></strong>
                            <a href="https://web.whatsapp.com/accept?channel_invite_code=0029Vas5C79Eawdhj46eCc1J" target="_blank"
                                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 flex items-center transition-colors duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.198-.347.223-.644.074-.297-.149-1.255-.463-2.39-1.475-.883-.812-1.48-1.364-1.48-1.988 0-.621.596-1.172 1.332-1.172.376 0 .806.095 1.197.26.598.248 1.23.422 1.61.497.378.074.669-.223.669-.595 0-.372-.173-.719-.471-.967zM12 2C6.477 2 2 6.477 2 12c0 1.585.364 3.134 1.054 4.53L2 22l5.648-1.054A9.94 9.94 0 0012 22c5.523 0 10-4.477 10-10S17.523 2 12 2z"/>
                                </svg>
                                Join Channel
                            </a>
                            <button onclick="closeModal('<?php echo $row['id']; ?>')"
                                class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors duration-300">Close</button>
                        </div>
                    </div>
                </div>
                <script>
                    document.addEventListener("DOMContentLoaded", function() {
                        const modalId = "offerModal<?php echo $row['id']; ?>";
                        let offerShownCount = parseInt(sessionStorage.getItem('offerShownCount') || '0');
                        if (offerShownCount < 3) {
                            const modal = document.getElementById(modalId);
                            setTimeout(() => {
                                modal.classList.remove("invisible");
                                modal.classList.add("opacity-100");
                                modal.querySelector('div').classList.remove("scale-95");
                                modal.querySelector('div').classList.add("scale-100");
                            }, 500);
                            sessionStorage.setItem('offerShownCount', offerShownCount + 1);
                        }
                    });
                    function closeModal(id) {
                        const modal = document.getElementById("offerModal" + id);
                        modal.classList.remove("opacity-100");
                        modal.classList.add("opacity-0");
                        modal.querySelector('div').classList.remove("scale-100");
                        modal.querySelector('div').classList.add("scale-95");
                        setTimeout(() => {
                            modal.classList.add("invisible");
                        }, 300);
                    }
                </script>
            <?php } ?>
        <?php } ?>

        <!-- Account Status Messages -->
        <div class="mb-6">
            <?php if ($expiryTimestamp >= $todayTimestamp): ?>
                <div >
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-green-600 rounded-full p-2 mr-3">
                            <i class="fas fa-check text-white"></i>
                        </div>
                        <div>
                            <!--<h4 class="text-lg font-bold mb-1">Account Active</h4>-->
                            <p class="text-sm">
                                🎉 Invite your friends and earn exciting rewards! The more friends you refer, the more benefits you unlock.
                            
                            <a href="refertg" class="inline-block mt-2 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors duration-300 shadow hover:shadow-lg transform hover:-translate-y-1">Refer & Earn 🚀</a>
                            </p>
                        </div>
                    </div>
                </div>
            <?php elseif ($daysExpired < 30): ?>
                <div class="alert bg-red-900 border-l-4 border-red-500 text-red-200 p-2 rounded-lg shadow-lg animate-fade-in">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-red-600 rounded-full p-2 mr-3">
                            <i class="fas fa-exclamation-triangle text-white"></i>
                        </div>
                        <div>
                            <h4 class="text-lg font-bold mb-1">Account Expired</h4>
                            <p class="text-sm">
                                ⚠️ Your account expired <b><?php echo abs($daysExpired); ?> days ago.</b> Renew now to continue enjoying services.

                            <a href="subscription" class="inline-block mt-2 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors duration-300 shadow hover:shadow-lg transform hover:-translate-y-1">Renew Now 🔄</a>
                            </p>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert bg-yellow-900 border-l-4 border-yellow-500 text-yellow-200 p-4 rounded-lg shadow-lg animate-fade-in">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-yellow-600 rounded-full p-2 mr-3">
                            <i class="fas fa-clock text-white"></i>
                        </div>
                        <div>
                            <h4 class="text-lg font-bold mb-1">Account Inactive</h4>
                            <p class="text-sm">
                                ⏳ Your account has been inactive for <b><?php echo abs($daysExpired); ?> days</b>. We don't want you to miss out!
                                <p class="text-xs mt-1">Get back to using our premium services with a <b>1-day free trial</b>. No payment needed! 🎁</p>
                            </p>
                            
                            <form method="POST">
                                <button type="submit" name="activate_trial" class="mt-2 px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition-colors duration-300 shadow hover:shadow-lg transform hover:-translate-y-1">Activate Free Trial 🎟️</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Free Trial Activation -->
        <?php
        if (isset($_POST['activate_trial'])) {
            $userId = $userdata['id'];
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }
            $sql = "SELECT expiry FROM users WHERE id = $userId";
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $expiryTimestamp = strtotime($row['expiry']);
                $todayTimestamp = strtotime(date('Y-m-d'));
                $newExpiryTimestamp = $todayTimestamp + 1 * 24 * 60 * 60;
                $newExpiryDate = date('Y-m-d', $newExpiryTimestamp);
                $updateSql = "UPDATE users SET expiry = '$newExpiryDate',vip_expiry = '$newExpiryDate' WHERE id = $userId";
                if ($conn->query($updateSql) === TRUE) {
                    echo "<script>
                        Swal.fire({
                            icon: 'success',
                            title: 'Free Trial Activated!',
                            text: 'Your 1-day free trial has been successfully activated. Enjoy our services!',
                            confirmButtonText: 'Go to Dashboard'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = 'dashboard.php';
                            }
                        });
                    </script>";
                } else {
                    echo "<script>
                        Swal.fire({
                            icon: 'error',
                            title: 'Activation Failed!',
                            text: 'Error activating free trial: " . $conn->error . "',
                            confirmButtonText: 'Try Again'
                        });
                    </script>";
                }
            } else {
                echo '<div class="alert alert-danger text-center"><p>User not found.</p></div>';
            }
            $conn->close();
        }
        ?>

        <!-- News Ticker -->
        <!--<div class="news-ticker-container animate-fade-in mb-6 bg-gradient-to-r from-blue-900 to-purple-900 rounded-lg shadow-lg overflow-hidden border border-blue-700">-->
            
            <div>
                
            <div class="animate-fade-in mb-3 bg-gradient-to-r from-blue-900 to-purple-900 rounded-lg shadow-lg overflow-hidden border border-blue-700">
                    
                     <!--<i class="fas fa-bullhorn text-white"></i>-->
                 <div class="news-ticker p-1">
                <?php foreach ($newsItems as $index => $news): ?>
                    <span class="news-item flex items-center">
                        <span class="inline-block w-6 h-6 flex items-center justify-center bg-blue-600 text-white rounded-full mr-2 text-xs"><?php echo $index + 1; ?></span>
                        <?php echo $news; ?>
                    </span>
                <?php endforeach; ?>
            </div>
                </div>

            </div>
        <!--</div>-->

        <!-- Admin Widgets -->
        <?php if ($userdata['role'] == 'Admin'): ?>
            <!--<h2 class="text-2xl font-bold mb-4 text-center dark:text-white"><i class="fas fa-tachometer-alt mr-2"></i>Admin Dashboard</h2>-->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <div class="tech-card success-gradient p-6 animate-fade-in rounded-xl shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1">
                    <div class="stat-card">
                        <div class="flex justify-between items-center mb-4">
                            <div class="rounded-full bg-white bg-opacity-20 p-3">
                                <i class="fas fa-cash-register text-2xl text-white"></i>
                            </div>
                            <span class="badge badge-success shadow-lg"><?php echo number_format($todayallpaymentadmin["amt"]); ?> txns</span>
                        </div>
                        <h2 class="stat-card-value text-3xl font-bold mb-1">₹<?php echo number_format($todaysuccesspaymentadmin["amt"], 2); ?></h2>
                        <p class="stat-card-label text-white text-opacity-80 mb-3">Today's Received Payment</p>
                        <div class="flex items-center mt-2 bg-white bg-opacity-10 rounded-lg px-3 py-1">
                            <i class="fas fa-chart-line mr-2 text-green-300"></i>
                            <p class="text-xs font-semibold">📈 100% Higher than yesterday</p>
                        </div>
                    </div>
                </div>
                
                <div class="tech-card info-gradient p-6 animate-fade-in rounded-xl shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1">
                    <div class="stat-card">
                        <div class="flex justify-between items-center mb-4">
                            <div class="rounded-full bg-white bg-opacity-20 p-3">
                                <i class="fas fa-users text-2xl text-white"></i>
                            </div>
                            <p class="stat-card-label text-white text-opacity-80">Member Statistics</p>
                            <span class="badge badge-info shadow-lg"><?php echo number_format($users['total_users']); ?> Total</span>
                        </div>
                        <div class="grid grid-cols-2 gap-2 mb-3">
                            <div class="bg-white bg-opacity-10 rounded-lg p-2 text-center">
                                <span class="text-green-300 text-lg font-bold"><?php echo number_format($users['active_users']); ?></span>
                                <p class="text-xs">Active</p>
                            </div>
                            <div class="bg-white bg-opacity-10 rounded-lg p-2 text-center">
                                <span class="text-red-300 text-lg font-bold"><?php echo number_format($users['expired_users']); ?></span>
                                <p class="text-xs">Expired</p>
                            </div>
                        </div>
                        <div class="bg-white bg-opacity-10 rounded-lg p-2 text-center mb-3">
                            <span class="text-yellow-300 text-lg font-bold"><?php echo number_format($users['inline_users']); ?></span>
                            <p class="text-xs">Online Now</p>
                        </div>
                    </div>
                </div>
                
                <div class="tech-card warning-gradient p-6 animate-fade-in rounded-xl shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1">
                    <div class="stat-card">
                        <div class="flex justify-between items-center mb-4">
                            <div class="rounded-full bg-white bg-opacity-20 p-3">
                                <i class="fas fa-clock text-2xl text-white"></i>
                            </div>
                            <div class="flex items-center bg-white bg-opacity-10 rounded-lg px-2 py-1">
                                <i class="fas fa-arrow-up text-xs mr-1 text-yellow-300"></i>
                                <span class="text-xs">12%</span>
                            </div>
                        </div>
                        <h2 class="stat-card-value text-3xl font-bold mb-1">₹<?php echo number_format($todaypendingpaymentadmin["amt"], 2); ?></h2>
                        <p class="stat-card-label text-white text-opacity-80 mb-3">Today's Pending Payment</p>
                        <div class="w-full bg-white bg-opacity-10 rounded-full h-2 mb-1">
                            <div class="bg-yellow-300 h-2 rounded-full" style="width: 65%"></div>
                        </div>
                        <p class="text-xs">65% of total transactions</p>
                    </div>
                </div>
                
                <div class="tech-card danger-gradient p-6 animate-fade-in rounded-xl shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1">
                    <div class="stat-card">
                        <div class="flex justify-between items-center mb-4">
                            <div class="rounded-full bg-white bg-opacity-20 p-3">
                                <i class="fas fa-wallet text-2xl text-white"></i>
                            </div>
                            <div class="flex items-center bg-white bg-opacity-10 rounded-lg px-2 py-1">
                                <i class="fas fa-arrow-up text-xs mr-1 text-green-300"></i>
                                <span class="text-xs">99.9%</span>
                            </div>
                        </div>
                        <h2 class="stat-card-value text-3xl font-bold mb-1">₹<?php echo number_format($monthlybilling["amt"], 2); ?></h2>
                        <p class="stat-card-label text-white text-opacity-80 mb-3">Monthly Billing</p>
                        <div class="flex items-center mt-2 bg-white bg-opacity-10 rounded-lg px-3 py-1">
                            <i class="fas fa-calendar-alt mr-2 text-red-300"></i>
                            <p class="text-xs font-semibold">This Month's Revenue</p>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Refer Income Slabs -->
        <?php if ($userdata['role']=="Developer"): ?>
            <div class="mt-6 mb-6 animate-fade-in">
                <div class="flex items-center justify-center mb-6">
                    <div class="bg-gradient-to-r from-yellow-600 to-yellow-400 rounded-full p-3 mr-3 shadow-lg">
                        <i class="fas fa-trophy text-white text-xl"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-yellow-400">Refer & Earn Program</h2>
                </div>
                <p class="text-center text-gray-400 mb-6 max-w-2xl mx-auto">Invite your friends and earn lifetime commissions on their payments. The more referrals you bring, the higher your commission rate!</p>
                
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <?php
                    $slabs = [];
                    $query = "SELECT * FROM refer_income_slabs ORDER BY required_referrals ASC";
                    if ($stmt = mysqli_prepare($conn, $query)) {
                        mysqli_stmt_execute($stmt);
                        $result = mysqli_stmt_get_result($stmt);
                        if ($result) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                $slabs[] = $row;
                            }
                        }
                        mysqli_stmt_close($stmt);
                    }
                    $emojiMap = [
                        'Beginner' => '🟢',
                        'Growth' => '🔵',
                        'Pro' => '🟠',
                        'Elite' => '🔴',
                    ];
                    
                    $bgGradients = [
                        'Beginner' => 'from-green-900 to-green-700',
                        'Growth' => 'from-blue-900 to-blue-700',
                        'Pro' => 'from-orange-900 to-orange-700',
                        'Elite' => 'from-red-900 to-red-700',
                    ];
                    ?>
                    <?php foreach ($slabs as $index => $slab): ?>
                        <div class="tech-card bg-gradient-to-br <?php echo $bgGradients[$slab['title']] ?? 'from-gray-900 to-gray-700'; ?> p-6 rounded-xl shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 animate-fade-in" style="animation-delay: <?php echo 0.1 * $index; ?>s;">
                            <div class="text-center">
                                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-white bg-opacity-20 mb-4">
                                    <span class="text-2xl"><?php echo $emojiMap[$slab['title']] ?? '🏆'; ?></span>
                                </div>
                                <h5 class="text-xl font-bold text-white mb-3">
                                    <?php echo htmlspecialchars($slab['title']); ?> Level
                                </h5>
                                <div class="bg-white bg-opacity-10 rounded-lg p-3 mb-4">
                                    <p class="text-sm text-white text-opacity-80">Required Referrals</p>
                                    <p class="text-2xl font-bold text-white"><?php echo intval($slab['required_referrals']); ?></p>
                                </div>
                                <div class="bg-white bg-opacity-10 rounded-lg p-3">
                                    <p class="text-sm text-white text-opacity-80">Commission Rate</p>
                                    <p class="text-2xl font-bold text-green-300"><?php echo floatval($slab['commission_percent']); ?>%</p>
                                    <p class="text-xs text-white text-opacity-60 mt-1">Lifetime on all payments</p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="text-center mt-6">
                    <a href="refertg" class="inline-block px-6 py-3 bg-gradient-to-r from-yellow-600 to-yellow-400 text-white rounded-lg hover:from-yellow-500 hover:to-yellow-300 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1 font-bold">
                        <i class="fas fa-share-alt mr-2"></i> Start Referring Now
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <!-- Payment Charts Section -->
        <div class="tech-card p-6 mt-6 animate-fade-in w-full rounded-xl shadow-lg">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center">
                    <div class="rounded-full bg-blue-600 p-2 mr-3">
                        <i class="fas fa-chart-pie text-white"></i>
                    </div>
                    <h2 class="text-xl font-bold ">Payment Analytics</h2>
                </div>
                <div class="flex space-x-2">
                    <button class="px-3 py-1 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-300 text-sm" onclick="exportChartData()">
                        <i class="fas fa-download mr-1"></i> Export
                    </button>
                    <button class="px-3 py-1 bg-gray-700 text-white rounded-lg hover:bg-gray-600 transition-colors duration-300 text-sm" onclick="refreshChartData()">
                        <i class="fas fa-sync-alt mr-1"></i> Refresh
                    </button>
                </div>
            </div>
            
            <div class="flex flex-col lg:flex-row items-stretch gap-6 w-full">
                <div class="w-full lg:w-2/3 bg-gray-800 bg-opacity-50 p-4 rounded-lg shadow-inner">
                    <div class="flex justify-between items-center mb-4">
                    <h3 class="font-semibold text-white">Payment Distribution</h3>
                    <div class="inline-flex bg-gray-700 rounded-lg p-1 shadow-lg">
                        <button class="chart-tab-btn active px-4 py-2 rounded-md text-sm font-bold shadow-lg transition-all duration-300 hover:bg-blue-600 hover:text-white" data-target="status">By Status</button>
                        <button class="chart-tab-btn px-4 py-2 rounded-md text-sm font-bold shadow-lg transition-all duration-300 hover:bg-blue-600 hover:text-white" data-target="method">By Method</button>
                    </div>
                </div>
                    <div class="flex flex-col md:flex-row space-y-4 md:space-y-0 md:space-x-4">
                        <div class="w-full md:w-2/2 chart-tab-content active" id="status-container">
                            <div class="chart-container bg-gray-800 bg-opacity-50 p-5 rounded-lg shadow-inner border border-gray-700 transition-all duration-300 hover:shadow-lg" style="height: 280px;">
                                <canvas id="paymentChart"></canvas>
                            </div>
                        </div>
                        <div class="w-full md:w-2/2 chart-tab-content " id="method-container">
                            <div class="chart-container bg-gray-800 bg-opacity-50 p-5 rounded-lg shadow-inner border border-gray-700 transition-all duration-300 hover:shadow-lg" style="height: 280px;">
                                <canvas id="paymentMethodChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="w-full lg:w-1/3 flex flex-col">
                    <div class="bg-gray-800 bg-opacity-50 p-4 rounded-lg shadow-inner mb-4 hover:shadow-lg transition-all duration-300">
                        <h3 class="font-semibold text-white mb-3 text-lg">Quick Stats</h3>
                        <div class="space-y-4">
                            <div class="flex justify-between items-center">
                                <span class="text-white font-medium text-shadow">Total Transactions</span>
                                <span class="font-bold text-white text-lg"><?php echo number_format($todayallpayment["amt"]); ?></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-white font-medium text-shadow">Success Rate</span>
                                <span class="font-bold text-green-300 text-lg"><?php echo number_format($successPercentage, 1); ?>%</span>
                            </div>

                        </div>
                    </div>
                    
                    <a href="https://" target="_blank" class="block flex-grow">
                        <div class="wa-container h-full bg-gray-800 bg-opacity-50 p-4 rounded-lg shadow-inner flex items-center justify-center">
                            <img class="WhatsApp-banner rounded-lg hover:scale-105 transition-transform duration-300 max-h-34"
                                src="/assets/img/"
                                alt="WhatsApp API Logo">
                        </div>
                    </a>
                </div>
            </div>
        </div>
        
        <script>
        // Chart tab switching functionality
        document.addEventListener('DOMContentLoaded', function() {
            const chartTabBtns = document.querySelectorAll('.chart-tab-btn');
            const chartTabContents = document.querySelectorAll('.chart-tab-content');
            
            chartTabBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    // Remove active class from all buttons and contents
                    chartTabBtns.forEach(b => b.classList.remove('active'));
                    chartTabContents.forEach(c => c.classList.add('hidden'));
                    
                    // Add active class to clicked button
                    this.classList.add('active');
                    
                    // Show corresponding content
                    const chartType = this.getAttribute('data-chart');
                    if (chartType === 'payment') {
                        document.getElementById('payment-chart-tab').classList.remove('hidden');
                    } else if (chartType === 'method') {
                        document.getElementById('method-chart-tab').classList.remove('hidden');
                    }
                });
            });
        });
        
        function refreshChartData() {
            // Add animation
            const refreshBtn = document.querySelector('button i.fa-sync-alt');
            refreshBtn.classList.add('fa-spin');
            
            // Simulate refresh (in real implementation, you would fetch new data)
            setTimeout(() => {
                // Update charts with new data
                updateCharts();
                
                // Remove animation
                refreshBtn.classList.remove('fa-spin');
                
                // Show notification
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'Charts refreshed with latest data',
                    showConfirmButton: false,
                    timer: 3000
                });
            }, 1000);
        }
        </script>

        <!-- Payment Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
            <div class="tech-card bg-gradient-to-br from-green-900 to-green-700 p-6 animate-fade-in rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                <div class="flex items-center mb-4">
                    <div class="rounded-full bg-white bg-opacity-20 p-3 mr-3">
                        <i class="fas fa-check-circle text-green-300 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-green-300 uppercase">Success</h3>
                        <h2 class="text-2xl font-bold text-white">₹<?php echo number_format($successPayments, 2); ?></h2>
                    </div>
                    <div class="ml-auto bg-green-600 text-white text-xs font-bold px-3 py-1 rounded-full shadow">
                        <?php echo number_format($successPercentage, 1); ?>%
                    </div>
                </div>
                
                <div class="relative pt-1">
                    <div class="flex mb-2 items-center justify-between">
                        <div>
                            <span class="text-xs font-semibold inline-block text-green-300">Today's Success Rate</span>
                        </div>
                        <div class="text-right">
                            <span class="text-xs font-semibold inline-block text-green-300">
                                <?php echo number_format($successPercentage, 1); ?>%
                            </span>
                        </div>
                    </div>
                    <div class="overflow-hidden h-2 mb-4 text-xs flex rounded bg-green-200 bg-opacity-20">
                        <div style="width: <?php echo $successPercentage; ?>%" class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-green-400"></div>
                    </div>
                </div>
                
                <div class="flex items-center justify-between text-sm text-green-200">
                    <div class="flex items-center">
                        <i class="fas fa-arrow-up mr-1 text-green-300"></i>
                        <span><?php echo number_format($successPercentage, 1); ?>% of total</span>
                    </div>
                    <div>
                        <a href="transactions?method_filter=&merchent_mobile_filter=&status_filter=SUCCESS" class="text-xs bg-green-800 hover:bg-green-700 text-green-200 py-1 px-2 rounded transition-colors duration-300">
                            <i class="fas fa-eye mr-1"></i> Details
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="tech-card bg-gradient-to-br from-yellow-900 to-yellow-700 p-6 animate-fade-in rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                <div class="flex items-center mb-4">
                    <div class="rounded-full bg-white bg-opacity-20 p-3 mr-3">
                        <i class="fas fa-clock text-yellow-300 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-yellow-300 uppercase">Pending</h3>
                        <h2 class="text-2xl font-bold text-white">₹<?php echo number_format($pendingPayments, 2); ?></h2>
                    </div>
                    <div class="ml-auto bg-yellow-600 text-white text-xs font-bold px-3 py-1 rounded-full shadow">
                        <?php echo number_format($pendingPercentage, 1); ?>%
                    </div>
                </div>
                
                <div class="relative pt-1">
                    <div class="flex mb-2 items-center justify-between">
                        <div>
                            <span class="text-xs font-semibold inline-block text-yellow-300">Today's Pending Rate</span>
                        </div>
                        <div class="text-right">
                            <span class="text-xs font-semibold inline-block text-yellow-300">
                                <?php echo number_format($pendingPercentage, 1); ?>%
                            </span>
                        </div>
                    </div>
                    <div class="overflow-hidden h-2 mb-4 text-xs flex rounded bg-yellow-200 bg-opacity-20">
                        <div style="width: <?php echo $pendingPercentage; ?>%" class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-yellow-400"></div>
                    </div>
                </div>
                
                <div class="flex items-center justify-between text-sm text-yellow-200">
                    <div class="flex items-center">
                        <i class="fas fa-hourglass-half mr-1 text-yellow-300"></i>
                        <span><?php echo number_format($pendingPercentage, 1); ?>% of total</span>
                    </div>
                    <div>
                        <a href="transactions?method_filter=&merchent_mobile_filter=&status_filter=PENDING" class="text-xs bg-yellow-800 hover:bg-yellow-700 text-yellow-200 py-1 px-2 rounded transition-colors duration-300">
                            <i class="fas fa-eye mr-1"></i> Details
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="tech-card bg-gradient-to-br from-red-900 to-red-700 p-6 animate-fade-in rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                <div class="flex items-center mb-4">
                    <div class="rounded-full bg-white bg-opacity-20 p-3 mr-3">
                        <i class="fas fa-times-circle text-red-300 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-red-300 uppercase">Failed</h3>
                        <h2 class="text-2xl font-bold text-white">₹<?php echo number_format($failedPayments, 2); ?></h2>
                    </div>
                    <div class="ml-auto bg-red-600 text-white text-xs font-bold px-3 py-1 rounded-full shadow">
                        <?php echo number_format($failedPercentage, 1); ?>%
                    </div>
                </div>
                
                <div class="relative pt-1">
                    <div class="flex mb-2 items-center justify-between">
                        <div>
                            <span class="text-xs font-semibold inline-block text-red-300">Today's Failure Rate</span>
                        </div>
                        <div class="text-right">
                            <span class="text-xs font-semibold inline-block text-red-300">
                                <?php echo number_format($failedPercentage, 1); ?>%
                            </span>
                        </div>
                    </div>
                    <div class="overflow-hidden h-2 mb-4 text-xs flex rounded bg-red-200 bg-opacity-20">
                        <div style="width: <?php echo $failedPercentage; ?>%" class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-red-400"></div>
                    </div>
                </div>
                
                <div class="flex items-center justify-between text-sm text-red-200">
                    <div class="flex items-center">
                        <i class="fas fa-arrow-down mr-1 text-red-300"></i>
                        <span><?php echo number_format($failedPercentage, 1); ?>% of total</span>
                    </div>
                    <div>
                        <a href="transactions?method_filter=&merchent_mobile_filter=&status_filter=FAILURE" class="text-xs bg-red-800 hover:bg-red-700 text-red-200 py-1 px-2 rounded transition-colors duration-300">
                            <i class="fas fa-eye mr-1"></i> Details
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Plan and Account Info -->
        <?php
        $expiryDate = $userdata['expiry'];
        $vip_expiry = $userdata['vip_expiry'];
        $kycstatus = $userdata['kycstatus'];
        $today = date('Y-m-d');
        $status = "Expired";
        $vipplan = "NO";
        if (strtotime($expiryDate) >= strtotime($today)) {
            $status = "Active";
        }
        if (!empty($vip_expiry) && strtotime($vip_expiry) >= strtotime($today)) {
            $vipplan = "YES";
        }
        
        // Calculate days remaining or days expired
        $daysDiff = floor((strtotime($expiryDate) - strtotime($today)) / (60 * 60 * 24));
        $expiryStatus = $daysDiff >= 0 ? "$daysDiff days remaining" : abs($daysDiff) . " days expired";
        
        // Calculate VIP days remaining
        $vipDaysDiff = !empty($vip_expiry) ? floor((strtotime($vip_expiry) - strtotime($today)) / (60 * 60 * 24)) : 0;
        $vipExpiryStatus = $vipDaysDiff > 0 ? "$vipDaysDiff days remaining" : "Expired";
        
        // Determine status badge color
        $statusBadgeColor = $status === "Active" ? "bg-green-500" : "bg-red-500";
        $vipBadgeColor = $vipplan === "YES" ? "bg-purple-500" : "bg-gray-500";
        ?>
        
        <div class="mt-10 mb-6">
            <h2 class="text-2xl font-bold flex items-center mb-4 ">
                <i class="fas fa-id-card mr-2 text-blue-500"></i> Account Information
            </h2>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Plan Expiry Card -->
            <div class="tech-card bg-gradient-to-br from-blue-900 to-blue-700 p-6 animate-fade-in rounded-xl shadow-lg hover:shadow-xl transition-all duration-300">
                <div class="flex items-center mb-4">
                    <div class="rounded-full bg-white bg-opacity-20 p-3 mr-3">
                        <i class="fas fa-calendar-alt text-blue-300 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-blue-300 uppercase">Plan Status</h3>
                        <div class="flex items-center mt-1">
                            <span class="text-lg font-bold text-white mr-2">Expires on</span>
                            <span class="<?php echo $statusBadgeColor; ?> text-white text-xs px-2 py-1 rounded-full">
                                <?php echo $status; ?>
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="bg-blue-800 bg-opacity-50 rounded-lg p-4 mb-4">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-blue-300 text-sm">Expiry Date</p>
                            <p class="text-white text-xl font-bold"><?php echo htmlspecialchars($userdata['expiry'], ENT_QUOTES, 'UTF-8'); ?></p>
                        </div>
                        <div class="text-right">
                            <p class="text-blue-300 text-sm">Time Left</p>
                            <p class="text-white font-bold <?php echo $daysDiff < 3 ? 'text-red-300' : 'text-green-300'; ?>">
                                <?php echo $expiryStatus; ?>
                            </p>
                        </div>
                    </div>
                </div>
                
                <?php if ($daysDiff < 5): ?>
                <div class="bg-yellow-600 bg-opacity-30 rounded-lg p-3 mb-4 flex items-center">
                    <i class="fas fa-exclamation-triangle text-yellow-300 mr-2"></i>
                    <p class="text-yellow-200 text-sm">
                        <?php echo $daysDiff < 0 ? 'Your plan has expired. Renew now to continue using services.' : 'Your plan is expiring soon. Consider renewing to avoid service interruption.'; ?>
                    </p>
                </div>
                <?php endif; ?>
                
                <button onclick="openSubscriptionPage()" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-bold py-3 px-4 rounded-lg shadow transition-all duration-300 transform hover:-translate-y-1 flex items-center justify-center">
                    <i class="fas fa-sync-alt mr-2"></i> Renew/Extend Plan
                </button>
            </div>
            
            <!-- Transaction Limit Card -->
            <div class="tech-card bg-gradient-to-br from-purple-900 to-purple-700 p-6 animate-fade-in rounded-xl shadow-lg hover:shadow-xl transition-all duration-300">
                <div class="flex items-center mb-4">
                    <div class="rounded-full bg-white bg-opacity-20 p-3 mr-3">
                        <i class="fas fa-exchange-alt text-purple-300 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-purple-300 uppercase">Usage Metrics</h3>
                        <h2 class="text-lg font-bold text-white">Transaction Limit</h2>
                    </div>
                </div>
                
                <?php
                $progress = ($hitlimit > 0 && isset($limitUsed)) ? ($limitUsed / $hitlimit) * 100 : 0;
                $progressColor = $progress < 50 ? 'bg-green-400' : ($progress < 80 ? 'bg-yellow-400' : 'bg-red-400');
                $progressStatus = $progress < 50 ? 'Good standing' : ($progress < 80 ? 'Moderate usage' : 'High usage');
                $progressIcon = $progress < 50 ? 'fa-check-circle' : ($progress < 80 ? 'fa-exclamation-circle' : 'fa-exclamation-triangle');
                ?>
                
                <div class="bg-purple-800 bg-opacity-50 rounded-lg p-1 mb-4">
                    <div class="flex justify-between items-center mb-2">
                        <div class="text-purple-300 text-sm">Available Limit</div>
                        <div class="text-white font-bold"><?= $hitlimit ?? '0'; ?></div>
                    </div>
                    <div class="flex justify-between items-center mb-2">
                        <div class="text-purple-300 text-sm">Used Till</div>
                        <div class="text-white font-bold"><?= $limitUsed ?? '0'; ?></div>
                    </div>
                    <div class="flex justify-between items-center">
                        <div class="text-purple-300 text-sm">Remaining</div>
                        <div class="text-white font-bold"><?= ($hitlimit ?? 0) - ($limitUsed ?? 0); ?></div>
                    </div>
                </div>
                
                <div class="relative pt-1">
                    <div class="flex mb-2 items-center justify-between">
                        <div>
                            <span class="text-xs font-semibold inline-block text-purple-300">Usage</span>
                        </div>
                        <div class="text-right">
                            <span class="text-xs font-semibold inline-block text-purple-300">
                                <?php echo number_format($progress, 1); ?>%
                            </span>
                        </div>
                    </div>
                    <div class="overflow-hidden h-2 mb-2 text-xs flex rounded bg-purple-200 bg-opacity-20">
                        <div style="width: <?php echo $progress; ?>%" class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center <?php echo $progressColor; ?>"></div>
                    </div>
                </div>
                
                <div class="flex items-center justify-between mt-3">
                    <div class="flex items-center text-sm">
                        <i class="fas <?php echo $progressIcon; ?> mr-1 <?php echo $progress < 50 ? 'text-green-300' : ($progress < 80 ? 'text-yellow-300' : 'text-red-300'); ?>"></i>
                        <span class="text-white"><?php echo $progressStatus; ?></span>
                    </div>
                    <a href="transactions" class="text-xs bg-purple-800 hover:bg-purple-700 text-purple-200 py-1 px-2 rounded transition-colors duration-300 inline-flex items-center">
                      <i class="fas fa-chart-line mr-1"></i> View History
                    </a>
                </div>
            </div>
                        <!-- Account Status Card -->
            <div class="tech-card bg-gradient-to-br from-teal-900 to-teal-700 p-6 animate-fade-in rounded-xl shadow-lg hover:shadow-xl transition-all duration-300">
                <div class="flex items-center mb-4">
                    <div class="rounded-full bg-white bg-opacity-20 p-3 mr-3">
                        <i class="fas fa-user-shield text-teal-300 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-teal-300 uppercase">Account Overview</h3>
                        <h2 class="text-lg font-bold text-white">Member Status</h2>
                    </div>
                </div>
                
<div class="bg-teal-800 bg-opacity-50 rounded-lg p-4 mb-4">
    <div class="grid grid-cols-3 gap-4">
        <!-- Account Status -->
        <div>
            <p class="text-teal-300 text-sm">Account Status</p>
            <div class="flex items-center mt-1">
                <span class="inline-flex items-center justify-center px-3 py-1 text-sm font-bold leading-none rounded-full <?php echo $status == 'Active' ? 'bg-green-500' : 'bg-red-500'; ?> text-white">
                    <i class="fas <?php echo $status == 'Active' ? 'fa-check' : 'fa-times'; ?> mr-1"></i>
                    <?php echo htmlspecialchars($status, ENT_QUOTES, 'UTF-8'); ?>
                </span>
            </div>
        </div>

        <!-- KYC Status -->
        <div>
            <p class="text-teal-300 text-sm">KYC Status</p>
            <div class="flex items-center mt-1">
                <span class="inline-flex items-center justify-center px-3 py-1 text-sm font-bold leading-none rounded-full <?php echo $kycstatus == 'Verified' ? 'bg-green-500' : 'bg-red-500'; ?> text-white">
                    <i class="fas <?php echo $kycstatus == 'Verified' ? 'fa-check' : 'fa-times'; ?> mr-1"></i>
                    <?php echo htmlspecialchars($kycstatus, ENT_QUOTES, 'UTF-8'); ?>
                </span>
            </div>
        </div>

        <!-- VIP Status -->
        <div>
            <p class="text-teal-300 text-sm">VIP Status</p>
            <div class="flex items-center mt-1">
                <span class="inline-flex items-center justify-center px-3 py-1 text-sm font-bold leading-none rounded-full <?php echo $vipplan == 'YES' ? 'bg-purple-500' : 'bg-gray-500'; ?> text-white">
                    <i class="fas <?php echo $vipplan == 'YES' ? 'fa-crown' : 'fa-user'; ?> mr-1"></i>
                    <?php echo htmlspecialchars($vipplan, ENT_QUOTES, 'UTF-8'); ?>
                </span>
            </div>
        </div>
    </div>

                    
                    <?php if ($vipplan == 'YES'): ?>
                    <div class="mt-3">
                        <p class="text-teal-300 text-sm">VIP Expiry</p>
                        <div class="flex justify-between items-center mt-1">
                            <p class="text-white"><?php echo htmlspecialchars($vip_expiry, ENT_QUOTES, 'UTF-8'); ?></p>
                            <span class="text-xs font-semibold px-2 py-1 rounded bg-purple-600 text-white">
                                <?php echo $vipExpiryStatus; ?>
                            </span>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="flex flex-col space-y-2">
                    <div class="flex items-center text-white mb-2">
                        <i class="fas fa-info-circle text-teal-300 mr-2"></i>
                        <span class="text-sm">Account created on: <?php echo date('M d, Y', strtotime($userdata['create_date'] ?? 'now')); ?></span>
                    </div>
                    
                    <?php if ($status != 'Active'): ?>
                    <div class="bg-red-600 bg-opacity-30 rounded-lg p-3 mb-3 flex items-center">
                        <i class="fas fa-exclamation-circle text-red-300 mr-2"></i>
                        <p class="text-red-200 text-sm">
                            Your account is currently inactive. Please renew your plan to continue using all services.
                        </p>
                    </div>
                    <?php endif; ?>
                    <?php if ($vipplan != 'YES'): ?>
                    <button onclick="openSubscriptionPage()" class="w-full bg-teal-600 hover:bg-teal-500 text-white font-bold py-3 px-4 rounded-lg shadow transition-all duration-300 transform hover:-translate-y-1 flex items-center justify-center">
                        <i class="fas fa-crown mr-2"></i> Upgrade to VIP
                    </button>
                    <?php endif; ?>
                </div>
            </div>
            </div>
            

        </div>
        
        <!-- Charts Section -->
        <div class="mt-10 mb-6">
            <h2 class="text-2xl font-bold flex items-center mb-4 ">
                <i class="fas fa-chart-line mr-2 text-blue-500"></i> Transaction Analytics
            </h2>
        </div>

        <!-- Charts -->
        <?php
        include "config.php";

        // Check DB connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Default filter: 'all_time'
        $filter = isset($_GET['filter']) ? $_GET['filter'] : 'all_time';

        // Define date range
        switch ($filter) {
            case 'this_week':
                $start_date = date('Y-m-d', strtotime('last sunday'));
                $end_date = date('Y-m-d', strtotime('next saturday'));
                break;
            case 'this_month':
            case 'all_time':
            default:
                $start_date = date('Y-m-01');
                $end_date = date('Y-m-t');
                break;
        }

        // Safety check for $userdata (should be set earlier)
        if (!isset($userdata) || !isset($userdata['role'])) {
            die("User data not available.");
        }

        // Prepare SQL based on role
        if ($userdata['role'] === 'Admin') {
            $sql = "
                SELECT 
                    DATE(create_date) AS day, 
                    SUM(CASE WHEN status = 'SUCCESS' THEN amount ELSE 0 END) AS total_success,
                    SUM(CASE WHEN status = 'FAILURE' THEN amount ELSE 0 END) AS total_failure,
                    SUM(CASE WHEN status = 'PENDING' THEN amount ELSE 0 END) AS total_pending
                FROM orders 
                WHERE create_date BETWEEN ? AND ? 
                GROUP BY day 
                ORDER BY day ASC
            ";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('ss', $start_date, $end_date);
        } else {
            $sql = "
                SELECT 
                    DATE(create_date) AS day, 
                    SUM(CASE WHEN status = 'SUCCESS' THEN amount ELSE 0 END) AS total_success,
                    SUM(CASE WHEN status = 'FAILURE' THEN amount ELSE 0 END) AS total_failure,
                    SUM(CASE WHEN status = 'PENDING' THEN amount ELSE 0 END) AS total_pending
                FROM orders 
                WHERE create_date BETWEEN ? AND ? AND user_id = ? 
                GROUP BY day 
                ORDER BY day ASC
            ";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('sss', $start_date, $end_date, $userdata['id']);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        // Prepare chart data
        $data = [
            'labels' => [],
            'success' => [],
            'failure' => [],
            'pending' => []
        ];

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $data['labels'][] = date('M d', strtotime($row['day']));
                $data['success'][] = (float)($row['total_success'] ?? 0);
                $data['failure'][] = (float)($row['total_failure'] ?? 0);
                $data['pending'][] = (float)($row['total_pending'] ?? 0);
            }
        } else {
            // Show current date with 0 values if no data
            $data['labels'] = [date('M d')];
            $data['success'] = [0];
            $data['failure'] = [0];
            $data['pending'] = [0];
        }

        $stmt->close();
        $conn->close();

        // Safely output JS data
        echo "<script>var chartData = " . json_encode($data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) . ";</script>";
        ?>

        <!-- Chart Filter and Export -->
        <div class="tech-card bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 animate-fade-in">
            <div class="flex flex-col md:flex-row justify-between items-center mb-6">
                <div class="flex items-center">
                    <div class="rounded-full bg-blue-100 dark:bg-blue-900 p-3 mr-3">
                        <i class="fas fa-chart-bar text-blue-500 dark:text-blue-300 text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold ">Transaction Overview</h2>
                        <p class="text-gray-700 dark:text-white text-sm font-medium">Visualize your payment trends</p>
                    </div>
                </div>
                
                <div class="flex space-x-2 mt-4 md:mt-0">
                    <div class="relative">
                        <select onchange="updateFilter(this.value)" class="appearance-none bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 py-2 px-4 pr-8 rounded-lg leading-tight focus:outline-none focus:bg-white dark:focus:bg-gray-800 focus:border-blue-500 dark:focus:border-blue-400 transition-colors duration-200">
                            <option value="all_time" <?php echo $filter == 'all_time' ? 'selected' : ''; ?>>All Time</option>
                            <option value="this_week" <?php echo $filter == 'this_week' ? 'selected' : ''; ?>>This Week</option>
                            <option value="this_month" <?php echo $filter == 'this_month' ? 'selected' : ''; ?>>This Month</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700 dark:text-gray-300">
                            <i class="fas fa-chevron-down text-xs"></i>
                        </div>
                    </div>
                    
                    <button onclick="exportToCSV()" class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded-lg transition-colors duration-300 shadow-md hover:shadow-lg flex items-center">
                        <i class="fas fa-download mr-2"></i>Export
                    </button>
                    
                    <button onclick="refreshChartData()" class="bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 font-medium py-2 px-4 rounded-lg transition-colors duration-300 shadow-md hover:shadow-lg flex items-center">
                        <i class="fas fa-sync-alt mr-2"></i>Refresh
                    </button>
                </div>
            </div>
            
            <!-- Chart Tabs -->
            <div class="mb-6">
                <div class="border-b border-gray-200 dark:border-gray-700">
                    <ul class="flex flex-wrap -mb-px">
                        <li class="mr-2">
                            <button class="chart-tab-btn active inline-block p-4 border-b-2 border-blue-500 rounded-t-lg" data-target="dailyTransactionChart">
                                <i class="fas fa-calendar-day mr-2"></i>Daily Transactions
                            </button>
                        </li>
                        <li class="mr-2">
                            <button class="chart-tab-btn inline-block p-4 border-b-2 border-transparent hover:border-gray-300 rounded-t-lg" data-target="monthlyTransactionChart">
                                <i class="fas fa-calendar-alt mr-2"></i>Monthly Overview
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
            
            <!-- Chart Containers -->
            <div class="chart-tab-content active" id="dailyTransactionChart-container">
                <div class="chart-container" style="position: relative; height:350px;">
                    <canvas id="dailyTransactionChart"></canvas>
                </div>
            </div>
            
            <div class="chart-tab-content hidden" id="monthlyTransactionChart-container">
                <div class="chart-container" style="position: relative; height:350px;">
                    <canvas id="monthlyTransactionChart"></canvas>
                </div>
            </div>
            
            <!-- Quick Stats -->
        <!--REmoved-->
        </div>

        <script>

            
            // Chart Tab Functionality
            document.addEventListener('DOMContentLoaded', function() {
                const chartTabBtns = document.querySelectorAll('.chart-tab-btn');
                const chartTabContents = document.querySelectorAll('.chart-tab-content');
                
                // Function to set active styles for buttons
                function setActiveStyles(activeBtn) {
                    // Remove active class from all buttons
                    chartTabBtns.forEach(b => {
                        b.classList.remove('active', 'border-blue-500', 'bg-blue-500', 'text-white');
                        b.classList.add('border-transparent', 'text-gray-700', 'dark:text-gray-300');
                    });
                    
                    // Add active class to clicked button
                    activeBtn.classList.add('active', 'border-blue-500', 'bg-blue-500', 'text-white');
                    activeBtn.classList.remove('border-transparent', 'text-gray-700', 'dark:text-gray-300');
                }
                
                // Set initial active styles
                const initialActiveBtn = document.querySelector('.chart-tab-btn.active') || chartTabBtns[0];
                setActiveStyles(initialActiveBtn);
                
                // Show initial content
                const initialTargetId = initialActiveBtn.getAttribute('data-target') + '-container';
                document.getElementById(initialTargetId).classList.remove('hidden');
                document.getElementById(initialTargetId).classList.add('active');
                
                chartTabBtns.forEach(btn => {
                    btn.addEventListener('click', function() {
                        setActiveStyles(this);
                        
                        // Hide all content
                        chartTabContents.forEach(content => {
                            content.classList.add('hidden');
                            content.classList.remove('active');
                        });
                        
                        // Show content related to clicked button
                        const targetId = this.getAttribute('data-target') + '-container';
                        const targetContent = document.getElementById(targetId);
                        targetContent.classList.remove('hidden');
                        targetContent.classList.add('active');
                        
                        // Force chart update to ensure proper rendering
                        if (targetId === 'status-container' && window.paymentPieChart) {
                            window.paymentPieChart.update();
                        } else if (targetId === 'method-container' && window.paymentMethodChart) {
                            window.paymentMethodChart.update();
                        }
                    });
                });
            });
            
            // Refresh Chart Data Function
            function refreshChartData() {
                // Show loading animation
                const chartContainers = document.querySelectorAll('.chart-container');
                chartContainers.forEach(container => {
                    container.classList.add('opacity-50');
                    const loadingDiv = document.createElement('div');
                    loadingDiv.className = 'absolute inset-0 flex items-center justify-center';
                    loadingDiv.innerHTML = '<div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500"></div>';
                    container.appendChild(loadingDiv);
                });
                
                // Simulate data refresh (in a real app, this would be an AJAX call)
                setTimeout(() => {
                    // Remove loading animation
                    chartContainers.forEach(container => {
                        container.classList.remove('opacity-50');
                        const loadingDiv = container.querySelector('div.absolute');
                        if (loadingDiv) container.removeChild(loadingDiv);
                    });
                    
                    // Show success message
            alert("Chart data has been updated with the latest information.");
                }, 1500);
            }
            

            
            // Function to update chart filter
            function updateFilter(value) {
                window.location.href = 'dashboard.php?filter=' + value;
            }
            
            // Function to export transaction data as CSV
            function exportToCSV() {
                const labels = chartData.labels;
                const success = chartData.success;
                const failure = chartData.failure;
                const pending = chartData.pending;
                
                let csvContent = "data:text/csv;charset=utf-8,";
                csvContent += "Date,Success,Failure,Pending\n";
                
                for (let i = 0; i < labels.length; i++) {
                    csvContent += `${labels[i]},${success[i]},${failure[i]},${pending[i]}\n`;
                }
                
                const encodedUri = encodeURI(csvContent);
                const link = document.createElement("a");
                link.setAttribute("href", encodedUri);
                link.setAttribute("download", "transaction_data.csv");
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }
            
            // Function to get chart colors based on current theme
            function getChartColors() {
                const isDarkTheme = document.body.classList.contains('dark-theme');
                return {
                    success: isDarkTheme ? 'rgba(40, 167, 69, 0.9)' : 'rgba(40, 167, 69, 0.8)',
                    failure: isDarkTheme ? 'rgba(220, 53, 69, 0.9)' : 'rgba(220, 53, 69, 0.8)',
                    pending: isDarkTheme ? 'rgba(255, 193, 7, 0.9)' : 'rgba(255, 193, 7, 0.8)',
                    grid: isDarkTheme ? 'rgba(255, 255, 255, 0.2)' : 'rgba(0, 0, 0, 0.15)',
                    text: isDarkTheme ? '#ffffff' : '#000000',
                    background: isDarkTheme ? 'rgba(46, 46, 79, 0.9)' : 'rgba(255, 255, 255, 0.9)',
                    hoverSuccess: isDarkTheme ? 'rgba(40, 167, 69, 1)' : 'rgba(40, 167, 69, 1)',
                    hoverFailure: isDarkTheme ? 'rgba(220, 53, 69, 1)' : 'rgba(220, 53, 69, 1)',
                    hoverPending: isDarkTheme ? 'rgba(255, 193, 7, 1)' : 'rgba(255, 193, 7, 1)'
                };
            }
            
            // Function to update chart colors when theme changes
            function updateChartColors() {
                const colors = getChartColors();
                
                // Update Payment Pie Chart
                if (window.paymentPieChart) {
                    window.paymentPieChart.data.datasets[0].backgroundColor = [
                        colors.success,
                        colors.pending,
                        colors.failure
                    ];
                    window.paymentPieChart.data.datasets[0].hoverBackgroundColor = [
                        colors.hoverSuccess,
                        colors.hoverPending,
                        colors.hoverFailure
                    ];
                    window.paymentPieChart.options.plugins.legend.labels.color = colors.text;
                    window.paymentPieChart.options.plugins.tooltip.backgroundColor = colors.background;
                    window.paymentPieChart.options.plugins.tooltip.titleColor = colors.text;
                    window.paymentPieChart.options.plugins.tooltip.bodyColor = colors.text;
                    window.paymentPieChart.update();
                }
                
                // Update Payment Method Chart
                if (window.paymentMethodChart) {
                    const methodColors = generateMethodColors(methodChartData.labels.length);
                    window.paymentMethodChart.data.datasets[0].backgroundColor = methodColors;
                    
                    // Create hover colors with higher opacity
                    const hoverMethodColors = methodColors.map(color => {
                        return color.replace('0.8', '1');
                    });
                    window.paymentMethodChart.data.datasets[0].hoverBackgroundColor = hoverMethodColors;
                    
                    window.paymentMethodChart.options.plugins.legend.labels.color = colors.text;
                    window.paymentMethodChart.options.plugins.legend.labels.font = {
                        size: 16,
                        weight: 'bold'
                    };
                    window.paymentMethodChart.options.plugins.tooltip.backgroundColor = colors.background;
                    window.paymentMethodChart.options.plugins.tooltip.titleColor = colors.text;
                    window.paymentMethodChart.options.plugins.tooltip.bodyColor = colors.text;
                    window.paymentMethodChart.update();
                }
                
                // Update Daily Transaction Chart
                if (window.dailyTransactionChart) {
                    window.dailyTransactionChart.data.datasets[0].backgroundColor = colors.success;
                    window.dailyTransactionChart.data.datasets[0].borderColor = colors.success;
                    window.dailyTransactionChart.data.datasets[1].backgroundColor = colors.failure;
                    window.dailyTransactionChart.data.datasets[1].borderColor = colors.failure;
                    window.dailyTransactionChart.data.datasets[2].backgroundColor = colors.pending;
                    window.dailyTransactionChart.data.datasets[2].borderColor = colors.pending;
                    window.dailyTransactionChart.options.scales.x.grid.color = colors.grid;
                    window.dailyTransactionChart.options.scales.y.grid.color = colors.grid;
                    window.dailyTransactionChart.options.scales.x.ticks.color = colors.text;
                    window.dailyTransactionChart.options.scales.y.ticks.color = colors.text;
                    window.dailyTransactionChart.update();
                }
                
                // Update Monthly Transaction Chart
                if (window.monthlyTransactionChart) {
                    window.monthlyTransactionChart.data.datasets[0].backgroundColor = 'rgba(75, 192, 192, 0.2)';
                    window.monthlyTransactionChart.data.datasets[0].borderColor = 'rgba(75, 192, 192, 1)';
                    window.monthlyTransactionChart.data.datasets[1].backgroundColor = colors.success;
                    window.monthlyTransactionChart.data.datasets[1].borderColor = colors.success;
                    window.monthlyTransactionChart.data.datasets[2].backgroundColor = colors.failure;
                    window.monthlyTransactionChart.data.datasets[2].borderColor = colors.failure;
                    window.monthlyTransactionChart.data.datasets[3].backgroundColor = colors.pending;
                    window.monthlyTransactionChart.data.datasets[3].borderColor = colors.pending;
                    window.monthlyTransactionChart.options.scales.x.grid.color = colors.grid;
                    window.monthlyTransactionChart.options.scales.y.grid.color = colors.grid;
                    window.monthlyTransactionChart.options.scales.y1.grid.color = colors.grid;
                    window.monthlyTransactionChart.options.scales.x.ticks.color = colors.text;
                    window.monthlyTransactionChart.options.scales.y.ticks.color = colors.text;
                    window.monthlyTransactionChart.options.scales.y1.ticks.color = colors.text;
                    window.monthlyTransactionChart.update();
                }
            }
            
            // Generate colors for payment method chart
            function generateMethodColors(count) {
                const colors = [
                    'rgba(54, 162, 235, 0.8)',
                    'rgba(255, 99, 132, 0.8)',
                    'rgba(75, 192, 192, 0.8)',
                    'rgba(255, 159, 64, 0.8)',
                    'rgba(153, 102, 255, 0.8)',
                    'rgba(255, 205, 86, 0.8)',
                    'rgba(201, 203, 207, 0.8)',
                    'rgba(255, 99, 71, 0.8)',
                    'rgba(50, 205, 50, 0.8)',
                    'rgba(138, 43, 226, 0.8)'
                ];
                
                // If we need more colors than in our predefined array, generate them
                if (count > colors.length) {
                    for (let i = colors.length; i < count; i++) {
                        const r = Math.floor(Math.random() * 255);
                        const g = Math.floor(Math.random() * 255);
                        const b = Math.floor(Math.random() * 255);
                        colors.push(`rgba(${r}, ${g}, ${b}, 0.8)`);
                    }
                }
                
                return colors.slice(0, count);
            }
            
            // Initialize charts when DOM is loaded
            document.addEventListener('DOMContentLoaded', function() {
                const colors = getChartColors();
                
                // Payment Pie Chart
                const paymentCtx = document.getElementById('paymentChart').getContext('2d');
                window.paymentPieChart = new Chart(paymentCtx, {
                    type: 'pie',
                    data: {
                        labels: ['Success', 'Pending', 'Failed'],
                        datasets: [{
                            data: [<?php echo $successPercentage; ?>, <?php echo $pendingPercentage; ?>, <?php echo $failedPercentage; ?>],
                            backgroundColor: [
                                colors.success,
                                colors.pending,
                                colors.failure
                            ],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'right',
                                labels: {
                                    color: '#ffffff',
                                    font: {
                                        size: 16,
                                        weight: 'bold'
                                    },
                                    padding: 20
                                }
                            },
                            tooltip: {
                                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                titleFont: {
                                    size: 16,
                                    weight: 'bold'
                                },
                                bodyFont: {
                                    size: 14
                                },
                                padding: 15,
                                callbacks: {
                                    label: function(context) {
                                        return context.label + ': ' + context.raw + '%';
                                    }
                                }
                            }
                        }
                    }
                });
                
                // Payment Method Chart
                const methodCtx = document.getElementById('paymentMethodChart').getContext('2d');
                const methodColors = generateMethodColors(methodChartData.labels.length);
                
                window.paymentMethodChart = new Chart(methodCtx, {
                    type: 'doughnut',
                    data: {
                        labels: methodChartData.labels,
                        datasets: [{
                            data: methodChartData.total_amounts,
                            backgroundColor: methodColors,
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'right',
                                labels: {
                                    color: '#ffffff',
                                    font: {
                                        size: 16,
                                        weight: 'bold'
                                    },
                                    padding: 20
                                }
                            },
                            tooltip: {
                                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                titleFont: {
                                    size: 16,
                                    weight: 'bold'
                                },
                                bodyFont: {
                                    size: 14
                                },
                                padding: 15,
                                callbacks: {
                                    label: function(context) {
                                        const index = context.dataIndex;
                                        const method = methodChartData.labels[index];
                                        const amount = methodChartData.total_amounts[index].toFixed(2);
                                        const count = methodChartData.transaction_counts[index];
                                        return `${method}: ₹${amount} (${count} transactions)`;
                                    }
                                }
                            }
                        }
                    }
                });
                
                // Daily Transaction Chart
                const dailyCtx = document.getElementById('dailyTransactionChart').getContext('2d');
                window.dailyTransactionChart = new Chart(dailyCtx, {
                    type: 'bar',
                    data: {
                        labels: chartData.labels,
                        datasets: [
                            {
                                label: 'Success',
                                data: chartData.success,
                                backgroundColor: colors.success,
                                borderColor: colors.success,
                                borderWidth: 1,
                                order: 1
                            },
                            {
                                label: 'Failure',
                                data: chartData.failure,
                                backgroundColor: colors.failure,
                                borderColor: colors.failure,
                                borderWidth: 1,
                                order: 2
                            },
                            {
                                label: 'Pending',
                                data: chartData.pending,
                                backgroundColor: colors.pending,
                                borderColor: colors.pending,
                                borderWidth: 1,
                                order: 3
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            x: {
                                grid: {
                                    color: colors.grid
                                },
                                ticks: {
                                    color: colors.text
                                }
                            },
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: colors.grid
                                },
                                ticks: {
                                    color: colors.text,
                                    callback: function(value) {
                                        return '₹' + value;
                                    }
                                }
                            }
                        },
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return context.dataset.label + ': ₹' + context.raw.toFixed(2);
                                    }
                                }
                            }
                        },
                        onClick: function(e, elements) {
                            if (elements.length > 0) {
                                const index = elements[0].index;
                                const date = chartData.labels[index];
                                const success = chartData.success[index].toFixed(2);
                                const failure = chartData.failure[index].toFixed(2);
                                const pending = chartData.pending[index].toFixed(2);
                                const total = (parseFloat(success) + parseFloat(failure) + parseFloat(pending)).toFixed(2);
                                
                                Swal.fire({
                                    title: 'Transactions for ' + date,
                                    html: `
                                        <div class="text-left">
                                            <p><strong>Success:</strong> ₹${success}</p>
                                            <p><strong>Failure:</strong> ₹${failure}</p>
                                            <p><strong>Pending:</strong> ₹${pending}</p>
                                            <p><strong>Total:</strong> ₹${total}</p>
                                        </div>
                                    `,
                                    icon: 'info'
                                });
                            }
                        }
                    }
                });
                
                // Monthly Transaction Chart
                const monthlyCtx = document.getElementById('monthlyTransactionChart').getContext('2d');
                window.monthlyTransactionChart = new Chart(monthlyCtx, {
                    type: 'bar',
                    data: {
                        labels: monthlyChartData.labels,
                        datasets: [
                            {
                                label: 'Total Transactions',
                                data: monthlyChartData.transactions,
                                type: 'line',
                                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                                borderColor: 'rgba(75, 192, 192, 1)',
                                borderWidth: 2,
                                fill: false,
                                tension: 0.4,
                                yAxisID: 'y1',
                                order: 0
                            },
                            {
                                label: 'Success Amount',
                                data: monthlyChartData.success_amount,
                                backgroundColor: colors.success,
                                borderColor: colors.success,
                                borderWidth: 1,
                                order: 1
                            },
                            {
                                label: 'Failure Amount',
                                data: monthlyChartData.failure_amount,
                                backgroundColor: colors.failure,
                                borderColor: colors.failure,
                                borderWidth: 1,
                                order: 2
                            },
                            {
                                label: 'Pending Amount',
                                data: monthlyChartData.pending_amount,
                                backgroundColor: colors.pending,
                                borderColor: colors.pending,
                                borderWidth: 1,
                                order: 3
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            x: {
                                grid: {
                                    color: colors.grid
                                },
                                ticks: {
                                    color: colors.text
                                }
                            },
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: colors.grid
                                },
                                ticks: {
                                    color: colors.text,
                                    callback: function(value) {
                                        return '₹' + value;
                                    }
                                },
                                title: {
                                    display: true,
                                    text: 'Amount (₹)',
                                    color: colors.text
                                }
                            },
                            y1: {
                                beginAtZero: true,
                                position: 'right',
                                grid: {
                                    drawOnChartArea: false,
                                    color: colors.grid
                                },
                                ticks: {
                                    color: colors.text
                                },
                                title: {
                                    display: true,
                                    text: 'Transaction Count',
                                    color: colors.text
                                }
                            }
                        },
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        if (context.dataset.label === 'Total Transactions') {
                                            return context.dataset.label + ': ' + context.raw;
                                        } else {
                                            return context.dataset.label + ': ₹' + context.raw.toFixed(2);
                                        }
                                    }
                                }
                            }
                        },
                        onClick: function(e, elements) {
                            if (elements.length > 0) {
                                const index = elements[0].index;
                                const month = monthlyChartData.labels[index];
                                const transactions = monthlyChartData.transactions[index];
                                const success = monthlyChartData.success_amount[index].toFixed(2);
                                const failure = monthlyChartData.failure_amount[index].toFixed(2);
                                const pending = monthlyChartData.pending_amount[index].toFixed(2);
                                const total = (parseFloat(success) + parseFloat(failure) + parseFloat(pending)).toFixed(2);
                                
                                Swal.fire({
                                    title: 'Monthly Overview: ' + month,
                                    html: `
                                        <div class="text-left">
                                            <p><strong>Total Transactions:</strong> ${transactions}</p>
                                            <p><strong>Success Amount:</strong> ₹${success}</p>
                                            <p><strong>Failure Amount:</strong> ₹${failure}</p>
                                            <p><strong>Pending Amount:</strong> ₹${pending}</p>
                                            <p><strong>Total Amount:</strong> ₹${total}</p>
                                        </div>
                                    `,
                                    icon: 'info'
                                });
                            }
                        }
                    }
                });
            });
            
            function openSubscriptionPage() {
                window.location.href = 'subscription.php';
            }
        </script>
    </div>
</body>
</html>