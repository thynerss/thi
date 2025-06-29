<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    $packageId = $_POST['package_id'] ?? null;
    $packageType = $_POST['package_type'] ?? null;
    $email = $_POST['email'] ?? null;
    
    if (!$packageId || !$packageType || !$email) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        exit;
    }
    
    if (!is_numeric($packageId)) {
        echo json_encode(['success' => false, 'message' => 'Invalid package ID']);
        exit;
    }
    
    if (!in_array($packageType, ['vps', 'proxy'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid package type']);
        exit;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email address']);
        exit;
    }
    
    $userId = $_SESSION['user_id'];
    $orderId = createOrder($userId, (int)$packageId, $packageType, $email);
    
    if ($orderId) {
        echo json_encode(['success' => true, 'message' => 'Order created successfully', 'order_id' => $orderId]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Insufficient balance or invalid package']);
    }
} catch (Exception $e) {
    error_log("Error in create_order.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
}
?>