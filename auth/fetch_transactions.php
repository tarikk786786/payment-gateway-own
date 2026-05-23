<?php
include "db_connection.php"; // include your database connection file

$start_date = $_POST['start_date'];
$end_date = $_POST['end_date'];
$status = $_POST['status'];

$query = "SELECT * FROM `orders` WHERE 1";

if (!empty($start_date)) {
    $query .= " AND DATE(`create_date`) >= '$start_date'";
}

if (!empty($end_date)) {
    $query .= " AND DATE(`create_date`) <= '$end_date'";
}

if (!empty($status)) {
    $query .= " AND `status` = '$status'";
}

$query .= " ORDER BY `create_date` DESC";

$query_run = mysqli_query($conn, $query);

if ($query_run) {
    while ($row = mysqli_fetch_assoc($query_run)) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8') . "</td>";
        echo "<td>" . htmlspecialchars($row['user_id'], ENT_QUOTES, 'UTF-8') . "</td>";
        echo "<td>" . htmlspecialchars($row['customer_mobile'], ENT_QUOTES, 'UTF-8') . "</td>";
        echo "<td>" . htmlspecialchars($row['create_date'], ENT_QUOTES, 'UTF-8') . "</td>";
        echo "<td>" . htmlspecialchars($row['method'], ENT_QUOTES, 'UTF-8') . "</td>";
        echo "<td>" . htmlspecialchars($row['gateway_txn'], ENT_QUOTES, 'UTF-8') . "</td>";
        echo "<td>" . htmlspecialchars($row['utr'], ENT_QUOTES, 'UTF-8') . "</td>";
        echo "<td>" . htmlspecialchars($row['order_id'], ENT_QUOTES, 'UTF-8') . "</td>";
        echo "<td>₹" . htmlspecialchars($row['amount'], ENT_QUOTES, 'UTF-8') . "</td>";
        $status = '';
        $class = '';

        if ($row['status'] == 'SUCCESS') {
            $status = 'Success';
            $class = 'badge badge-success';
        } elseif ($row['status'] == 'FAILURE') {
            $status = 'Failure';
            $class = 'badge badge-danger';
        } else {
            $status = 'Pending';
            $class = 'badge badge-warning';
        }

        echo "<td><button[_{{{CITATION{{{_1{](https://github.com/kumban970/pemira/tree/7d8614cc6c66e565d88112586a326fee763c0b6b/resources%2Fviews%2Fadmin%2Findex.blade.php)[_{{{CITATION{{{_2{](https://github.com/Jaimi-AJ27/Campus-Recruitment-System-Project/tree/455c46a24f9b84da6b615b02a6e496961a0dcd95/Admin%2Fhtml%2Fdist%2Fdatatables.php)[_{{{CITATION{{{_3{](https://github.com/paulamilenyy/arquivaqui/tree/6d248601482ead10a97ffc53b270afa3b70a7e5f/resources%2Fviews%2Fperfil.blade.php)[_{{{CITATION{{{_4{](https://github.com/alibudi/App_laporaan/tree/3145bdc30db31e4f90e89c08accbc0d3ab7298b1/resources%2Fviews%2Fpengajuan%2Fcreate.blade.php)[_{{{CITATION{{{_5{](https://github.com/pro-cms/MMS/tree/c0c0142618973d76453f18c9558e52c4d5e54a97/resources%2Fviews%2Fadmin%2Fusers%2Findex.blade.php)[_{{{CITATION{{{_6{](https://github.com/ShibbirAhmad/news_application/tree/99be228a2c2faaa8f11e18b2e85f43690cd3f8c3/resources%2Fviews%2Fadmin%2Fadvertisement%2FIndex.blade.php)[_{{{CITATION{{{_7{](https://github.com/oszo/OTG-Lab/tree/b9905c090bd34cf875e1b5785ec0bf6409397de0/10.%20Business%20Logic%20Testing%2Fdocker%2Fweb2%2Falbumlist.php)