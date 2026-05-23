<?php
// Database connection
include "config.php";


// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Default filter: All Time
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all_time';

// Set the date range based on filter
switch ($filter) {
    case 'this_week':
        $start_date = date('Y-m-d', strtotime('last sunday'));
        $end_date = date('Y-m-d', strtotime('next saturday'));
        break;
    case 'this_month':
        $start_date = date('Y-m-01');
        $end_date = date('Y-m-t');
        break;
    case 'all_time':
    default:
        $start_date = '2000-01-01';  // You can change this to your database's start date
        $end_date = date('Y-m-d');
        break;
}

// SQL query with date filter
$sql = "SELECT DATE(create_date) AS day, status, SUM(amount) AS total_amount
        FROM orders
        WHERE create_date BETWEEN '$start_date' AND '$end_date'
        GROUP BY day, status
        ORDER BY day ASC";

$result = $conn->query($sql);

$data = ['labels' => [], 'success' => [], 'failure' => [], 'pending' => []];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $data['labels'][] = $row['day'];
        if ($row['status'] == 'SUCCESS') {
            $data['success'][] = $row['total_amount'];
            $data['failure'][] = 0;
            $data['pending'][] = 0;
        } elseif ($row['status'] == 'FAILURE') {
            $data['failure'][] = $row['total_amount'];
            $data['success'][] = 0;
            $data['pending'][] = 0;
        } else {
            $data['pending'][] = $row['total_amount'];
            $data['success'][] = 0;
            $data['failure'][] = 0;
        }
    }
} else {
    echo "0 results";
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daywise Status Chart</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-zoom"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker"></script>
    <script src="https://cdn.jsdelivr.net/npm/FileSaver.js"></script>
</head>
<body>

<!-- Filters and Date Range Picker -->
<form method="get" action="">
    <label for="filter">Select Filter:</label>
    <select name="filter" id="filter">
        <option value="this_week" <?php echo ($filter == 'this_week') ? 'selected' : ''; ?>>This Week</option>
        <option value="this_month" <?php echo ($filter == 'this_month') ? 'selected' : ''; ?>>This Month</option>
        <option value="all_time" <?php echo ($filter == 'all_time') ? 'selected' : ''; ?>>All Time</option>
    </select>
    <input type="submit" value="Apply Filter">
</form>

<!-- Date Range Picker -->
<input type="text" id="dateRange" value="">

<!-- CSV Export Button -->
<button id="exportBtn">Export to CSV</button>

<canvas id="myChart" width="400" height="250"></canvas>

<script>
    // PHP se data pass kar rahe hain JavaScript mein
    var labels = <?php echo json_encode($data['labels']); ?>;
    var successData = <?php echo json_encode($data['success']); ?>;
    var failureData = <?php echo json_encode($data['failure']); ?>;
    var pendingData = <?php echo json_encode($data['pending']); ?>;

    // Initialize Chart.js chart
var ctx = document.getElementById('myChart').getContext('2d');

var myChart = new Chart(ctx, {
    data: {
        labels: labels, // Dates as X-axis labels
        datasets: [
            // Bar Chart Datasets
            {
                label: 'SUCCESS',
                data: successData, // Success data
                backgroundColor: 'rgba(0, 128, 0, 0.7)', // Transparent green
                borderColor: 'green',
                borderWidth: 1,
                type: 'bar',
                stack: 'Stack 0' // Group for stacking
            },
            {
                label: 'FAILURE',
                data: failureData, // Failure data
                backgroundColor: 'rgba(255, 0, 0, 0.7)', // Transparent red
                borderColor: 'red',
                borderWidth: 1,
                type: 'bar',
                stack: 'Stack 0' // Group for stacking
            },
            {
                label: 'PENDING',
                data: pendingData, // Pending data
                backgroundColor: 'rgba(255, 255, 0, 0.7)', // Transparent yellow
                borderColor: 'yellow',
                borderWidth: 1,
                type: 'bar',
                stack: 'Stack 0' // Group for stacking
            },

            // Line Chart Datasets
            {
                label: 'SUCCESS (Line)',
                data: successData,
                borderColor: 'green',
                borderWidth: 2,
                fill: false,
                type: 'line',
                yAxisID: 'y'
            },
            {
                label: 'FAILURE (Line)',
                data: failureData,
                borderColor: 'red',
                borderWidth: 2,
                fill: false,
                type: 'line',
                yAxisID: 'y'
            },
            {
                label: 'PENDING (Line)',
                data: pendingData,
                borderColor: 'yellow',
                borderWidth: 2,
                fill: false,
                type: 'line',
                yAxisID: 'y'
            }
        ]
    },
    options: {
        responsive: true,
        plugins: {
            tooltip: {
                mode: 'index',
                intersect: false, // Ensure tooltips show for both lines and bars
                callbacks: {
                    label: function (tooltipItem) {
                        var datasetLabel = tooltipItem.dataset.label || '';
                        var value = tooltipItem.raw;
                        return datasetLabel + ': ' + value;
                    }
                }
            },
            legend: {
                display: true
            }
        },
        scales: {
            x: {
                stacked: true // Ensure bars are stacked
            },
            y: {
                beginAtZero: true,
                stacked: true // Ensure stacking for bars
            }
        },
        interaction: {
            mode: 'index',
            intersect: false
        }
    }
});

    // CSV Export Function
    document.getElementById("exportBtn").onclick = function() {
        var csvContent = "Day, SUCCESS, FAILURE, PENDING\n";
        for (var i = 0; i < labels.length; i++) {
            csvContent += labels[i] + "," + successData[i] + "," + failureData[i] + "," + pendingData[i] + "\n";
        }
        var blob = new Blob([csvContent], { type: 'text/csv' });
        saveAs(blob, "chart_data.csv");
    };
</script>

</body>
</html>
