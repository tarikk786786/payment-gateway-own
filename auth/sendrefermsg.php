<?php
// Include necessary files
include "header.php";
// include "function.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mobile = $_POST['mobileNumber'] ?? '';
    $message = $_POST['message'] ?? '';

    // PHP function ko yahin call karo
    $result = sendWA($mobile, $message);

    // Debug ke liye check karo kya result mila
    if ($result === true) {
        echo "success";
    } else {
        // Agar sendWA() kuch return karta hai to usse show karo
        echo "fail: " . $result;
    }
}

?>
