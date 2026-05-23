<?php include "header.php"; ?>

<?php 
// सुरक्षित SQL क्वेरी
$stmt = $conn->prepare('SELECT * FROM subscription_plan WHERE status = ?');
$status = "active";
$stmt->bind_param("s", $status);
$stmt->execute();
$query = $stmt->get_result();

$stmt = $conn->prepare('SELECT vip_plan FROM site_settings');
$stmt->execute();
$vplan = $stmt->get_result();
$row = $vplan->fetch_assoc();
$vip_plan = $row['vip_plan']; // Correct way to access the vip_plan value

$activePlan = $userdata['planId'];

// ✅ merchants टेबल से डेटा लाओ
$merchantTables = [];
$result = $conn->query("SELECT merchantname, tablename FROM merchants WHERE status = 1");

while ($row = $result->fetch_assoc()) {
    $merchantTables[$row['merchantname']] = $row['tablename'];
}

$totalMerchantCount = 0;
$merchantDetails = [];

// ✅ अब डायनामिक merchants टेबल से यूज़र का डेटा लो
foreach ($merchantTables as $name => $table) {
    $querym = mysqli_query($conn, "SELECT COUNT(*) as total FROM `$table` WHERE user_token='{$userdata['user_token']}'");
    $count = mysqli_fetch_assoc($querym)['total'];
    $merchantDetails[$name] = $count;
    $totalMerchantCount += $count;
}

// Determine the connected merchant dynamically
$bbbytemerchant = '';
$singleMerchantCount = 0;

// Logic to find the connected merchant
foreach ($merchantDetails as $merchantName => $count) {
    if ($count > $singleMerchantCount) {  
        $bbbytemerchant = $merchantName;  
        $singleMerchantCount = $count;    
    }
}

// If no merchant is connected, default to first
if (empty($bbbytemerchant)) {
    $bbbytemerchant = array_key_first($merchantTables);  
    $singleMerchantCount = $merchantDetails[$bbbytemerchant];
}

echo "🎯 Single Connected merchants: $singleMerchantCount&nbsp;&nbsp;&nbsp;🎯 Total Connected merchants: $totalMerchantCount";
?>



