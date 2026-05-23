<?php

require 'vendor/autoload.php';

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Logo\Logo;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;

// Allowed IPs List
$allowed_ips = [
    '198.38.88.206',
    '152.59.6.24',
    '::1',
];

// Get client's IP address
$user_ip = $_SERVER['REMOTE_ADDR'];

// Unauthorized Access Check
if (!in_array($user_ip, $allowed_ips)) {
    header('Content-Type: application/json', true, 403);
    echo json_encode([
        'error' => 'Unauthorized Access',
        'your_ip' => $user_ip
    ]);
    exit;
}

// Set API response header
header('Content-Type: application/json');

// Get request data
$requestMethod = $_SERVER['REQUEST_METHOD'];
$rawInput = file_get_contents('php://input');

// Default values
$data = '';
$useLogo = true;

if ($requestMethod === 'POST') {
    $decoded = json_decode($rawInput, true);
    $data = isset($decoded['data']) ? $decoded['data'] : '';
    $useLogo = isset($decoded['logo']) ? filter_var($decoded['logo'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) !== false : true;
} else {
    $data = isset($_GET['data']) ? $_GET['data'] : '';
    $useLogo = isset($_GET['logo']) ? filter_var($_GET['logo'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) !== false : true;
}

if (empty($data)) {
    echo json_encode(['error' => 'Data parameter is required']);
    exit;
}

try {
    $qrCode = QrCode::create($data)
        ->setEncoding(new Encoding('UTF-8'))
        ->setSize(500)
        ->setMargin(20);

    $writer = new PngWriter();

    // Default: No logo
    $logo = null;

    if ($useLogo) {
        $logoPath = __DIR__ . '/common/img/qrlogo.png';
        if (!file_exists($logoPath)) {
            echo json_encode(['error' => 'Logo file not found! Upload qrlogo.png in script directory.']);
            exit;
        }
        $logo = Logo::create($logoPath)->setResizeToWidth(100);
    }

    // Write QR code (with or without logo)
    $result = $writer->write($qrCode, $logo);

    $base64 = base64_encode($result->getString());

    echo json_encode([
        'status' => 'success',
        'qr_code' => 'data:image/png;base64,' . $base64
    ]);
} catch (Exception $e) {
    echo json_encode([
        'error' => 'QR Code generation failed!',
        'message' => $e->getMessage()
    ]);
}

?>
