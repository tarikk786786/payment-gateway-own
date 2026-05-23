<?php

// जाँच करें कि क्या यह AJAX रिक्वेस्ट है
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    include('config.php');
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? null;

    if ($action === 'check_status') {
        $transactionId = $input['id'] ?? 0;
        
        $stmt = $conn->prepare("SELECT status, utr, payerUpi FROM orders WHERE id = ?"); // यहाँ utr और payerUpi जोड़ा गया है
        $stmt->bind_param("i", $transactionId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            echo json_encode([
                'success' => true,
                'status' => $row['status'],
                'utr' => $row['utr'], // UTR भी भेजा जा रहा है
                'payerUpi' => $row['payerUpi'] // PayerUpi भी भेजा जा रहा है
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => "Record not found."]);
        }
        $stmt->close();
        exit();
    } elseif ($action === 'manual_update') {
        // Manual update logic
        $transactionId = $input['id'] ?? 0;
        $orderId = $input['order_id'] ?? '';
        $user_token = $input['user_token'] ?? '';
        $byteTransactionId = $input['byteTransactionId'] ?? '';
        $utr = $input['utr'] ?? '';
        $remark = $input['remark'] ?? '';

        // Validate required fields
        if (!$transactionId || !$utr) {
            echo json_encode(['success' => false, 'error' => 'Transaction ID and UTR are required.']);
            exit();
        }

        // Update the transaction status, UTR, and remark1 (not remark)
        $stmt = $conn->prepare("UPDATE orders SET status = 'SUCCESS', utr = ?, remark1 = ? WHERE id = ?");
        $stmt->bind_param("ssi", $utr, $remark, $transactionId);
        $updateSuccess = $stmt->execute();
        $stmt->close();

        if ($updateSuccess) {
            echo json_encode(['success' => true, 'message' => 'Transaction successfully updated and user notified.']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Database update failed.']);
        }
        exit();
    }
}

include "header.php";

// Date range filters - Get from GET or POST for backward compatibility
$start_date = $_GET['start_date'] ?? $_POST['start_date'] ?? date('Y-m-d');
$end_date = $_GET['end_date'] ?? $_POST['end_date'] ?? date('Y-m-d');

// Filters from GET or POST for backward compatibility
$method = $_GET['method_filter'] ?? $_POST['method_filter'] ?? '';
$status = $_GET['status_filter'] ?? $_POST['status_filter'] ?? '';
// --- NEW FILTERS ---
$merchent_mobile = $_GET['merchent_mobile_filter'] ?? $_POST['merchent_mobile_filter'] ?? '';
$user_id = $_GET['user_id_filter'] ?? $_POST['user_id_filter'] ?? '';
$utr = $_GET['utr_filter'] ?? $_POST['utr_filter'] ?? ''; // UTR from Bank RRN field


// Build WHERE conditions for dropdown options. This is to ensure dropdowns only show relevant options.
$initial_conditions = [];
$initial_conditions[] = "DATE(create_date) BETWEEN '$start_date' AND '$end_date'";

// If not admin, filter by user token for dropdowns
if ($userdata['role'] != 'Admin') {
    $token = mysqli_real_escape_string($conn, $userdata['user_token']);
    $initial_conditions[] = "user_token = '$token'";
}

$initial_where = implode(' AND ', $initial_conditions);

// Filtered method options (only in current date range)
$methodOptions = [];
$methodResult = mysqli_query($conn, "SELECT DISTINCT method FROM orders WHERE method IS NOT NULL AND method != '' AND $initial_where");
while ($row = mysqli_fetch_assoc($methodResult)) {
    $methodOptions[] = $row['method'];
}

// Filtered status options (only in current date range)
$statusOptions = [];
$statusResult = mysqli_query($conn, "SELECT DISTINCT status FROM orders WHERE status IS NOT NULL AND status != '' AND $initial_where");
while ($row = mysqli_fetch_assoc($statusResult)) {
    $statusOptions[] = $row['status'];
}


// --- Build FINAL WHERE conditions for the main query ---
$conditions = [];
$conditions[] = "DATE(create_date) BETWEEN '$start_date' AND '$end_date'";

if (!empty($method)) {
    $conditions[] = "method = '".mysqli_real_escape_string($conn, $method)."'";
}
if (!empty($status)) {
    $conditions[] = "status = '".mysqli_real_escape_string($conn, $status)."'";
}

// --- ADDING NEW FILTER CONDITIONS ---
if (!empty($merchent_mobile)) {
    $conditions[] = "merchentMobile = '".mysqli_real_escape_string($conn, $merchent_mobile)."'";
}
if (!empty($user_id)) {
    $conditions[] = "user_id = '".mysqli_real_escape_string($conn, $user_id)."'";
}
if (!empty($utr)) {
    $conditions[] = "utr = '".mysqli_real_escape_string($conn, $utr)."'";
}


if ($userdata['role'] != 'Admin') {
    $token = $userdata['user_token'];
    $conditions[] = "user_token = '".mysqli_real_escape_string($conn, $token)."'";
}

$whereClause = implode(" AND ", $conditions);
// First, get summary data for the entire date range
$summaryQuery = "SELECT 
    COUNT(*) as totalTransactions,
    SUM(amount) as totalAmount,
    SUM(CASE WHEN status = 'SUCCESS' THEN 1 ELSE 0 END) as successCount,
    SUM(CASE WHEN status = 'SUCCESS' THEN amount ELSE 0 END) as successAmount,
    SUM(CASE WHEN status = 'FAILURE' THEN 1 ELSE 0 END) as failureCount,
    SUM(CASE WHEN status = 'FAILURE' THEN amount ELSE 0 END) as failureAmount,
    SUM(CASE WHEN status != 'SUCCESS' AND status != 'FAILURE' THEN 1 ELSE 0 END) as pendingCount,
    SUM(CASE WHEN status != 'SUCCESS' AND status != 'FAILURE' THEN amount ELSE 0 END) as pendingAmount
FROM orders 
WHERE $whereClause";

$summaryResult = mysqli_query($conn, $summaryQuery);

if (!$summaryResult) {
    echo "Summary Query Error: " . mysqli_error($conn);
    $totalAmount = $totalTransactions = $successCount = $pendingCount = $failureCount = 0;
    $successAmount = $pendingAmount = $failureAmount = 0;
} else {
    $summaryData = mysqli_fetch_assoc($summaryResult);
    $totalTransactions = $summaryData['totalTransactions'] ?? 0;
    $totalAmount = $summaryData['totalAmount'] ?? 0;
    $successCount = $summaryData['successCount'] ?? 0;
    $successAmount = $summaryData['successAmount'] ?? 0;
    $failureCount = $summaryData['failureCount'] ?? 0;
    $failureAmount = $summaryData['failureAmount'] ?? 0;
    $pendingCount = $summaryData['pendingCount'] ?? 0;
    $pendingAmount = $summaryData['pendingAmount'] ?? 0;
}

// Pagination setup
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$recordsPerPage = isset($_GET['records']) ? intval($_GET['records']) : 25;
$offset = ($page - 1) * $recordsPerPage;

// Get total records for pagination
$countQuery = "SELECT COUNT(*) as total FROM orders WHERE $whereClause";
$countResult = mysqli_query($conn, $countQuery);
$totalRecords = mysqli_fetch_assoc($countResult)['total'] ?? 0;
$totalPages = ceil($totalRecords / $recordsPerPage);

// Get paginated data
$query = "SELECT * FROM orders WHERE $whereClause ORDER BY create_date DESC LIMIT $offset, $recordsPerPage";
$query_run = mysqli_query($conn, $query);

$rows = [];
if (!$query_run) {
    echo "Query Error: " . mysqli_error($conn);
} else {
    while ($row = mysqli_fetch_assoc($query_run)) {
        $rows[] = $row;
    }
}
?>

<!-- START PAGE CONTENT -->
<!--<link href="./assets/vendors/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet" />-->
<!--<link href="./assets/vendors/font-awesome/css/font-awesome.min.css" rel="stylesheet" />-->
<!--<link href="./assets/vendors/themify-icons/css/themify-icons.css" rel="stylesheet" />-->
<!--<link href="./assets/vendors/DataTables/datatables.min.css" rel="stylesheet" />-->
<!--<link href="assets/css/main.min.css" rel="stylesheet" />-->

