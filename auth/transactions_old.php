<?php
include "header.php";

// PHP for Current Month Data
$currentMonth = date('Y-m');
if ($userdata['role'] == 'User') {
    $token = $userdata['user_token'];
    $query = "SELECT * FROM orders WHERE user_token = '$token' AND DATE_FORMAT(create_date, '%Y-%m') = '$currentMonth' ORDER BY create_date DESC";
} else {
    $query = "SELECT * FROM orders WHERE DATE_FORMAT(create_date, '%Y-%m') = '$currentMonth' ORDER BY create_date DESC";
}

$totalAmount = $totalTransactions = $successCount = $pendingCount = $failureCount = 0;
$successAmount = $pendingAmount = $failureAmount = 0; // New variables for amounts
$rows = []; // Array to hold table rows
$query_run = mysqli_query($conn, $query);

if (!$query_run) {
    echo "Query Error: " . mysqli_error($conn);
} else {
    while ($row = mysqli_fetch_assoc($query_run)) {
        $totalAmount += (float)$row['amount']; // Ensure numeric
        $totalTransactions++;
        
        if ($row['status'] === 'SUCCESS') {
            $successCount++;
            $successAmount += (float)$row['amount']; // Add to success amount
        } elseif ($row['status'] === 'FAILURE') {
            $failureCount++;
            $failureAmount += (float)$row['amount']; // Add to failure amount
        } else {
            $pendingCount++;
            $pendingAmount += (float)$row['amount']; // Add to pending amount
        }
        $rows[] = $row; // Store each row for later use in the table
    }
}
?>

<!-- START PAGE CONTENT -->
<link href="./assets/vendors/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet" />
<link href="./assets/vendors/font-awesome/css/font-awesome.min.css" rel="stylesheet" />
<link href="./assets/vendors/themify-icons/css/themify-icons.css" rel="stylesheet" />
<link href="./assets/vendors/DataTables/datatables.min.css" rel="stylesheet" />
<link href="assets/css/main.min.css" rel="stylesheet" />

<div class="page-heading">
    <h1 class="page-title">Transactions</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="index.html"><i class="la la-home font-20"></i></a>
        </li>
    </ol>
</div>

<div class="page-content fade-in-up">
    <div class="ibox">

<!-- Summary Section -->
<div class="row summary-section mb-4">
    <div class="col-md-12">
        <div class="alert alert-info text-center">
            <strong>Note:</strong> Data shown below is for the <span class="text-primary">current month</span> only.
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm text-center border-primary">
            <div class="card-header bg-primary text-white">Total Transactions</div>
            <div class="card-body">
                <h3><?= $totalTransactions; ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm text-center border-success">
            <div class="card-header bg-success text-white">Total Amount</div>
            <div class="card-body">
                <h3>₹<?= number_format($totalAmount, 2); ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card shadow-sm text-center border-success">
            <div class="card-header bg-success text-white">Success</div>
            <div class="card-body">
                <h3><?= $successCount; ?> <small>| ₹<?= number_format($successAmount, 2); ?></small></h3>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card shadow-sm text-center border-warning">
            <div class="card-header bg-warning text-dark">Pending</div>
            <div class="card-body">
                <h3><?= $pendingCount; ?> <small>| ₹<?= number_format($pendingAmount, 2); ?></small></h3>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card shadow-sm text-center border-danger">
            <div class="card-header bg-danger text-white">Failure</div>
            <div class="card-body">
                <h3><?= $failureCount; ?> <small>| ₹<?= number_format($failureAmount, 2); ?></small></h3>
            </div>
        </div>
    </div>
</div>



        <!-- Export Buttons -->
        <form method="post" action="export.php">
            <button type="submit" name="export_csv" class="btn btn-success">Export to CSV</button>
            <button type="submit" name="export_excel" class="btn btn-info">Export to Excel</button>
            <button type="submit" name="export_pdf" class="btn btn-danger">Export to PDF</button>
        </form>
        <div class="ibox-head">
            <div class="ibox-title">Date Filter</div>
            <div class="ibox-tools">
                <!-- Status and Date Filters -->
                <select id="status-filter" class="form-control">
                    <option value="">All</option>
                    <option value="SUCCESS">Success</option>
                    <option value="PENDING">Pending</option>
                    <option value="FAILURE">Failure</option>
                </select>
                <input type="date" id="start-date" placeholder="Start Date">
                <input type="date" id="end-date" placeholder="End Date">
                <button id="filter-date" class="btn btn-primary">Filter</button>
                <button id="clear-filter" class="btn btn-secondary">Clear Filter</button>
            </div>
        </div>
        <!-- Transactions Table -->
