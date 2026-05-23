<?php
include "header.php"; // This should contain your database connection ($conn) and potentially user data ($userdata)
if ($userdata['role']=="Admin") {

$redirect = "<script>window.location.href = window.location.href;</script>";

// --- CSRF Token Generation ---
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// --- Flash Message Display ---
if (isset($_SESSION['message'])) {
    $messageType = $_SESSION['message']['type'] ?? 'info';
    $messageText = $_SESSION['message']['text'] ?? '';
    echo '<div class="alert alert-' . htmlspecialchars($messageType) . ' alert-dismissible fade show" role="alert">';
    echo htmlspecialchars($messageText);
    echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
    echo '</div>';
    unset($_SESSION['message']); // Clear the message after displaying
}

// --- Backend Logic for Actions (Status Update, Delete, UPI Update) ---

// Helper function for setting messages
function setFlashMessage(string $type, string $text) {
    $_SESSION['message'] = ['type' => $type, 'text' => $text];
}

// Handle Status Updates
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['status']) && isset($_POST['merchant_id']) && isset($_POST['merchant_type'])) {
    // CSRF Check for status update
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        setFlashMessage('danger', 'CSRF token mismatch. Action blocked.');
        echo $redirect;
        exit();
    }

    $merchantType = $_POST['merchant_type'];
    $merchantId = $_POST['merchant_id'];
    $newStatus = $_POST['status']; // 'Active' or 'Deactive'

    // Basic validation
    if (!in_array($newStatus, ['Active', 'Deactive'])) {
        setFlashMessage('danger', 'Invalid status provided.');
        echo $redirect;
        exit();
    }

    $tableName = '';
    $idColumn = 'id'; // Default ID column name
    switch (strtolower($merchantType)) { // Use strtolower for case-insensitive matching
        case 'hdfc':       $tableName = 'hdfc'; break;
        case 'phonepe':    $tableName = 'phonepe_tokens'; $idColumn = 'sl'; break;
        case 'paytm':      $tableName = 'paytm_tokens'; break;
        case 'bharatpe':   $tableName = 'bharatpe_tokens'; break;
        case 'googlepay':  $tableName = 'googlepay_tokens'; break;
        case 'freecharge': $tableName = 'freecharge_token'; break;
        case 'sbi':        $tableName = 'sbi_token'; break;
        case 'mobikwik':   $tableName = 'mobikwik_token'; break;
        case 'manual':     $tableName = 'manual_token'; break;
        case 'amazon':     $tableName = 'amazon_token'; break;
        case 'paynearby':  $tableName = 'paynearby_token'; break;
        case 'quintuspay': $tableName = 'quintuspay_token'; break;
        // case 'bajaj':      $tableName = 'bajaj_token'; break;
        default:
            setFlashMessage('danger', 'Unknown merchant type for status update.');
            echo $redirect;
            exit();
    }

    if ($tableName) {
        $updateSql = "UPDATE $tableName SET status = ? WHERE $idColumn = ?";
        $stmt = $conn->prepare($updateSql);
        if ($stmt) {
            $stmt->bind_param("si", $newStatus, $merchantId); // Assuming ID is integer
            if ($stmt->execute()) {
                setFlashMessage('success', strtoupper($merchantType) . ' status updated to ' . $newStatus . '.');
            } else {
                setFlashMessage('danger', 'Error updating status: ' . $stmt->error);
            }
            $stmt->close();
        } else {
            setFlashMessage('danger', 'Failed to prepare status update statement: ' . $conn->error);
        }
    }
    echo $redirect;
    exit();
}