<div class="page-content fade-in-up">
    <div class="ibox">

        <!-- Summary Section -->
<!-- Summary Section -->
<!-- Summary Section -->
        <div class="row summary-section mb-4">
            <!-- Visual Summary Cards -->
            <div class="col-md-12 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Transaction Summary</h5>
                        <div class="btn-group">
                            <button type="button" class="btn btn-sm btn-light" id="view-chart">Chart View</button>
                            <button type="button" class="btn btn-sm btn-outline-light active" id="view-cards">Card View</button>
                        </div>
        
        <!-- Required Libraries -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
        
        <!-- Styles moved to styles.css -->

        <!-- Styles moved to styles.css -->
        
        <script>
          document.addEventListener('DOMContentLoaded', function() {
             // Animated counter function
             function animateCounter(element, start, end, duration, prefix = '', suffix = '') {
                 let startTimestamp = null;
                 const step = (timestamp) => {
                     if (!startTimestamp) startTimestamp = timestamp;
                     const progress = Math.min((timestamp - startTimestamp) / duration, 1);
                     const easeProgress = 1 - Math.pow(1 - progress, 3); // Cubic ease out
                     let value;
                     
                     // Handle large numbers differently
                     if (end > 1000) {
                         value = Math.floor(start + (end - start) * easeProgress);
                         element.innerHTML = prefix + value.toLocaleString() + suffix;
                     } else {
                         // For percentages or smaller numbers, show one decimal place
                         value = start + (end - start) * easeProgress;
                         element.innerHTML = prefix + value.toFixed(1) + suffix;
                     }
                     
                     if (progress < 1) {
                         window.requestAnimationFrame(step);
                     }
                 };
                 window.requestAnimationFrame(step);
             }
             
             // Animate progress bars
             function animateProgressBars() {
                 const progressBars = document.querySelectorAll('.progress-bar');
                 progressBars.forEach(bar => {
                     const width = bar.style.width;
                     bar.style.width = '0%';
                     setTimeout(() => {
                         bar.style.width = width;
                     }, 300);
                 });
             }
             
             // Toggle between chart and card views
             const viewChartBtn = document.getElementById('view-chart');
             const viewCardsBtn = document.getElementById('view-cards');
             const chartView = document.getElementById('chart-view');
             const cardView = document.getElementById('card-view');
             let chartsInitialized = false;
             
             viewChartBtn.addEventListener('click', function() {
                 chartView.style.display = 'block';
                 cardView.style.display = 'none';
                 viewChartBtn.classList.add('active');
                 viewChartBtn.classList.remove('btn-light');
                 viewChartBtn.classList.add('btn-outline-light');
                 viewCardsBtn.classList.remove('active');
                 viewCardsBtn.classList.add('btn-light');
                 viewCardsBtn.classList.remove('btn-outline-light');
                 
                 // Initialize charts when chart view is shown
                 if (!chartsInitialized) {
                     initCharts();
                     chartsInitialized = true;
                     
                     // Animate counters in chart view
                     const statNumbers = document.querySelectorAll('#chart-view .stat-item h3');
                     statNumbers.forEach((element, index) => {
                         const value = parseInt(element.innerText.replace(/[^0-9.-]+/g, ''));
                         element.innerText = '0';
                         
                         setTimeout(() => {
                             if (element.innerText.includes('₹')) {
                                 animateCounter(element, 0, value, 1500, '₹');
                             } else if (element.innerText.includes('%')) {
                                 animateCounter(element, 0, value, 1500, '', '%');
                             } else {
                                 animateCounter(element, 0, value, 1500);
                             }
                         }, 300 + (index * 100));
                     });
                 }
             });
             
             viewCardsBtn.addEventListener('click', function() {
                 chartView.style.display = 'none';
                 cardView.style.display = 'block';
                 viewCardsBtn.classList.add('active');
                 viewCardsBtn.classList.remove('btn-light');
                 viewCardsBtn.classList.add('btn-outline-light');
                 viewChartBtn.classList.remove('active');
                 viewChartBtn.classList.add('btn-light');
                 viewChartBtn.classList.remove('btn-outline-light');
                 
                 // Animate card view elements
                 animateProgressBars();
                 
                 // Animate counters in card view
                 const cardTitles = document.querySelectorAll('#card-view .card-title');
                 cardTitles.forEach((element, index) => {
                     const text = element.innerText;
                     const value = parseInt(text.replace(/[^0-9.-]+/g, ''));
                     element.innerHTML = '0 Transactions';
                     
                     setTimeout(() => {
                         animateCounter(element, 0, value, 1500, '', ' Transactions');
                     }, 300 + (index * 100));
                 });
                 
                 const cardSubtitles = document.querySelectorAll('#card-view .card-subtitle');
                 cardSubtitles.forEach((element, index) => {
                     const text = element.innerText;
                     const value = parseInt(text.replace(/[^0-9.-]+/g, ''));
                     element.innerHTML = '₹0';
                     
                     setTimeout(() => {
                         animateCounter(element, 0, value, 1500, '₹');
                     }, 600 + (index * 100));
                 });
             });
             
             function initCharts() {
                 // Transaction Status Chart
                 const statusCtx = document.getElementById('transactionStatusChart').getContext('2d');
                 const statusChart = new Chart(statusCtx, {
                     type: 'doughnut',
                     data: {
                         labels: ['Success', 'Pending', 'Failed'],
                         datasets: [{
                             data: [<?= $successCount ?>, <?= $pendingCount ?>, <?= $failureCount ?>],
                             backgroundColor: ['#28a745', '#ffc107', '#dc3545'],
                             borderWidth: 0
                         }]
                     },
                     options: {
                         responsive: true,
                         maintainAspectRatio: true,
                         plugins: {
                             legend: {
                                 position: 'bottom',
                                 labels: {
                                     padding: 20,
                                     usePointStyle: true,
                                     pointStyle: 'circle'
                                 }
                             },
                             tooltip: {
                                 callbacks: {
                                     label: function(context) {
                                         const label = context.label || '';
                                         const value = context.raw || 0;
                                         const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                         const percentage = Math.round((value / total) * 100);
                                         return `${label}: ${value} (${percentage}%)`;
                                     }
                                 },
                                 backgroundColor: 'rgba(0,0,0,0.7)',
                                 padding: 12,
                                 cornerRadius: 6,
                                 titleFont: {
                                     size: 14,
                                     weight: 'bold'
                                 }
                             }
                         },
                         animation: {
                             animateScale: true,
                             animateRotate: true,
                             duration: 2000
                         },
                         cutout: '70%',
                         elements: {
                             arc: {
                                 borderWidth: 0
                             }
                         }
                     }
                 });
                 
                 // Add center text to doughnut chart
                 Chart.register({
                     id: 'doughnutCenterText',
                     beforeDraw: function(chart) {
                         if (chart.config.type === 'doughnut') {
                             // Get ctx from chart
                             const ctx = chart.ctx;
                             
                             // Get options from the center object in options
                             const centerConfig = chart.config.options.center;
                             if (centerConfig) {
                                 const fontStyle = centerConfig.fontStyle || 'Arial';
                                 const txt = centerConfig.text;
                                 const color = centerConfig.color || '#000';
                                 const fontSize = centerConfig.fontSize || 30;
                                 const sidePadding = centerConfig.sidePadding || 20;
                                 const sidePaddingCalculated = (sidePadding / 100) * (chart.innerRadius * 2);
                                 
                                 ctx.font = fontSize + "px " + fontStyle;
                                 
                                 // Get center of the chart
                                 const centerX = ((chart.chartArea.left + chart.chartArea.right) / 2);
                                 const centerY = ((chart.chartArea.top + chart.chartArea.bottom) / 2);
                                 
                                 // Draw text in center
                                 ctx.fillStyle = color;
                                 ctx.textAlign = 'center';
                                 ctx.textBaseline = 'middle';
                                 ctx.fillText(txt, centerX, centerY);
                             }
                         }
                     }
                 });
                 
                 // Set center text for doughnut chart
                 statusChart.options.center = {
                     text: '<?= $totalCount ?>',
                     color: '#333',
                     fontStyle: 'Arial',
                     fontSize: 24,
                     sidePadding: 20
                 };
                 
                 // Transaction Amount Chart
                 const amountCtx = document.getElementById('transactionAmountChart').getContext('2d');
                 const amountChart = new Chart(amountCtx, {
                     type: 'bar',
                     data: {
                         labels: ['Success', 'Pending', 'Failed'],
                         datasets: [{
                             label: 'Amount (₹)',
                             data: [<?= $successAmount ?>, <?= $pendingAmount ?>, <?= $failureAmount ?>],
                             backgroundColor: ['rgba(40, 167, 69, 0.7)', 'rgba(255, 193, 7, 0.7)', 'rgba(220, 53, 69, 0.7)'],
                             borderColor: ['#28a745', '#ffc107', '#dc3545'],
                             borderWidth: 1,
                             borderRadius: 6,
                             maxBarThickness: 50
                         }]
                     },
                     options: {
                         responsive: true,
                         maintainAspectRatio: true,
                         scales: {
                             y: {
                                 beginAtZero: true,
                                 grid: {
                                     drawBorder: false,
                                     color: 'rgba(0,0,0,0.05)'
                                 },
                                 ticks: {
                                     callback: function(value) {
                                         return '₹' + value.toLocaleString();
                                     },
                                     font: {
                                         size: 11
                                     }
                                 }
                             },
                             x: {
                                 grid: {
                                     display: false
                                 }
                             }
                         },
                         plugins: {
                             legend: {
                                 display: false
                             },
                             tooltip: {
                                 callbacks: {
                                     label: function(context) {
                                         return '₹' + context.raw.toLocaleString();
                                     }
                                 },
                                 backgroundColor: 'rgba(0,0,0,0.7)',
                                 padding: 10,
                                 cornerRadius: 6
                             }
                         },
                         animation: {
                             duration: 2000,
                             easing: 'easeOutQuart',
                             delay: function(context) {
                                 return context.dataIndex * 300;
                             }
                         }
                     }
                 });
             }
             
             // No animation on page load for card view as requested
             const statItems = document.querySelectorAll('.stat-item');
             statItems.forEach((item, index) => {
                 item.style.opacity = '1';
                 item.style.transform = 'translateY(0)';
                 item.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
             });
             
             // No counter animations on page load as requested
             
             // Add hover effects to cards
             const cards = document.querySelectorAll('.card');
             cards.forEach(card => {
                 card.addEventListener('mouseenter', function() {
                     this.style.transform = 'translateY(-5px)';
                     this.style.boxShadow = '0 10px 20px rgba(0,0,0,0.1)';
                 });
                 
                 card.addEventListener('mouseleave', function() {
                     this.style.transform = '';
                     this.style.boxShadow = '';
                 });
             });
             
             // No progress bar animation on page load as requested
             
             // Add pulse effect to stat icons
             const statIcons = document.querySelectorAll('.stat-icon');
             statIcons.forEach(icon => {
                 icon.classList.add('pulse-effect');
             });
             
             // Add click interaction to stat items
             statItems.forEach(item => {
                 item.addEventListener('click', function() {
                     this.classList.add('clicked');
                     setTimeout(() => {
                         this.classList.remove('clicked');
                     }, 500);
                 });
             });
         });
        </script>
                    </div>
                    <div class="card-body">
                        <?php 
                        $successRate = $totalTransactions > 0 ? round(($successCount / $totalTransactions) * 100, 1) : 0;
                        $avgAmount = $totalTransactions > 0 ? $totalAmount / $totalTransactions : 0;
                        ?>
                        
                        <!-- Chart View (Initially Hidden) -->
                        <div id="chart-view" class="mb-4" style="display: none;">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="chart-container" style="height: 300px;">
                                        <h5 class="chart-title">Transaction Status Distribution</h5>
                                        <div style="position: relative; height: 250px;">
                                            <canvas id="transactionStatusChart"></canvas>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="chart-container" style="height: 300px;">
                                        <h5 class="chart-title">Transaction Amount Distribution</h5>
                                        <div style="position: relative, height: 250px;">
                                            <canvas id="transactionAmountChart"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Summary Stats in Single Row -->
                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="card shadow-sm">
                                        <div class="card-body p-0">
                                            <div class="d-flex flex-wrap justify-content-between align-items-center p-3">
                                                <!-- All stats in one row -->
                                                <div class="stat-item text-center px-2 py-1" style="flex: 1; min-width: 150px;">
                                                    <div class="d-flex align-items-center">
                                                        <div class="stat-icon bg-primary text-white rounded-circle me-2 d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                                                            <i class="fas fa-exchange-alt"></i>
                                                        </div>
                                                        <div class="text-start">
                                                            <h3 class="mb-0" style="font-size: 1.1rem;"><?= $totalTransactions; ?></h3>
                                                            <small class="text-muted" style="font-size: 0.8rem;">Total Transactions</small>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="stat-item text-center px-2 py-1" style="flex: 1; min-width: 150px;">
                                                    <div class="d-flex align-items-center">
                                                        <div class="stat-icon bg-success text-white rounded-circle me-2 d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                                                            <i class="fas fa-check"></i>
                                                        </div>
                                                        <div class="text-start">
                                                            <h3 class="mb-0" style="font-size: 1.1rem;"><?= $successCount; ?></h3>
                                                            <small class="text-muted" style="font-size: 0.8rem;">Success (<?= $successRate; ?>%)</small>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="stat-item text-center px-2 py-1" style="flex: 1; min-width: 150px;">
                                                    <div class="d-flex align-items-center">
                                                        <div class="stat-icon bg-warning text-dark rounded-circle me-2 d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                                                            <i class="fas fa-clock"></i>
                                                        </div>
                                                        <div class="text-start">
                                                            <h3 class="mb-0" style="font-size: 1.1rem;"><?= $pendingCount; ?></h3>
                                                            <small class="text-muted" style="font-size: 0.8rem;">Pending (<?= $totalTransactions > 0 ? round(($pendingCount / $totalTransactions) * 100, 1) : 0; ?>%)</small>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="stat-item text-center px-2 py-1" style="flex: 1; min-width: 150px;">
                                                    <div class="d-flex align-items-center">
                                                        <div class="stat-icon bg-danger text-white rounded-circle me-2 d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                                                            <i class="fas fa-times"></i>
                                                        </div>
                                                        <div class="text-start">
                                                            <h3 class="mb-0" style="font-size: 1.1rem;"><?= $failureCount; ?></h3>
                                                            <small class="text-muted" style="font-size: 0.8rem;">Failed (<?= $totalTransactions > 0 ? round(($failureCount / $totalTransactions) * 100, 1) : 0; ?>%)</small>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="stat-item text-center px-2 py-1" style="flex: 1; min-width: 150px;">
                                                    <div class="d-flex align-items-center">
                                                        <div class="stat-icon bg-info text-white rounded-circle me-2 d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                                                            <i class="fas fa-rupee-sign"></i>
                                                        </div>
                                                        <div class="text-start">
                                                            <h3 class="mb-0" style="font-size: 1.1rem;">₹<?= number_format($totalAmount, 0); ?></h3>
                                                            <small class="text-muted" style="font-size: 0.8rem;">Total Amount</small>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="stat-item text-center px-2 py-1" style="flex: 1; min-width: 150px;">
                                                    <div class="d-flex align-items-center">
                                                        <div class="stat-icon bg-secondary text-white rounded-circle me-2 d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                                                            <i class="fas fa-calculator"></i>
                                                        </div>
                                                        <div class="text-start">
                                                            <h3 class="mb-0" style="font-size: 1.1rem;">₹<?= number_format($avgAmount, 0); ?></h3>
                                                            <small class="text-muted" style="font-size: 0.8rem;">Average Amount</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Card View (Initially Visible) -->
                        <div id="card-view">
                            <div class="row mt-3">
                                <!-- Success Rate -->
                                <div class="col-md-2 mb-2">
                                    <div class="card shadow-sm text-center border-info h-100 stat-item success">
                                        <div class="card-header bg-info text-white py-1"><i class="fas fa-percentage me-2"></i>Success Rate</div>
                                        <div class="card-body py-2">
                                            <div class="d-flex justify-content-center align-items-center">
                                                <div>
                                                    <h5 class="mb-0"><?= $successRate; ?>%</h5>
                                                </div>
                                            </div>
                                            <div class="progress mt-1" style="height: 6px;">
                                                <div class="progress-bar bg-info" role="progressbar" 
                                                    style="width: <?= $successRate; ?>%" 
                                                    aria-valuenow="<?= $successRate; ?>" 
                                                    aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Average Transaction -->
                                <div class="col-md-2 mb-2">
                                    <div class="card shadow-sm text-center border-secondary h-100 stat-item total">
                                        <div class="card-header bg-secondary text-white py-1"><i class="fas fa-calculator me-2"></i>Average</div>
                                        <div class="card-body py-2">
                                            <div class="d-flex justify-content-center align-items-center">
                                                <div>
                                                    <h5 class="mb-0">₹<?= number_format($avgAmount, 0); ?></h5>
                                                </div>
                                            </div>
                                            <div class="progress mt-1" style="height: 6px;">
                                                <div class="progress-bar bg-secondary" role="progressbar" 
                                                    style="width: 100%" 
                                                    aria-valuenow="100" 
                                                    aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Success Transactions -->
                                <div class="col-md-2 mb-2">
                                    <div class="card shadow-sm text-center border-success h-100 stat-item success">
                                        <div class="card-header bg-success text-white py-1"><i class="fas fa-check-circle me-2"></i>Success</div>
                                        <div class="card-body py-2">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h5 class="mb-0"><?= $successCount; ?></h5>
                                                    <small class="text-muted" style="font-size: 0.8rem;"><?= $totalTransactions > 0 ? round(($successCount / $totalTransactions) * 100, 1) : 0; ?>%</small>
                                                </div>
                                                <div>
                                                    <h5 class="mb-0">₹<?= number_format($successAmount, 0); ?></h5>
                                                </div>
                                            </div>
                                            <div class="progress mt-1" style="height: 6px;">
                                                <div class="progress-bar bg-success" role="progressbar" 
                                                    style="width: <?= $totalTransactions > 0 ? ($successCount / $totalTransactions) * 100 : 0; ?>%" 
                                                    aria-valuenow="<?= $totalTransactions > 0 ? ($successCount / $totalTransactions) * 100 : 0; ?>" 
                                                    aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Pending Transactions -->
                                <div class="col-md-2 mb-2">
                                    <div class="card shadow-sm text-center border-warning h-100 stat-item pending">
                                        <div class="card-header bg-warning text-dark py-1"><i class="fas fa-clock me-2"></i>Pending</div>
                                        <div class="card-body py-2">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h5 class="mb-0"><?= $pendingCount; ?></h5>
                                                    <small class="text-muted" style="font-size: 0.8rem;"><?= $totalTransactions > 0 ? round(($pendingCount / $totalTransactions) * 100, 1) : 0; ?>%</small>
                                                </div>
                                                <div>
                                                    <h5 class="mb-0">₹<?= number_format($pendingAmount, 0); ?></h5>
                                                </div>
                                            </div>
                                            <div class="progress mt-1" style="height: 6px;">
                                                <div class="progress-bar bg-warning" role="progressbar" 
                                                    style="width: <?= $totalTransactions > 0 ? ($pendingCount / $totalTransactions) * 100 : 0; ?>%" 
                                                    aria-valuenow="<?= $totalTransactions > 0 ? ($pendingCount / $totalTransactions) * 100 : 0; ?>" 
                                                    aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Failed Transactions -->
                                <div class="col-md-2 mb-2">
                                    <div class="card shadow-sm text-center border-danger h-100 stat-item failure">
                                        <div class="card-header bg-danger text-white py-1"><i class="fas fa-times-circle me-2"></i>Failure</div>
                                        <div class="card-body py-2">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h5 class="mb-0"><?= $failureCount; ?></h5>
                                                    <small class="text-muted" style="font-size: 0.8rem;"><?= $totalTransactions > 0 ? round(($failureCount / $totalTransactions) * 100, 1) : 0; ?>%</small>
                                                </div>
                                                <div>
                                                    <h5 class="mb-0">₹<?= number_format($failureAmount, 0); ?></h5>
                                                </div>
                                            </div>
                                            <div class="progress mt-1" style="height: 6px;">
                                                <div class="progress-bar bg-danger" role="progressbar" 
                                                    style="width: <?= $totalTransactions > 0 ? ($failureCount / $totalTransactions) * 100 : 0; ?>%" 
                                                    aria-valuenow="<?= $totalTransactions > 0 ? ($failureCount / $totalTransactions) * 100 : 0; ?>" 
                                                    aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Total Summary -->
                                <div class="col-md-2 mb-2">
                                    <div class="card shadow-sm text-center border-primary h-100 stat-item total">
                                        <div class="card-header bg-primary text-white py-1"><i class="fas fa-chart-bar me-2"></i>Total Summary</div>
                                        <div class="card-body py-2">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h5 class="mb-0"><?= $totalTransactions; ?></h5>
                                                    <small class="text-muted" style="font-size: 0.8rem;">Transactions</small>
                                                </div>
                                                <div>
                                                    <h5 class="mb-0">₹<?= number_format($totalAmount, 0); ?></h5>
                                                </div>
                                            </div>
                                            <div class="progress mt-1" style="height: 6px;">
                                                <div class="progress-bar bg-primary" role="progressbar" style="width: 100%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
           <!-- Date Range Filter -->
