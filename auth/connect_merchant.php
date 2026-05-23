<?php
// --- Session & Authentication ---
include "header.php";
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($userdata['user_token'])) {
    echo "<script>window.location.href='login.php';</script>";
    exit;
}

// Since OTP system is removed, user is considered verified and can access the page
// You can put the rest of your merchant connect settings page code below

?>

<div class="container-fluid mt-4">
    <div class="alert alert-info shadow-sm" role="alert">
        <h5 class="alert-heading mb-2">
            <i class="fas fa-info-circle me-2"></i> Merchant Connect Settings
        </h5>
        <p class="mb-0">
            Here you can manage your Merchant Connect settings verification.
        </p>
    </div>

    <!-- Your Merchant Connect settings content goes here -->

</div>

<!-- Bootstrap JS and Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>




            <!-- START PAGE CONTENT-->
            <div class="page-heading">
                <h1 class="page-title">Merchant Connect Setting</h1>
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
                        
                         <!--<div class="row"> -->
                             <!--<div class="col-md-4"> -->
                                <div class="card m-t-20 m-b-20">
                                    <div class="card-body">
                                    <div class="main-panel">
				<div class="content">
					<div class="container-fluid">
					    
						 <!--<h4 class="page-title">UPI APP Settings</h4> -->
						<div class="row row-card-no-pd">
							<div class="col-md-12">

<?php

class MerchantManager {
    private $conn;
    private $userToken;
    private $userData;
    private $merchantTables = [];
    
    public function __construct($conn, $userData) {
        $this->conn = $conn;
        $this->userData = $userData;
        $this->userToken = $userData['user_token'];
        $this->loadMerchantConfiguration();
    }
    
