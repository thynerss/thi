<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

// Check if user is admin
if (!isAdmin()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $orderId = (int)$_POST['order_id'];
    $order = getOrderById($orderId);
    
    if (!$order) {
        echo json_encode(['success' => false, 'message' => 'Order not found']);
        exit;
    }
    
    $success = false;
    
    if ($order['package_type'] === 'vps') {
        $vpsInfo = getVPSInfo($orderId);
        if ($vpsInfo) {
            $success = sendVPSInfoEmail($orderId);
        } else {
            echo json_encode(['success' => false, 'message' => 'VPS info not found']);
            exit;
        }
    } else {
        $proxyInfo = getProxyInfo($orderId);
        if ($proxyInfo) {
            $success = sendProxyInfoEmail($orderId);
        } else {
            echo json_encode(['success' => false, 'message' => 'Proxy info not found']);
            exit;
        }
    }
    
    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Email sent successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to send email']);
    }
    
} catch (Exception $e) {
    error_log("Error in send_order_email.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
}
?>