// Handle Delete Merchant
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete'])) {
    // CSRF Check for delete
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        setFlashMessage('danger', 'CSRF token mismatch. Action blocked.');
        echo $redirect;
        exit();
    }

    $merchantType = $_POST['merchant_type'];
    $merchantNumber = ''; // Will store the phone number based on merchant type

    // Determine the correct phone number input name
    if (isset($formActions[strtolower($merchantType)])) {
        $inputName = $formActions[strtolower($merchantType)]['input_name'];
        $merchantNumber = $_POST[$inputName] ?? '';
    }

    if (empty($merchantNumber)) {
        setFlashMessage('danger', 'Merchant number not provided for delete.');
        echo $redirect;
        exit();
    }

    $tableName = '';
    $numberColumn = 'number'; // Default column name for phone number
    switch (strtolower($merchantType)) {
        case 'hdfc':       $tableName = 'hdfc'; break;
        case 'phonepe':    $tableName = 'phonepe_tokens'; $numberColumn = 'phoneNumber'; break;
        case 'paytm':      $tableName = 'paytm_tokens'; $numberColumn = 'phoneNumber'; break;
        case 'bharatpe':   $tableName = 'bharatpe_tokens'; $numberColumn = 'phoneNumber'; break;
        case 'googlepay':  $tableName = 'googlepay_tokens'; $numberColumn = 'phoneNumber'; break;
        case 'freecharge': $tableName = 'freecharge_token'; $numberColumn = 'phoneNumber'; break;
        case 'sbi':        $tableName = 'sbi_token'; $numberColumn = 'phoneNumber'; break;
        case 'mobikwik':   $tableName = 'mobikwik_token'; $numberColumn = 'phoneNumber'; break;
        case 'manual':     $tableName = 'manual_token'; $numberColumn = 'phoneNumber'; break;
        case 'amazon':     $tableName = 'amazon_token'; $numberColumn = 'phoneNumber'; break;
        case 'paynearby':  $tableName = 'paynearby_token'; $numberColumn = 'phoneNumber'; break;
        case 'bajaj':      $tableName = 'bajaj_token'; $numberColumn = 'phoneNumber'; break;
        case 'quintuspay': $tableName = 'quintuspay_token'; $numberColumn = 'phoneNumber'; break;
        default:
            setFlashMessage('danger', 'Invalid merchant type for deletion.');
            echo $redirect;
            exit();
    }

    if ($tableName) {
        $deleteSql = "DELETE FROM $tableName WHERE $numberColumn = ?";
        $stmt = $conn->prepare($deleteSql);
        if ($stmt) {
            $stmt->bind_param("s", $merchantNumber); // Assuming phone number is string
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    setFlashMessage('success', strtoupper($merchantType) . ' merchant with number ' . htmlspecialchars($merchantNumber) . ' deleted successfully.');
                } else {
                    setFlashMessage('warning', strtoupper($merchantType) . ' merchant with number ' . htmlspecialchars($merchantNumber) . ' not found or already deleted.');
                }
            } else {
                setFlashMessage('danger', 'Error deleting merchant: ' . $stmt->error);
            }
            $stmt->close();
        } else {
            setFlashMessage('danger', 'Failed to prepare delete statement: ' . $conn->error);
        }
    }
    echo $redirect;
    exit();
}

// Handle PhonePe UPI ID Update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_phonepe_upi']) && isset($_POST['upi_id']) && isset($_POST['merchant_number']) && isset($_POST['user_token'])) {
    // CSRF Check for UPI update
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        setFlashMessage('danger', 'CSRF token mismatch. Action blocked.');
        echo $redirect;
        exit();
    }

    $newUpiId = trim($_POST['upi_id']);
    $merchantNumber = $_POST['merchant_number'];
    $userToken = $_POST['user_token']; // This is the merchant's user_token, not for authentication here.

    // Basic validation for UPI ID (you might want a more robust regex)
    if (empty($newUpiId) || (!filter_var($newUpiId, FILTER_VALIDATE_EMAIL) && !preg_match('/^[a-zA-Z0-9.\-]+@[a-zA-Z0-9.\-]+$/', $newUpiId))) {
         setFlashMessage('danger', 'Invalid UPI ID format.');
         echo $redirect;
         exit();
    }

    $updateSql = "UPDATE phonepe_tokens SET merchant_upi = ? WHERE phoneNumber = ? AND user_token='$userToken'";
    $stmt = $conn->prepare($updateSql);
    if ($stmt) {
        $stmt->bind_param("ss", $newUpiId, $merchantNumber);
        if ($stmt->execute()) {
            setFlashMessage('success', 'PhonePe UPI ID updated successfully for ' . htmlspecialchars($merchantNumber) . '!');
        } else {
            setFlashMessage('danger', 'Error updating PhonePe UPI ID: ' . $stmt->error);
        }
        $stmt->close();
    } else {
        setFlashMessage('danger', 'Failed to prepare UPI update statement: ' . $conn->error);
    }
    echo $redirect;
    exit();
}