    /**
     * Load merchant configuration dynamically from database
     */
    private function loadMerchantConfiguration() {
        $query = "SELECT merchantname, tablename, foldername, user_update_column, status FROM merchants WHERE status = 1 ORDER BY id";
        $result = mysqli_query($this->conn, $query);
        
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $merchantName = $row['merchantname'];
                $tableName = $row['tablename'];
                $folderName = $row['foldername'];
                $userColumn = $row['user_update_column'];
                
                // Determine phone column based on table name
                $phoneColumn = $this->getPhoneColumnName($tableName);
                
                // Build configuration
                $config = [
                    'table' => $tableName,
                    'phone_column' => $phoneColumn,
                    'user_column' => $userColumn,
                    'mobile_field' => $merchantName . '_mobile',
                    'folder_name' => $folderName
                ];
                
                // Add special configurations for specific merchants
                $config = $this->addSpecialConfigurations($merchantName, $tableName, $config);
                
                $this->merchantTables[$merchantName] = $config;
            }
        }
        
        // Fallback to default configuration if database is empty
        if (empty($this->merchantTables)) {
            $this->loadDefaultConfiguration();
        }
    }
    
    /**
     * Determine phone column name based on table name
     */
    private function getPhoneColumnName($tableName) {
        // Special cases
        $specialCases = [
            'hdfc' => 'number'
        ];
        
        return $specialCases[$tableName] ?? 'phoneNumber';
    }
    
    /**
     * Add special configurations for specific merchants
     */
    private function addSpecialConfigurations($merchantName, $tableName, $config) {
        // Special configurations for different merchants
        $specialConfigs = [
            'hdfc' => [
                'insert_query' => "INSERT INTO {$tableName} (user_token, number, status) VALUES (?, ?, 'Deactive')",
                'requires_user_id' => false
            ],
            'phonepe' => [
                'insert_query' => "INSERT INTO {$tableName} (user_token, phoneNumber) VALUES (?, ?)",
                'extra_delete' => "DELETE FROM store_id WHERE user_token = ?",
                'requires_user_id' => false
            ],
            'paytm' => [
                'insert_query' => "INSERT INTO {$tableName} (user_token, phoneNumber) VALUES (?, ?)",
                'requires_user_id' => false
            ],
            'bharatpe' => [
                'insert_query' => "INSERT INTO {$tableName} (user_token, phoneNumber) VALUES (?, ?)",
                'requires_user_id' => false
            ],
            'googlepay' => [
                'insert_query' => "INSERT INTO {$tableName} (user_token, phoneNumber) VALUES (?, ?)",
                'requires_user_id' => false
            ]
        ];
        
        // Default configuration for merchants requiring user_id
        $defaultWithUserId = [
            'insert_query' => "INSERT INTO {$tableName} (user_token, phoneNumber, user_id) VALUES (?, ?, ?)",
            'requires_user_id' => true
        ];
        
        // Apply special configuration or default
        if (isset($specialConfigs[$merchantName])) {
            $config = array_merge($config, $specialConfigs[$merchantName]);
        } else {
            $config = array_merge($config, $defaultWithUserId);
        }
        
        return $config;
    }
    
    /**
     * Fallback configuration if database is not available
     */
    private function loadDefaultConfiguration() {
        $this->merchantTables = [
            'hdfc' => [
                'table' => 'hdfc',
                'phone_column' => 'number',
                'user_column' => 'hdfc_connected',
                'mobile_field' => 'hdfc_mobile',
                'insert_query' => "INSERT INTO hdfc (user_token, number, status) VALUES (?, ?, 'Deactive')"
            ],
            'phonepe' => [
                'table' => 'phonepe_tokens',
                'phone_column' => 'phoneNumber',
                'user_column' => 'phonepe_connected',
                'insert_query' => "INSERT INTO phonepe_tokens (user_token, phoneNumber) VALUES (?, ?)",
                'extra_delete' => "DELETE FROM store_id WHERE user_token = ?"
            ],
            'paytm' => [
                'table' => 'paytm_tokens',
                'phone_column' => 'phoneNumber',
                'user_column' => 'paytm_connected',
                'mobile_field' => 'paytm_mobile',
                'insert_query' => "INSERT INTO paytm_tokens (user_token, phoneNumber) VALUES (?, ?)"
            ],
            'bharatpe' => [
                'table' => 'bharatpe_tokens',
                'phone_column' => 'phoneNumber',
                'user_column' => 'bharatpe_connected',
                'mobile_field' => 'bharatpe_mobile',
                'insert_query' => "INSERT INTO bharatpe_tokens (user_token, phoneNumber) VALUES (?, ?)"
            ],
            'googlepay' => [
                'table' => 'googlepay_tokens',
                'phone_column' => 'phoneNumber',
                'user_column' => 'googlepay_connected',
                'insert_query' => "INSERT INTO googlepay_tokens (user_token, phoneNumber) VALUES (?, ?)"
            ],
            'sbi' => [
                'table' => 'sbi_token',
                'phone_column' => 'phoneNumber',
                'user_column' => 'sbi_connected',
                'insert_query' => "INSERT INTO sbi_token (user_token, phoneNumber, user_id) VALUES (?, ?, ?)",
                'requires_user_id' => true
            ],
            'freecharge' => [
                'table' => 'freecharge_token',
                'phone_column' => 'phoneNumber',
                'user_column' => 'freecharge_connected',
                'mobile_field' => 'freecharge_mobile',
                'insert_query' => "INSERT INTO freecharge_token (user_token, phoneNumber, user_id) VALUES (?, ?, ?)",
                'requires_user_id' => true
            ],
            'mobikwik' => [
                'table' => 'mobikwik_token',
                'phone_column' => 'phoneNumber',
                'user_column' => 'mobikwik_connected',
                'mobile_field' => 'mobikwik_mobile',
                'insert_query' => "INSERT INTO mobikwik_token (user_token, phoneNumber, user_id) VALUES (?, ?, ?)",
                'requires_user_id' => true
            ],
            'manual' => [
                'table' => 'manual_token',
                'phone_column' => 'phoneNumber',
                'user_column' => 'manual_connected',
                'mobile_field' => 'manual_mobile',
                'insert_query' => "INSERT INTO manual_token (user_token, phoneNumber, user_id) VALUES (?, ?, ?)",
                'requires_user_id' => true
            ],
            'amazon' => [
                'table' => 'amazon_token',
                'phone_column' => 'phoneNumber',
                'user_column' => 'amazon_connected',
                'mobile_field' => 'amazon_mobile',
                'insert_query' => "INSERT INTO amazon_token (user_token, phoneNumber, user_id) VALUES (?, ?, ?)",
                'requires_user_id' => true
            ],
            'quintuspay' => [
                'table' => 'quintuspay_token',
                'phone_column' => 'phoneNumber',
                'user_column' => 'quintuspay_connected',
                'mobile_field' => 'quintuspay_mobile',
                'insert_query' => "INSERT INTO quintuspay_token (user_token, phoneNumber, user_id) VALUES (?, ?, ?)",
                'requires_user_id' => true
            ]
        ];
    }
    
    /**
     * Get or update routing status
     */
    public function handleRouting() {
        $routingStatus = $this->getRoutingStatus();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['routing_start'])) {
            $routingChoice = $_POST['routing_start'];
            $routingStatus = $this->updateRoutingStatus($routingChoice);
        }
        
        return $routingStatus;
    }
    
    private function getRoutingStatus() {
        $stmt = mysqli_prepare($this->conn, "SELECT merchentRouting FROM users WHERE user_token = ?");
        mysqli_stmt_bind_param($stmt, "s", $this->userToken);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            return $row['merchentRouting'];
        }
        return 0;
    }
    
    private function updateRoutingStatus($status) {
        $stmt = mysqli_prepare($this->conn, "UPDATE users SET merchentRouting = ? WHERE user_token = ?");
        mysqli_stmt_bind_param($stmt, "ss", $status, $this->userToken);
        mysqli_stmt_execute($stmt);
        return $status;
    }
    
    /**
     * Get subscription limits
     */
    public function getSubscriptionLimits() {
        $planId = $this->userData['planId'];
        $stmt = mysqli_prepare($this->conn, "SELECT singleMerchantLimit, totalMerchantLimit FROM subscription_plan WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $planId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            return [
                'total' => $row['totalMerchantLimit'],
                'single' => $row['singleMerchantLimit']
            ];
        }
        return ['total' => 0, 'single' => 0];
    }
    
    /**
     * Get merchant statistics
     */
    public function getMerchantStats() {
        $merchantDetails = [];
        $totalCount = 0;
        
        foreach ($this->merchantTables as $name => $config) {
            $stmt = mysqli_prepare($this->conn, "SELECT COUNT(*) as total FROM {$config['table']} WHERE user_token = ?");
            mysqli_stmt_bind_param($stmt, "s", $this->userToken);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $count = mysqli_fetch_assoc($result)['total'];
            
            $merchantDetails[$name] = $count;
            $totalCount += $count;
        }
        
        return [
            'details' => $merchantDetails,
            'total' => $totalCount,
            'primary' => $this->getPrimaryMerchant($merchantDetails)
        ];
    }
    
    private function getPrimaryMerchant($merchantDetails) {
        $primaryMerchant = '';
        $maxCount = 0;
        
        foreach ($merchantDetails as $name => $count) {
            if ($count > $maxCount) {
                $primaryMerchant = $name;
                $maxCount = $count;
            }
        }
        
        return $primaryMerchant ?: array_key_first($this->merchantTables);
    }
    
    /**
     * Update merchant status
     */
    public function updateMerchantStatus($merchantType, $merchantNumber, $newStatus) {
        if (!isset($this->merchantTables[$merchantType])) {
            return $this->jsonResponse(false, 'Invalid merchant type');
        }
        
        $config = $this->merchantTables[$merchantType];
        $phoneColumn = $config['phone_column'];
        
        $stmt = mysqli_prepare($this->conn, "UPDATE {$config['table']} SET status = ? WHERE {$phoneColumn} = ? AND user_token = ?");
        mysqli_stmt_bind_param($stmt, "sss", $newStatus, $merchantNumber, $this->userToken);
        $result = mysqli_stmt_execute($stmt);
        
        if ($result) {
            $this->updateUserConnection($merchantType);
            return $this->jsonResponse(true, 'Status updated successfully');
        }
        
        return $this->jsonResponse(false, 'Failed to update status');
    }
    
    private function updateUserConnection($merchantType) {
        $config = $this->merchantTables[$merchantType];
        $activeCount = $this->getActiveCount($config['table']);
        $connectionStatus = $activeCount > 0 ? 'Yes' : 'No';
        
        $stmt = mysqli_prepare($this->conn, "UPDATE users SET {$config['user_column']} = ? WHERE user_token = ?");
        mysqli_stmt_bind_param($stmt, "ss", $connectionStatus, $this->userToken);
        mysqli_stmt_execute($stmt);
    }
    
    private function getActiveCount($table) {
        $stmt = mysqli_prepare($this->conn, "SELECT COUNT(*) as count FROM {$table} WHERE status = 'Active' AND user_token = ?");
        mysqli_stmt_bind_param($stmt, "s", $this->userToken);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        return mysqli_fetch_assoc($result)['count'];
    }
    
    /**
     * Add new merchant
     */
    public function addMerchant($merchantType, $phoneNumber, $userId = null) {
        // Check if plan is expired
        if ($this->isPlanExpired()) {
            return $this->alertResponse('error', 'Plan Expired!', 
                'Your plan expired on: ' . date('d M, Y', strtotime($this->userData['expiry'])) . '. Please renew your plan to add a Merchant.', 
                'subscription');
        }
        if ($merchantType=='googlepay') {
        if (!$this->isKYC()) {
            return $this->alertResponse('error', 'KYC Required!', 
                'Please complete your KYC', 
                'profile');
        }
        }
        
        // Check limits
        $limits = $this->getSubscriptionLimits();
        $stats = $this->getMerchantStats();
        
        if ($stats['total'] >= $limits['total']) {
            return $this->alertResponse('error', 'Limit Exceeded!', 
                "You can only add up to {$limits['total']} merchants.", 
                'connect_merchant',true);
        }
        
        $singleCount = $stats['details'][$merchantType] ?? 0;
        if ($singleCount >= $limits['single']) {
            return $this->alertResponse('error', 'Limit Exceeded!', 
                "You can only add up to {$limits['single']} merchants of the same type.", 
                'connect_merchant',true);
        }
        
        // Insert merchant
        if ($this->insertMerchant($merchantType, $phoneNumber, $userId)) {
            return $this->alertResponse('success', 'Success!', 
                'Merchant added successfully.', 
                'connect_merchant');
        }
        
        return $this->alertResponse('error', 'Error!', 
            'Failed to add merchant.', 
            'dashboard');
    }
    
    private function isPlanExpired() {
        $expiry = strtotime($this->userData['expiry']);
        $today = strtotime(date('Y-m-d'));
        return $expiry < $today;
    }
    private function isKYC() {
        $kyc = $this->userData['kycstatus'];
        echo($kyc);
        return $kyc == 'Verified';
    }
    
    private function insertMerchant($merchantType, $phoneNumber, $userId) {
        if (!isset($this->merchantTables[$merchantType])) {
            return false;
        }
        
        $config = $this->merchantTables[$merchantType];
        $stmt = mysqli_prepare($this->conn, $config['insert_query']);
        
        if ($config['requires_user_id'] ?? false) {
            mysqli_stmt_bind_param($stmt, "sss", $this->userToken, $phoneNumber, $userId);
        } else {
            mysqli_stmt_bind_param($stmt, "ss", $this->userToken, $phoneNumber);
        }
        
        return mysqli_stmt_execute($stmt);
    }
    
    /**
     * Delete merchant
     */
    public function deleteMerchant($merchantType, $phoneNumber = null) {
        if (!isset($this->merchantTables[$merchantType])) {
            return $this->alertResponse('error', 'Invalid merchant type', '', 'connect_merchant');
        }
        
        $config = $this->merchantTables[$merchantType];
        $result = $this->deleteMerchantRecord($config, $phoneNumber);
        
        if ($result) {
            // Update user connection status
            $this->updateUserConnection($merchantType);
            
            // Handle extra deletions
            if (isset($config['extra_delete'])) {
                $stmt = mysqli_prepare($this->conn, $config['extra_delete']);
                mysqli_stmt_bind_param($stmt, "s", $this->userToken);
                mysqli_stmt_execute($stmt);
            }
            
            return $this->alertResponse('success', 'Success!', 
                'Merchant deleted successfully.', 
                'connect_merchant');
        }
        
        return $this->alertResponse('error', 'Error!', 
            'Merchant not deleted! Contact Admin.', 
            'connect_merchant');
    }
    
    private function deleteMerchantRecord($config, $phoneNumber) {
        if ($phoneNumber && isset($config['phone_column'])) {
            $stmt = mysqli_prepare($this->conn, "DELETE FROM {$config['table']} WHERE user_token = ? AND {$config['phone_column']} = ?");
            mysqli_stmt_bind_param($stmt, "ss", $this->userToken, $phoneNumber);
        } else {
            $stmt = mysqli_prepare($this->conn, "DELETE FROM {$config['table']} WHERE user_token = ?");
            mysqli_stmt_bind_param($stmt, "s", $this->userToken);
        }
        
        return mysqli_stmt_execute($stmt);
    }
    
    /**
     * Handle form submissions
     */
    public function handleRequests() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }
        
        if (isset($_POST['routing_start'])) {
            $this->handleRouting();
        } elseif (isset($_POST['status']) && isset($_POST['merchant_type'])) {
            $this->updateMerchantStatus($_POST['merchant_type'], $_POST['merchant_number'], $_POST['status']);
        } elseif (isset($_POST['addmerchant'])) {
            $this->addMerchant($_POST['merchant_name'], $_POST['c_mobile'], $_SESSION['user_id'] ?? null);
        } elseif (isset($_POST['delete'])) {
            $merchantType = $_POST['merchant_type'];
            $phoneNumber = null;
            
            if (isset($this->merchantTables[$merchantType]['mobile_field'])) {
                $field = $this->merchantTables[$merchantType]['mobile_field'];
                $phoneNumber = $_POST[$field] ?? null;
            }
            
            $this->deleteMerchant($merchantType, $phoneNumber);
        }
    }
    
    /**
     * Get available merchant types from database
     */
    public function getAvailableMerchants() {
        return array_keys($this->merchantTables);
    }
    
    /**
     * Get merchant configuration by name
     */
    public function getMerchantConfig($merchantName) {
        return $this->merchantTables[$merchantName] ?? null;
    }
    
    /**
     * Add new merchant type to database
     */
    public function addMerchantType($merchantName, $tableName, $folderName, $userColumn, $phoneColumn = 'phoneNumber') {
        $stmt = mysqli_prepare($this->conn, 
            "INSERT INTO merchants (merchantname, tablename, foldername, user_update_column, status) VALUES (?, ?, ?, ?, 1)"
        );
        mysqli_stmt_bind_param($stmt, "ssss", $merchantName, $tableName, $folderName, $userColumn);
        $result = mysqli_stmt_execute($stmt);
        
        if ($result) {
            // Reload configuration to include new merchant
            $this->loadMerchantConfiguration();
        }
        
        return $result;
    }
    
    /**
     * Update merchant status in database
     */
    public function updateMerchantTypeStatus($merchantName, $status) {
        $stmt = mysqli_prepare($this->conn, "UPDATE merchants SET status = ? WHERE merchantname = ?");
        mysqli_stmt_bind_param($stmt, "is", $status, $merchantName);
        $result = mysqli_stmt_execute($stmt);
        
        if ($result) {
            // Reload configuration
            $this->loadMerchantConfiguration();
        }
        
        return $result;
    }
    
    /**
     * Get merchant statistics for specific merchant type
     */
    public function getMerchantTypeStats($merchantType) {
        if (!isset($this->merchantTables[$merchantType])) {
            return null;
        }
        
        $config = $this->merchantTables[$merchantType];
        $stmt = mysqli_prepare($this->conn, "SELECT COUNT(*) as total FROM {$config['table']} WHERE user_token = ?");
        mysqli_stmt_bind_param($stmt, "s", $this->userToken);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        return mysqli_fetch_assoc($result)['total'];
    }
    
    /**
     * Refresh merchant configuration from database
     */
    public function refreshConfiguration() {
        $this->loadMerchantConfiguration();
        return true;
    }
    
    /**
     * Utility methods
     */
    private function jsonResponse($success, $message, $data = null) {
        return json_encode([
            'success' => $success,
            'message' => $message,
            'data' => $data
        ]);
    }
    
