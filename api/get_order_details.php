<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid order ID']);
    exit;
}

try {
    $orderId = (int)$_GET['id'];
    $order = getOrderById($orderId);
    
    if (!$order || $order['user_id'] != $_SESSION['user_id']) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Order not found']);
        exit;
    }
    
    // Generate HTML content for order details
    $html = '<div class="space-y-6">';
    
    // Order Information
    $html .= '<div class="bg-gray-50 rounded-lg p-4">';
    $html .= '<h3 class="font-semibold text-gray-900 mb-3">Thông tin đơn hàng</h3>';
    $html .= '<div class="grid grid-cols-1 md:grid-cols-2 gap-4">';
    $html .= '<div><p class="text-sm text-gray-500">Mã đơn hàng</p><p class="font-medium">' . htmlspecialchars($order['order_code']) . '</p></div>';
    $html .= '<div><p class="text-sm text-gray-500">Trạng thái</p><div class="flex items-center space-x-2">' . getStatusIcon($order['status']) . '<span class="font-medium">' . getStatusText($order['status']) . '</span></div></div>';
    $html .= '<div><p class="text-sm text-gray-500">Ngày đặt</p><p class="font-medium">' . date('d/m/Y H:i', strtotime($order['created_at'])) . '</p></div>';
    $html .= '<div><p class="text-sm text-gray-500">Số tiền</p><p class="font-medium">' . formatCurrency($order['amount']) . '</p></div>';
    $html .= '</div></div>';
    
    // Service Information
    if ($order['package_type'] === 'vps') {
        $html .= '<div class="bg-blue-50 rounded-lg p-4">';
        $html .= '<h3 class="font-semibold text-gray-900 mb-3 flex items-center space-x-2"><i class="fas fa-server text-blue-600"></i><span>Thông tin VPS</span></h3>';
        $html .= '<div class="grid grid-cols-1 md:grid-cols-2 gap-4">';
        $html .= '<div><p class="text-sm text-gray-500">Tên gói</p><p class="font-medium">' . htmlspecialchars($order['package_name']) . '</p></div>';
        $html .= '<div><p class="text-sm text-gray-500">Nhà cung cấp</p><p class="font-medium">' . htmlspecialchars($order['provider'] ?? 'N/A') . '</p></div>';
        $html .= '<div><p class="text-sm text-gray-500">Email</p><p class="font-medium">' . htmlspecialchars($order['customer_email']) . '</p></div>';
        $html .= '<div><p class="text-sm text-gray-500">Loại</p><p class="font-medium">VPS</p></div>';
        $html .= '</div></div>';
    } else {
        $html .= '<div class="bg-green-50 rounded-lg p-4">';
        $html .= '<h3 class="font-semibold text-gray-900 mb-3 flex items-center space-x-2"><i class="fas fa-shield-alt text-green-600"></i><span>Thông tin Proxy</span></h3>';
        $html .= '<div class="grid grid-cols-1 md:grid-cols-2 gap-4">';
        $html .= '<div><p class="text-sm text-gray-500">Tên gói</p><p class="font-medium">' . htmlspecialchars($order['package_name']) . '</p></div>';
        $html .= '<div><p class="text-sm text-gray-500">Nhà cung cấp</p><p class="font-medium">' . htmlspecialchars($order['provider'] ?? 'N/A') . '</p></div>';
        $html .= '<div><p class="text-sm text-gray-500">Email</p><p class="font-medium">' . htmlspecialchars($order['customer_email']) . '</p></div>';
        $html .= '<div><p class="text-sm text-gray-500">Giao thức</p><p class="font-medium">SOCKS5</p></div>';
        $html .= '</div></div>';
    }
    
    // Status-specific information
    if ($order['status'] === 'pending') {
        $html .= '<div class="bg-blue-50 rounded-lg p-4">';
        $html .= '<h3 class="font-semibold text-blue-900 mb-2">Đơn hàng đang chờ xử lý</h3>';
        $html .= '<p class="text-blue-800 text-sm">Đơn hàng của bạn đang được xử lý. Bạn sẽ nhận được email với thông tin dịch vụ khi hoàn tất.</p>';
        $html .= '</div>';
    } elseif ($order['status'] === 'processing') {
        $html .= '<div class="bg-yellow-50 rounded-lg p-4">';
        $html .= '<h3 class="font-semibold text-yellow-900 mb-2">Đang thiết lập dịch vụ</h3>';
        $html .= '<p class="text-yellow-800 text-sm">Dịch vụ của bạn đang được thiết lập. Thường mất 15-30 phút. Bạn sẽ nhận được email khi hoàn tất.</p>';
        $html .= '</div>';
    } elseif ($order['status'] === 'completed') {
        // Mock service info for completed orders
        if ($order['package_type'] === 'vps') {
            $html .= '<div class="bg-green-50 rounded-lg p-4">';
            $html .= '<h3 class="font-semibold text-gray-900 mb-3 flex items-center space-x-2"><i class="fas fa-server text-green-600"></i><span>Thông tin truy cập VPS</span></h3>';
            $html .= '<div class="grid grid-cols-1 md:grid-cols-2 gap-4">';
            $html .= '<div><p class="text-sm text-gray-500">IP Server</p><p class="font-mono font-medium">192.168.1.' . (100 + $order['id']) . '</p></div>';
            $html .= '<div><p class="text-sm text-gray-500">SSH Port</p><p class="font-mono font-medium">22</p></div>';
            $html .= '<div><p class="text-sm text-gray-500">Username</p><p class="font-mono font-medium">root</p></div>';
            $html .= '<div><p class="text-sm text-gray-500">Password</p><p class="font-mono font-medium">VPS' . $order['id'] . 'Pass!</p></div>';
            $html .= '</div>';
            $html .= '<div class="mt-4 p-3 bg-yellow-50 rounded-lg">';
            $html .= '<p class="text-sm text-yellow-800">Vui lòng thay đổi mật khẩu sau lần đăng nhập đầu tiên để đảm bảo bảo mật.</p>';
            $html .= '</div></div>';
        } else {
            $html .= '<div class="bg-green-50 rounded-lg p-4">';
            $html .= '<h3 class="font-semibold text-gray-900 mb-3 flex items-center space-x-2"><i class="fas fa-shield-alt text-green-600"></i><span>Thông tin truy cập Proxy</span></h3>';
            $html .= '<div class="grid grid-cols-1 md:grid-cols-2 gap-4">';
            $html .= '<div><p class="text-sm text-gray-500">Proxy IP</p><p class="font-mono font-medium">proxy' . $order['id'] . '.vpsvietnam.com</p></div>';
            $html .= '<div><p class="text-sm text-gray-500">Port</p><p class="font-mono font-medium">' . (1080 + $order['id']) . '</p></div>';
            $html .= '<div><p class="text-sm text-gray-500">Username</p><p class="font-mono font-medium">user' . $order['id'] . '</p></div>';
            $html .= '<div><p class="text-sm text-gray-500">Password</p><p class="font-mono font-medium">proxy' . $order['id'] . 'pass</p></div>';
            $html .= '</div>';
            $html .= '<div class="mt-4 p-3 bg-blue-50 rounded-lg">';
            $html .= '<p class="text-sm text-blue-800">Proxy sẽ hoạt động trong 30 ngày kể từ ngày kích hoạt. Liên hệ để gia hạn.</p>';
            $html .= '</div></div>';
        }
    }
    
    $html .= '</div>';
    
    echo json_encode(['success' => true, 'html' => $html]);
    
} catch (Exception $e) {
    error_log("Error in get_order_details.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
}
?>