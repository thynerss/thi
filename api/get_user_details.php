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

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
    exit;
}

try {
    $userId = (int)$_GET['id'];
    $user = getUserById($userId);
    
    if (!$user) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit;
    }
    
    // Get user orders
    $orders = getUserOrders($userId);
    $topups = getUserTopUps($userId);
    $transactions = getUserTransactions($userId);
    
    // Generate HTML content
    $html = '<div class="space-y-6">';
    
    // User basic info
    $html .= '<div class="bg-gray-50 rounded-lg p-4">';
    $html .= '<h3 class="font-semibold text-gray-900 mb-3">Thông tin cơ bản</h3>';
    $html .= '<div class="grid grid-cols-1 md:grid-cols-2 gap-4">';
    $html .= '<div><p class="text-sm text-gray-500">ID</p><p class="font-medium">#' . $user['id'] . '</p></div>';
    $html .= '<div><p class="text-sm text-gray-500">Tên đăng nhập</p><p class="font-medium">' . htmlspecialchars($user['username']) . '</p></div>';
    $html .= '<div><p class="text-sm text-gray-500">Email</p><p class="font-medium">' . htmlspecialchars($user['email']) . '</p></div>';
    $html .= '<div><p class="text-sm text-gray-500">Số dư</p><p class="font-medium text-green-600">' . formatCurrency($user['balance']) . '</p></div>';
    $html .= '<div><p class="text-sm text-gray-500">Vai trò</p><p class="font-medium">' . ($user['role'] === 'admin' ? 'Admin' : 'User') . '</p></div>';
    $html .= '<div><p class="text-sm text-gray-500">Trạng thái</p><p class="font-medium">' . ($user['status'] === 'active' ? 'Hoạt động' : 'Không hoạt động') . '</p></div>';
    $html .= '<div><p class="text-sm text-gray-500">Ngày tham gia</p><p class="font-medium">' . date('d/m/Y H:i', strtotime($user['created_at'])) . '</p></div>';
    $html .= '<div><p class="text-sm text-gray-500">Cập nhật cuối</p><p class="font-medium">' . date('d/m/Y H:i', strtotime($user['updated_at'])) . '</p></div>';
    $html .= '</div></div>';
    
    // Statistics
    $totalSpent = array_sum(array_column($orders, 'amount'));
    $totalTopup = array_sum(array_column($topups, 'amount'));
    
    $html .= '<div class="bg-blue-50 rounded-lg p-4">';
    $html .= '<h3 class="font-semibold text-gray-900 mb-3">Thống kê</h3>';
    $html .= '<div class="grid grid-cols-1 md:grid-cols-3 gap-4">';
    $html .= '<div><p class="text-sm text-gray-500">Tổng đơn hàng</p><p class="font-medium">' . count($orders) . '</p></div>';
    $html .= '<div><p class="text-sm text-gray-500">Tổng chi tiêu</p><p class="font-medium">' . formatCurrency($totalSpent) . '</p></div>';
    $html .= '<div><p class="text-sm text-gray-500">Tổng nạp tiền</p><p class="font-medium">' . formatCurrency($totalTopup) . '</p></div>';
    $html .= '</div></div>';
    
    // Recent orders
    if (!empty($orders)) {
        $html .= '<div class="bg-white border rounded-lg p-4">';
        $html .= '<h3 class="font-semibold text-gray-900 mb-3">Đơn hàng gần đây</h3>';
        $html .= '<div class="space-y-2">';
        foreach (array_slice($orders, 0, 5) as $order) {
            $html .= '<div class="flex justify-between items-center p-2 bg-gray-50 rounded">';
            $html .= '<div>';
            $html .= '<p class="font-medium text-sm">' . htmlspecialchars($order['package_name']) . '</p>';
            $html .= '<p class="text-xs text-gray-500">' . $order['order_code'] . ' • ' . date('d/m/Y', strtotime($order['created_at'])) . '</p>';
            $html .= '</div>';
            $html .= '<div class="text-right">';
            $html .= '<p class="font-medium text-sm">' . formatCurrency($order['amount']) . '</p>';
            $html .= '<span class="px-2 py-1 rounded-full text-xs font-medium ' . getStatusColor($order['status']) . '">' . getStatusText($order['status']) . '</span>';
            $html .= '</div>';
            $html .= '</div>';
        }
        $html .= '</div></div>';
    }
    
    // Recent transactions
    if (!empty($transactions)) {
        $html .= '<div class="bg-white border rounded-lg p-4">';
        $html .= '<h3 class="font-semibold text-gray-900 mb-3">Giao dịch gần đây</h3>';
        $html .= '<div class="space-y-2">';
        foreach (array_slice($transactions, 0, 5) as $transaction) {
            $html .= '<div class="flex justify-between items-center p-2 bg-gray-50 rounded">';
            $html .= '<div>';
            $html .= '<p class="font-medium text-sm">' . htmlspecialchars($transaction['description']) . '</p>';
            $html .= '<p class="text-xs text-gray-500">' . $transaction['transaction_id'] . ' • ' . date('d/m/Y H:i', strtotime($transaction['created_at'])) . '</p>';
            $html .= '</div>';
            $html .= '<div class="text-right">';
            $html .= '<p class="font-medium text-sm ' . ($transaction['amount'] > 0 ? 'text-green-600' : 'text-red-600') . '">';
            $html .= ($transaction['amount'] > 0 ? '+' : '') . formatCurrency($transaction['amount']);
            $html .= '</p>';
            $html .= '</div>';
            $html .= '</div>';
        }
        $html .= '</div></div>';
    }
    
    $html .= '</div>';
    
    echo json_encode(['success' => true, 'html' => $html]);
    
} catch (Exception $e) {
    error_log("Error in get_user_details.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
}
?>