private function alertResponse($type, $title, $text, $redirect, $planButton = false) {
    echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';
    echo "<script>
            Swal.fire({
                icon: '$type',
                title: '$title',
                text: '$text',";

    if ($planButton) {
        echo "showCancelButton: true,
              confirmButtonText: 'Change/Activate Plan',
              cancelButtonText: 'OK',
              reverseButtons: true"; // Optional: Swaps the position of confirm and cancel buttons
    } else {
        echo "confirmButtonText: 'OK'";
    }

    echo "
            }).then((result) => {";
    
    if ($planButton) {
        echo "if (result.isConfirmed) {
                    window.location.href='/auth/subscription'; // Redirect if 'Change/Activate Plan' is clicked
                } else {
                    // Do nothing or handle cancellation if 'OK' is clicked
                }";
    } else {
        echo "window.location.href='$redirect';";
    }

    echo "
            });
        </script>";
}
}

// Usage
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Initialize the merchant manager
$merchantManager = new MerchantManager($conn, $userdata);

// Handle all requests
$merchantManager->handleRequests();

// Get routing status
$routingStatus = $merchantManager->handleRouting();

// Get subscription limits
$limits = $merchantManager->getSubscriptionLimits();

// Get merchant statistics
$merchantStats = $merchantManager->getMerchantStats();