<!-- Date Range Filter -->
<div class="ibox-head">
    <div class="ibox-tools" style="display: flex; justify-content: space-between; align-items: center;">
        <!-- Filter Form (Left) -->
<form id="dateRangeForm" method="GET" class="mb-4">
    <div class="row g-2 align-items-end">
        <div class="col-md-2" title="From Date">
            <input type="date" class="form-control" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>">
        </div>
        <div class="col-md-2" title="To Date">
            <input type="date" class="form-control" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>">
        </div>

        <div class="col-md-2">
            <select class="form-select" name="method_filter" title="Filter by Method">
                <option value="">All Methods</option>
                <?php foreach ($methodOptions as $opt): ?>
                    <option value="<?php echo htmlspecialchars($opt); ?>" <?php echo ($method == $opt) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($opt); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-1">
            <input type="text" class="form-control" name="merchent_mobile_filter" value="<?php echo htmlspecialchars($merchent_mobile); ?>" placeholder="M_Mobile">
        </div>
        <div class="col-md-1">
            <select class="form-select" name="status_filter" title="Filter by Status">
                <option value="">All Status</option>
                <?php foreach ($statusOptions as $opt): ?>
                    <option value="<?php echo htmlspecialchars($opt); ?>" <?php echo ($status == $opt) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($opt); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>


        <div class="col-md-1">
            <input type="text" class="form-control" name="utr_filter" value="<?php echo htmlspecialchars($utr); ?>" placeholder="UTR">
        </div>
        <? if ($userdata['role'] == 'Admin') {?>
        <div class="col-md-1">
            <input type="text" class="form-control" name="user_id_filter" value="<?php echo htmlspecialchars($user_id); ?>" placeholder="User ID">
        </div>
        <? }?>
        <div class="col-md-1">
            <button type="submit" class="btn btn-primary w-100">Filter</button>
        </div>
        <!-- Hidden fields to preserve pagination state -->
        <input type="hidden" name="page" value="1"> <!-- Reset to page 1 when filtering -->
        <input type="hidden" name="records" value="<?php echo $recordsPerPage; ?>">
    </div>
