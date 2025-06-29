<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is admin
if (!isAdmin()) {
    redirectTo('../login.php');
}

$orders = getAllOrders();

// Handle order actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_status') {
        $orderId = (int)$_POST['order_id'];
        $status = $_POST['status'];
        
        if (updateOrderStatus($orderId, $status)) {
            showAlert('Cập nhật trạng thái đơn hàng thành công!', 'success');
        } else {
            showAlert('Có lỗi xảy ra khi cập nhật trạng thái!', 'error');
        }
        redirectTo('orders.php');
    } elseif ($_POST['action'] === 'add_vps_info') {
        $orderId = (int)$_POST['order_id'];
        $serverIp = sanitizeInput($_POST['server_ip']);
        $username = sanitizeInput($_POST['username']);
        $password = sanitizeInput($_POST['password']);
        $sshPort = (int)$_POST['ssh_port'];
        $controlPanelUrl = sanitizeInput($_POST['control_panel_url']);
        $controlPanelUser = sanitizeInput($_POST['control_panel_user']);
        $controlPanelPass = sanitizeInput($_POST['control_panel_pass']);
        $osTemplate = sanitizeInput($_POST['os_template']);
        $datacenter = sanitizeInput($_POST['datacenter']);
        $additionalInfo = sanitizeInput($_POST['additional_info']);
        
        if (addVPSInfo($orderId, $serverIp, $username, $password, $sshPort, $controlPanelUrl, $controlPanelUser, $controlPanelPass, $osTemplate, $datacenter, $additionalInfo)) {
            updateOrderStatus($orderId, 'completed');
            sendVPSInfoEmail($orderId);
            showAlert('Thêm thông tin VPS và gửi email thành công!', 'success');
        } else {
            showAlert('Có lỗi xảy ra khi thêm thông tin VPS!', 'error');
        }
        redirectTo('orders.php');
    } elseif ($_POST['action'] === 'add_proxy_info') {
        $orderId = (int)$_POST['order_id'];
        $proxyIp = sanitizeInput($_POST['proxy_ip']);
        $proxyPort = (int)$_POST['proxy_port'];
        $username = sanitizeInput($_POST['username']);
        $password = sanitizeInput($_POST['password']);
        $protocol = sanitizeInput($_POST['protocol']);
        $location = sanitizeInput($_POST['location']);
        $additionalInfo = sanitizeInput($_POST['additional_info']);
        
        if (addProxyInfo($orderId, $proxyIp, $proxyPort, $username, $password, $protocol, $location, $additionalInfo)) {
            updateOrderStatus($orderId, 'completed');
            sendProxyInfoEmail($orderId);
            showAlert('Thêm thông tin Proxy và gửi email thành công!', 'success');
        } else {
            showAlert('Có lỗi xảy ra khi thêm thông tin Proxy!', 'error');
        }
        redirectTo('orders.php');
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý đơn hàng - VPS & Proxy Việt Nam</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .gradient-bg { background: linear-gradient(135deg, #1e3a8a 0%, #3730a3 50%, #7c3aed 100%); }
        .card-hover { transition: all 0.3s ease; }
        .card-hover:hover { transform: translateY(-4px); box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Admin Header -->
    <header class="bg-white shadow-lg border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center space-x-4">
                    <a href="../index.php" class="flex items-center space-x-2">
                        <div class="bg-gradient-to-r from-blue-600 to-purple-600 p-2 rounded-lg">
                            <i class="fas fa-server text-white"></i>
                        </div>
                        <span class="text-xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
                            VPS Admin
                        </span>
                    </a>
                </div>

                <nav class="hidden md:flex items-center space-x-8">
                    <a href="index.php" class="text-gray-700 hover:text-blue-600 transition-colors duration-200 font-medium">Dashboard</a>
                    <a href="orders.php" class="text-blue-600 font-medium">Đơn hàng</a>
                    <a href="topups.php" class="text-gray-700 hover:text-blue-600 transition-colors duration-200 font-medium">Nạp tiền</a>
                    <a href="users.php" class="text-gray-700 hover:text-blue-600 transition-colors duration-200 font-medium">Người dùng</a>
                    <a href="settings.php" class="text-gray-700 hover:text-blue-600 transition-colors duration-200 font-medium">Cài đặt</a>
                </nav>

                <div class="flex items-center space-x-4">
                    <a href="../index.php" class="text-sm text-blue-600 hover:text-blue-700 font-medium">Xem trang web</a>
                    <a href="../logout.php" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors duration-200">
                        <i class="fas fa-sign-out-alt mr-2"></i>Đăng xuất
                    </a>
                </div>
            </div>
        </div>
    </header>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Quản lý đơn hàng</h1>
            <p class="text-gray-600 mt-2">Quản lý tất cả đơn hàng VPS và Proxy</p>
        </div>

        <!-- Alert Messages -->
        <?php $alert = getAlert(); if ($alert): ?>
        <div class="bg-<?php echo $alert['type'] === 'success' ? 'green' : 'red'; ?>-50 border border-<?php echo $alert['type'] === 'success' ? 'green' : 'red'; ?>-200 rounded-lg p-4 mb-6">
            <div class="flex items-center space-x-2">
                <i class="fas fa-<?php echo $alert['type'] === 'success' ? 'check-circle' : 'exclamation-circle'; ?> text-<?php echo $alert['type'] === 'success' ? 'green' : 'red'; ?>-500"></i>
                <span class="text-<?php echo $alert['type'] === 'success' ? 'green' : 'red'; ?>-700 font-medium"><?php echo htmlspecialchars($alert['message']); ?></span>
            </div>
        </div>
        <?php endif; ?>

        <!-- Filter Tabs -->
        <div class="mb-6">
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-8">
                    <button onclick="filterOrders('all')" id="tab-all" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm transition-colors duration-200">
                        Tất cả
                    </button>
                    <button onclick="filterOrders('pending')" id="tab-pending" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm transition-colors duration-200">
                        Chờ xử lý
                    </button>
                    <button onclick="filterOrders('processing')" id="tab-processing" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm transition-colors duration-200">
                        Đang xử lý
                    </button>
                    <button onclick="filterOrders('completed')" id="tab-completed" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm transition-colors duration-200">
                        Hoàn thành
                    </button>
                </nav>
            </div>
        </div>

        <!-- Orders Table -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-900">Danh sách đơn hàng</h2>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mã đơn hàng</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Khách hàng</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dịch vụ</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Số tiền</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lợi nhuận</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trạng thái</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ngày tạo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($orders as $order): ?>
                        <tr class="hover:bg-gray-50 order-row" data-status="<?php echo $order['status']; ?>">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="font-mono text-sm font-medium text-gray-900"><?php echo htmlspecialchars($order['order_code']); ?></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div>
                                    <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($order['username']); ?></div>
                                    <div class="text-sm text-gray-500"><?php echo htmlspecialchars($order['customer_email']); ?></div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div>
                                    <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($order['package_name']); ?></div>
                                    <div class="text-sm text-gray-500">
                                        <span class="px-2 py-1 rounded-full text-xs font-medium <?php echo $order['package_type'] === 'vps' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'; ?>">
                                            <?php echo strtoupper($order['package_type']); ?>
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?php echo formatCurrency($order['amount']); ?></div>
                                <?php if ($order['cost_price'] > 0): ?>
                                <div class="text-sm text-gray-500">Vốn: <?php echo formatCurrency($order['cost_price']); ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if ($order['profit'] > 0): ?>
                                <div class="text-sm font-medium text-green-600">+<?php echo formatCurrency($order['profit']); ?></div>
                                <?php else: ?>
                                <div class="text-sm text-gray-500">-</div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 rounded-full text-xs font-medium <?php echo getStatusColor($order['status']); ?>">
                                    <?php echo getStatusText($order['status']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <button onclick="updateOrderStatus(<?php echo $order['id']; ?>, '<?php echo $order['status']; ?>')" 
                                            class="text-blue-600 hover:text-blue-900" title="Cập nhật trạng thái">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    
                                    <?php if ($order['status'] === 'processing' && $order['package_type'] === 'vps'): ?>
                                    <button onclick="addVPSInfo(<?php echo $order['id']; ?>)" 
                                            class="text-green-600 hover:text-green-900" title="Thêm thông tin VPS">
                                        <i class="fas fa-server"></i>
                                    </button>
                                    <?php endif; ?>
                                    
                                    <?php if ($order['status'] === 'processing' && $order['package_type'] === 'proxy'): ?>
                                    <button onclick="addProxyInfo(<?php echo $order['id']; ?>)" 
                                            class="text-purple-600 hover:text-purple-900" title="Thêm thông tin Proxy">
                                        <i class="fas fa-shield-alt"></i>
                                    </button>
                                    <?php endif; ?>
                                    
                                    <button onclick="viewOrderDetails(<?php echo $order['id']; ?>)" 
                                            class="text-gray-600 hover:text-gray-900" title="Xem chi tiết">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    
                                    <button onclick="sendEmailManual(<?php echo $order['id']; ?>)" 
                                            class="text-orange-600 hover:text-orange-900" title="Gửi email">
                                        <i class="fas fa-envelope"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Update Status Modal -->
    <div id="statusModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50 hidden">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full">
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-gray-900">Cập nhật trạng thái đơn hàng</h2>
                    <button onclick="closeStatusModal()" class="p-2 hover:bg-gray-100 rounded-lg">
                        <i class="fas fa-times text-gray-500"></i>
                    </button>
                </div>

                <form method="POST">
                    <input type="hidden" name="action" value="update_status">
                    <input type="hidden" name="order_id" id="modal_order_id">
                    
                    <div class="mb-6">
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Trạng thái mới</label>
                        <select name="status" id="modal_status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="pending">Chờ xử lý</option>
                            <option value="processing">Đang xử lý</option>
                            <option value="completed">Hoàn thành</option>
                            <option value="cancelled">Đã hủy</option>
                        </select>
                    </div>

                    <div class="flex space-x-3">
                        <button type="button" onclick="closeStatusModal()" class="flex-1 py-2 px-4 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                            Hủy
                        </button>
                        <button type="submit" class="flex-1 bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700">
                            Cập nhật
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add VPS Info Modal -->
    <div id="vpsInfoModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50 hidden">
        <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-gray-900">Thêm thông tin VPS</h2>
                    <button onclick="closeVPSInfoModal()" class="p-2 hover:bg-gray-100 rounded-lg">
                        <i class="fas fa-times text-gray-500"></i>
                    </button>
                </div>

                <form method="POST">
                    <input type="hidden" name="action" value="add_vps_info">
                    <input type="hidden" name="order_id" id="vps_order_id">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="server_ip" class="block text-sm font-medium text-gray-700 mb-2">IP Server *</label>
                            <input type="text" name="server_ip" id="server_ip" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="192.168.1.100">
                        </div>
                        
                        <div>
                            <label for="ssh_port" class="block text-sm font-medium text-gray-700 mb-2">SSH Port</label>
                            <input type="number" name="ssh_port" id="ssh_port" value="22"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="username" class="block text-sm font-medium text-gray-700 mb-2">Username *</label>
                            <input type="text" name="username" id="username" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="root">
                        </div>
                        
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password *</label>
                            <input type="text" name="password" id="password" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="Mật khẩu mạnh">
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="control_panel_url" class="block text-sm font-medium text-gray-700 mb-2">Control Panel URL</label>
                            <input type="url" name="control_panel_url" id="control_panel_url"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="https://panel.example.com">
                        </div>
                        
                        <div>
                            <label for="os_template" class="block text-sm font-medium text-gray-700 mb-2">OS Template</label>
                            <select name="os_template" id="os_template" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="Ubuntu 20.04">Ubuntu 20.04</option>
                                <option value="Ubuntu 22.04">Ubuntu 22.04</option>
                                <option value="CentOS 8">CentOS 8</option>
                                <option value="Debian 11">Debian 11</option>
                                <option value="Windows Server 2019">Windows Server 2019</option>
                                <option value="Windows Server 2022">Windows Server 2022</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="control_panel_user" class="block text-sm font-medium text-gray-700 mb-2">Control Panel User</label>
                            <input type="text" name="control_panel_user" id="control_panel_user"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="admin">
                        </div>
                        
                        <div>
                            <label for="control_panel_pass" class="block text-sm font-medium text-gray-700 mb-2">Control Panel Password</label>
                            <input type="text" name="control_panel_pass" id="control_panel_pass"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="Mật khẩu panel">
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="datacenter" class="block text-sm font-medium text-gray-700 mb-2">Datacenter</label>
                        <input type="text" name="datacenter" id="datacenter"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="Singapore, Tokyo, New York...">
                    </div>
                    
                    <div class="mb-6">
                        <label for="additional_info" class="block text-sm font-medium text-gray-700 mb-2">Thông tin bổ sung</label>
                        <textarea name="additional_info" id="additional_info" rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                  placeholder="Ghi chú thêm về VPS..."></textarea>
                    </div>

                    <div class="flex space-x-3">
                        <button type="button" onclick="closeVPSInfoModal()" class="flex-1 py-2 px-4 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                            Hủy
                        </button>
                        <button type="submit" class="flex-1 bg-green-600 text-white py-2 px-4 rounded-lg hover:bg-green-700">
                            Lưu & Gửi Email
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Proxy Info Modal -->
    <div id="proxyInfoModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50 hidden">
        <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-gray-900">Thêm thông tin Proxy</h2>
                    <button onclick="closeProxyInfoModal()" class="p-2 hover:bg-gray-100 rounded-lg">
                        <i class="fas fa-times text-gray-500"></i>
                    </button>
                </div>

                <form method="POST">
                    <input type="hidden" name="action" value="add_proxy_info">
                    <input type="hidden" name="order_id" id="proxy_order_id">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="proxy_ip" class="block text-sm font-medium text-gray-700 mb-2">Proxy IP *</label>
                            <input type="text" name="proxy_ip" id="proxy_ip" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="proxy.example.com">
                        </div>
                        
                        <div>
                            <label for="proxy_port" class="block text-sm font-medium text-gray-700 mb-2">Port *</label>
                            <input type="number" name="proxy_port" id="proxy_port" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="1080">
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="proxy_username" class="block text-sm font-medium text-gray-700 mb-2">Username *</label>
                            <input type="text" name="username" id="proxy_username" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="user123">
                        </div>
                        
                        <div>
                            <label for="proxy_password" class="block text-sm font-medium text-gray-700 mb-2">Password *</label>
                            <input type="text" name="password" id="proxy_password" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="Mật khẩu proxy">
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="protocol" class="block text-sm font-medium text-gray-700 mb-2">Protocol</label>
                            <select name="protocol" id="protocol" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="socks5">SOCKS5</option>
                                <option value="http">HTTP</option>
                                <option value="https">HTTPS</option>
                            </select>
                        </div>
                        
                        <div>
                            <label for="proxy_location" class="block text-sm font-medium text-gray-700 mb-2">Location</label>
                            <input type="text" name="location" id="proxy_location"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="Vietnam, Singapore, USA...">
                        </div>
                    </div>
                    
                    <div class="mb-6">
                        <label for="proxy_additional_info" class="block text-sm font-medium text-gray-700 mb-2">Thông tin bổ sung</label>
                        <textarea name="additional_info" id="proxy_additional_info" rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                  placeholder="Ghi chú thêm về Proxy..."></textarea>
                    </div>

                    <div class="flex space-x-3">
                        <button type="button" onclick="closeProxyInfoModal()" class="flex-1 py-2 px-4 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                            Hủy
                        </button>
                        <button type="submit" class="flex-1 bg-purple-600 text-white py-2 px-4 rounded-lg hover:bg-purple-700">
                            Lưu & Gửi Email
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Filter orders by status
        function filterOrders(status) {
            const rows = document.querySelectorAll('.order-row');
            const tabs = document.querySelectorAll('[id^="tab-"]');
            
            // Update tab styles
            tabs.forEach(tab => {
                tab.className = 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm transition-colors duration-200';
            });
            document.getElementById('tab-' + status).className = 'border-blue-500 text-blue-600 whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm';
            
            // Filter rows
            rows.forEach(row => {
                if (status === 'all' || row.dataset.status === status) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        function updateOrderStatus(orderId, currentStatus) {
            document.getElementById('modal_order_id').value = orderId;
            document.getElementById('modal_status').value = currentStatus;
            document.getElementById('statusModal').classList.remove('hidden');
        }

        function closeStatusModal() {
            document.getElementById('statusModal').classList.add('hidden');
        }

        function addVPSInfo(orderId) {
            document.getElementById('vps_order_id').value = orderId;
            document.getElementById('vpsInfoModal').classList.remove('hidden');
        }

        function closeVPSInfoModal() {
            document.getElementById('vpsInfoModal').classList.add('hidden');
        }

        function addProxyInfo(orderId) {
            document.getElementById('proxy_order_id').value = orderId;
            document.getElementById('proxyInfoModal').classList.remove('hidden');
        }

        function closeProxyInfoModal() {
            document.getElementById('proxyInfoModal').classList.add('hidden');
        }

        function viewOrderDetails(orderId) {
            // Open order details in new window or modal
            window.open('../api/get_order_details.php?id=' + orderId, '_blank');
        }

        function sendEmailManual(orderId) {
            if (confirm('Bạn có chắc muốn gửi lại email cho đơn hàng này?')) {
                fetch('../api/send_order_email.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'order_id=' + orderId
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Gửi email thành công!');
                    } else {
                        alert('Lỗi: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('Có lỗi xảy ra khi gửi email');
                });
            }
        }

        // Initialize with all orders
        filterOrders('all');
    </script>
</body>
</html>