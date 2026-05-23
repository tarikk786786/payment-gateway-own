<?php include "header.php"; 
?>
<?php

// Database connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

<?php if($userdata["role"] == 'Admin') {?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <form id="walletForm" method="POST" action="" class="p-4 border rounded bg-light shadow">
        <h3 class="mb-4">Transaction Form</h3>
        
        <div class="mb-3">
            <label for="username" class="form-label">Username:</label>
            <select id="username" name="username" class="form-select select2" required>
                <option value="">Select a Username</option>
                <?php
                // Fetch usernames and full names from the database
                $userQuery = "SELECT id, name, mobile FROM users";
                $userResult = $conn->query($userQuery);

                if ($userResult->num_rows > 0) {
                    while ($user = $userResult->fetch_assoc()) {
                        echo '<option value="' . $user['id'] . '">' . $user['name'] . ' [' . $user['mobile'] . ']</option>';
                    }
                } else {
                    echo '<option value="">No users available</option>';
                }
                ?>
            </select>
        </div>

        <div id="userDetails" class="info mb-3" style="display: none;">
            <p><strong>Balance:</strong> <span id="balance">0</span></p>
            <p><strong>Wallet Points:</strong> <span id="walletPoints">0</span></p>
        </div>

        <div class="mb-3">
            <label for="type" class="form-label">Transaction Type:</label>
            <select id="type" name="type" class="form-select" required>
                <option value="">Select Type</option>
                <option value="credit">Credit</option>
                <option value="debit">Debit</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="amount" class="form-label">Amount:</label>
            <input type="number" id="amount" name="amount" class="form-control" placeholder="Enter amount" min="1" required>
        </div>

        <button type="submit" name="submit" class="btn btn-primary w-100">Submit</button>
    </form>



        <?php
        if (isset($_POST['submit'])) {
            $username = $conn->real_escape_string($_POST['username']);
            $type = $_POST['type'];
            $amount = (float)$_POST['amount'];

            // Fetch user details
            $query = "SELECT * FROM users WHERE id = '$username'";
            $result = $conn->query($query);

            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();

                // Perform transaction
                if ($type === "credit") {
                    credit_balance($username, $amount, "0000", "Admin Credit");
                } elseif ($type === "debit") {

                    debit_balance($username, $amount, "0000", "Admin Debit");
                } else {
                    echo "<script>Swal.fire('Error', 'Invalid transaction type!', 'error');</script>";
                    exit;
                }
        }
}}else{
?>
    <div class="main-panel">
        <!-- BEGIN : Main Content-->
        <div class="main-content">
          <div class="content-wrapper">
<h1 class="text-danger text-center">This Feature Available Only for ADMIN.</h1>
<img class="img-fluid" src="../bootstrap/img/cloud.png">
 </div>
    </div>
    </div>
<?php
}
?>

                <!-- END PAGE CONTENT-->
<?php include "footer.php";?>
<script>
    $(document).ready(function() {
        // Initialize Select2 on the username select box
        $('#username').select2({
            placeholder: "Search for a username", // Placeholder text
            allowClear: true                     // Clear button enable karein
        });
    });
</script>
<!-- End of Main Content -->
<!-- Include Select2 CSS -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-rc.0/css/select2.min.css" rel="stylesheet" />
<!-- Include jQuery -->
<!--<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>-->
<!-- Include Select2 JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-rc.0/js/select2.min.js"></script>
<!-- Include Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Include Bootstrap JS -->
<!--<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>-->

<!-- PAGE LEVEL SCRIPTS-->
<!--<script src="./assets/vendors/jquery/dist/jquery.min.js"></script>-->
<!--<script src="./assets/vendors/bootstrap/dist/js/bootstrap.min.js"></script>-->
<!--<script src="./assets/vendors/DataTables/datatables.min.js"></script>-->
<!--<script src="assets/js/app.min.js"></script>-->