// --- Pagination Settings ---
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = isset($_GET['limit']) && in_array($_GET['limit'], [10, 25, 50, 100]) ? intval($_GET['limit']) : 25;
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$statusFilter = isset($_GET['status']) ? $_GET['status'] : '';
$merchantTypeFilter = isset($_GET['merchant_type']) ? $_GET['merchant_type'] : '';

// --- Configuration and Data Retrieval ---
// Define the base query for fetching merchant data.
function getMerchantFetchQuery(): string
{
    return "
        SELECT 'hdfc' AS merchant_type, id, failCount, number, upi_hdfc AS upi_id, date, COALESCE(status, 'Deactive') AS status FROM hdfc 
        UNION ALL
        SELECT 'phonepe' AS merchant_type, sl AS id, failCount, phoneNumber AS number, merchant_upi AS upi_id, date, COALESCE(status, 'Deactive') AS status FROM phonepe_tokens 
        UNION ALL
        SELECT 'paytm' AS merchant_type, id, failCount, phoneNumber AS number, Upiid AS upi_id, date, COALESCE(status, 'Deactive') AS status FROM paytm_tokens 
        UNION ALL
        SELECT 'bharatpe' AS merchant_type, id, failCount, phoneNumber AS number, Upiid AS upi_id, date, COALESCE(status, 'Deactive') AS status FROM bharatpe_tokens 
        UNION ALL
        SELECT 'googlepay' AS merchant_type, id, failCount, phoneNumber AS number, NULL AS upi_id, date, COALESCE(status, 'Deactive') AS status FROM googlepay_tokens 
        UNION ALL
        SELECT 'freecharge' AS merchant_type, id, failCount, phoneNumber AS number, NULL AS upi_id, date, COALESCE(status, 'Deactive') AS status FROM freecharge_token 
        UNION ALL
        SELECT 'sbi' AS merchant_type, id, failCount, phoneNumber AS number, NULL AS upi_id, date, COALESCE(status, 'Deactive') AS status FROM sbi_token 
        UNION ALL
        SELECT 'mobikwik' AS merchant_type, id, failCount, phoneNumber AS number, merchant_upi AS upi_id, date, COALESCE(status, 'Deactive') AS status FROM mobikwik_token 
        UNION ALL
        SELECT 'manual' AS merchant_type, id, failCount, phoneNumber AS number, upi_id, date, COALESCE(status, 'Deactive') AS status FROM manual_token 
        UNION ALL
        SELECT 'amazon' AS merchant_type, id, failCount, phoneNumber AS number, upi_id, date, COALESCE(status, 'Deactive') AS status FROM amazon_token 
        UNION ALL
        SELECT 'paynearby' AS merchant_type, id, failCount, phoneNumber AS number, upi_id, date, COALESCE(status, 'Deactive') AS status FROM paynearby_token 
        UNION ALL
        SELECT 'quintuspay' AS merchant_type, id, failCount, phoneNumber AS number, upi_id, date, COALESCE(status, 'Deactive') AS status FROM quintuspay_token 
    ";
}

