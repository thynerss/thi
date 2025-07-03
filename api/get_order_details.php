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
        // Get real VPS/Proxy info from database
        if ($order['package_type'] === 'vps') {
            $vpsInfo = getVPSInfo($orderId);
            if ($vpsInfo) {
                $html .= '<div class="bg-green-50 rounded-lg p-4">';
                $html .= '<h3 class="font-semibold text-gray-900 mb-3 flex items-center space-x-2"><i class="fas fa-server text-green-600"></i><span>Thông tin truy cập VPS</span></h3>';
                $html .= '<div class="grid grid-cols-1 md:grid-cols-2 gap-4">';
                $html .= '<div><p class="text-sm text-gray-500">IP Server</p><div class="flex items-center space-x-2"><p class="font-mono font-medium">' . htmlspecialchars($vpsInfo['server_ip']) . '</p><button onclick="copyToClipboard(\'' . htmlspecialchars($vpsInfo['server_ip']) . '\')" class="text-blue-600 hover:text-blue-800"><i class="fas fa-copy"></i></button></div></div>';
                $html .= '<div><p class="text-sm text-gray-500">SSH Port</p><p class="font-mono font-medium">' . htmlspecialchars($vpsInfo['ssh_port']) . '</p></div>';
                $html .= '<div><p class="text-sm text-gray-500">Username</p><div class="flex items-center space-x-2"><p class="font-mono font-medium">' . htmlspecialchars($vpsInfo['username']) . '</p><button onclick="copyToClipboard(\'' . htmlspecialchars($vpsInfo['username']) . '\')" class="text-blue-600 hover:text-blue-800"><i class="fas fa-copy"></i></button></div></div>';
                $html .= '<div><p class="text-sm text-gray-500">Password</p><div class="flex items-center space-x-2"><p class="font-mono font-medium" id="password-' . $orderId . '">••••••••</p><button onclick="togglePassword(' . $orderId . ', \'' . htmlspecialchars($vpsInfo['password']) . '\')" class="text-blue-600 hover:text-blue-800"><i class="fas fa-eye" id="eye-' . $orderId . '"></i></button><button onclick="copyToClipboard(\'' . htmlspecialchars($vpsInfo['password']) . '\')" class="text-blue-600 hover:text-blue-800"><i class="fas fa-copy"></i></button></div></div>';
                
                if ($vpsInfo['os_template']) {
                    $html .= '<div><p class="text-sm text-gray-500">Hệ điều hành</p><p class="font-medium">' . htmlspecialchars($vpsInfo['os_template']) . '</p></div>';
                }
                if ($vpsInfo['datacenter']) {
                    $html .= '<div><p class="text-sm text-gray-500">Datacenter</p><p class="font-medium">' . htmlspecialchars($vpsInfo['datacenter']) . '</p></div>';
                }
                
                $html .= '</div>';
                
                if ($vpsInfo['control_panel_url']) {
                    $html .= '<div class="mt-4 p-3 bg-blue-50 rounded-lg">';
                    $html .= '<h4 class="font-semibold text-blue-900 mb-2">Control Panel</h4>';
                    $html .= '<p class="text-sm text-blue-800">URL: <a href="' . htmlspecialchars($vpsInfo['control_panel_url']) . '" target="_blank" class="underline">' . htmlspecialchars($vpsInfo['control_panel_url']) . '</a></p>';
                    if ($vpsInfo['control_panel_user']) {
                        $html .= '<p class="text-sm text-blue-800">Username: <span class="font-mono">' . htmlspecialchars($vpsInfo['control_panel_user']) . '</span></p>';
                    }
                    if ($vpsInfo['control_panel_pass']) {
                        $html .= '<p class="text-sm text-blue-800">Password: <span class="font-mono">' . htmlspecialchars($vpsInfo['control_panel_pass']) . '</span></p>';
                    }
                    $html .= '</div>';
                }
                
                if ($vpsInfo['additional_info']) {
                    $html .= '<div class="mt-4 p-3 bg-gray-50 rounded-lg">';
                    $html .= '<h4 class="font-semibold text-gray-900 mb-2">Thông tin bổ sung</h4>';
                    $html .= '<p class="text-sm text-gray-700">' . nl2br(htmlspecialchars($vpsInfo['additional_info'])) . '</p>';
                    $html .= '</div>';
                }
                
                $html .= '<div class="mt-4 p-3 bg-yellow-50 rounded-lg">';
                $html .= '<p class="text-sm text-yellow-800"><i class="fas fa-exclamation-triangle mr-1"></i>Vui lòng thay đổi mật khẩu sau lần đăng nhập đầu tiên để đảm bảo bảo mật.</p>';
                $html .= '</div></div>';
            } else {
                $html .= '<div class="bg-yellow-50 rounded-lg p-4">';
                $html .= '<h3 class="font-semibold text-yellow-900 mb-2">Đang chuẩn bị thông tin VPS</h3>';
                $html .= '<p class="text-yellow-800 text-sm">Thông tin truy cập VPS đang được chuẩn bị. Bạn sẽ nhận được email khi hoàn tất.</p>';
                $html .= '</div>';
            }
        } else {
            $proxyInfo = getProxyInfo($orderId);
            if ($proxyInfo) {
                $html .= '<div class="bg-green-50 rounded-lg p-4">';
                $html .= '<h3 class="font-semibold text-gray-900 mb-3 flex items-center space-x-2"><i class="fas fa-shield-alt text-green-600"></i><span>Thông tin truy cập Proxy</span></h3>';
                $html .= '<div class="grid grid-cols-1 md:grid-cols-2 gap-4">';
                $html .= '<div><p class="text-sm text-gray-500">Proxy IP</p><div class="flex items-center space-x-2"><p class="font-mono font-medium">' . htmlspecialchars($proxyInfo['proxy_ip']) . '</p><button onclick="copyToClipboard(\'' . htmlspecialchars($proxyInfo['proxy_ip']) . '\')" class="text-blue-600 hover:text-blue-800"><i class="fas fa-copy"></i></button></div></div>';
                $html .= '<div><p class="text-sm text-gray-500">Port</p><p class="font-mono font-medium">' . htmlspecialchars($proxyInfo['proxy_port']) . '</p></div>';
                $html .= '<div><p class="text-sm text-gray-500">Username</p><div class="flex items-center space-x-2"><p class="font-mono font-medium">' . htmlspecialchars($proxyInfo['username']) . '</p><button onclick="copyToClipboard(\'' . htmlspecialchars($proxyInfo['username']) . '\')" class="text-blue-600 hover:text-blue-800"><i class="fas fa-copy"></i></button></div></div>';
                $html .= '<div><p class="text-sm text-gray-500">Password</p><div class="flex items-center space-x-2"><p class="font-mono font-medium" id="proxy-password-' . $orderId . '">••••••••</p><button onclick="toggleProxyPassword(' . $orderId . ', \'' . htmlspecialchars($proxyInfo['password']) . '\')" class="text-blue-600 hover:text-blue-800"><i class="fas fa-eye" id="proxy-eye-' . $orderId . '"></i></button><button onclick="copyToClipboard(\'' . htmlspecialchars($proxyInfo['password']) . '\')" class="text-blue-600 hover:text-blue-800"><i class="fas fa-copy"></i></button></div></div>';
                $html .= '<div><p class="text-sm text-gray-500">Giao thức</p><p class="font-medium">' . strtoupper($proxyInfo['protocol']) . '</p></div>';
                if ($proxyInfo['location']) {
                    $html .= '<div><p class="text-sm text-gray-500">Vị trí</p><p class="font-medium">' . htmlspecialchars($proxyInfo['location']) . '</p></div>';
                }
                $html .= '</div>';
                
                if ($proxyInfo['additional_info']) {
                    $html .= '<div class="mt-4 p-3 bg-gray-50 rounded-lg">';
                    $html .= '<h4 class="font-semibold text-gray-900 mb-2">Thông tin bổ sung</h4>';
                    $html .= '<p class="text-sm text-gray-700">' . nl2br(htmlspecialchars($proxyInfo['additional_info'])) . '</p>';
                    $html .= '</div>';
                }
                
                $html .= '<div class="mt-4 p-3 bg-blue-50 rounded-lg">';
                $html .= '<p class="text-sm text-blue-800"><i class="fas fa-info-circle mr-1"></i>Proxy sẽ hoạt động trong 30 ngày kể từ ngày kích hoạt. Liên hệ để gia hạn.</p>';
                $html .= '</div></div>';
            } else {
                $html .= '<div class="bg-yellow-50 rounded-lg p-4">';
                $html .= '<h3 class="font-semibold text-yellow-900 mb-2">Đang chuẩn bị thông tin Proxy</h3>';
                $html .= '<p class="text-yellow-800 text-sm">Thông tin truy cập Proxy đang được chuẩn bị. Bạn sẽ nhận được email khi hoàn tất.</p>';
                $html .= '</div>';
            }
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