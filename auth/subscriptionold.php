<?php include "header.php"; ?>

<?php 
$query = $conn->query('SELECT * FROM `subscription_plan` WHERE `status` = "active"');
?>

<!-- START PAGE CONTENT-->
<div class="page-heading">
    <h1 class="page-title">Subscription & Plans</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="index.html"><i class="la la-home font-20"></i></a>
        </li>
        <!-- <li class="breadcrumb-item">Icons</li> -->
    </ol>
</div>
<div class="page-content fade-in-up">
    <div class="ibox">
        <div class="ibox-body">
            <!-- <div class="row"> -->
            <!-- <div class="col-md-4"> -->
            <div class="card m-t-20 m-b-20">
                <div class="card-body">
                    <div class="main-panel">
                        <div class="main-panel">
                            <div class="content">
                                <div class="row">
                                    <?php while($row = $query->fetch_assoc()){ ?>
                                    <div class="col-md-3">
                                        <div class="card text-center">
                                            <div class="card-header">
                                                <h4 class="card-title"><?= $row["plan_name"] ?></h4>
                                                <h2 class="text-center">₹<?= number_format($row["amount"]) ?></h2>
                                                <p class="card-category">
                                                    <?= $row["expiry"] ?>
                                                    Month
                                                </p>
                                            </div>
                                            <div class="card-body">
                                                <table class="mx-auto">
                                                    <tbody>
                                                        <tr>
                                                            <td><i class="icon-md text-primary me-2" data-feather="check"></i></td>
                                                            <td><p>0 Transaction Fee</p></td>
                                                        </tr>
                                                        <tr>
                                                            <td><i class="icon-md text-primary me-2" data-feather="check"></i></td>
                                                            <td><p>Realtime Transaction</p></td>
                                                        </tr>
                                                        <tr>
                                                            <td><i class="icon-md text-primary me-2" data-feather="check"></i></td>
                                                            <td><p>No Amount Limit</p></td>
                                                        </tr>
                                                        <tr>
                                                            <td><i class="icon-md text-primary me-2" data-feather="check"></i></td>
                                                            <td><p style="color: red;">HDFC Vyapar | BharatPE | SBI Merchet</p></td>
                                                        </tr>
                                                        <tr>
                                                            <td><i class="icon-md text-primary me-2" data-feather="check"></i></td>
                                                            <td><p style="color: red;">Phonepe | Paytm | Mobiqick | Freecharge</p></td>
                                                        </tr>
                                                        <tr>
                                                            <td><i class="icon-md text-danger me-2" data-feather="x"></i></td>
                                                            <td><p>Dynamic QR Code</p></td>
                                                        </tr>

                                                        <tr>
                                                            <td><i class="icon-md text-danger me-2" data-feather="x"></i></td>
                                                            <td><p>Direct UPI Intent</p></td>
                                                        </tr>
                                                        <tr>
                                                            <td><i class="icon-md text-danger me-2" data-feather="x"></i></td>
                                                            <td><p>Accept All UPI Apps</p></td>
                                                        </tr>
                                                        <tr>
                                                            <td><i class="icon-md text-primary me-2" data-feather="check"></i></td>
                                                            <td><p>24*7 WhatsApp Support</p></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                            <div class="card-footer">
                                                <?php
                                                 $balance = $userdata["balance"];
                                                 $planamount = $row["amount"];
                                                 $planid = $row["id"];
                                                 
                                                 if ($balance>0) {
                                                     $display = "flex";
                                                 }else {
                                                     $display = "none";
                                                 }
                                                ?>
                                                 
<form method="POST" action="lib/pay">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>" />
    <input type="hidden" name="amount" value="<?= $planamount ?>" />
    <input type="hidden" name="planid" value="<?= $planid ?>" />
    <input type="hidden" name="for" value="PlanRenew" />
    
    <!-- Checkbox for Wallet Balance -->
    <div style="margin: 15px 0; display: <?= $display ?>; align-items: center; gap: 15px;">
        <input
            type="checkbox"
            id="use_wallet"
            name="use_wallet"
            value="yes"
            style="
                width: 50px;
                height: 50px;
                cursor: pointer;
                accent-color: #28a745;
                border: 2px solid #ddd;
                border-radius: 5px;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1), inset 0 -2px 4px rgba(0, 0, 0, 0.2);
                background: linear-gradient(to bottom, #fff, #e6e6e6);
                transition: all 0.3s ease-in-out;
            "
            onmouseover="this.style.transform='scale(1.1)'; this.style.boxShadow='0 6px 10px rgba(0, 0, 0, 0.2)';"
            onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='0 4px 6px rgba(0, 0, 0, 0.1), inset 0 -2px 4px rgba(0, 0, 0, 0.2)';"
        />
        <label
            style="
                font-size: 18px;
                font-weight: bold;
                color: #333;
                cursor: pointer;
                text-shadow: 1px 1px 1px rgba(0, 0, 0, 0.1);
                background: linear-gradient(to bottom, #ffffff, #f0f0f0);
                padding: 5px 4px;
                border-radius: 8px;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1), inset 0 -2px 4px rgba(255, 255, 255, 0.6);
                transition: all 0.3s ease-in-out;
            "
        >
            ⬅️Tick for use wallet balance: <br>₹ <?= $balance ?>
        </label>
    </div>

    <!-- Submit Button -->
    <button type="submit" name="upigate" class="btn btn-outline-success btn-block">Buy Now</button>
</form>


                                            </div>
                                        </div>
                                    </div>

                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
<script src="./assets/vendors/jquery/dist/jquery.min.js" type="text/javascript"></script>
<script src="assets/js/plugin/jquery-ui-1.12.1.custom/jquery-ui.min.js"></script>
<script src="assets/js/core/popper.min.js"></script>
<script src="assets/js/core/bootstrap.min.js"></script>
<script src="assets/js/plugin/bootstrap-notify/bootstrap-notify.min.js"></script>
<script src="assets/js/plugin/bootstrap-toggle/bootstrap-toggle.min.js"></script>
<script src="assets/js/plugin/jquery-mapael/jquery.mapael.min.js"></script>
<script src="assets/js/plugin/jquery-mapael/maps/world_countries.min.js"></script>
<script src="assets/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js"></script>
<script src="assets/js/ready.min.js"></script>
<script src="assets/js/rechpay.js?1697835127"></script>
<script src="./assets/js/scripts/dashboard_1_demo.js" type="text/javascript"></script>
<script type="text/javascript">
</html>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.1/css/dataTables.bootstrap5.min.css"/>
<script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.1/js/dataTables.bootstrap5.min.js"></script>
<script>
$(document).ready(function () {
    $("#dataTable").DataTable();
});
</script>
<script src="assets/js/bharatpe.js?1697835127"></script>