// Extract variables for backward compatibility
$totalMerchantLimit = $limits['total'];
$singleMerchantLimit = $limits['single'];
$totalMerchantCount = $merchantStats['total'];
$merchantDetails = $merchantStats['details'];
$bbbytemerchant = $merchantStats['primary'];
$singleMerchantCount = $merchantDetails[$bbbytemerchant] ?? 0;

//phonepe upi id pudate
if (isset($_POST['upi_id'])&&isset($_POST['merchant_number'])) {

    $upi_id = $_POST['upi_id'];
    $phoneNumber = $_POST['merchant_number'];
    $user_token = $_POST['user_token'];
    db_update($conn,"phonepe_tokens",["merchant_upi" => "$upi_id"],"phoneNumber='$phoneNumber' AND user_token='$user_token'"
);

} 

?>
</div>
<div class="alert alert-warning shadow mt-3">
    <h5 class="fw-bold text-danger">⚠️ How Do Merchant Limits Work?</h5>
    <ul>
        <li>You can connect up to <strong><?php echo $totalMerchantLimit; ?></strong> different merchants.</li>
        <li>You can connect up to <strong><?php echo $singleMerchantLimit; ?></strong> accounts of the same merchant type.</li>
        <li>If you reach your limit, you need to remove old merchants or <a href="subscription" class="text-primary fw-bold">Upgrade Your Plan</a>.</li>
        <li>With multiple merchants connected, you can now use them <strong>randomly</strong>! This helps spread the load across all accounts, so no single account gets overwhelmed.</li>
    </ul>
<style>
.toggle-switch {
    position: relative;
    display: inline-block;
    width: 50px;
    height: 24px;
}

.toggle-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #808080; /* Inactive (Deactive) color */
    transition: 0.3s;
    border-radius: 24px;
}

.slider:before {
    position: absolute;
    content: "";
    height: 20px;
    width: 20px;
    left: 2px;
    bottom: 2px;
    background-color: white;
    transition: 0.3s;
    border-radius: 50%;
}

input:checked + .slider {
    background-color: #28a745; /* Active color */
}

