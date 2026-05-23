<?php 
include "header.php"; 
// include 'function.php';

// Handle Approve/Reject actions
if (isset($_POST['action']) && isset($_POST['order_id'])) {
    $order_id = mysqli_real_escape_string($conn, $_POST['order_id']);
    $plan_id = mysqli_real_escape_string($conn, $_POST['planid']);
    $user_id = mysqli_real_escape_string($conn, $_POST['userid']);
    $action = $_POST['action'];
    
    if ($action == 'approve') {
        if (isset($_POST['utr_verification']) && !empty($_POST['utr_verification'])) {
            $utr_verification = mysqli_real_escape_string($conn, $_POST['utr_verification']);
            
            // Start transaction
            mysqli_begin_transaction($conn);
            
            try {
                // Update order status
                $update_query = "UPDATE planorders SET status = 'SUCCESS', Remark = 'Approved by Admin', utr = '$utr_verification' WHERE id = '$order_id'";
                if (!mysqli_query($conn, $update_query)) {
                    throw new Exception("Error updating order: " . mysqli_error($conn));
                }
                
                $plan_query = "SELECT expiry FROM subscription_plan WHERE id = '$plan_id'";
                $plan_result = mysqli_query($conn, $plan_query);
                
                if (!$plan_result) {
                    throw new Exception("Error fetching plan: " . mysqli_error($conn));
                }
                
                $plan_data = mysqli_fetch_assoc($plan_result);
                $duration = $plan_data['expiry'];
                
                // Check user's current plan expiry
                $user_query = "SELECT expiry FROM users WHERE id = '$user_id'";
                $user_result = mysqli_query($conn, $user_query);
                
                if (!$user_result) {
                    throw new Exception("Error fetching user: " . mysqli_error($conn));
                }
                
                $user_data = mysqli_fetch_assoc($user_result);
                $current_expiry = $user_data['expiry'];
                $current_date = date('Y-m-d H:i:s');
                
                // Calculate new expiry date
                if ($current_expiry && $current_expiry > $current_date) {
                    // If plan hasn't expired, add months to existing expiry
                    $expiry_date = date('Y-m-d H:i:s', strtotime($current_expiry . " +$duration months"));
                } else {
                    // If plan has expired or doesn't exist, add months from today
                    $expiry_date = date('Y-m-d H:i:s', strtotime("+$duration months"));
                }
                
                // Update user plan
                    $user_update_query = "UPDATE users SET 
                    planId = '$plan_id',
                    expiry = '$expiry_date',
                    tranjection_Count = 0
                    WHERE id = '$user_id'";
                
                if (!mysqli_query($conn, $user_update_query)) {
                    throw new Exception("Error updating user: " . mysqli_error($conn));
                }
                
                // Commit transaction
                mysqli_commit($conn);
                echo "<script>alert('Order approved and user plan updated successfully'); window.location.href='planorders.php';</script>";
            } catch (Exception $e) {
                mysqli_rollback($conn);
                echo "<script>alert('" . $e->getMessage() . "'); window.location.href='planorders.php';</script>";
            }
        }
    } elseif ($action == 'reject') {
        $update_query = "UPDATE planorders SET status = 'FAILURE', Remark = 'Rejected by Admin' WHERE id = '$order_id'";
        if (mysqli_query($conn, $update_query)) {
            echo "<script>alert('Order rejected successfully'); window.location.href='planorders.php';</script>";
        } else {
            echo "<script>alert('Error rejecting order: " . mysqli_error($conn) . "');</script>";
        }
    }
}
?>