</form>


        <!-- Export Form (Right) -->
        <form method="POST" action="export.php">
            <input type="hidden" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>">
            <input type="hidden" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>">
            <input type="hidden" name="method_filter" value="<?php echo htmlspecialchars($method); ?>">
            <input type="hidden" name="status_filter" value="<?php echo htmlspecialchars($status); ?>">
            <input type="hidden" name="merchent_mobile_filter" value="<?php echo htmlspecialchars($merchent_mobile); ?>">
            <input type="hidden" name="user_id_filter" value="<?php echo htmlspecialchars($user_id); ?>">
            <input type="hidden" name="utr_filter" value="<?php echo htmlspecialchars($utr); ?>">

            <div class="btn-group" role="group" aria-label="Export options">
                <button type="button" class="btn btn-secondary btn-sm">EXPORT</button>
                <button type="submit" name="export_csv" class="btn btn-success btn-sm">CSV</button>
                <button type="submit" name="export_excel" class="btn btn-info btn-sm">Excel</button>
                <button type="submit" name="export_pdf" class="btn btn-danger btn-sm">PDF</button>
            </div>
        </form>
    </div>
</div>

<!-- Styles moved to styles.css -->


<!-- Transactions Table with Pagination Controls -->
<div class="table-responsive">
    <div class="ibox-body">
        <!-- Records per page selector -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <label for="recordsPerPage" class="me-2">Records per page:</label>
                <select id="recordsPerPage" class="form-select form-select-sm d-inline-block w-auto" onchange="changeRecordsPerPage(this.value)">
                    <option value="10" <?= $recordsPerPage == 10 ? 'selected' : '' ?>>10</option>
                    <option value="25" <?= $recordsPerPage == 25 ? 'selected' : '' ?>>25</option>
                    <option value="50" <?= $recordsPerPage == 50 ? 'selected' : '' ?>>50</option>
                    <option value="100" <?= $recordsPerPage == 100 ? 'selected' : '' ?>>100</option>
                </select>
            </div>
            <div>
                <span class="text-muted">Showing <?= min(($page - 1) * $recordsPerPage + 1, $totalRecords) ?> to <?= min($page * $recordsPerPage, $totalRecords) ?> of <?= $totalRecords ?> entries</span>
            </div>
        </div>
        