// Get all data first
$fetchDataQuery = getMerchantFetchQuery();

// Add WHERE conditions for filtering
$whereConditions = [];
if (!empty($search)) {
    $whereConditions[] = "(merchant_type LIKE '%$search%' OR number LIKE '%$search%' OR upi_id LIKE '%$search%')";
}
if (!empty($statusFilter)) {
    $whereConditions[] = "status = '$statusFilter'";
}
if (!empty($merchantTypeFilter)) {
    $whereConditions[] = "UPPER(merchant_type) = UPPER('$merchantTypeFilter')";
}

$whereClause = !empty($whereConditions) ? ' WHERE ' . implode(' AND ', $whereConditions) : '';
$finalQuery = "SELECT * FROM ($fetchDataQuery) as merchants $whereClause ORDER BY id DESC";

// Get total count for pagination
$countQuery = "SELECT COUNT(*) as total FROM ($finalQuery) as counted_merchants";
$countResult = $conn->query($countQuery);
$totalCount = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalCount / $limit);

// Get paginated data
$paginatedQuery = "$finalQuery LIMIT $limit OFFSET $offset";
$stmt = $conn->prepare($paginatedQuery);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

// Execute and get results
$stmt->execute();
$ssData = $stmt->get_result();

// Store all data in array for JavaScript (only current page)
$allMerchantsData = [];
$merchantTypeCounts = [];
$activeMerchantTypeCounts = [];

// Get summary data (all records for summary, not just current page)
$summaryQuery = "SELECT * FROM ($fetchDataQuery) as merchants $whereClause";
$summaryResult = $conn->query($summaryQuery);
while ($row = $summaryResult->fetch_assoc()) {
    $normalizedMerchantType = strtoupper($row['merchant_type']);
    $merchantTypeCounts[$normalizedMerchantType] = ($merchantTypeCounts[$normalizedMerchantType] ?? 0) + 1;
    if ($row['status'] === 'Active') {
        $activeMerchantTypeCounts[$normalizedMerchantType] = ($activeMerchantTypeCounts[$normalizedMerchantType] ?? 0) + 1;
    }
}

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
    'paynearby'  => ['action' => 'paynearbyverify', 'input_name' => 'paynearby_mobile'],
    'bajaj'      => ['action' => 'bajajverify', 'input_name' => 'bajaj_mobile'],
    'quintuspay' => ['action' => 'quintuspayverify', 'input_name' => 'quintuspay_mobile'],
];

$csrfRequiredTypes = ['mobikwik', 'manual', 'amazon']; // Merchant types requiring CSRF token for verification (handled by external script)

// --- HTML Structure ---
?>

<style>
/* ... (Your existing CSS for toggle switch and performance indicator) ... */
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

.performance-cell {
    text-align: center;
}

.performance-indicator {
    display: inline-flex;
    /*flex-direction: column;*/
    align-items: center;
    padding: 2px;
    border-radius: 8px;
    font-weight: bold;
    color: white; /* Default text color */
    min-width: 120px;
}

.performance-indicator .icon {
    font-size: 1.2em;
}

.performance-indicator .value {
    font-size: 0.9em;
}

.performance-indicator .label {
    font-size: 0.7em;
    text-transform: uppercase;
}

.performance-indicator.best {
    background-color: #28a745; /* Green */
}

.performance-indicator.good {
    background-color: #17a2b8; /* Info blue */
}

.performance-indicator.average {
    background-color: #ffc107; /* Yellow */
    color: #333; /* Darker text for readability */
}

.performance-indicator.poor {
    background-color: #dc3545; /* Red */
}

.pagination-wrapper {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 20px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 5px;
}

.pagination-info {
    font-size: 14px;
    color: #6c757d;
}

.page-size-selector {
    display: flex;
    align-items: center;
    gap: 10px;
}
</style>