<!-- START PAGE CONTENT-->
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
            <div class="ibox-title" style="display: flex; justify-content: space-between; align-items: center;">
                <!-- Date and Status Filter (Left Side) -->
                <div class="filter-section">
                    <form id="dateRangeForm" method="GET">
                        <label for="start_date">From:</label>
                        <input type="date" id="start_date" name="start_date" value="<?php echo isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d'); ?>">
                        <label for="end_date">To:</label>
                        <input type="date" id="end_date" name="end_date" value="<?php echo isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d'); ?>">
                        <label for="status">Status:</label>
                        <select id="status" name="status">
                            <option value="all" <?php echo (isset($_GET['status']) && $_GET['status'] == 'all') ? 'selected' : ''; ?>>All</option>
                            <option value="SUCCESS" <?php echo (isset($_GET['status']) && $_GET['status'] == 'SUCCESS') ? 'selected' : ''; ?>>Success</option>
                            <option value="PENDING" <?php echo (isset($_GET['status']) && $_GET['status'] == 'PENDING') ? 'selected' : ''; ?>>Pending</option>
                            <option value="FAILURE" <?php echo (isset($_GET['status']) && $_GET['status'] == 'FAILURE') ? 'selected' : ''; ?>>Failure</option>
                        </select>
                        <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                    </form>
                </div>
                
                <!-- Summary Section (Right Side) -->
                <div class="summary-section">
                    <?php
                    $userid = $userdata['id'];
                    $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d');
                    $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
                    $status = isset($_GET['status']) ? $_GET['status'] : 'all';

                    // Query to get status counts and amounts
                    $summary_query = "SELECT 
                        SUM(CASE WHEN status = 'SUCCESS' THEN 1 ELSE 0 END) as success_count,
                        SUM(CASE WHEN status = 'PENDING' THEN 1 ELSE 0 END) as pending_count,
                        SUM(CASE WHEN status = 'FAILURE' THEN 1 ELSE 0 END) as failure_count,
                        SUM(CASE WHEN status = 'SUCCESS' THEN amount ELSE 0 END) as success_amount,
                        SUM(CASE WHEN status = 'PENDING' THEN amount ELSE 0 END) as pending_amount,
                        SUM(CASE WHEN status = 'FAILURE' THEN amount ELSE 0 END) as failure_amount
                        FROM planorders 
                        WHERE ";
                    
                    if ($userdata['role'] != 'Admin') {
                        $summary_query .= "userid = '$userid' AND ";
                    }
                    
                    $summary_query .= "DATE(payment_date) BETWEEN '$start_date' AND '$end_date'";
                    
                    if ($status != 'all') {
                        $summary_query .= " AND status = '" . mysqli_real_escape_string($conn, $status) . "'";
                    }

                    $summary_result = mysqli_query($conn, $summary_query);
                    $summary_data = mysqli_fetch_assoc($summary_result);
                    ?>
                    <!--<h4 style="font-size: 16px;">Summary (<?php echo $start_date . ' to ' . $end_date; ?>)</h4>-->
                    <p style="color: green; margin: 2px 0;"><strong>Success:</strong> <?php echo $summary_data['success_count'] ?? 0; ?> orders (₹<?php echo number_format($summary_data['success_amount'] ?? 0, 2); ?>)</p>
                    <p style="color: orange; margin: 2px 0;"><strong>Pending:</strong> <?php echo $summary_data['pending_count'] ?? 0; ?> orders (₹<?php echo number_format($summary_data['pending_amount'] ?? 0, 2); ?>)</p>
                    <p style="color: red; margin: 2px 0;"><strong>Failure:</strong> <?php echo $summary_data['failure_count'] ?? 0; ?> orders (₹<?php echo number_format($summary_data['failure_amount'] ?? 0, 2); ?>)</p>
                </div>
            </div>
        </div>
        <div class="ibox-body">
            <table class="table table-striped table-bordered table-hover" id="example-table" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>User ID</th>
                        <th>PlanID</th>
                        <th>order_id</th>
                        <th>Amount</th>
                        <th>UTR</th>
                        <th>Mobile</th>
                        <th>Payment Date</th>
                        <th>Expiry</th>
                        <th>Status</th>
                        <th>Remark</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $query = "SELECT `id`, `utr`, `userid`, `amount`, `status`, `payment_date`, `planid`, `userMobile`, `Remark`, `expiry_date`, `order_id`
                          FROM planorders 
                          WHERE ";
                
                if ($userdata['role'] != 'Admin') {
                    $query .= "userid = '$userid' AND ";
                }
                
                $query .= "DATE(payment_date) BETWEEN '$start_date' AND '$end_date'";
                
                if ($status != 'all') {
                    $query .= " AND status = '" . mysqli_real_escape_string($conn, $status) . "'";
                }
                
                $query .= " ORDER BY payment_date DESC";

                $query_run = mysqli_query($conn, $query);

                if ($query_run) {
                    while ($row = mysqli_fetch_assoc($query_run)) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8') . "</td>";
                        echo "<td>" . htmlspecialchars($row['userid'], ENT_QUOTES, 'UTF-8') . "</td>";
                        echo "<td>" . htmlspecialchars($row['planid'], ENT_QUOTES, 'UTF-8') . "</td>";
                        echo "<td>" . htmlspecialchars($row['order_id'], ENT_QUOTES, 'UTF-8') . "</td>";
                        echo "<td>" . htmlspecialchars($row['amount'], ENT_QUOTES, 'UTF-8') . "</td>";
                        echo "<td>" . htmlspecialchars($row['utr'], ENT_QUOTES, 'UTF-8') . "</td>";
                        echo "<td>" . htmlspecialchars($row['userMobile'], ENT_QUOTES, 'UTF-8') . "</td>";
                        echo "<td>" . htmlspecialchars($row['payment_date'], ENT_QUOTES, 'UTF-8') . "</td>";
                        echo "<td>" . htmlspecialchars($row['expiry_date'], ENT_QUOTES, 'UTF-8') . "</td>";
                        echo "<td>" . htmlspecialchars($row['status'], ENT_QUOTES, 'UTF-8') . "</td>";
                        echo "<td>" . htmlspecialchars($row['Remark'], ENT_QUOTES, 'UTF-8') . "</td>";
                        
                        // Action column
                        echo "<td>";
                        if ($userdata['role'] == 'Admin' && $row['status'] == 'PENDING') {
                            echo '<form method="POST" style="display:inline;" onsubmit="return confirm(\'Please verify UTR before approving\');">';
                            echo '<input type="hidden" name="order_id" value="' . $row['id'] . '">';
                            echo '<input type="hidden" name="planid" value="' . $row['planid'] . '">';
                            echo '<input type="hidden" name="userid" value="' . $row['userid'] . '">';
                            echo '<input type="text" name="utr_verification" placeholder="Enter UTR to verify" required style="width: 120px; margin-right: 5px;">';
                            echo '<button type="submit" name="action" value="approve" class="btn btn-success btn-sm">Approve</button>';
                            echo '</form>';
                            echo ' ';
                            echo '<form method="POST" style="display:inline;">';
                            echo '<input type="hidden" name="order_id" value="' . $row['id'] . '">';
                            echo '<input type="hidden" name="planid" value="' . $row['planid'] . '">';
                            echo '<input type="hidden" name="userid" value="' . $row['userid'] . '">';
                            echo '<button type="submit" name="action" value="reject" class="btn btn-danger btn-sm">Reject</button>';
                            echo '</form>';
                        } else {
                            echo 'N/A';
                        }
                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='12'>Error in query: " . mysqli_error($conn) . "</td></tr>"; 
                }
                ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- PAGE LEVEL PLUGINS -->
<script src="./assets/vendors/DataTables/datatables.min.js" type="text/javascript"></script>

<!-- PAGE LEVEL SCRIPTS -->
<script type="text/javascript">
    $(function() {
        $('#example-table').DataTable({
            pageLength: 15,
            order: [[0, 'desc']]
        });
    });
</script>