input:checked + .slider:before {
    transform: translateX(26px);
}
</style>    
    
    
</div>
<!-- Total Merchants Allowed Card -->
<div class="col-lg-6 col-md-6 col-sm-12 mb-3">
    <div class="card shadow-sm border-0 rounded-3 h-100">
        <div class="card-body p-3">
            <h6 class="fw-bold text-primary mb-2">Total Merchants</h6>
            <p class="small text-muted mb-2">Max: <strong><?php echo $totalMerchantLimit; ?></strong></p>
            <p class="small fw-bold mb-1">Connected:</p>
            <ul class="list-group list-group-flush small">
                <?php foreach ($merchantDetails as $merchant => $count) {
                    if ($count > 0) {
                        echo "<li class='list-group-item py-1 px-0 d-flex justify-content-between'>
                                $merchant <span class='badge bg-primary rounded-pill'>$count</span>
                              </li>";
                    }
                } ?>
            </ul>
            <div class="progress mt-2" style="height: 15px;">
                <div class="progress-bar bg-primary" role="progressbar"
                    style="width: <?php echo ($totalMerchantCount > 0) ? min(($totalMerchantCount / $totalMerchantLimit) * 100, 100) : 0; ?>%;"
                    aria-valuenow="<?php echo $totalMerchantCount; ?>" 
                    aria-valuemin="0" 
                    aria-valuemax="<?php echo $totalMerchantLimit; ?>">
                    <?php echo min($totalMerchantCount, $totalMerchantLimit) . "/" . $totalMerchantLimit; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Single Merchant Allowed Card -->
        <div class="col-lg-6 col-md-6 col-sm-12 mb-3">
            <div class="card shadow-sm border-0 rounded-3 h-100">
                <div class="card-body p-3">
                    <h6 class="fw-bold text-success mb-2">Single Merchant</h6>
                    <p class="small text-muted mb-2">Max: <strong><?php echo $singleMerchantLimit; ?></strong></p>
<?php foreach ($merchantDetails as $merchantName => $count): ?>
    <?php if ($count > 0): ?>

                    <p class="small fw-bold mb-1">Selected: <?php echo $merchantName; ?></p>
                    <div class="progress mt-2" style="height: 15px;">
                        <div class="progress-bar bg-success" role="progressbar"
                             style="width: <?php echo ($count > 0) ? min(($count / $singleMerchantLimit) * 100, 100) : 0; ?>%;"
                             aria-valuenow="<?php echo $count; ?>" 
                             aria-valuemin="0" 
                             aria-valuemax="<?php echo $singleMerchantLimit; ?>">
                             <?php echo min($count, $singleMerchantLimit) . "/" . $singleMerchantLimit; ?>
                        </div>
                    </div>
    <?php endif; ?>
<?php endforeach; ?>
                </div>
            </div>
        </div>

<!-- Routing Toggle -->
<div class="col-12 mb-3">
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-body p-3">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <label for="routing_start" class="fw-bold text-dark mb-0">Start Routing?</label>
                <form method="POST" action="" class="d-flex align-items-center">
                    <select class="form-select form-select-sm me-2" id="routing_start" name="routing_start" style="width: 100px;" required>
                        <option value="1" <?= $routingStatus == 1 ? 'selected' : '' ?>>Yes</option>
                        <option value="0" <?= $routingStatus == 0 ? 'selected' : '' ?>>No</option>
                    </select>
                    <button type="submit" class="btn btn-sm btn-primary">Apply</button>
                </form>
                        <!-- वर्तमान स्टेटस दिखाने वाला सेक्शन -->
            <div class="alert <?= $routingStatus == 1 ? 'alert-success' : 'alert-secondary' ?> small mb-0">
                Current Status: <strong><?= $routingStatus == 1 ? 'Routing Active' : 'Routing Inactive' ?></strong>
            </div>
            </div>

        </div>
    </div>
</div>
</div>
   <style>
        .container { display: flex; gap: 20px; }
        .form-container { flex: 1; }
        .iframe-container { flex: 1; }
        iframe { width: 100%; height: 300px; border: 1px solid #ccc; }
    </style>

    <div class="container mt-4">
        <div class="form-container">
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="mb-2">
                <div id="merchant">
                    <div class="form-field">
                        <label for="merchant_select">Merchant Name</label>
                    <select name="merchant_name" id="merchant_select" class="form-control" onchange="updateIframe()">
                        <?php
                        $sql = "SELECT id, merchantname, tablename,DisplayName FROM merchants WHERE status = 1 ORDER BY id ASC";
                        $result = $conn->query($sql);

                        if ($result && $result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                // Use tablename for the option value as it matches your database structure for dynamic queries later
                                $value = htmlspecialchars($row['merchantname'], ENT_QUOTES, 'UTF-8');
                                // Use merchantname for display
                                $displayText = htmlspecialchars($row['DisplayName'], ENT_QUOTES, 'UTF-8');
                                echo '<option value="' . $value . '">' . $displayText . '</option>';
                            }
                        } else {
                            echo '<option value="">No merchants available</option>';
                        }
                        // Free result set
                        if ($result) {
                            $result->free();
                        }
                        ?>
                    </select>
                    </div>
                    <div class="form-field"> 
                        <label for="c_mobile">Cashier Mobile Number</label> 
                        <input type="number" name="c_mobile" id="c_mobile" placeholder="Enter Mobile Number" class="form-control" onkeypress="if(this.value.length==10) return false;" required> 
                    </div>
                    <div class="form-field"> 
                        <button type="submit" name="addmerchant" class="btn btn-primary btn-block">Add Merchant</button> 
                    </div>
                </div>
            </form>
        </div>
        <div class="iframe-container">
            <iframe id="merchant_iframe" src="hdfc.html"></iframe>
        </div>
    </div>

<script>
    function updateIframe() {
        const select = document.getElementById('merchant_select');
        const iframe = document.getElementById('merchant_iframe');
        const selectedValue = select.value;

        if (selectedValue) {
            iframe.src = 'connectHelp/' + selectedValue + '.php'; // Add .html if needed
        }
    }
    document.getElementById('merchant_select').addEventListener('change', updateIframe);

    window.addEventListener('DOMContentLoaded', () => {
        updateIframe(); // Load default iframe on page load
    });
