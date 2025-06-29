<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!isset($_GET['id']) || !is_numeric($_GET['id']) || !isset($_GET['type'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid parameters']);
    exit;
}

try {
    $packageId = (int)$_GET['id'];
    $packageType = $_GET['type'];
    
    if ($packageType === 'vps') {
        $package = getVPSPackageById($packageId);
    } elseif ($packageType === 'proxy') {
        $package = getProxyPackageById($packageId);
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid package type']);
        exit;
    }
    
    if ($package) {
        // Đảm bảo có đầy đủ thông tin cần thiết
        if ($packageType === 'vps') {
            $package['provider'] = $package['provider'] ?? 'Unknown';
            $package['type'] = $package['type'] ?? 'official';
            $package['stock_quantity'] = $package['stock_quantity'] ?? 0;
        } else {
            $package['provider'] = $package['provider'] ?? 'Unknown';
            $package['stock_quantity'] = $package['stock_quantity'] ?? 0;
        }
        
        echo json_encode($package);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Package not found']);
    }
} catch (Exception $e) {
    error_log("Error in get_package.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}
?>