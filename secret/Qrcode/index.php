<?php
// PHP QR Code library path
include('../Qrcode/phpqrcode/qrlib.php');

// Data to encode
$data = 'Raja Siddique';

// File path to save the QR code image
//$file = 'image/qrcode.png';
$file = 'image/qrcode_' . uniqid() . '.png';

// ECC level (L, M, Q, H)
$ecc = 'L';

// QR code size
$size = 10;

// Generate QR code image
QRcode::png($data, $file, $ecc, $size);

// Encode the image to base64
$imageData = base64_encode(file_get_contents($file));

// Output the QR code as a base64 data URI
echo '<img src="data:../Qrcode/image/png;base64,' . $imageData . '">';
?>