</script>

<?php
// --- Configuration and Data Retrieval ---
// Define the base query for fetching merchant data.
// Using a function or a more structured approach for UNION ALL queries can be beneficial for larger applications,
// but for this specific snippet, a well-formatted string is acceptable.
function getMerchantFetchQuery(): string
{
    return "
        SELECT 'hdfc' AS merchant_type, id, failCount, number, upi_hdfc AS upi_id, date, COALESCE(status, 'Deactive') AS status FROM hdfc WHERE user_token = ?
        UNION ALL
        SELECT 'phonepe' AS merchant_type, sl AS id, failCount, phoneNumber AS number, merchant_upi AS upi_id, date, COALESCE(status, 'Deactive') AS status FROM phonepe_tokens WHERE user_token = ?
        UNION ALL
        SELECT 'paytm' AS merchant_type, id, failCount, phoneNumber AS number, Upiid AS upi_id, date, COALESCE(status, 'Deactive') AS status FROM paytm_tokens WHERE user_token = ?
        UNION ALL
        SELECT 'bharatpe' AS merchant_type, id, failCount, phoneNumber AS number, Upiid AS upi_id, date, COALESCE(status, 'Deactive') AS status FROM bharatpe_tokens WHERE user_token = ?
        UNION ALL
        SELECT 'googlepay' AS merchant_type, id, failCount, phoneNumber AS number, Upiid AS upi_id, date, COALESCE(status, 'Deactive') AS status FROM googlepay_tokens WHERE user_token = ?
        UNION ALL
        SELECT 'freecharge' AS merchant_type, id, failCount, phoneNumber AS number, Upiid AS upi_id, date, COALESCE(status, 'Deactive') AS status FROM freecharge_token WHERE user_token = ?
        UNION ALL
        SELECT 'sbi' AS merchant_type, id, failCount, phoneNumber AS number, merchant_upi AS upi_id, date, COALESCE(status, 'Deactive') AS status FROM sbi_token WHERE user_token = ?
        UNION ALL
        SELECT 'mobikwik' AS merchant_type, id, failCount, phoneNumber AS number, merchant_upi AS upi_id, date, COALESCE(status, 'Deactive') AS status FROM mobikwik_token WHERE user_token = ?
        UNION ALL
        SELECT 'manual' AS merchant_type, id, failCount, phoneNumber AS number, upi_id, date, COALESCE(status, 'Deactive') AS status FROM manual_token WHERE user_token = ?
        UNION ALL
        SELECT 'amazon' AS merchant_type, id, failCount, phoneNumber AS number, upi_id, date, COALESCE(status, 'Deactive') AS status FROM amazon_token WHERE user_token = ?
        UNION ALL
        SELECT 'paynearby' AS merchant_type, id, failCount, phoneNumber AS number, upi_id, date, COALESCE(status, 'Deactive') AS status FROM paynearby_token WHERE user_token = ?
        UNION ALL
        SELECT 'quintuspay' AS merchant_type, id, failCount, phoneNumber AS number, upi_id, date, COALESCE(status, 'Deactive') AS status FROM quintuspay_token WHERE user_token = ?
    ";
}

$cxrrrrtoken = $userdata['user_token'] ?? '';
$fetchDataQuery = getMerchantFetchQuery();

// Prepare and verify the statement
$stmt = $conn->prepare($fetchDataQuery);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

// Bind parameters dynamically
$paramTypes = str_repeat('s', 12); // 11 placeholders
$params = array_fill(0, 12, $cxrrrrtoken);

// Use reflection for dynamic parameter binding
$bindNames = [$paramTypes];
foreach ($params as $key => $value) {
    $bindNames[] = &$params[$key]; // Must pass by reference
}

call_user_func_array([$stmt, 'bind_param'], $bindNames);

// Execute and get results
$stmt->execute();
$ssData = $stmt->get_result();

// Define mappings for easier management
$formActions = [
    'hdfc'       => ['action' => 'send_hdfcotp', 'input_name' => 'hdfc_mobile'],
    'phonepe'    => ['action' => 'send_phonepeotp', 'input_name' => 'phonepe_mobile'],
    'paytm'      => ['action' => 'send_paytmotp', 'input_name' => 'paytm_mobile'],
    'bharatpe'   => ['action' => 'send_bharatpeotp', 'input_name' => 'bharatpe_mobile'],
    'googlepay'  => ['action' => 'send_googlepayotp', 'input_name' => 'googlepay_mobile'],
    'freecharge' => ['action' => 'send_freechargeotp', 'input_name' => 'freecharge_mobile'],
    'sbi'        => ['action' => 'send_sbiotp', 'input_name' => 'sbi_mobile'],
    'mobikwik'   => ['action' => 'send_mobikwikotp', 'input_name' => 'mobikwik_mobile'],
    'manual'     => ['action' => 'manualpay', 'input_name' => 'manual_mobile'],
    'amazon'     => ['action' => 'amazonverify', 'input_name' => 'amazon_mobile'],
    'paynearby'     => ['action' => 'paynearbyverify', 'input_name' => 'paynearby_mobile'],
    'bajaj'     => ['action' => 'bajajverify', 'input_name' => 'bajaj_mobile'],
    'quintuspay'     => ['action' => 'send_quintuspayotp', 'input_name' => 'quintuspay_mobile'],
];

$csrfRequiredTypes = ['mobikwik', 'manual', 'amazon']; // Merchant types requiring CSRF token

// --- HTML Structure ---
?>

