<?php include "header.php"; ?>

<?php 
if($userdata["role"] != 'Admin'){
    echo '<script>window.location.href = "dashboard";</script>';
    exit;
}

// सुरक्षित SQL क्वेरी
$stmt = $conn->prepare('SELECT * FROM subscription_plan');
$stmt->execute();
$query = $stmt->get_result();

// Subscription अपडेट करने की प्रक्रिया
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["updatesub"])) {
    $srno = $_POST['srno'];
    $planname = $_POST['plan_name'];
    $amount = $_POST['amount'];
    $expiry = $_POST['expiry'];
    $hitLimit = $_POST['hitLimit'];
    $singleMerchantLimit = $_POST['singleMerchantLimit'];
    $totalMerchantLimit = $_POST['totalMerchantLimit'];
    $status = $_POST['status'];

    $update_stmt = $conn->prepare("UPDATE subscription_plan SET plan_name = ?, amount = ?, expiry = ?, hitLimit = ?, singleMerchantLimit = ?, totalMerchantLimit = ?, status = ? WHERE id = ?");
    $update_stmt->bind_param("sssisssi", $planname, $amount, $expiry, $hitLimit, $singleMerchantLimit, $totalMerchantLimit, $status, $srno);

    if ($update_stmt->execute()) {
        echo "<script>
            Swal.fire({title: 'Success!', text: 'Subscription updated successfully!', icon: 'success', confirmButtonText: 'OK'})
            .then(() => { window.location.href = 'manage_subscription'; });
        </script>";
    } else {
        echo "<script>
            Swal.fire({title: 'Error!', text: 'Error updating Subscription', icon: 'error', confirmButtonText: 'OK'})
            .then(() => { window.location.href = 'manage_subscription'; });
        </script>";
    }
    $update_stmt->close();
}

// Add Plan की प्रक्रिया
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["addplan"])) {
    $planname = $_POST['plan_name'];
    $amount = $_POST['amount'];
    $expiry = $_POST['expiry'];
    $hitLimit = $_POST['hitLimit'];
    $singleMerchantLimit = $_POST['singleMerchantLimit'];
    $totalMerchantLimit = $_POST['totalMerchantLimit'];
    $status = $_POST['status'];

    $insert_stmt = $conn->prepare("INSERT INTO subscription_plan (plan_name, amount, expiry, hitLimit, singleMerchantLimit, totalMerchantLimit, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $insert_stmt->bind_param("sssisss", $planname, $amount, $expiry, $hitLimit, $singleMerchantLimit, $totalMerchantLimit, $status);

    if ($insert_stmt->execute()) {
        echo "<script>
            Swal.fire({title: 'Success!', text: 'Plan added successfully!', icon: 'success', confirmButtonText: 'OK'})
            .then(() => { window.location.href = 'manage_subscription'; });
        </script>";
    } else {
        echo "<script>
            Swal.fire({title: 'Error!', text: 'Error adding plan', icon: 'error', confirmButtonText: 'OK'})
            .then(() => { window.location.href = 'manage_subscription'; });
        </script>";
    }
    $insert_stmt->close();
}

// Delete Plan की प्रक्रिया
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["deleteplan"])) {
    $srno = $_POST['srno'];

    $delete_stmt = $conn->prepare("DELETE FROM subscription_plan WHERE id = ?");
    $delete_stmt->bind_param("i", $srno);

    if ($delete_stmt->execute()) {
        echo "<script>
            Swal.fire({title: 'Success!', text: 'Plan deleted successfully!', icon: 'success', confirmButtonText: 'OK'})
            .then(() => { window.location.href = 'manage_subscription'; });
        </script>";
    } else {
        echo "<script>
            Swal.fire({title: 'Error!', text: 'Error deleting plan', icon: 'error', confirmButtonText: 'OK'})
            .then(() => { window.location.href = 'manage_subscription'; });
        </script>";
    }
    $delete_stmt->close();
}
?>

<div class="page-heading">
    <h1 class="page-title">Update Subscription & Plans</h1>
</div>

