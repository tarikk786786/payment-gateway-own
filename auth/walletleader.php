<?php 
include "header.php"; 

// Initialize session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Handle POST request and set session variables
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $_SESSION['start_date'] = $_POST['start_date'] ?? date('Y-m-d');
    $_SESSION['end_date'] = $_POST['end_date'] ?? date('Y-m-d');
}

// Set default dates or use session variables
$start_date = $_SESSION['start_date'] ?? date('Y-m-d');
$end_date = $_SESSION['end_date'] ?? date('Y-m-d');

$userid = $userdata['id'];

if ($userdata['role'] == 'User') {
    $query = "SELECT `transaction_id`, `user_id`, `amount`, `type`, `timestamp`, `opening_balance`, `closing_balance`, `utr`, `Remark` 
              FROM wallet_transactions 
              WHERE user_id = '$userid' AND DATE(timestamp) BETWEEN '$start_date' AND '$end_date' 
              ORDER BY timestamp DESC";
} else {
    $query = "SELECT `transaction_id`, `user_id`, `amount`, `type`, `timestamp`, `opening_balance`, `closing_balance`, `utr`, `Remark` 
              FROM wallet_transactions 
              WHERE DATE(timestamp) BETWEEN '$start_date' AND '$end_date' 
              ORDER BY timestamp DESC";
}

$totalAmount = $totalTransactions = $successCount = $pendingCount = $failureCount = 0;
$successAmount = $pendingAmount = $failureAmount = 0;
$rows = [];
$query_run = mysqli_query($conn, $query);

if ($query_run) {
    while ($row = mysqli_fetch_assoc($query_run)) {
        $totalAmount += (float)$row['amount'];
        $totalTransactions++;
        
        if ($row['type'] === 'SUCCESS') {
            $successCount++;
            $successAmount += (float)$row['amount'];
        } elseif ($row['type'] === 'FAILURE') {
            $failureCount++;
            $failureAmount += (float)$row['amount'];
        } else {
            $pendingCount++;
            $pendingAmount += (float)$row['amount'];
        }
        $rows[] = $row;
    }
} else {
    echo "Error in query: " . mysqli_error($conn); 
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction List</title>
    <link href="./assets/vendors/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="./assets/vendors/font-awesome/css/font-awesome.min.css" rel="stylesheet" />
    <link href="./assets/vendors/themify-icons/css/themify-icons.css" rel="stylesheet" />
    <link href="./assets/vendors/DataTables/datatables.min.css" rel="stylesheet" />
    <link href="assets/css/main.min.css" rel="stylesheet" />
</head>
<body>
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
                <div class="ibox-title">Date Filter</div>
                <div class="ibox-tools">
                    <form id="dateFilterForm" method="POST">
                        <label for="start_date">From:</label>
                        <input type="date" id="start_date" name="start_date" value="<?php echo $start_date; ?>">
                        
                        <label for="end_date">To:</label>
                        <input type="date" id="end_date" name="end_date" value="<?php echo $end_date; ?>">
                        
                        <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                    </form>
                </div>
            </div>

            <div class="ibox-body">
                <table class="table table-striped table-bordered table-hover" id="example-table" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>User ID</th>
                            <th>Time</th>
                            <th>Type</th>
                            <th>UTR</th>
                            <th>Opening Balance</th>
                            <th>Amount</th>
                            <th>Closing Balance</th>
                            <th>Remark</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($rows as $row) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['transaction_id'], ENT_QUOTES, 'UTF-8') . "</td>";
                            echo "<td>" . htmlspecialchars($row['user_id'], ENT_QUOTES, 'UTF-8') . "</td>";
                            echo "<td>" . htmlspecialchars($row['timestamp'], ENT_QUOTES, 'UTF-8') . "</td>";
                            echo "<td>" . htmlspecialchars($row['type'], ENT_QUOTES, 'UTF-8') . "</td>";
                            echo "<td>" . htmlspecialchars($row['utr'], ENT_QUOTES, 'UTF-8') . "</td>";
                            echo "<td>" . htmlspecialchars($row['opening_balance'], ENT_QUOTES, 'UTF-8') . "</td>";
                            echo "<td>" . htmlspecialchars($row['amount'], ENT_QUOTES, 'UTF-8') . "</td>";
                            echo "<td>" . htmlspecialchars($row['closing_balance'], ENT_QUOTES, 'UTF-8') . "</td>";
                            echo "<td>" . htmlspecialchars($row['Remark'], ENT_QUOTES, 'UTF-8') . "</td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- CORE PLUGINS-->
    <script src="./assets/vendors/jquery/dist/jquery.min.js" type="text/javascript"></script>
    <script src="./assets/vendors/popper.js/dist/umd/popper.min.js" type="text/javascript"></script>
    <script src="./assets/vendors/bootstrap/dist/js/bootstrap.min.js" type="text/javascript"></script>
    <script src="./assets/vendors/metisMenu/dist/metisMenu.min.js" type="text/javascript"></script>
    <script src="./assets/vendors/jquery-slimscroll/jquery.slimscroll.min.js" type="text/javascript"></script>
    <!-- PAGE LEVEL PLUGINS-->
    <script src="./assets/vendors/DataTables/datatables.min.js" type="text/javascript"></script>
    <!-- CORE SCRIPTS-->
    <script src="assets/js/app.min.js" type="text/javascript"></script>
    <!-- PAGE LEVEL SCRIPTS-->
    <script type="text/javascript">
        $(function() {
            $('#example-table').DataTable({
                pageLength: 10,
                //"ajax": './assets/demo/data/table_data.json',
                /*"columns": [
                    { "data": "name" },
                    { "data": "office" },
                    { "data": "extn" },
                    { "data": "start_date" },
                    { "data": "salary" }
                ]*/
            });
        })
    </script>
</body>
</html>

