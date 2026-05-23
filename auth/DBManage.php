<?php
session_start();


$database = 'clcxkjhp_Paymentgateway1432'; // Replace with your database name

// Security: Add basic authentication for cron access
$valid_cron_token = 'your_secure_token_here'; // Change this to a secure token

// Set cutoff date: first of the month, 4 months ago
$cutoffDate = date('Y-m-01', strtotime('-4 months'));

// Enhanced cron functionality with authentication
if (isset($_GET['cron']) && $_GET['cron'] == '1') {
    include "database_connection.php";
    include "function.php";
    header('Content-Type: application/json');
    
    // Token authentication for cron
    if (!isset($_GET['token']) || $_GET['token'] !== $valid_cron_token) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized access']);
        exit;
    }
    
    $tables = [
        ['name' => 'orders', 'column' => 'create_date', 'condition' => ''],
        ['name' => 'reports', 'column' => 'date', 'condition' => ''],
        ['name' => 'planorders', 'column' => 'created_at', 'condition' => "AND status != 'SUCCESS'"]
    ];

    $responses = [];
    $total_deleted = 0;

    foreach ($tables as $tableInfo) {
        $table = $tableInfo['name'];
        $column = $tableInfo['column'];
        $condition = $tableInfo['condition'];

        $delete_query = "DELETE FROM $table WHERE $column < ? $condition";
        $stmt = $conn->prepare($delete_query);
        $stmt->bind_param('s', $cutoffDate);

        if ($stmt->execute()) {
            $affected_rows = $stmt->affected_rows;
            $total_deleted += $affected_rows;
            $responses[$table] = [
                'status' => 'success',
                'message' => "Deleted $affected_rows records older than $cutoffDate",
                'deleted_count' => $affected_rows
            ];
        } else {
            $responses[$table] = [
                'status' => 'error',
                'message' => $stmt->error,
                'deleted_count' => 0
            ];
        }
        $stmt->close();
    }

    $responses['summary'] = [
        'total_deleted' => $total_deleted,
        'cutoff_date' => $cutoffDate,
        'execution_time' => date('Y-m-d H:i:s')
    ];

    $conn->close();
    echo json_encode($responses, JSON_PRETTY_PRINT);
    exit;
}

