<?php
require 'config.php'; // Database connection
require 'vendor/autoload.php'; // Ensure you have PhpSpreadsheet and mPDF libraries
session_start();

// Fetch data for export
if (isset($_SESSION['username'])) {
    $mobile = $_SESSION['username'];
    $user = "SELECT * FROM users WHERE mobile = '$mobile'";
    $uu = mysqli_query($conn, $user);
    $userdata = mysqli_fetch_array($uu);


if (isset($_POST["start_date"]) && isset($_POST["end_date"])) {
    $from = $_POST["start_date"];
    $to = $_POST["end_date"];
    
    // SQL Injection से बचने के लिए डेट को सही से एस्केप करें
    $from = mysqli_real_escape_string($conn, $from);
    $to = mysqli_real_escape_string($conn, $to);

    if ($userdata['role'] == 'User') {
        $token = mysqli_real_escape_string($conn, $userdata['user_token']);
        $query = "SELECT id, user_id,merchentMobile, customer_mobile, create_date, method, gateway_txn, utr, order_id, amount, status 
                  FROM orders 
                  WHERE user_token = '$token' 
                  AND create_date BETWEEN '$from 00:00:00' AND '$to 23:59:59' 
                  ORDER BY create_date DESC";
    } else {
        $query = "SELECT id, user_id,merchentMobile, customer_mobile, create_date, method, gateway_txn, utr, order_id, amount, status 
                  FROM orders 
                  WHERE create_date BETWEEN '$from 00:00:00' AND '$to 23:59:59' 
                  ORDER BY create_date DESC";
    }
} else {
    $currentMonth = date('Y-m');

    if ($userdata['role'] == 'User') {
        $token = mysqli_real_escape_string($conn, $userdata['user_token']);
        $query = "SELECT id, user_id,merchentMobile, customer_mobile, create_date, method, gateway_txn, utr, order_id, amount, status 
                  FROM orders 
                  WHERE user_token = '$token' 
                  AND DATE_FORMAT(create_date, '%Y-%m') = '$currentMonth' 
                  ORDER BY create_date DESC";
    } else {
        $query = "SELECT id, user_id,merchentMobile, customer_mobile, create_date, method, gateway_txn, utr, order_id, amount, status 
                  FROM orders 
                  WHERE DATE_FORMAT(create_date, '%Y-%m') = '$currentMonth' 
                  ORDER BY create_date DESC";
    }
}

$result = mysqli_query($conn, $query);
$data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
}

// Export CSV
if (isset($_POST['export_csv'])) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="transactions.csv"');
    $output = fopen("php://output", "w");
    fputcsv($output, array_keys($data[0]));
    foreach ($data as $row) fputcsv($output, $row);
    fclose($output);
    exit();
}

// Export Excel
if (isset($_POST['export_excel'])) {
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->fromArray(array_merge([array_keys($data[0])], $data), NULL, 'A1');
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="transactions.xlsx"');
    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $writer->save('php://output');
    exit();
}

// Export PDF
if (isset($_POST['export_pdf'])) {
    $html = '<h1>Transaction Report</h1><table border="1" cellpadding="5"><tr>';
    foreach (array_keys($data[0]) as $header) $html .= "<th>$header</th>";
    $html .= '</tr>';
    foreach ($data as $row) {
        $html .= '<tr>';
        foreach ($row as $cell) $html .= "<td>$cell</td>";
        $html .= '</tr>';
    }
    $html .= '</table>';

    $mpdf = new \Mpdf\Mpdf();
    $mpdf->WriteHTML($html);
    $mpdf->Output('transactions.pdf', 'D');
    exit();
}
}
?>