<!-- Filter and Search Section -->
<div class="row mb-3">
    <div class="col-md-4">
        <form method="GET" class="d-flex">
            <input type="text" name="search" class="form-control" placeholder="Search by User ID, UPI ID, etc." value="<?php echo htmlspecialchars($search); ?>">
            <button class="btn btn-primary ms-1" type="submit">Search</button>
            <?php if (!empty($search) || !empty($statusFilter) || !empty($merchantTypeFilter)): ?>
                <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="btn btn-secondary ms-1">Reset</a>
            <?php endif; ?>
            <!-- Hidden inputs to preserve pagination -->
            <input type="hidden" name="status" value="<?php echo htmlspecialchars($statusFilter); ?>">
            <input type="hidden" name="merchant_type" value="<?php echo htmlspecialchars($merchantTypeFilter); ?>">
            <input type="hidden" name="limit" value="<?php echo $limit; ?>">
        </form>
    </div>
    <div class="col-md-3">
        <form method="GET" class="d-flex">
            <select name="status" class="form-select" onchange="this.form.submit()">
                <option value="">All Statuses</option>
                <option value="Active" <?php echo $statusFilter === 'Active' ? 'selected' : ''; ?>>Active</option>
                <option value="Deactive" <?php echo $statusFilter === 'Deactive' ? 'selected' : ''; ?>>Deactive</option>
            </select>
            <!-- Preserve other filters -->
            <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
            <input type="hidden" name="merchant_type" value="<?php echo htmlspecialchars($merchantTypeFilter); ?>">
            <input type="hidden" name="limit" value="<?php echo $limit; ?>">
            <input type="hidden" name="page" value="1"> <!-- Reset to page 1 when filtering -->
        </form>
    </div>
    <div class="col-md-3">
        <form method="GET" class="d-flex">
            <select name="merchant_type" class="form-select" onchange="this.form.submit()">
                <option value="">All Merchants</option>
                <option value="HDFC" <?php echo $merchantTypeFilter === 'HDFC' ? 'selected' : ''; ?>>HDFC</option>
                <option value="PHONEPE" <?php echo $merchantTypeFilter === 'PHONEPE' ? 'selected' : ''; ?>>PHONEPE</option>
                <option value="PAYTM" <?php echo $merchantTypeFilter === 'PAYTM' ? 'selected' : ''; ?>>PAYTM</option>
                <option value="BHARATPE" <?php echo $merchantTypeFilter === 'BHARATPE' ? 'selected' : ''; ?>>BHARATPE</option>
                <option value="GOOGLEPAY" <?php echo $merchantTypeFilter === 'GOOGLEPAY' ? 'selected' : ''; ?>>GOOGLEPAY</option>
                <option value="FREECHARGE" <?php echo $merchantTypeFilter === 'FREECHARGE' ? 'selected' : ''; ?>>FREECHARGE</option>
                <option value="SBI" <?php echo $merchantTypeFilter === 'SBI' ? 'selected' : ''; ?>>SBI</option>
                <option value="MOBIKWIK" <?php echo $merchantTypeFilter === 'MOBIKWIK' ? 'selected' : ''; ?>>MOBIKWIK</option>
                <option value="MANUAL" <?php echo $merchantTypeFilter === 'MANUAL' ? 'selected' : ''; ?>>MANUAL</option>
                <option value="AMAZON" <?php echo $merchantTypeFilter === 'AMAZON' ? 'selected' : ''; ?>>AMAZON</option>
                <option value="PAYNEARBY" <?php echo $merchantTypeFilter === 'PAYNEARBY' ? 'selected' : ''; ?>>PAYNEARBY</option>
                <option value="QUINTUSPAY" <?php echo $merchantTypeFilter === 'QUINTUSPAY' ? 'selected' : ''; ?>>QUINTUSPAY</option>
            </select>
            <!-- Preserve other filters -->
            <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
            <input type="hidden" name="status" value="<?php echo htmlspecialchars($statusFilter); ?>">
            <input type="hidden" name="limit" value="<?php echo $limit; ?>">
            <input type="hidden" name="page" value="1"> <!-- Reset to page 1 when filtering -->
        </form>
    </div>
    <div class="col-md-2">
        <form method="GET" class="d-flex">
            <select name="limit" class="form-select" onchange="this.form.submit()">
                <option value="10" <?php echo $limit === 10 ? 'selected' : ''; ?>>Show 10</option>
                <option value="25" <?php echo $limit === 25 ? 'selected' : ''; ?>>Show 25</option>
                <option value="50" <?php echo $limit === 50 ? 'selected' : ''; ?>>Show 50</option>
                <option value="100" <?php echo $limit === 100 ? 'selected' : ''; ?>>Show 100</option>
            </select>
            <!-- Preserve other filters -->
            <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
            <input type="hidden" name="status" value="<?php echo htmlspecialchars($statusFilter); ?>">
            <input type="hidden" name="merchant_type" value="<?php echo htmlspecialchars($merchantTypeFilter); ?>">
            <input type="hidden" name="page" value="1"> <!-- Reset to page 1 when changing limit -->
        </form>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Merchant Summary Overview (Total: <?php echo $totalCount; ?>)</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-10 d-flex flex-wrap gap-1 justify-content-center">
                        <?php
                        foreach ($merchantTypeCounts as $type => $total) {
                            $active = $activeMerchantTypeCounts[$type] ?? 0;
                            echo '<div class="card text-center mb-2" style="width: 12rem;">';
                            echo '<div class="card-body">';
                            echo '<h6 class="card-title text-primary">' . $type . '</h6>';
                            echo '<p class="card-text mb-1">Total: <span class="badge bg-info">' . $total . '</span></p>';
                            echo '<p class="card-text">Active: <span class="badge bg-success">' . $active . '</span></p>';
                            echo '</div>';
                            echo '</div>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="table-responsive">
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
                $counter = $offset + 1; // Start counter from offset
                
                // Reset $ssData pointer to the beginning
                $ssData->data_seek(0);

                while ($merchant = $ssData->fetch_assoc()) {
                    // Sanitize and prepare data for display
                    $merchantType = htmlspecialchars($merchant['merchant_type'] ?? '', ENT_QUOTES, 'UTF-8');
                    $merchantNumber = htmlspecialchars($merchant['number'] ?? '', ENT_QUOTES, 'UTF-8');
                    $lastSyncDate = htmlspecialchars($merchant['date'] ?? '', ENT_QUOTES, 'UTF-8');
                    $upiId = htmlspecialchars($merchant['upi_id'] ?? '', ENT_QUOTES, 'UTF-8');
                    $status = htmlspecialchars($merchant['status'] ?? 'Deactive', ENT_QUOTES, 'UTF-8');
                    $failCount = (int)($merchant['failCount'] ?? 0);

                    // Store data for JavaScript (current page only)
                    $allMerchantsData[] = [
                        'merchant_type' => $merchantType,
                        'number' => $merchantNumber,
                        'upi_id' => $upiId,
                        'status' => $status,
                    ];

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
                            if (array_key_exists(strtolower($merchantType), $formActions)) {
                                $action = htmlspecialchars($formActions[strtolower($merchantType)]['action']);
                                $inputName = htmlspecialchars($formActions[strtolower($merchantType)]['input_name']);
                            ?>
                                <form action="<?php echo $action; ?>" method="post">
                                    <input type="hidden" name="<?php echo $inputName; ?>" value="<?php echo $merchantNumber; ?>">
                                    <?php if (in_array(strtolower($merchantType), $csrfRequiredTypes)) : ?>
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
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                                <?php
                                $displayStatus = ($status === 'Active' || $status === 'Deactive') ? $status : '';
                                $checked = $displayStatus === 'Active' ? 'checked' : '';
                                ?>
                                <label class="toggle-switch">
                                    <input type="checkbox" class="status-toggle"
                                        data-merchant-type="<?php echo $merchantType; ?>"
                                        data-merchant-number="<?php echo $merchantNumber; ?>"
                                        data-form-id="statusForm_<?php echo $merchant['id']; ?>"
                                        data-current-status="<?php echo $displayStatus; ?>"
                                        <?php echo $checked; ?>>
                                    <span class="slider"></span>
                                </label>
                            </form>
                        </td>
                        <td>
                            <?php
                            $allowedMerchantTypes = ['hdfc', 'phonepe', 'paytm', 'bharatpe', 'googlepay', 'freecharge', 'sbi', 'mobikwik', 'manual', 'amazon', 'paynearby', 'bajaj', 'quintuspay'];

                            if (in_array(strtolower($merchantType), $allowedMerchantTypes)) {
                            ?>
                                <div style="display: flex; gap: 10px; align-items: center;">
                                    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" style="margin: 0;">
                                        <input type="hidden" name="<?php echo $formActions[strtolower($merchantType)]['input_name']; ?>" value="<?php echo $merchantNumber; ?>">
                                        <input type="hidden" name="merchant_type" value="<?php echo $merchantType; ?>">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                                        <button class="btn ripple btn-danger px-2" name="delete">Delete</button>
                                    </form>
                                    <?php if (strtolower($merchantType) === 'phonepe') {?>
                                        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                                            <input type="text" name="upi_id" placeholder="Enter UPI ID (e.g., yourname@bank)" required value="<?php echo $upiId; ?>">
                                            <input type="hidden" name="merchant_number" value="<?php echo $merchantNumber; ?>">
                                            <input type="hidden" name="user_token" value="<?php echo htmlspecialchars($merchant['user_token'] ?? ''); ?>">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                                            <button type="submit" class="btn btn-sm btn-info" name="update_phonepe_upi">Update UPI ID</button>
                                        </form>
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

<!-- Pagination -->
<div class="pagination-wrapper">
    <div class="pagination-info">
        <span>Showing <?php echo min($offset + 1, $totalCount); ?> to <?php echo min($offset + $limit, $totalCount); ?> of <?php echo $totalCount; ?> entries</span>
    </div>
    
    <div class="pagination-controls">
        <?php if ($totalPages > 1): ?>
            <nav aria-label="Page navigation">
                <ul class="pagination pagination-sm justify-content-center mb-0">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => 1])); ?>">First</a>
                        </li>
                        <li class="page-item">
                            <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">Previous</a>
                        </li>
                    <?php endif; ?>
                    
                    <?php
                    $startPage = max(1, $page - 2);
                    $endPage = min($totalPages, $page + 2);
                    
                    for ($i = $startPage; $i <= $endPage; $i++):
                    ?>
                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">Next</a>
                        </li>
                        <li class="page-item">
                            <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $totalPages])); ?>">Last</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
    
    <div class="page-size-selector">
        <small class="text-muted">Page <?php echo $page; ?> of <?php echo $totalPages; ?></small>
    </div>