<div class="table-responsive">
    <h5>All Merchants</h5>
    <table class="table table-sm table-hover table-bordered table-head-bg-primary" id="dataTable" width="100%">
        <thead>
            <tr>
                <th>#</th>
                <th>Merchant Type</th>
                <th>User ID</th>
                <th>Last Sync</th>
                <th>UPI ID</th>
                <th>Verify</th>
                <th>Status</th>
                <th>Action</th>
                <th>Performance</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($ssData && $ssData->num_rows > 0) {
                $counter = 1;
                while ($merchant = $ssData->fetch_assoc()) {
                    // Sanitize and prepare data for display
                    $merchantType = htmlspecialchars($merchant['merchant_type'] ?? '', ENT_QUOTES, 'UTF-8');
                    $merchantNumber = htmlspecialchars($merchant['number'] ?? '', ENT_QUOTES, 'UTF-8');
                    $lastSyncDate = htmlspecialchars($merchant['date'] ?? '', ENT_QUOTES, 'UTF-8');
                    $upiId = htmlspecialchars($merchant['upi_id'] ?? '', ENT_QUOTES, 'UTF-8');
                    $status = htmlspecialchars($merchant['status'] ?? 'Deactive', ENT_QUOTES, 'UTF-8');
                    $failCount = (int)($merchant['failCount'] ?? 0);

                    // Determine performance
                    $performancePercentage = 100 - (max(0, min(15, $failCount)) * (100 / 15));
                    $performancePercentage = round($performancePercentage);
                    $performanceIcon = '';
                    $performanceLabel = '';
                    $performanceClass = '';

                    if ($failCount === 0) {
                        $performanceClass = 'best';
                        $performanceLabel = 'Excellent';
                        $performanceIcon = '✔️';
                    } elseif ($failCount <= 4) {
                        $performanceClass = 'good';
                        $performanceLabel = 'Good';
                        $performanceIcon = '👍';
                    } elseif ($failCount <= 10) {
                        $performanceClass = 'average';
                        $performanceLabel = 'Average';
                        $performanceIcon = '⚠️';
                    } else {
                        $performanceClass = 'poor';
                        $performanceLabel = 'Poor';
                        $performanceIcon = '❌';
                    }
            ?>
                    <tr>
                        <td><?php echo $counter++; ?></td>
                        <td><?php echo strtoupper($merchantType); ?></td>
                        <td><?php echo $merchantNumber; ?></td>
                        <td><button type="button" class="btn ripple btn-primary px-2"><?php echo $lastSyncDate; ?></button></td>
                        <td><?php echo $upiId; ?></td>
                        <td>
                            <?php
                            if (array_key_exists($merchantType, $formActions)) {
                                $action = htmlspecialchars($formActions[$merchantType]['action']);
                                $inputName = htmlspecialchars($formActions[$merchantType]['input_name']);
                            ?>
                                <form action="<?php echo $action; ?>" method="post">
                                    <input type="hidden" name="<?php echo $inputName; ?>" value="<?php echo $merchantNumber; ?>">
                                    <input type="hidden" name="user_token" value="<?php echo $cxrrrrtoken; ?>">
                                    <?php if (in_array($merchantType, $csrfRequiredTypes)) : ?>
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                                    <?php endif; ?>
                                    <button class="btn ripple btn-primary px-2" name="Verify">Verify</button>
                                </form>
                            <?php
                            }
                            ?>
                        </td>
                        <td>
                            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" id="statusForm_<?php echo $merchant['id']; ?>" class="status-form">
                                <input type="hidden" name="merchant_type" value="<?php echo $merchantType; ?>">
                                <input type="hidden" name="merchant_id" value="<?php echo htmlspecialchars($merchant['id']); ?>">
                                <input type="hidden" name="merchant_number" value="<?php echo $merchantNumber; ?>">
                                <input type="hidden" name="status" id="statusInput_<?php echo $merchant['id']; ?>">
                                <?php
                                $displayStatus = ($status === 'Active' || $status === 'Deactive') ? $status : '';
                                $checked = $displayStatus === 'Active' ? 'checked' : '';
                                ?>
                                <label class="toggle-switch">
                                    <input type="checkbox" class="status-toggle" data-merchant-type="<?php echo $merchantType; ?>" data-merchant-number="<?php echo $merchantNumber; ?>" data-form-id="statusForm_<?php echo $merchant['id']; ?>" data-current-status="<?php echo $displayStatus; ?>" <?php echo $checked; ?>>
                                    <span class="slider"></span>
                                </label>
                            </form>
                        </td>
                        <td>
                            <?php
                            $allowedMerchantTypes = ['hdfc', 'phonepe', 'paytm', 'bharatpe', 'googlepay', 'freecharge', 'sbi', 'mobikwik', 'manual', 'amazon','paynearby','bajaj','quintuspay'];

                            if (in_array(strtolower($merchantType), $allowedMerchantTypes)) {
                            ?>
                                <div style="display: flex; gap: 10px; align-items: center;">
                                    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" style="margin: 0;">
                                        <input type="hidden" name="<?php echo strtolower($merchantType); ?>_mobile" value="<?php echo $merchantNumber; ?>">
                                        <input type="hidden" name="merchant_type" value="<?php echo $merchantType; ?>">
                                        <button class="btn ripple btn-danger px-2" name="delete">Delete</button>
                                    </form>
                                    <?php if ($merchantType === 'phonepe') {?>
                                        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                                            <input type="text" name="upi_id" placeholder="Enter UPI ID (e.g., yourname@bank)" required>
                                            <input type="hidden" name="merchant_number" value="<?php echo $merchantNumber; ?>">
                                            <input type="hidden" name="user_token" value="<?php echo htmlspecialchars($cxrrrrtoken); ?>">
                                            <button type="submit" class="btn btn-sm btn-info">Update UPI ID</button>
                                        </form>
                                        <?php
                                        // $message variable needs to be handled globally or passed if this logic is moved.
                                        if (isset($_SESSION['phonepe_message'])) { // Assuming you'd store the message in a session or similar
                                            echo '<div class="message">' . $_SESSION['phonepe_message'] . '</div>';
                                            unset($_SESSION['phonepe_message']); // Clear the message after displaying
                                        }
                                        ?>
                                    <?php } ?>
                                    <?php if ($merchantType === 'hdfc') { ?>
                                        <a href="/auth/costom_checkout" class="btn btn-info">Disable Upi Requests</a>
                                    <?php } ?>
                                </div>
                            <?php } ?>
                        </td>
                        <td class="performance-cell">
                            <div class="performance-indicator <?php echo $performanceClass; ?>">
                                <span class="icon"><?php echo $performanceIcon; ?></span>
                                <span class="value"><?php echo $performancePercentage . "%"; ?></span>
                                <span class="label"><?php echo $performanceLabel; ?></span>
                            </div>
                        </td>
                    </tr>
            <?php
                }
            } else {
                echo '<tr><td colspan="9">No merchants found.</td></tr>';
            }
            ?>
        </tbody>
    </table>
