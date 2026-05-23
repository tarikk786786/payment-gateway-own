<?php
header('Content-Type: application/json');

include('../Qrcode/phpqrcode/qrlib.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = isset($_POST['data']) ? $_POST['data'] : '';
    if (empty($data)) {
        echo json_encode(['error' => 'No data provided']);
        http_response_code(400);
        exit();
    }

    $ecc = isset($_POST['ecc']) ? $_POST['ecc'] : 'L';
    $size = isset($_POST['size']) ? intval($_POST['size']) : 10;

    $file = 'image/qrcode_' . uniqid() . '.png';

    try {
        QRcode::png($data, $file, $ecc, $size);

        $imageData = base64_encode(file_get_contents($file));
        echo json_encode(['qr_code' => 'data:image/png;base64,' . $imageData]);
    } catch (Exception $e) {
        echo json_encode(['error' => 'Failed to generate QR code']);
        http_response_code(500);
    }
} else {
    echo json_encode(['error' => 'Invalid request method']);
    http_response_code(405);
}
?>