<div class="page-content fade-in-up">
    <div class="ibox">
        <div class="ibox-body">
            <div class="row p-4">
                <!-- Add Plan Button -->
                <?php if(!isset($_GET["srno"]) && !isset($_GET["add"])) { ?>
                    <div class="col-md-12 mb-3">
                        <button onclick="window.location.href='manage_subscription?add=1'" class="btn btn-primary">Add Plan</button>
                    </div>
                <?php } ?>

                <!-- Add Plan Form -->
                <?php if(isset($_GET["add"])) { ?>
                <div class="col-md-12">
                    <h3>Add New Plan</h3>
                    <form method="POST" action="">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Plan Name</label>
                                <input type="text" name="plan_name" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Amount</label>
                                <input type="text" name="amount" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Expiry (Months)</label>
                                <input type="text" name="expiry" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Hit Limit</label>
                                <input type="text" name="hitLimit" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Single Merchant Limit</label>
                                <input type="text" name="singleMerchantLimit" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Total Merchant Limit</label>
                                <input type="text" name="totalMerchantLimit" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Status</label>
                                <select name="status" class="form-control" required>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-12 mb-3">
                                <button type="submit" name="addplan" class="btn btn-primary">Add Plan</button>
                                <a href="manage_subscription" class="btn btn-secondary">Cancel</a>
                            </div>
                        </div>
                    </form>
                </div>
                <?php } elseif(isset($_GET["srno"])) { ?>
                <!-- Update Plan Form -->
                <?php
                    $fetch_stmt = $conn->prepare("SELECT * FROM subscription_plan WHERE id = ?");
                    $fetch_stmt->bind_param("i", $_GET["srno"]);
                    $fetch_stmt->execute();
                    $fetchdata = $fetch_stmt->get_result()->fetch_assoc();
                    $fetch_stmt->close();
                ?>
                <form method="POST" action="">
                    <input type="hidden" name="srno" value="<?= htmlspecialchars($fetchdata["id"]) ?>">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Plan Name</label>
                            <input type="text" name="plan_name" value="<?= htmlspecialchars($fetchdata["plan_name"]) ?>" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Amount</label>
                            <input type="text" name="amount" value="<?= htmlspecialchars($fetchdata["amount"]) ?>" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Expiry (Months)</label>
                            <input type="text" name="expiry" value="<?= htmlspecialchars($fetchdata["expiry"]) ?>" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Hit Limit</label>
                            <input type="text" name="hitLimit" value="<?= htmlspecialchars($fetchdata["hitLimit"]) ?>" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Single Merchant Limit</label>
                            <input type="text" name="singleMerchantLimit" value="<?= htmlspecialchars($fetchdata["singleMerchantLimit"]) ?>" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Total Merchant Limit</label>
                            <input type="text" name="totalMerchantLimit" value="<?= htmlspecialchars($fetchdata["totalMerchantLimit"]) ?>" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Status</label>
                            <select name="status" class="form-control" required>
                                <option value="active" <?= $fetchdata["status"] == 'active' ? 'selected' : '' ?>>Active</option>
                                <option value="inactive" <?= $fetchdata["status"] == 'inactive' ? 'selected' : '' ?>>Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-12 mb-3">
                            <button type="submit" name="updatesub" class="btn btn-primary">Save Changes</button>
                        </div>
                    </div>
                </form>
                <?php } else { ?>
                <!-- Plan List -->
                <div class="row">
                    <?php while($row = $query->fetch_assoc()){ ?>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-header bg-dark text-white">
                                <h4><?= htmlspecialchars($row["plan_name"]) ?></h4>
                                <h2>₹<?= number_format($row["amount"]) ?></h2>
                                <p>Validity: <?= htmlspecialchars($row["expiry"]) ?> Month</p>
                            </div>
                            <div class="card-body">
                                <ul class="list-group text-start">
                                    <li class="list-group-item d-flex"><span class="text-success">✅</span> <span>Hit Limit: <?= $row["hitLimit"] == 0 ? 'Unlimited' : htmlspecialchars($row["hitLimit"]) ?></span></li>
                                    <li class="list-group-item d-flex"><span class="text-success">✅</span> <span>Single Merchant Limit: <?= htmlspecialchars($row["singleMerchantLimit"]) ?></span></li>
                                    <li class="list-group-item d-flex"><span class="text-success">✅</span> <span>Total Merchant Limit: <?= htmlspecialchars($row["totalMerchantLimit"]) ?></span></li>
                                    <!--<li class="list-group-item d-flex"><span class="text-success">✅</span> <span>Realtime Transaction</span></li>-->
                                    <!--<li class="list-group-item d-flex"><span class="text-success">✅</span> <span>Status: <?= htmlspecialchars($row["status"]) ?></span></li>-->
                                    <!--<li class="list-group-item d-flex"><span class="text-danger">❌</span> <span>Dynamic QR Code</span></li>-->
                                    <!--<li class="list-group-item d-flex"><span class="text-danger">❌</span> <span>Direct UPI Intent</span></li>-->
                                    <!--<li class="list-group-item d-flex"><span class="text-success">✅</span> <span>24*7 WhatsApp Support</span></li>-->
                                </ul>
                            </div>
                            <div class="card-footer">
                                <button onclick="window.location.href='manage_subscription?srno=<?= $row["id"] ?>'" class="btn btn-success btn-block">Edit</button>
                                <form method="POST" action="" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this plan?');">
                                    <input type="hidden" name="srno" value="<?= $row["id"] ?>">
                                    <button type="submit" name="deleteplan" class="btn btn-danger btn-block mt-2" <?= $row["id"] == 5 ? 'disabled' : '' ?>>Delete</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php } ?>
                </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function () {
    $("#dataTable").DataTable();
});
</script>