</div>

<?php
// Close the statement and connection
$stmt->close();
// $conn->close(); // Close connection if it's not needed elsewhere after this script
?>
</div>
</div>

					</div>
				</div>
</div>
</div>
</div>
<!-- Move this JavaScript code OUTSIDE the loop, just before the closing </body> tag -->
<!-- Replace the existing JavaScript in the loop with this, and ensure it appears only ONCE -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
function showInvalidPasswordAlert(id) {
    alert('Invalid Password for merchant with ID: ' + id);
}
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Status toggle script loaded'); // Debug: Confirm script runs

    // Block any jQuery form submissions to prevent conflicts
    if (typeof jQuery !== 'undefined') {
        jQuery('form.status-form').off('submit'); // Remove any existing submit handlers
        console.log('jQuery detected, blocked form submit handlers');
    }

    document.querySelectorAll('.status-toggle').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault(); // Prevent any default behavior
            e.stopPropagation(); // Stop event propagation
            console.log('Button clicked, current status:', this.getAttribute('data-current-status')); // Debug: Log click

            const currentStatus = this.getAttribute('data-current-status');
            const newStatus = currentStatus === 'Active' ? 'Deactive' : (currentStatus === 'Deactive' ? 'Active' : currentStatus);
            const merchantType = this.getAttribute('data-merchant-type');
            const merchantNumber = this.getAttribute('data-merchant-number');
            const formId = this.getAttribute('data-form-id');
            const form = document.getElementById(formId);
            const statusInput = form.querySelector(`#statusInput_${formId.split('_')[1]}`);

            console.log('Current status:', currentStatus, 'Proposed new status:', newStatus); // Debug: Log status change

            // Handle Deactive to Active
            if (currentStatus === 'Deactive' && newStatus === 'Active') {
                console.log('Showing SweetAlert for verification'); // Debug: Confirm prompt trigger
                Swal.fire({
                    title: 'Verify Merchant?',
                    text: 'Do you want to verify the merchant again? Activating without verification may cause transaction issues. Ignore If Verified Alrady Before',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Verify',
                    cancelButtonText: 'No, JustActivate',
                    allowOutsideClick: false,
                    allowEscapeKey: false
                }).then((result) => {
                    console.log('SweetAlert result:', result); // Debug: Log user choice
                    if (result.isConfirmed) {
                        // Map merchant type to verification URL
                        const verifyUrls = {
                            'hdfc': 'send_hdfcotp',
                            'phonepe': 'send_phonepeotp',
                            'paytm': 'send_paytmotp',
                            'bharatpe': 'send_bharatpeotp',
                            'googlepay': 'send_googlepayotp',
                            'freecharge': 'send_freechargeotp',
                            'sbi': 'send_sbiotp',
                            'mobikwik': 'send_mobikwikotp',
                            'manual': 'manualpay'
                        };

                        const verifyUrl = verifyUrls[merchantType] || '';
                        if (verifyUrl) {
                            console.log('Submitting verification form to:', verifyUrl); // Debug: Log verification URL
                            // Create a temporary form for verification
                            const verifyForm = document.createElement('form');
                            verifyForm.method = 'POST';
                            verifyForm.action = verifyUrl;

                            // Add mobile number input
                            const mobileInput = document.createElement('input');
                            mobileInput.type = 'hidden';
                            mobileInput.name = merchantType + '_mobile';
                            mobileInput.value = merchantNumber;
                            verifyForm.appendChild(mobileInput);

                            // Add CSRF token for mobikwik and manual
                            if (merchantType === 'mobikwik' || merchantType === 'manual') {
                                const csrfInput = document.createElement('input');
                                csrfInput.type = 'hidden';
                                csrfInput.name = 'csrf_token';
                                csrfInput.value = '<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>';
                                verifyForm.appendChild(csrfInput);
                            }

                            // Add Verify button input
                            const verifyInput = document.createElement('input');
                            verifyInput.type = 'hidden';
                            verifyInput.name = 'Verify';
                            verifyInput.value = 'Verify';
                            verifyForm.appendChild(verifyInput);

                            document.body.appendChild(verifyForm);
                            verifyForm.submit();
                        } else {
                            console.log('Verification URL not found for:', merchantType); // Debug: Log error
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: 'Verification not available for this merchant type.',
                                confirmButtonText: 'OK'
                            });
                        }
                    } else if (result.dismiss === Swal.DismissReason.cancel) {
                        console.log('Activating without verification'); // Debug: Log activation
                        // Update the status input and submit
                        statusInput.value = 'Active';
                        form.submit();
                    }
                });
            }
            // Handle Active to Deactive
            else if (currentStatus === 'Active' && newStatus === 'Deactive') {
                console.log('Showing SweetAlert for deactivation'); // Debug: Confirm prompt trigger
                Swal.fire({
                    title: 'Deactivate Merchant?',
                    text: 'Are you sure you want to deactivate this merchant? This may affect ongoing transactions.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Deactivate',
                    cancelButtonText: 'No, Cancel',
                    allowOutsideClick: false,
                    allowEscapeKey: false
                }).then((result) => {
                    console.log('SweetAlert result:', result); // Debug: Log user choice
                    if (result.isConfirmed) {
                        console.log('Deactivating merchant'); // Debug: Log deactivation
                        // Update the status input and submit
                        statusInput.value = 'Deactive';
                        form.submit();
                    } else {
                        console.log('Deactivation cancelled'); // Debug: Log cancellation
                    }
                });
            }
            // Handle blank or invalid status (no change)
            else {
                console.log('No status change needed:', currentStatus); // Debug: Log no action
            }
        });
    });
});
</script>

</body>
</html>