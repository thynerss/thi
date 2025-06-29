<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirectTo('login.php');
}

$user = getUserById($_SESSION['user_id']);
$orders = getUserOrders($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đơn hàng của tôi - VPS & Proxy Việt Nam</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .card-hover { transition: all 0.3s ease; }
        .card-hover:hover { transform: translateY(-4px); box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <?php include 'includes/header.php'; ?>

    <div class="min-h-screen py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Đơn hàng của tôi</h1>
                <p class="text-gray-600 mt-2">Theo dõi và quản lý các đơn hàng VPS và Proxy của bạn</p>
            </div>

            <?php if (empty($orders)): ?>
            <div class="text-center py-12">
                <div class="bg-white rounded-2xl shadow-lg p-8">
                    <i class="fas fa-shopping-cart text-6xl text-gray-300 mb-4"></i>
                    <h3 class="text-xl font-medium text-gray-900 mb-2">Chưa có đơn hàng nào</h3>
                    <p class="text-gray-600 mb-6">Hãy bắt đầu bằng cách chọn một gói dịch vụ phù hợp với nhu cầu của bạn</p>
                    <a href="packages.php" class="inline-flex items-center space-x-2 bg-gradient-to-r from-blue-600 to-purple-600 text-white px-6 py-3 rounded-lg hover:from-blue-700 hover:to-purple-700 transition-all duration-200">
                        <i class="fas fa-server"></i>
                        <span>Xem gói dịch vụ</span>
                    </a>
                </div>
            </div>
            <?php else: ?>
            <div class="grid gap-6">
                <?php foreach ($orders as $index => $order): ?>
                <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden card-hover">
                    <div class="p-6">
                        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                            <div class="flex-1">
                                <div class="flex items-center space-x-3 mb-3">
                                    <i class="fas fa-<?php echo $order['package_type'] === 'vps' ? 'server' : 'shield-alt'; ?> text-2xl text-<?php echo $order['package_type'] === 'vps' ? 'blue' : 'green'; ?>-600"></i>
                                    <h3 class="text-xl font-semibold text-gray-900">
                                        <?php echo htmlspecialchars($order['package_name']); ?>
                                    </h3>
                                    <span class="px-3 py-1 rounded-full text-xs font-medium <?php echo getStatusColor($order['status']); ?>">
                                        <?php echo getStatusText($order['status']); ?>
                                    </span>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                                    <div>
                                        <p class="text-sm text-gray-500">Mã đơn hàng</p>
                                        <p class="font-medium text-gray-900"><?php echo htmlspecialchars($order['order_code']); ?></p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Số tiền</p>
                                        <p class="font-medium text-gray-900"><?php echo formatCurrency($order['amount']); ?></p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Ngày đặt</p>
                                        <p class="font-medium text-gray-900">
                                            <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?>
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Email</p>
                                        <p class="font-medium text-gray-900"><?php echo htmlspecialchars($order['customer_email']); ?></p>
                                    </div>
                                </div>

                                <?php if ($order['package_type'] === 'vps'): ?>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                                    <div class="flex items-center space-x-2">
                                        <span class="text-gray-500">CPU:</span>
                                        <span class="font-medium"><?php echo htmlspecialchars($order['cpu']); ?></span>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <span class="text-gray-500">RAM:</span>
                                        <span class="font-medium"><?php echo htmlspecialchars($order['ram']); ?></span>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <span class="text-gray-500">Lưu trữ:</span>
                                        <span class="font-medium"><?php echo htmlspecialchars($order['storage']); ?></span>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <span class="text-gray-500">Băng thông:</span>
                                        <span class="font-medium"><?php echo htmlspecialchars($order['bandwidth']); ?></span>
                                    </div>
                                </div>
                                <?php else: ?>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                                    <div class="flex items-center space-x-2">
                                        <span class="text-gray-500">Vị trí:</span>
                                        <span class="font-medium"><?php echo htmlspecialchars($order['location'] ?? 'N/A'); ?></span>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <span class="text-gray-500">Tốc độ:</span>
                                        <span class="font-medium"><?php echo htmlspecialchars($order['speed'] ?? 'N/A'); ?></span>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <span class="text-gray-500">Kết nối:</span>
                                        <span class="font-medium"><?php echo htmlspecialchars($order['concurrent_connections'] ?? 'N/A'); ?></span>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <span class="text-gray-500">Giao thức:</span>
                                        <span class="font-medium"><?php echo strtoupper($order['type'] ?? 'SOCKS5'); ?></span>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>

                            <div class="mt-4 lg:mt-0 lg:ml-6 flex flex-col items-end space-y-2">
                                <div class="flex items-center space-x-2">
                                    <?php echo getStatusIcon($order['status']); ?>
                                    <span class="text-sm font-medium text-gray-700">
                                        <?php echo getStatusText($order['status']); ?>
                                    </span>
                                </div>
                                
                                <button onclick="viewOrderDetails(<?php echo $order['id']; ?>)" class="flex items-center space-x-2 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors duration-200">
                                    <i class="fas fa-eye"></i>
                                    <span>Xem chi tiết</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <!-- Order Details Modal -->
    <div id="orderDetailsModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50 hidden">
        <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-900">Chi tiết đơn hàng</h2>
                    <button onclick="closeOrderDetailsModal()" class="p-2 hover:bg-gray-100 rounded-lg transition-colors duration-200">
                        <i class="fas fa-times text-gray-500"></i>
                    </button>
                </div>

                <div id="orderDetailsContent">
                    <!-- Order details will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <script>
        function viewOrderDetails(orderId) {
            fetch(`api/get_order_details.php?id=${orderId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('orderDetailsContent').innerHTML = data.html;
                        document.getElementById('orderDetailsModal').classList.remove('hidden');
                    } else {
                        alert('Không thể tải thông tin đơn hàng');
                    }
                })
                .catch(error => {
                    alert('Có lỗi xảy ra khi tải thông tin đơn hàng');
                });
        }

        function closeOrderDetailsModal() {
            document.getElementById('orderDetailsModal').classList.add('hidden');
        }

        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                alert('Đã sao chép: ' + text);
            });
        }
    </script>
</body>
</html>