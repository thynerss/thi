<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is admin
if (!isAdmin()) {
    redirectTo('../login.php');
}

$stats = getDashboardStats();
$recentOrders = array_slice(getAllOrders(), 0, 5);
$pendingTopUps = array_slice(getAllTopUps(), 0, 5);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - VPS & Proxy Việt Nam</title>
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
                    <a href="index.php" class="text-blue-600 font-medium">Dashboard</a>
                    <a href="orders.php" class="text-gray-700 hover:text-blue-600 transition-colors duration-200 font-medium">Đơn hàng</a>
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
            <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
            <p class="text-gray-600 mt-2">Tổng quan hệ thống VPS & Proxy Việt Nam</p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-lg p-6 card-hover">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-medium">Tổng người dùng</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1"><?php echo number_format($stats['totalUsers']); ?></p>
                        <div class="flex items-center mt-2">
                            <i class="fas fa-arrow-up text-green-500 mr-1"></i>
                            <span class="text-green-500 text-sm font-medium">+12%</span>
                            <span class="text-gray-500 text-sm ml-1">so với tháng trước</span>
                        </div>
                    </div>
                    <div class="bg-blue-500 p-3 rounded-lg">
                        <i class="fas fa-users text-white"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6 card-hover">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-medium">Tổng đơn hàng</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1"><?php echo number_format($stats['totalOrders']); ?></p>
                        <div class="flex items-center mt-2">
                            <i class="fas fa-arrow-up text-green-500 mr-1"></i>
                            <span class="text-green-500 text-sm font-medium">+8%</span>
                            <span class="text-gray-500 text-sm ml-1">so với tháng trước</span>
                        </div>
                    </div>
                    <div class="bg-green-500 p-3 rounded-lg">
                        <i class="fas fa-shopping-cart text-white"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6 card-hover">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-medium">Tổng doanh thu</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1"><?php echo formatCurrency($stats['totalRevenue']); ?></p>
                        <div class="flex items-center mt-2">
                            <i class="fas fa-arrow-up text-green-500 mr-1"></i>
                            <span class="text-green-500 text-sm font-medium">+15%</span>
                            <span class="text-gray-500 text-sm ml-1">so với tháng trước</span>
                        </div>
                    </div>
                    <div class="bg-purple-500 p-3 rounded-lg">
                        <i class="fas fa-dollar-sign text-white"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6 card-hover">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-medium">Dịch vụ hoạt động</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1"><?php echo number_format($stats['activeServices']); ?></p>
                        <div class="flex items-center mt-2">
                            <i class="fas fa-arrow-up text-green-500 mr-1"></i>
                            <span class="text-green-500 text-sm font-medium">+5%</span>
                            <span class="text-gray-500 text-sm ml-1">so với tháng trước</span>
                        </div>
                    </div>
                    <div class="bg-orange-500 p-3 rounded-lg">
                        <i class="fas fa-server text-white"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Recent Orders -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold text-gray-900">Đơn hàng gần đây</h2>
                    <span class="bg-red-100 text-red-800 px-3 py-1 rounded-full text-sm font-medium">
                        <?php echo $stats['pendingOrders']; ?> chờ xử lý
                    </span>
                </div>
                
                <div class="space-y-4">
                    <?php foreach ($recentOrders as $order): ?>
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div class="flex items-center space-x-3">
                            <?php echo getStatusIcon($order['status']); ?>
                            <div>
                                <p class="font-medium text-gray-900"><?php echo htmlspecialchars($order['order_code']); ?></p>
                                <p class="text-sm text-gray-600"><?php echo htmlspecialchars($order['username']); ?> • <?php echo formatCurrency($order['amount']); ?></p>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="px-2 py-1 rounded-full text-xs font-medium <?php echo getStatusColor($order['status']); ?>">
                                <?php echo getStatusText($order['status']); ?>
                            </span>
                            <p class="text-xs text-gray-500 mt-1">
                                <?php echo date('d/m/Y', strtotime($order['created_at'])); ?>
                            </p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="mt-4">
                    <a href="orders.php" class="text-blue-600 hover:text-blue-700 font-medium text-sm">
                        Xem tất cả đơn hàng →
                    </a>
                </div>
            </div>

            <!-- Pending Top-ups -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold text-gray-900">Yêu cầu nạp tiền</h2>
                    <span class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-sm font-medium">
                        <?php echo $stats['pendingTopUps']; ?> chờ duyệt
                    </span>
                </div>
                
                <div class="space-y-4">
                    <?php foreach ($pendingTopUps as $topup): ?>
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div class="flex items-center space-x-3">
                            <i class="fas fa-dollar-sign text-green-500"></i>
                            <div>
                                <p class="font-medium text-gray-900"><?php echo htmlspecialchars($topup['username']); ?></p>
                                <p class="text-sm text-gray-600"><?php echo formatCurrency($topup['amount']); ?> • <?php echo htmlspecialchars($topup['payment_method']); ?></p>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="px-2 py-1 rounded-full text-xs font-medium <?php echo getStatusColor($topup['status']); ?>">
                                <?php echo getStatusText($topup['status']); ?>
                            </span>
                            <p class="text-xs text-gray-500 mt-1">
                                <?php echo date('d/m/Y', strtotime($topup['created_at'])); ?>
                            </p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="mt-4">
                    <a href="topups.php" class="text-blue-600 hover:text-blue-700 font-medium text-sm">
                        Xem tất cả yêu cầu →
                    </a>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="gradient-bg rounded-xl shadow-lg p-6 text-white">
            <h2 class="text-xl font-bold mb-4">Tổng quan nhanh</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="text-center">
                    <div class="text-3xl font-bold"><?php echo $stats['pendingOrders']; ?></div>
                    <div class="text-blue-100">Đơn hàng cần xử lý</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold"><?php echo $stats['pendingTopUps']; ?></div>
                    <div class="text-blue-100">Nạp tiền cần duyệt</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold"><?php echo $stats['totalOrders'] > 0 ? number_format(($stats['activeServices'] / $stats['totalOrders']) * 100, 1) : 0; ?>%</div>
                    <div class="text-blue-100">Tỷ lệ thành công</div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>