<table class="table table-striped table-bordered table-hover" id="transactions-table" cellspacing="0" width="100%">
    <thead>
        <tr>
            <th class="p-3 border">#</th>
            <th class="p-3 border">Date_Time</th>
            <th class="p-3 border">Payer</th>
            <th class="p-3 border">Order ID</th>
            <th class="p-3 border">Amount</th>
            <th class="p-3 border">Bank RRN</th>
            <th class="p-3 border">Status</th>
            <th class="p-3 border" style="display: flex; align-items: center; gap: 8px;"> <?php if($pendingCount!=0){?>  <img src="/auth/assets/img/ai.gif" style="width: 40px;" alt="AI"> <span>in <?php }?> Action</span> </th>
            <th class="p-3 border">Recived From</th>
            <th class="p-3 border">Merchant Mobile</th>
            <th class="p-3 border">Merchant</th>
            <th class="p-3 border">User Id</th>
        </tr>
    </thead>
<tbody>
<?php
if (empty($rows)) {
    echo "<tr><td colspan='12' class='text-center p-3 text-red-500'>No data found.</td></tr>";
} else {
    foreach ($rows as $row) {
        $statusClass = $row['status'] === 'SUCCESS' ? 'bg-green-500 text-white' : ($row['status'] === 'FAILURE' ? 'bg-red-500 text-white' : 'bg-yellow-500 text-black');
        
        // यहाँ हमने डेटा एट्रीब्यूट्स को जोड़ा है
        echo "<tr class='border-b hover:bg-gray-50' data-id='" . htmlspecialchars($row['id']) . "' data-status='" . htmlspecialchars($row['status']) . "'>";
        
        echo "<td class='p-3 border'>" . htmlspecialchars($row['id']) . "</td>";
        echo "<td class='p-3 border'>" . date('d/m/Y h:i:sA', strtotime($row['create_date'])) . "</td>";
        echo "<td class='p-3 border'>" . htmlspecialchars(substr_replace($row['customer_mobile'], 'XXXXXX', 0, -4)) . "</td>";
        echo "<td class='p-3 border'>" . htmlspecialchars($row['order_id']) . "</td>";
        echo "<td class='p-3 border font-semibold'>₹" . htmlspecialchars($row['amount']) . "</td>";
        echo "<td class='p-3 border'>" . htmlspecialchars($row['utr']) . "</td>";
        echo "<td class='p-3 border status-cell'><span class='px-3 py-1 rounded-lg $statusClass'>" . htmlspecialchars($row['status']) . "</span></td>";
        echo "<td class='p-3 border action-cell'>";
        echo "<div class='flex gap-2'>";
        echo "<button class='bg-gray-400 text-white px-2 py-1 rounded-lg hover:bg-gray-600' onclick=\"showTransactionDetails(" . htmlspecialchars(json_encode($row)) . ")\">View</button>";

        if ($row['status'] !== 'SUCCESS') {
            $createDate = strtotime($row['create_date']);
            $fiveMinutesAgo = time() - (5 * 60);

            // यह शर्त 5 मिनट के बाद STATUS बटन दिखाने के लिए है,
            // अब हम 5 मिनट के दौरान "Checking..." बटन को जावास्क्रिप्ट से कंट्रोल करेंगे।
            if ($createDate > $fiveMinutesAgo && $row['status']=='PENDING' && $row['method']!="Manual") {
                // --- ADD PROGRESS BAR CONTAINER HERE ---
                echo "<div data-action-status='checking'>
                        <button class='bg-gray-400 text-white px-2 py-1 rounded-lg cursor-not-allowed flex items-center gap-2'>
                            <div class='animate-spin rounded-full h-4 w-4 border-t-2 border-b-2 border-white'></div>
                            Checking...
                        </button>
                        <div class='progress-bar-container' style='width:100%; margin-top:4px;'>
                            <div class='progress-bar' style='height:4px; width:0%; background:#3b82f6; border-radius:2px; transition:none;'></div>
                        </div>
                    </div>";
            } else {
                echo "<div data-action-status='check-now'>
                        <button class='bg-blue-400 text-white px-2 py-1 rounded-lg hover:bg-blue-600' onclick=\"statusChecker('" . $row['id'] . "', '" . $row['order_id'] . "', '" . $row['user_token'] . "', '" . $row['byteTransactionId'] . "')\"> STATUS </button>
                    </div>";
            }
        }
        echo "</div>";
        echo "</td>";
        echo "<td class='p-3 border'>" . htmlspecialchars($row['payerUpi']) . "</td>";
        echo "<td class='p-3 border'>" . htmlspecialchars($row['merchentMobile']) . "</td>";
        echo "<td class='p-3 border'>" . htmlspecialchars($row['method']) . "</td>";
        echo "<td class='p-3 border'>" . htmlspecialchars($row['user_id']) . "</td>";
        echo "</tr>";
    }
}
?>
</tbody>
</table>
        
        <!-- Pagination Controls -->
        <?php if ($totalPages > 1): ?>
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div>
                <button class="btn btn-sm btn-outline-primary me-1" onclick="goToPage(1)" <?= $page <= 1 ? 'disabled' : '' ?>>
                    <i class="fa fa-angle-double-left"></i> First
                </button>
                <button class="btn btn-sm btn-outline-primary me-1" onclick="goToPage(<?= max(1, $page - 1) ?>)" <?= $page <= 1 ? 'disabled' : '' ?>>
                    <i class="fa fa-angle-left"></i> Previous
                </button>
            </div>
            
            <div class="d-flex">
                <?php 
                $startPage = max(1, min($page - 2, $totalPages - 4));
                $endPage = min($totalPages, max($page + 2, 5));
                
                if ($startPage > 1) {
                    echo '<span class="btn btn-sm btn-outline-secondary disabled mx-1">...</span>';
                }
                
                for ($i = $startPage; $i <= $endPage; $i++) {
                    $activeClass = $i == $page ? 'btn-primary' : 'btn-outline-primary';
                    echo "<button class='btn btn-sm $activeClass mx-1' onclick='goToPage($i)'>$i</button>";
                }
                
                if ($endPage < $totalPages) {
                    echo '<span class="btn btn-sm btn-outline-secondary disabled mx-1">...</span>';
                }
                ?>
            </div>
            
            <div>
                <button class="btn btn-sm btn-outline-primary me-1" onclick="goToPage(<?= min($totalPages, $page + 1) ?>)" <?= $page >= $totalPages ? 'disabled' : '' ?>>
                    Next <i class="fa fa-angle-right"></i>
                </button>
                <button class="btn btn-sm btn-outline-primary" onclick="goToPage(<?= $totalPages ?>)" <?= $page >= $totalPages ? 'disabled' : '' ?>>
                    Last <i class="fa fa-angle-double-right"></i>
                </button>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Pagination JavaScript -->