<div class="ibox-body">
    <table class="table table-striped table-bordered table-hover" id="example-table" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th>#</th>
                <th>User Id</th>
                <th>Mobile</th>
                <th>Date Time</th>
                <th>Merchant</th>
                <th>Gateway Txn</th>
                <th>Bank RRN</th>
                <th>Order ID</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (empty($rows)) {
                echo "<tr><td colspan='11'>No data found for the current month.</td></tr>";
            } else {
                foreach ($rows as $row) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8') . "</td>";
                    echo "<td>" . htmlspecialchars($row['user_id'], ENT_QUOTES, 'UTF-8') . "</td>";
                    echo "<td>" . htmlspecialchars($row['customer_mobile'], ENT_QUOTES, 'UTF-8') . "</td>";
                    echo "<td>" . htmlspecialchars($row['create_date'], ENT_QUOTES, 'UTF-8') . "</td>";
                    echo "<td>" . htmlspecialchars($row['method'], ENT_QUOTES, 'UTF-8') . "</td>";
                    echo "<td>" . htmlspecialchars($row['gateway_txn'], ENT_QUOTES, 'UTF-8') . "</td>";
                    echo "<td>" . htmlspecialchars($row['utr'], ENT_QUOTES, 'UTF-8') . "</td>";
                    echo "<td>" . htmlspecialchars($row['order_id'], ENT_QUOTES, 'UTF-8') . "</td>";
                    echo "<td>₹" . htmlspecialchars($row['amount'], ENT_QUOTES, 'UTF-8') . "</td>";

                    $status = $row['status'] === 'SUCCESS' ? 'Success' : ($row['status'] === 'FAILURE' ? 'Failure' : 'Pending');
                    $class = $row['status'] === 'SUCCESS' ? 'badge badge-success' : ($row['status'] === 'FAILURE' ? 'badge badge-danger' : 'badge badge-warning');
                    echo "<td><button class='$class'>" . $status . "</button></td>";

                    // Last Action Column
                    if ($row['status'] === 'PENDING') {
                        echo "<td>
                            <button class='btn btn-primary btn-sm' onclick=\"makeSuccessWithUTR('" . $row['id'] . "', '" . $row['order_id'] . "')\">Make Success</button>
                        </td>";
                    } else {
                        echo "<td>-</td>";
                    }
                    echo "</tr>";
                }
            }
            ?>
        </tbody>
    </table>
</div>

<!-- SweetAlert CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function makeSuccessWithUTR(id, orderId) {
    Swal.fire({
        title: 'Enter UTR for the transaction',
        input: 'text',
        inputLabel: '⚠️ Proceed carefully! We did not verify this.',
        inputPlaceholder: 'Enter UTR here',
        showCancelButton: true,
        confirmButtonText: 'Submit',
        preConfirm: (utr) => {
            if (!utr) {
                Swal.showValidationMessage('UTR is required!');
            } else {
                return utr;
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const utr = result.value;
            Swal.fire({
                title: 'Are you sure?',
                  text: "❗ Important Note:\nMarking this transaction as SUCCESS will immediately trigger the Success Callback to your portal/website.\n\n✅ Please double-check all transaction details before proceeding.\n\n⚠️ This action cannot be undone.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, mark it!'
            }).then((confirmResult) => {
                if (confirmResult.isConfirmed) {
                    fetch('update_status.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ id: id, order_id: orderId, utr: utr })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire(
                                'Success!',
                                'Transaction updated successfully!',
                                'success'
                            ).then(() => location.reload());
                        } else {
                            Swal.fire(
                                'Error!',
                                'Failed to update transaction: ' + data.error,
                                'error'
                            );
                        }
                    })
                    .catch(error => {
                        console.error("Error:", error);
                        Swal.fire(
                            'Error!',
                            'An error occurred while updating the transaction.',
                            'error'
                        );
                    });
                }
            });
        }
    });
}
</script>



    </div>
</div>


<!-- PAGE LEVEL SCRIPTS-->
<script src="./assets/vendors/jquery/dist/jquery.min.js"></script>
<script src="./assets/vendors/bootstrap/dist/js/bootstrap.min.js"></script>
<script src="./assets/vendors/DataTables/datatables.min.js"></script>
<script src="assets/js/app.min.js"></script>

<script type="text/javascript">
$(function() {
    var table = $('#example-table').DataTable({
        pageLength: 10,
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
        order: [[3, 'desc']]
    });

    // Date and Status Filter
    $('#filter-date').click(function() {
        var startDate = $('#start-date').val();
        var endDate = $('#end-date').val();
        var statusFilter = $('#status-filter').val();
        $.fn.dataTable.ext.search.pop();

        $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
            var date = new Date(data[3]).setHours(0, 0, 0, 0);
            var start = startDate ? new Date(startDate).setHours(0, 0, 0, 0) : null;
            var end = endDate ? new Date(endDate).setHours(23, 59, 59, 999) : null;
            var status = data[9] === 'Success' ? 'SUCCESS' : data[9] === 'Failure' ? 'FAILURE' : 'PENDING';

            if ((start === null || start <= date) &&
                (end === null || date <= end) &&
                (statusFilter === "" || status === statusFilter)) {
                return true;
            }
            return false;
        });
        table.draw();
    });

    // Clear Filter
    $('#clear-filter').click(function() {
        $('#start-date').val('');
        $('#end-date').val('');
        $('#status-filter').val('');
        $.fn.dataTable.ext.search.pop();
        table.draw();
    });

    // Real-time Search
    $('#table-search').on('keyup', function() {
        table.search(this.value).draw();
    });
});
</script>
</html>