include "header.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enhanced Database Management</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .health-good { color: #28a745; }
        .health-warning { color: #ffc107; }
        .health-danger { color: #dc3545; }
        .card-hover:hover { transform: translateY(-2px); transition: all 0.3s; }
        .progress-animated { animation: pulse 2s infinite; }
        @keyframes pulse { 0% { opacity: 1; } 50% { opacity: 0.5; } 100% { opacity: 1; } }
    </style>
</head>
<body>
<div class="container my-5">
    <div class="row">
        <div class="col-12">
            <h1 class="text-center text-primary mb-4">
                <i class="bi bi-database"></i> Enhanced Database Management
            </h1>
        </div>
    </div>

    <?php
    if (!$conn) {
        die("<div class='alert alert-danger'><i class='bi bi-exclamation-triangle'></i> Connection failed: " . mysqli_connect_error() . "</div>");
    }

    // Enhanced database information display
    function getDatabaseSize($conn, $database) {
        $query = "SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'Size'
                  FROM information_schema.tables 
                  WHERE table_schema = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('s', $database);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $stmt->close();
        return $data['Size'] ?? 0;
    }

    function getTableInfo($conn, $database) {
        $query = "SELECT 
                    table_name,
                    ROUND((data_length + index_length) / 1024 / 1024, 2) AS size_mb,
                    table_rows,
                    ROUND(data_length / 1024 / 1024, 2) AS data_size,
                    ROUND(index_length / 1024 / 1024, 2) AS index_size
                  FROM information_schema.tables 
                  WHERE table_schema = ? 
                  ORDER BY (data_length + index_length) DESC";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('s', $database);
        $stmt->execute();
        $result = $stmt->get_result();
        $tables = [];
        while ($row = $result->fetch_assoc()) {
            $tables[] = $row;
        }
        $stmt->close();
        return $tables;
    }

    function getHealthStatus($size_mb) {
        if ($size_mb < 100) return ['status' => 'good', 'text' => 'Excellent', 'class' => 'health-good'];
        elseif ($size_mb < 500) return ['status' => 'warning', 'text' => 'Good', 'class' => 'health-warning'];
        else return ['status' => 'danger', 'text' => 'Needs Attention', 'class' => 'health-danger'];
    }

    $totalSize = getDatabaseSize($conn, $database);
    $health = getHealthStatus($totalSize);
    $tables = getTableInfo($conn, $database);
    ?>

    <!-- Database Overview -->
    <div class="row mb-4">
        <div class="col-lg-4 col-md-6 mb-3">
            <div class="card card-hover border-primary">
                <div class="card-body text-center">
                    <i class="bi bi-hdd-stack display-4 text-primary"></i>
                    <h5 class="card-title mt-2">Database Size</h5>
                    <h3 class="text-primary"><?php echo $totalSize; ?> MB</h3>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6 mb-3">
            <div class="card card-hover border-<?php echo $health['status']; ?>">
                <div class="card-body text-center">
                    <i class="bi bi-heart-pulse display-4 <?php echo $health['class']; ?>"></i>
                    <h5 class="card-title mt-2">Database Health</h5>
                    <h3 class="<?php echo $health['class']; ?>"><?php echo $health['text']; ?></h3>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6 mb-3">
            <div class="card card-hover border-info">
                <div class="card-body text-center">
                    <i class="bi bi-calendar-date display-4 text-info"></i>
                    <h5 class="card-title mt-2">Cleanup Cutoff</h5>
                    <h3 class="text-info"><?php echo $cutoffDate; ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Information -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="bi bi-table"></i> Table Information</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Table Name</th>
                                    <th>Size (MB)</th>
                                    <th>Rows</th>
                                    <th>Data Size (MB)</th>
                                    <th>Index Size (MB)</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tables as $table): ?>
                                    <?php $tableHealth = getHealthStatus($table['size_mb']); ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($table['table_name']); ?></strong></td>
                                        <td><?php echo $table['size_mb']; ?></td>
                                        <td><?php echo number_format($table['table_rows']); ?></td>
                                        <td><?php echo $table['data_size']; ?></td>
                                        <td><?php echo $table['index_size']; ?></td>
                                        <td><span class="badge bg-<?php echo $tableHealth['status']; ?>"><?php echo $tableHealth['text']; ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php
    // Enhanced old data analysis
    function getOldDataInfo($conn, $table, $column, $cutoffDate, $condition = '') {
        $query = "SELECT COUNT(*) AS total FROM $table WHERE $column < ? $condition";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('s', $cutoffDate);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $stmt->close();
        return $data['total'];
    }

    $oldDataTables = [
        ['table' => 'orders', 'column' => 'create_date', 'condition' => '', 'title' => 'Orders'],
        ['table' => 'reports', 'column' => 'date', 'condition' => '', 'title' => 'Reports'],
        ['table' => 'planorders', 'column' => 'created_at', 'condition' => "AND status != 'SUCCESS'", 'title' => 'Plan Orders (Non-SUCCESS)']
    ];
    ?>

    <!-- Old Data Management -->
    <div class="row mb-4">
        <div class="col-12">
            <h3 class="text-warning"><i class="bi bi-archive"></i> Old Data Management</h3>
        </div>
        <?php foreach ($oldDataTables as $tableInfo): ?>
            <?php $oldCount = getOldDataInfo($conn, $tableInfo['table'], $tableInfo['column'], $cutoffDate, $tableInfo['condition']); ?>
            <div class="col-lg-4 col-md-6 mb-3">
                <div class="card card-hover <?php echo $oldCount > 0 ? 'border-warning' : 'border-success'; ?>">
                    <div class="card-body">
                        <h5 class="card-title text-<?php echo $oldCount > 0 ? 'warning' : 'success'; ?>">
                            <i class="bi bi-<?php echo $oldCount > 0 ? 'exclamation-diamond' : 'check-circle'; ?>"></i>
                            <?php echo $tableInfo['title']; ?>
                        </h5>
                        <p class="card-text">
                            Records older than <?php echo $cutoffDate; ?>:
                            <strong class="<?php echo $oldCount > 0 ? 'text-warning' : 'text-success'; ?>">
                                <?php echo number_format($oldCount); ?>
                            </strong>
                        </p>
                        <?php if ($oldCount > 0): ?>
                            <form method="POST" onsubmit="return confirm('Are you sure you want to delete <?php echo number_format($oldCount); ?> records from <?php echo $tableInfo['title']; ?>?');">
                                <input type="hidden" name="table" value="<?php echo $tableInfo['table']; ?>">
                                <input type="hidden" name="date_column" value="<?php echo $tableInfo['column']; ?>">
                                <input type="hidden" name="condition" value="<?php echo $tableInfo['condition']; ?>">
                                <button type="submit" name="delete" class="btn btn-warning">
                                    <i class="bi bi-trash"></i> Clean Old Data
                                </button>
                            </form>
                        <?php else: ?>
                            <span class="badge bg-success">No old data found</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Bulk Operations -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-danger">
                <div class="card-header bg-danger text-white">
                    <h4 class="mb-0"><i class="bi bi-lightning"></i> Bulk Operations</h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i>
                        <strong>Warning:</strong> These operations will permanently delete data. Make sure you have a backup!
                    </div>
                    <form method="POST" onsubmit="return confirm('WARNING: This will delete ALL old records from ALL tables. This action cannot be undone. Are you absolutely sure?');">
                        <button type="submit" name="delete_all" class="btn btn-danger">
                            <i class="bi bi-trash3"></i> Delete All Old Data
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php
    // Handle manual deletion
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['delete']) || isset($_POST['delete_all'])) {
            echo "<div class='row mb-4'><div class='col-12'>";
            
            if (isset($_POST['delete_all'])) {
                // Delete from all tables
                $total_deleted = 0;
                foreach ($oldDataTables as $tableInfo) {
                    $table = $tableInfo['table'];
                    $column = $tableInfo['column'];
                    $condition = $tableInfo['condition'];
                    
                    $delete_query = "DELETE FROM $table WHERE $column < ? $condition";
                    $stmt = $conn->prepare($delete_query);
                    $stmt->bind_param('s', $cutoffDate);
                    
                    if ($stmt->execute()) {
                        $deleted = $stmt->affected_rows;
                        $total_deleted += $deleted;
                        echo "<div class='alert alert-success'><i class='bi bi-check-circle'></i> Deleted $deleted records from <strong>{$tableInfo['title']}</strong></div>";
                    } else {
                        echo "<div class='alert alert-danger'><i class='bi bi-exclamation-triangle'></i> Error deleting from {$tableInfo['title']}: " . $stmt->error . "</div>";
                    }
                    $stmt->close();
                }
                echo "<div class='alert alert-info'><i class='bi bi-info-circle'></i> <strong>Total deleted:</strong> " . number_format($total_deleted) . " records</div>";
            } else {
                // Delete from single table
                $table = $_POST['table'];
                $date_column = $_POST['date_column'];
                $condition = $_POST['condition'] ?? '';
                
                $delete_query = "DELETE FROM $table WHERE $date_column < ? $condition";
                $stmt = $conn->prepare($delete_query);
                $stmt->bind_param('s', $cutoffDate);
                
                if ($stmt->execute()) {
                    $deleted = $stmt->affected_rows;
                    echo "<div class='alert alert-success'><i class='bi bi-check-circle'></i> Successfully deleted " . number_format($deleted) . " records from <strong>$table</strong> older than $cutoffDate</div>";
                } else {
                    echo "<div class='alert alert-danger'><i class='bi bi-exclamation-triangle'></i> Error deleting from $table: " . $stmt->error . "</div>";
                }
                $stmt->close();
            }
            
            echo "</div></div>";
            // Refresh the page after 3 seconds to show updated counts
            echo "<script>setTimeout(function(){ window.location.reload(); }, 3000);</script>";
        }
    }

    $conn->close();
    ?>

    <!-- System Information -->
    <div class="row">
        <div class="col-12">
            <div class="card border-info">
                <div class="card-header bg-info text-white">
                    <h4 class="mb-0"><i class="bi bi-info-circle"></i> System Information</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>PHP Version:</strong> <?php echo phpversion(); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Last Update:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
                            <p><strong>Cutoff Date:</strong> <?php echo $cutoffDate; ?></p>
                        </div>
                    </div>
                    <div class="alert alert-light">
                        <strong>Cron URL:</strong><br>
                        <code><?php echo $server;?>/DBManage.php?cron=1&token=your_secure_token_here</code>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>