<script>
function goToPage(page) {
    const urlParams = new URLSearchParams(window.location.search);
    urlParams.set('page', page);
    window.location.href = window.location.pathname + '?' + urlParams.toString();
}

function changeRecordsPerPage(records) {
    const urlParams = new URLSearchParams(window.location.search);
    urlParams.set('records', records);
    urlParams.set('page', 1); // Reset to first page when changing records per page
    window.location.href = window.location.pathname + '?' + urlParams.toString();
}
</script>

<!-- Modal for Transaction Details -->
<div id="transactionModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden">
    <div class="bg-white rounded-lg p-6 w-full max-w-lg shadow-lg">
        <div id="receiptContent">
            <h2 class="text-2xl font-bold text-center mb-4 text-gray-800">Transaction Receipt</h2>
            <div class="border-t-2 border-b-2 border-gray-300 py-4 mb-4">
                <div id="transactionDetails" class="space-y-3 text-sm">
                    <!-- Transaction details will be populated here -->
                </div>
            </div>
            <div class="text-center text-xs text-gray-500">
                <p>Thank you for your transaction</p>
                <p>Generated on: <span id="generatedDate"></span></p>
            </div>
        </div>
        <div class="mt-6 flex justify-end space-x-3">
            <button class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600" onclick="printReceipt()">Print</button>
            <button class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600" onclick="closeModal()">Close</button>
        </div>
    </div>
</div>

<!-- JavaScript for Modal and Print -->
<script>
function showTransactionDetails(transaction) {
    const modal = document.getElementById('transactionModal');
    const detailsContainer = document.getElementById('transactionDetails');
    const generatedDate = document.getElementById('generatedDate');
    
    // Format the date
    const formattedDate = new Date(transaction.create_date).toLocaleString('en-IN', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        hour12: true
    });

    // Set current date for receipt
    const currentDate = new Date().toLocaleString('en-IN', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        hour12: true
    });
    generatedDate.textContent = currentDate;

    // Define fields to display
    const fields = [
        { label: 'ID', value: transaction.id },
        { label: 'Order ID', value: transaction.order_id },
        { label: 'Status', value: transaction.status },
        { label: 'Amount', value: transaction.amount ? `₹${transaction.amount}` : null },
        { label: 'Bank RRN (UTR)', value: transaction.utr },
        { label: 'Customer Name', value: transaction.customer_name },
        { label: 'Payer', value: transaction.customer_mobile },
        { label: 'payerUpi', value: transaction.payerUpi },
        // { label: 'Redirect URL', value: transaction.redirect_url },
        { label: 'Remark 1', value: transaction.remark1 },
        { label: 'Remark 2', value: transaction.remark2 },
        { label: 'Gateway Txn', value: transaction.gateway_txn },
        { label: 'Method', value: transaction.method },
        { label: 'Merchant Mobile', value: transaction.merchentMobile },
        { label: 'HDFC TXNID', value: transaction.HDFC_TXNID },
        { label: 'Description', value: transaction.description },
        { label: 'Byte Transaction ID', value: transaction.byteTransactionId },
        { label: 'Create Date', value: formattedDate },
        { label: 'Paytm Txn Ref', value: transaction.paytm_txn_ref },
        { label: 'User ID', value: transaction.user_id },
        { label: 'Webhook Sent', value: transaction.webhook_sent },
        { label: 'Notification Sent', value: transaction.notifi_send }
    ];

    // Filter out fields with blank values and generate HTML
    const detailsHtml = fields
        .filter(field => field.value !== undefined && field.value !== null && field.value !== '')
        .map(field => `<div class="flex justify-between"><span class="font-semibold">${field.label}:</span> <span>${field.value}</span></div>`)
        .join('');

    // Populate transaction details
    detailsContainer.innerHTML = detailsHtml || '<p class="text-center text-gray-500">No details available</p>';

    // Show modal
    modal.classList.remove('hidden');
}

function closeModal() {
    const modal = document.getElementById('transactionModal');
    modal.classList.add('hidden');
}