</div>

<script src="./assets/vendors/DataTables/datatables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Store original data from PHP to avoid re-parsing DOM repeatedly for summary
    const allMerchantsData = <?php echo json_encode($allMerchantsData ?? []); ?>;

    // Block any jQuery form submissions to prevent conflicts if jQuery is loaded elsewhere
    if (typeof jQuery !== 'undefined') {
        jQuery('form.status-form').off('submit'); // Remove any existing submit handlers
        console.log('jQuery detected, blocked form submit handlers');
    }

    document.querySelectorAll('.status-toggle').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault(); // Prevent any default behavior initially
            e.stopPropagation(); // Stop event propagation
            console.log('Button clicked, current status:', this.getAttribute('data-current-status'));

            const currentStatus = this.getAttribute('data-current-status');
            // Determine the *proposed* new status based on current display
            const newStatus = (currentStatus === 'Deactive' || currentStatus === '') ? 'Active' : 'Deactive';

            const merchantType = this.getAttribute('data-merchant-type');
            const merchantNumber = this.getAttribute('data-merchant-number');
            const formId = this.getAttribute('data-form-id');
            const form = document.getElementById(formId);
            const statusInput = form.querySelector(`#statusInput_${formId.split('_')[1]}`);

            console.log('Current status:', currentStatus, 'Proposed new status:', newStatus);

            // Handle Deactive to Active
            if (newStatus === 'Active') {
                console.log('Showing SweetAlert for verification/activation');
                Swal.fire({
                    title: 'Verify Merchant?',
                    text: 'Do you want to verify the merchant again? Activating without verification may cause transaction issues. Ignore If Verified Already Before.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Verify',
                    cancelButtonText: 'No, Just Activate',
                    allowOutsideClick: false,
                    allowEscapeKey: false
                }).then((result) => {
                    console.log('SweetAlert result:', result);
                    if (result.isConfirmed) {
                        // Map merchant type to verification URL/action
                        const verifyActions = {
                            'hdfc': 'send_hdfcotp',
                            'phonepe': 'send_phonepeotp',
                            'paytm': 'send_paytmotp',
                            'bharatpe': 'send_bharatpeotp',
                            'googlepay': 'send_googlepayotp',
                            'freecharge': 'send_freechargeotp',
                            'sbi': 'send_sbiotp',
                            'mobikwik': 'send_mobikwikotp',
                            'manual': 'manualpay',
                            'amazon': 'amazonverify',
                            'paynearby': 'paynearbyverify',
                            'bajaj': 'bajajverify',
                            'quintuspay': 'quintuspayverify'
                        };

                        const verifyUrl = verifyActions[merchantType.toLowerCase()] || '';
                        if (verifyUrl) {
                            console.log('Submitting verification form to:', verifyUrl);
                            // Create a temporary form for verification
                            const verifyForm = document.createElement('form');
                            verifyForm.method = 'POST';
                            verifyForm.action = verifyUrl;

                            // Add mobile number input using the dynamic input name from formActions map
                            const formActionsMap = <?php echo json_encode($formActions); ?>;
                            const inputName = formActionsMap[merchantType.toLowerCase()] ? formActionsMap[merchantType.toLowerCase()]['input_name'] : 'mobile';
                            const mobileInput = document.createElement('input');
                            mobileInput.type = 'hidden';
                            mobileInput.name = inputName;
                            mobileInput.value = merchantNumber;
                            verifyForm.appendChild(mobileInput);

                            // Add CSRF token for specific types if required by the verification endpoint
                            const csrfRequiredForVerify = <?php echo json_encode($csrfRequiredTypes); ?>;
                            if (csrfRequiredForVerify.includes(merchantType.toLowerCase())) {
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
                            console.log('Verification URL not found for:', merchantType);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: 'Verification not available for this merchant type. Activating directly.',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                statusInput.value = 'Active';
                                form.submit();
                            });
                        }
                    } else if (result.dismiss === Swal.DismissReason.cancel) {
                        console.log('Activating without verification');
                        statusInput.value = 'Active';
                        form.submit();
                    }
                });
            }
            // Handle Active to Deactive
            else if (newStatus === 'Deactive') {
                console.log('Showing SweetAlert for deactivation');
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
                    console.log('SweetAlert result:', result);
                    if (result.isConfirmed) {
                        console.log('Deactivating merchant');
                        statusInput.value = 'Deactive';
                        form.submit();
                    } else {
                        console.log('Deactivation cancelled');
                        e.target.checked = !e.target.checked;
                    }
                });
            }
        });
    });
});
</script>

<?php
// Close the statement and connection
$stmt->close();
} // End Admin check
?>