<div class="page-content fade-in-up">
    <div class="ibox">
        <div class="ibox-body">
            <div class="row">
                <?php while($row = $query->fetch_assoc()){
                    $hitLimit = $row["hitLimit"];
                    if ($hitLimit === 0) {
                        $hitLimit = "Unlimited";
                    }
                    $planamount = $row["amount"];
                    $planid = $row["id"];
                    $balance = $userdata["balance"];
                    $display = ($balance > 0) ? "flex" : "none";
                    $expiry = $row["expiry"];
                ?>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-header bg-dark text-white">
                            <?php if ($planid == $activePlan) { ?>
                                <span class="badge badge-success active-plan-badge">Active</span>
                            <?php } ?>
                            <h4 class="card-title"><?= htmlspecialchars($row["plan_name"]) ?></h4>
                            <h2>
                                <span class="currency-symbol">₹</span>
                                <span id="base-amount-<?= $planid ?>" class="amount-value"><?= number_format($planamount) ?></span>
                                <span class="currency-code">INR</span>
                            </h2>
                            <p><i class="bi bi-calendar-check"></i> Validity: <span class="validity-period"><?= htmlspecialchars($row["expiry"]) ?> Month</span></p>
                        </div>
                        <div class="card-body">
                            <ul class="list-group text-start feature-list">
                                <li class="list-group-item d-flex align-items-center">
                                    <span class="feature-icon text-success">✅</span>
                                    <span class="feature-text">Hit Limit: <strong class="feature-value"><?= $hitLimit?></strong></span>
                                </li>
                                <li class="list-group-item d-flex align-items-center">
                                    <span class="feature-icon text-success">✅</span>
                                    <span class="feature-text">Same Merchant Limit: <strong class="feature-value"><?= $row["singleMerchantLimit"] ?></strong></span>
                                </li>
                                <li class="list-group-item d-flex align-items-center">
                                    <span class="feature-icon text-success">✅</span>
                                    <span class="feature-text">Total Merchant Limit: <strong class="feature-value"><?= $row["totalMerchantLimit"] ?></strong></span>
                                </li>
                                <li class="list-group-item d-flex align-items-center">
                                    <span class="feature-icon text-success">✅</span>
                                    <span class="feature-text">QR Code, UPI Intent, UPI Requests</span>
                                </li>
                                <li class="list-group-item d-flex align-items-center">
                                    <span class="feature-icon text-primary">♻️</span>
                                    <span class="feature-text">Merchant Routing</span>
                                </li>
                            </ul>
                        </div>
                        <div class="card-footer">
<form method="POST" action="lib/pay" class="paymentForm" data-planid="<?= $planid ?>">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>" />
    <input type="hidden" name="planid" value="<?= $planid ?>" />
    <input type="hidden" name="for" value="PlanRenew" />
    <input type="hidden" name="wallet_used" class="wallet-used-input" data-planid="<?= $planid ?>" value="0" />

    <div class="form-group custom-checkbox-group d-flex align-items-start gap-2 mb-4">
        <input type="checkbox" name="vip_sub" class="vip_sub custom-checkbox" id="vip_sub_<?= $planid ?>" data-planid="<?= $planid ?>" value="yes" />
        <label for="vip_sub_<?= $planid ?>" class="custom-checkbox-label">
            VIP Subscription (No UpiGateway Branding)
        </label>
    </div>

    <div class="custom-checkbox-group d-flex align-items-start gap-2 my-4 wallet-checkbox-group" <?php if ($display == 'none') echo 'style="display: none !important;"'; ?>>
        <input type="checkbox" class="use_wallet custom-checkbox" id="use_wallet_<?= $planid ?>" data-planid="<?= $planid ?>" name="use_wallet" value="yes" />
        <label for="use_wallet_<?= $planid ?>" class="custom-checkbox-label">
            Use Wallet Balance
            <span class="text-success font-weight-bold">₹ <?= number_format($balance, 2) ?></span>
        </label>
    </div>
    <input type="hidden" name="amount" class="final-amount" data-planid="<?= $planid ?>" value="<?= $planamount ?>"/>

    <div class="payment-summary" data-planid="<?= $planid ?>">
        <div class="summary-header">
            <p class="summary-title">Payment Summary</p>
        </div>
        <div class="summary-item">
            <i class="bi bi-tag summary-icon text-secondary"></i>
            <p class="summary-text">
                Plan Price: 
                <span class="summary-value plan-price" data-planid="<?= $planid ?>">₹<?= number_format($planamount) ?></span>
            </p>
        </div>
        <div class="summary-item">
            <i class="bi bi-star summary-icon text-warning"></i>
            <p class="summary-text">
                VIP Subscription: 
                <span class="summary-value vip-price" data-planid="<?= $planid ?>">₹0</span>
            </p>
        </div>
        <div class="summary-item">
            <i class="bi bi-wallet summary-icon text-success"></i>
            <p class="summary-text">
                Wallet Used: 
                <span class="summary-value text-success wallet-used" data-planid="<?= $planid ?>">₹0</span>
            </p>
        </div>
        <hr class="summary-divider">
        <div class="summary-item">
            <i class="bi bi-currency-rupee summary-icon text-primary"></i>
            <p class="summary-text font-weight-bold">
                <strong>Payable Amount:</strong> 
                <span class="summary-value text-primary font-weight-bold payable" data-planid="<?= $planid ?>">₹<?= number_format($planamount) ?></span>
            </p>
        </div>
    </div>
<?php
// Check if either singleMerchantCount or totalMerchantCount exceeds the plan's limits
$hideButton = ($totalMerchantCount > $row["totalMerchantLimit"] || $singleMerchantCount > $row["singleMerchantLimit"]) ? "style='display: none;'" : "";
?>
    <button type="submit" class="btn btn-purchase" <?= $hideButton ?>>
        <i class="bi bi-credit-card-fill me-2"></i>Buy Now
    </button>
<?php if ($totalMerchantCount > $row["totalMerchantLimit"]) { ?>
    <div class="warning-message">
        <i class="bi bi-exclamation-triangle-fill warning-icon"></i>
        <p>You have connected more total merchants than allowed in this plan. Please delete extra merchants to downgrade.</p>
    </div>
<?php } ?>
<?php if ($singleMerchantCount > $row["singleMerchantLimit"]) { ?>
    <div class="warning-message">
        <i class="bi bi-exclamation-triangle-fill warning-icon"></i>
        <p>You have connected more single merchants than allowed in this plan. Please delete extra merchants to downgrade.</p>
    </div>
<?php } ?>
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
<script>
$(document).ready(function () {
    // Store plan data in JavaScript
    const plans = {
        <?php 
        $query->data_seek(0); // Reset query pointer
        while($row = $query->fetch_assoc()) {
            echo "'{$row["id"]}': {
                baseAmount: {$row["amount"]},
                months: {$row["expiry"]}
            },";
        }
        ?>
    };

    const walletBalance = <?= $balance ?>;

    // Assuming $vip_plan is defined in PHP and contains the monthly VIP cost
    const vipMonthlyCost = <?= isset($vip_plan) ? $vip_plan : 99 ?>; // Default to 99 if $vip_plan is not set

    function updateSummary(planId) {
        const plan = plans[planId];
        const vipChecked = $('.vip_sub[data-planid="' + planId + '"]').is(':checked');
        const walletChecked = $('.use_wallet[data-planid="' + planId + '"]').is(':checked');
        
        // Calculate VIP cost
        let vipCost = 0;
        if (vipChecked) {
            vipCost = plan.months <= 1 ? vipMonthlyCost : (vipMonthlyCost * 10); // Monthly: $vip_plan, Yearly: $vip_plan * 10
        }

        // Total amount before wallet
        let totalAmount = plan.baseAmount + vipCost;
        
        // Calculate wallet usage and payable amount
        let walletUsed = 0;
        let payableAmount = totalAmount;
        
        if (walletChecked && walletBalance > 0) {
            walletUsed = Math.min(walletBalance, totalAmount);
            payableAmount = totalAmount - walletUsed;
        }

        // Update summary display
        $('.plan-price[data-planid="' + planId + '"]').text(plan.baseAmount.toLocaleString('en-IN'));
        $('.vip-price[data-planid="' + planId + '"]').text(vipCost.toLocaleString('en-IN'));
        $('.wallet-used[data-planid="' + planId + '"]').text(walletUsed.toLocaleString('en-IN'));
        $('.payable[data-planid="' + planId + '"]').text(payableAmount.toLocaleString('en-IN'));
        $('.final-amount[data-planid="' + planId + '"]').val(payableAmount);
        
        // Update the hidden wallet_used input
        $('.wallet-used-input[data-planid="' + planId + '"]').val(walletUsed);
    }

    // Event listeners
    $('.vip_sub').change(function() {
        const planId = $(this).data('planid');
        updateSummary(planId);
    });

    $('.use_wallet').change(function() {
        const planId = $(this).data('planid');
        updateSummary(planId);
    });

    // Initial update for all plans
    Object.keys(plans).forEach(planId => {
        updateSummary(planId);
    });

    // Handle form submission using a jQuery event listener
    $('.paymentForm').on('submit', function(event) {
        // Prevent the default form submission
        event.preventDefault();

        // Get the form element that triggered the event
        const form = event.target;
        
        // Create the data to be sent in the POST request using FormData
        const formData = new FormData(form);

        // Open a loading dialog while the server processes the request
        openDialog('../pages/loading.php', 'Processing...');

        // Use fetch() to send the POST request
        fetch(form.action, {
            method: 'POST',
            body: formData
        })
        .then(response => {
            // Check if the response is successful (status code 200)
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json(); // Parse the JSON response
        })
        .then(data => {
            // Check if the server response indicates success and has the payment URL
            if (data.status === true && data.result && data.result.payment_url) {
                // Open the dialog with the URL from the response
                openDialog(data.result.payment_url, 'UpiGateway Payment');
            } else {
                // Handle cases where the status is false or the URL is missing
                const errorMessage = data.message || 'Unknown error occurred.';
                document.getElementById('linkDialogLoading').innerHTML = `<div style="color: #e53e3e;">❌ Error: ${errorMessage}</div>`;
            }
        })
        .catch(error => {
            // Handle network errors or issues with the fetch request
            console.error('There was a problem with the fetch operation:', error);
            document.getElementById('linkDialogLoading').innerHTML = `<div style="color: #e53e3e;">❌ Error: Network problem or server error.</div>`;
        });
    });
});
</script>