function printReceipt() {
    const receiptContent = document.getElementById('receiptContent').innerHTML;
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
        <head>
            <title>Transaction Receipt</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .receipt { max-width: 500px; margin: 0 auto; }
                h2 { text-align: center; color: #333; }
                .details { border-top: 2px solid #ccc; border-bottom: 2px solid #ccc; padding: 15px 0; margin-bottom: 15px; }
                .details div { display: flex; justify-content: space-between; margin-bottom: 8px; }
                .details span:first-child { font-weight: bold; }
                .footer { text-align: center; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class="receipt">
                ${receiptContent}
            </div>
        </body>
        </html>
    `);
    printWindow.document.close();
    printWindow.print();
}

// Close modal when clicking outside
document.getElementById('transactionModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});
</script>

<!-- CSS for Modal -->

<style>
    /* Base styles for the dialog */
    dialog {
        padding: 0;
        border: none;
        border-radius: 0.5rem;
    }

    /* Backdrop for the dialog */
    dialog::backdrop {
        background-color: rgba(0, 0, 0, 0.5);
    }
</style>
<!-- SweetAlert CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// यह एक ऑब्जेक्ट है जो हर ट्रांजेक्शन के लिए setInterval ID स्टोर करेगा
// ताकि हम बाद में इसे रोक सकें
const statusIntervals = {};

// यह फंक्शन एक विशिष्ट रो के लिए स्थिति की जाँच करता है और अपडेट करता है
function checkAndRefreshStatus(rowElement, rowData) {
    const { id } = rowData;

    fetch('transactions.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            action: 'check_status',
            id: id
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.status) {
            const currentStatus = rowElement.dataset.status;
            const newStatus = data.status;

            if (currentStatus !== newStatus) {
                rowElement.dataset.status = newStatus;

                const statusCell = rowElement.querySelector('.status-cell');
                let statusClass = 'bg-yellow-500 text-black';
                if (newStatus === 'SUCCESS') {
                    statusClass = 'bg-green-500 text-white';
                } else if (newStatus === 'FAILURE') {
                    statusClass = 'bg-red-500 text-white';
                }
                statusCell.innerHTML = `<span class='px-3 py-1 rounded-lg ${statusClass}'>${newStatus}</span>`;

                const utrCell = rowElement.querySelector('td:nth-child(6)');
                if (utrCell && data.utr) {
                    utrCell.textContent = data.utr;
                }

                const payerUpiCell = rowElement.querySelector('td:nth-child(9)');
                if (payerUpiCell && data.payerUpi) {
                    payerUpiCell.textContent = data.payerUpi;
                }

                if (newStatus === 'SUCCESS' || newStatus === 'FAILURE') {
                    clearInterval(statusIntervals[id]);
                    delete statusIntervals[id];

                    const actionCell = rowElement.querySelector('.action-cell');
                    if (actionCell) {
                        const viewButton = actionCell.querySelector('button');
                        actionCell.innerHTML = `<div class="flex gap-2"></div>`;
                        actionCell.querySelector('.flex').appendChild(viewButton);
                    }
                }
            }
        }
    })
    .catch(error => {
        console.error(`Error checking status for ID ${id}:`, error);
    });
}

// यह फंक्शन पेज लोड होने पर सभी पात्र ट्रांजेक्शन के लिए जाँच शुरू करता है
function startPeriodicChecks() {
    // सभी ट्रांजेक्शन रो को चुनें
    const rows = document.querySelectorAll('#transactions-table tbody tr');

    rows.forEach(row => {
        const id = row.dataset.id;
        const status = row.dataset.status;
        const actionStatusElement = row.querySelector('[data-action-status]');

        // केवल उन रो के लिए जाँच शुरू करें जिनमें "checking" स्थिति है
        if (actionStatusElement && actionStatusElement.dataset.actionStatus === 'checking') {
            // 30 सेकंड (30000 milliseconds) के अंतराल पर जाँच करें
            statusIntervals[id] = setInterval(() => {
                // यहां हम RowData को PHP से JSON-encoded डेटा के रूप में पास नहीं कर सकते।
                // इसके बजाय, हमें PHP से सीधे सभी डेटा को एक ही बार में लेना चाहिए
                // या केवल id के साथ एक नया `fetch` कॉल करना चाहिए।
                // हम एक नया फंक्शन बनाएंगे जो केवल `id` के साथ स्टेटस चेक करेगा
                // और बाकी की जानकारी के साथ `statusChecker` को अपडेट करेगा।
                
                const rowData = {
                    id: id,
                    order_id: row.querySelector('td:nth-child(4)').textContent,
                    user_token: '...user_token...', // यहाँ आपको user_token को डेटाबेस से लाना होगा
                    byteTransactionId: '...byteTransactionId...' // और byteTransactionId भी
                };
                
                // आपके मूल कोड के अनुसार, हमें यह डेटा HTML में स्टोर करने की आवश्यकता है
                // ताकि statusChecker फ़ंक्शन सही ढंग से काम कर सके
                // इसलिए, हमें PHP में कुछ और एट्रीब्यूट्स जोड़ना होगा
                
                // बेहतर होगा कि हम `check_status_from_db.php` को कॉल करें
                // जो केवल db से स्टेटस लाएगा
                
                const rowDataFromDom = {
                    id: id,
                    order_id: row.querySelector('td:nth-child(4)').textContent,
                    user_token: row.dataset.userToken, // इन एट्रीब्यूट्स को PHP में जोड़ना होगा
                    byteTransactionId: row.dataset.byteTransactionId // इन एट्रीब्यूट्स को PHP में जोड़ना होगा
                };
                
                checkAndRefreshStatus(row, rowDataFromDom);
            }, 30000); // 30 सेकंड
        }
    });
}

// पेज लोड होने पर जाँच शुरू करें
document.addEventListener('DOMContentLoaded', startPeriodicChecks);


    // यह फंक्शन `statusChecker` है, जिसमें कोई बदलाव नहीं किया गया है
function statusChecker(id, orderId, user_token, byteTransactionId) {
        // Create and show loading indicator
        const loadingDialog = document.createElement('dialog');
        loadingDialog.id = 'loading-dialog';
        loadingDialog.innerHTML = `
            <div class="p-4 flex flex-col items-center justify-center">
                <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500 mb-4"></div>
                <p class="text-lg font-semibold text-gray-700">Checking status...</p>
                <p class="text-sm text-gray-500">Please wait while we check the transaction status.</p>
            </div>
        `;
        document.body.appendChild(loadingDialog);
        loadingDialog.showModal();

        fetch('update_status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id: id, order_id: orderId, user_token: user_token, byteTransactionId: byteTransactionId })
        })
        .then(response => response.json())
        .then(data => {
            // Close the loading dialog
            loadingDialog.close();
            loadingDialog.remove();

            // Create the main status dialog
            const statusDialog = document.createElement('dialog');
            statusDialog.className = 'p-6 rounded-lg shadow-xl max-w-lg w-full bg-white';
            statusDialog.innerHTML = '<div id="dialog-content"></div>';
            document.body.appendChild(statusDialog);

            const contentDiv = statusDialog.querySelector('#dialog-content');

            if (data.success) {
                // Check if we have status data
                if (data.data && data.data.status) {
                    const status = data.data.status.toLowerCase();
                    const message = data.data.message || 'Status checked successfully';
                    const method = data.data.method || '';

                    // Format API response for display
                    let apiRespHtml = '';
                    if (data.data.apiResp) {
                        let apiRespData;
                        if (typeof data.data.apiResp === 'string') {
                            try {
                                apiRespData = JSON.parse(data.data.apiResp);
                            } catch (e) {
                                apiRespData = { raw: data.data.apiResp };
                            }
                        } else {
                            apiRespData = data.data.apiResp;
                        }

                        apiRespHtml = '<div class="mt-3 pt-3 border-t border-gray-200">';
                        apiRespHtml += '<h4 class="font-bold mb-2 text-lg">Response :</h4>';
                        apiRespHtml += '<div class="text-left">';
                        for (const [key, value] of Object.entries(apiRespData)) {
                            apiRespHtml += `<div class="mb-1 text-sm"><span class="font-semibold">${key}:</span> ${value}</div>`;
                        }
                        apiRespHtml += '</div></div>';
                    }

                    // Determine icon based on status
                    let statusIcon = '';
                    let statusTitle = 'Transaction Status';
                    let statusColor = 'text-blue-500';

                    if (status === 'success') {
                        statusIcon = '✅';
                        statusColor = 'text-green-500';
                        statusTitle = 'Success!';
                    } else if (status === 'failure') {
                        statusIcon = '❌';
                        statusColor = 'text-red-500';
                        statusTitle = 'Error!';
                    } else {
                         // --- MANUAL UPDATE LOGIC START ---
                        const manualDialog = document.createElement('dialog');
                        manualDialog.className = 'p-6 rounded-lg shadow-xl max-w-lg w-full bg-white';
                        manualDialog.innerHTML = `
                            <div class="text-center mb-4">

                                <p class="mt-2 text-gray-700 font-semibold">Method : "${method.toUpperCase()}".</p>
                                <p class="mt-2 ${statusColor} font-semibold">Transaction status is "${status.toUpperCase()}".</p>
                                ${apiRespHtml}
                                <div class="text-4xl text-yellow-500 mb-2">⚠️</div>
                                <h3 class="text-2xl font-bold text-yellow-500">Warning!</h3>
                                <p class="text-sm text-gray-500 mt-2">
                                    You can manually mark this transaction as SUCCESS. Please be aware that this action will not be cross-checked by us.
                                </p>
                                <p class="text-sm text-gray-500">
                                    A success callback will be sent to the user's website immediately.
                                </p>
                            </div>
                            <div class="mt-4">
                                <label for="utrInput" class="block text-sm font-medium text-gray-700">Enter UTR</label>
                                <input type="text" id="utrInput" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" placeholder="e.g., 1234567890">
                            </div>
                            <div class="mt-4">
                                <label for="remarkInput" class="block text-sm font-medium text-gray-700">Remark</label>
                                <input type="text" id="remarkInput" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" placeholder="e.g., Manual success update">
                            </div>
                            <div class="flex justify-end mt-6 space-x-4">
                                <button class="bg-gray-200 text-gray-800 px-4 py-2 rounded-lg font-semibold hover:bg-gray-300" onclick="document.querySelector('dialog').close()">Cancel</button>
                                <button id="makeSuccessBtn" class="bg-green-500 text-white px-4 py-2 rounded-lg font-semibold hover:bg-green-600">Make Success</button>
                            </div>
                        `;
                        document.body.appendChild(manualDialog);
                        manualDialog.showModal();

                        document.getElementById('makeSuccessBtn').addEventListener('click', () => {
                            const utr = document.getElementById('utrInput').value;
                            const remark = document.getElementById('remarkInput').value;
                            if (!utr) {
                                alert("UTR is required to proceed.");
                                return;
                            }
                            manualSuccessUpdate(id, orderId, user_token, byteTransactionId, utr, remark);
                            manualDialog.close();
                        });

                        manualDialog.addEventListener('close', () => manualDialog.remove());

                        // Close the main status dialog since we are showing a new one
                        statusDialog.close();
                        statusDialog.remove();
                        return; // Exit the function to prevent showing the default dialog
                        // --- MANUAL UPDATE LOGIC END ---
                    }

                    let htmlContent = `
                        <div class="text-center mb-4">
                            <div class="text-4xl mb-2">${statusIcon}</div>
                            <h3 class="text-2xl font-bold ${statusColor}">${statusTitle}</h3>
                            <p class="text-lg font-semibold mt-2">${status.toUpperCase()}</p>
                        </div>
                        <div class="grid grid-cols-2 gap-y-2 gap-x-4 text-left mb-4 text-sm">
                            <div><span class="font-semibold">Status:</span> ${status}</div>
                            <div><span class="font-semibold">Method:</span> ${method}</div>
                            <div class="col-span-2"><span class="font-semibold">Message:</span> ${message}</div>
                        </div>
                        ${apiRespHtml}
                        <div class="flex justify-center mt-4">
                            <button class="bg-gray-200 text-gray-800 px-4 py-2 rounded-lg font-semibold hover:bg-gray-300" onclick="document.querySelector('dialog').close()">OK</button>
                        </div>
                    `;
                    contentDiv.innerHTML = htmlContent;

                    statusDialog.showModal();

                    statusDialog.addEventListener('close', () => {
                        if (status === 'success') {
                            location.reload();
                        }
                        statusDialog.remove();
                    });
                } else if (data.status && data.message) {
                    // Direct success response from update_status.php
                    contentDiv.innerHTML = `
                        <div class="text-center mb-4">
                            <div class="text-4xl text-green-500 mb-2">✅</div>
                            <h3 class="text-2xl font-bold text-green-500">Success!</h3>
                            <p class="mt-2">${data.message}</p>
                        </div>
                        <div class="flex justify-center mt-4">
                            <button class="bg-gray-200 text-gray-800 px-4 py-2 rounded-lg font-semibold hover:bg-gray-300" onclick="document.querySelector('dialog').close()">OK</button>
                        </div>
                    `;
                    statusDialog.showModal();
                    statusDialog.addEventListener('close', () => {
                        location.reload();
                        statusDialog.remove();
                    });

                } else {
                    // Generic success
                    contentDiv.innerHTML = `
                        <div class="text-center mb-4">
                            <div class="text-4xl text-green-500 mb-2">✅</div>
                            <h3 class="text-2xl font-bold text-green-500">Success!</h3>
                            <p class="mt-2">Transaction status checked successfully.</p>
                        </div>
                        <div class="flex justify-center mt-4">
                            <button class="bg-gray-200 text-gray-800 px-4 py-2 rounded-lg font-semibold hover:bg-gray-300" onclick="document.querySelector('dialog').close()">OK</button>
                        </div>
                    `;
                    statusDialog.showModal();
                    statusDialog.addEventListener('close', () => {
                        location.reload();
                        statusDialog.remove();
                    });
                }
            } else {
                contentDiv.innerHTML = `
                    <div class="text-center mb-4">
                        <div class="text-4xl text-red-500 mb-2">❌</div>
                        <h3 class="text-2xl font-bold text-red-500">Error!</h3>
                        <p class="mt-2">Failed to update transaction: ${data.error || 'Unknown error'}</p>
                    </div>
                    <div class="flex justify-center mt-4">
                        <button class="bg-gray-200 text-gray-800 px-4 py-2 rounded-lg font-semibold hover:bg-gray-300" onclick="document.querySelector('dialog').close()">OK</button>
                    </div>
                `;
                statusDialog.showModal();
                statusDialog.addEventListener('close', () => {
                    statusDialog.remove();
                });
            }
        })
        .catch(error => {
            console.error("Error:", error);
            // Close the loading dialog
            loadingDialog.close();
            loadingDialog.remove();

            const errorDialog = document.createElement('dialog');
            errorDialog.className = 'p-6 rounded-lg shadow-xl max-w-lg w-full bg-white';
            errorDialog.innerHTML = `
                <div class="text-center mb-4">
                    <div class="text-4xl text-red-500 mb-2">❌</div>
                    <h3 class="text-2xl font-bold text-red-500">Error!</h3>
                    <p class="mt-2">An error occurred while checking the transaction status.</p>
                </div>
                <div class="flex justify-center mt-4">
                    <button class="bg-gray-200 text-gray-800 px-4 py-2 rounded-lg font-semibold hover:bg-gray-300" onclick="document.querySelector('dialog').close()">OK</button>
                </div>
            `;
            document.body.appendChild(errorDialog);
            errorDialog.showModal();
            errorDialog.addEventListener('close', () => {
                errorDialog.remove();
            });
        });
    }

    // नया फंक्शन जो बटन की स्थिति को 5 मिनट तक नियंत्रित करेगा
function updateButtonPeriodically(row, containerId) {
    const createDate = new Date(row.create_date);
    const container = document.getElementById(containerId);
    let progressTimer, buttonTimer;

    function startProgressBar() {
        const progressBar = container.querySelector(".progress-bar");
        if (!progressBar) return;
        progressBar.style.width = "0%";
        progressBar.style.transition = "none";
        requestAnimationFrame(() => {
            progressBar.style.transition = "width 30s linear";
            progressBar.style.width = "100%";
        });
    }

    function showStatusButton() {
        container.innerHTML = `
            <button class='bg-blue-400 text-white px-2 py-1 rounded-lg hover:bg-blue-600' 
                onclick="statusChecker('${row.id}', '${row.order_id}', '${row.user_token}', '${row.byteTransactionId}')">
                STATUS
            </button>`;
        clearInterval(progressTimer);
        clearInterval(buttonTimer);
    }

    function updateButton() {
        const now = new Date();
        const timeDiff = now - createDate;
        const fiveMinutes = 5 * 60 * 1000;
        if (timeDiff >= fiveMinutes) {
            showStatusButton();
        }
    }

    // Progress bar हर 30 सेकंड में भरना और reset करना
    progressTimer = setInterval(startProgressBar, 30000);
    startProgressBar();

    // हर 60 सेकंड में चेक करना कि 5 मिनट पूरे हुए या नहीं
    buttonTimer = setInterval(updateButton, 60000);
}

    
function manualSuccessUpdate(id, orderId, user_token, byteTransactionId, utr, remark) {
    // ...existing code...
    fetch('transactions.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            action: 'manual_update',
            id: id,
            order_id: orderId,
            user_token: user_token,
            byteTransactionId: byteTransactionId,
            utr: utr,
            remark: remark
        })
    })
    .then(response => response.json())
    .then(data => {
        // Only close/remove loadingDialog if it exists
        if (typeof loadingDialog !== 'undefined' && loadingDialog) {
            try { loadingDialog.close(); } catch(e) {}
            try { loadingDialog.remove(); } catch(e) {}
        }

        const resultDialog = document.createElement('dialog');
        resultDialog.className = 'p-6 rounded-lg shadow-xl max-w-lg w-full bg-white';
        document.body.appendChild(resultDialog);

        if (data.success) {
            resultDialog.innerHTML = `
                <div class="text-center mb-4">
                    <div class="text-4xl text-green-500 mb-2">✅</div>
                    <h3 class="text-2xl font-bold text-green-500">Success!</h3>
                    <p class="mt-2">Transaction successfully updated and user notified.</p>
                </div>
                <div class="flex justify-center mt-4">
                    <button class="bg-gray-200 text-gray-800 px-4 py-2 rounded-lg font-semibold hover:bg-gray-300" onclick="document.querySelector('dialog').close()">OK</button>
                </div>
            `;
            resultDialog.showModal();
            resultDialog.addEventListener('close', () => {
                resultDialog.remove();
                location.reload();
            });
        } else {
            resultDialog.innerHTML = `
                <div class="text-center mb-4">
                    <div class="text-4xl text-red-500 mb-2">❌</div>
                    <h3 class="text-2xl font-bold text-red-500">Error!</h3>
                    <p class="mt-2">Failed to manually update transaction: ${data.error || 'Unknown error'}</p>
                </div>
                <div class="flex justify-center mt-4">
                    <button class="bg-gray-200 text-gray-800 px-4 py-2 rounded-lg font-semibold hover:bg-gray-300" onclick="document.querySelector('dialog').close()">OK</button>
                </div>
            `;
            resultDialog.showModal();
            resultDialog.addEventListener('close', () => resultDialog.remove());
        }
    })
    .catch(error => {
        if (typeof loadingDialog !== 'undefined' && loadingDialog) {
            try { loadingDialog.close(); } catch(e) {}
            try { loadingDialog.remove(); } catch(e) {}
        }
        console.error("Manual update error:", error);
        // ...existing error dialog logic...
    });
}
</script>

<script>
// --- Progress bar animation for "Checking..." buttons ---
function animateCheckingProgressBars() {
    document.querySelectorAll('[data-action-status="checking"] .progress-bar').forEach(bar => {
        bar.style.transition = 'none';
        bar.style.width = '0%';
        setTimeout(() => {
            bar.style.transition = 'width 30s linear';
            bar.style.width = '100%';
        }, 50);
    });
}
document.addEventListener('DOMContentLoaded', function() {
    animateCheckingProgressBars();
    setInterval(animateCheckingProgressBars, 30000);
});
</script>
<!--        }, 50);-->
<!--    });-->
<!--}-->

<!--// Animate on page load and every 30 seconds-->
<!--document.addEventListener('DOMContentLoaded', function() {-->
<!--    animateCheckingProgressBars();-->
<!--    setInterval(animateCheckingProgressBars, 30000);-->
<!--});-->
<!--</script>-->

