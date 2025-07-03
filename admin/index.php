<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is admin
if (!isAdmin()) {
    redirectTo('../login.php');
}

$pageTitle = 'Dashboard';
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .gradient-bg { background: linear-gradient(135deg, #1e3a8a 0%, #3730a3 50%, #7c3aed 100%); }
        .card-hover { transition: all 0.3s ease; }
        .card-hover:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(0,0,0,0.1); }
        .stat-card { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .stat-card-2 { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
        .stat-card-3 { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
        .stat-card-4 { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); }
        .chart-container { position: relative; height: 300px; }
        .loading-spinner {
            border: 3px solid #f3f4f6;
            border-top: 3px solid #3b82f6;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Sidebar -->
    <?php include 'layout/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="lg:ml-64">
        <!-- Header -->
        <?php include 'layout/header.php'; ?>

        <!-- Content -->
        <main class="p-6">
            <!-- Welcome Section -->
            <div class="mb-8">
                <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-2xl p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-2xl font-bold mb-2">Chào mừng trở lại! 👋</h2>
                            <p class="text-blue-100">Tổng quan hệ thống VPS & Proxy Việt Nam hôm nay</p>
                        </div>
                        <div class="hidden md:block">
                            <div class="bg-white bg-opacity-20 rounded-lg p-4">
                                <i class="fas fa-chart-line text-3xl"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="stat-card rounded-2xl p-6 text-white card-hover">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-blue-100 text-sm font-medium">Tổng người dùng</p>
                            <p class="text-3xl font-bold mt-2"><?php echo number_format($stats['totalUsers']); ?></p>
                            <div class="flex items-center mt-3">
                                <i class="fas fa-arrow-up text-green-300 mr-1"></i>
                                <span class="text-green-300 text-sm font-medium">+12%</span>
                                <span class="text-blue-200 text-sm ml-1">tháng này</span>
                            </div>
                        </div>
                        <div class="bg-white bg-opacity-20 p-4 rounded-xl">
                            <i class="fas fa-users text-2xl"></i>
                        </div>
                    </div>
                </div>

                <div class="stat-card-2 rounded-2xl p-6 text-white card-hover">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-pink-100 text-sm font-medium">Tổng đơn hàng</p>
                            <p class="text-3xl font-bold mt-2"><?php echo number_format($stats['totalOrders']); ?></p>
                            <div class="flex items-center mt-3">
                                <i class="fas fa-arrow-up text-green-300 mr-1"></i>
                                <span class="text-green-300 text-sm font-medium">+8%</span>
                                <span class="text-pink-200 text-sm ml-1">tháng này</span>
                            </div>
                        </div>
                        <div class="bg-white bg-opacity-20 p-4 rounded-xl">
                            <i class="fas fa-shopping-cart text-2xl"></i>
                        </div>
                    </div>
                </div>

                <div class="stat-card-3 rounded-2xl p-6 text-white card-hover">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-blue-100 text-sm font-medium">Tổng doanh thu</p>
                            <p class="text-3xl font-bold mt-2"><?php echo formatCurrency($stats['totalRevenue']); ?></p>
                            <div class="flex items-center mt-3">
                                <i class="fas fa-arrow-up text-green-300 mr-1"></i>
                                <span class="text-green-300 text-sm font-medium">+15%</span>
                                <span class="text-blue-200 text-sm ml-1">tháng này</span>
                            </div>
                        </div>
                        <div class="bg-white bg-opacity-20 p-4 rounded-xl">
                            <i class="fas fa-dollar-sign text-2xl"></i>
                        </div>
                    </div>
                </div>

                <div class="stat-card-4 rounded-2xl p-6 text-white card-hover">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-green-100 text-sm font-medium">Dịch vụ hoạt động</p>
                            <p class="text-3xl font-bold mt-2"><?php echo number_format($stats['activeServices']); ?></p>
                            <div class="flex items-center mt-3">
                                <i class="fas fa-arrow-up text-green-300 mr-1"></i>
                                <span class="text-green-300 text-sm font-medium">+5%</span>
                                <span class="text-green-200 text-sm ml-1">tháng này</span>
                            </div>
                        </div>
                        <div class="bg-white bg-opacity-20 p-4 rounded-xl">
                            <i class="fas fa-server text-2xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts and Recent Activity -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <!-- Revenue Chart -->
                <div class="bg-white rounded-2xl shadow-lg p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-bold text-gray-900">Doanh thu 7 ngày qua</h3>
                        <div class="flex items-center space-x-2">
                            <div class="w-3 h-3 bg-blue-500 rounded-full"></div>
                            <span class="text-sm text-gray-600">Doanh thu</span>
                        </div>
                    </div>
                    <div class="chart-container">
                        <div id="revenueChartLoading" class="flex items-center justify-center h-full">
                            <div class="loading-spinner"></div>
                        </div>
                        <canvas id="revenueChart" style="display: none;"></canvas>
                    </div>
                </div>

                <!-- Order Status Chart -->
                <div class="bg-white rounded-2xl shadow-lg p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-bold text-gray-900">Trạng thái đơn hàng</h3>
                        <select class="text-sm border border-gray-300 rounded-lg px-3 py-1">
                            <option>Tháng này</option>
                            <option>Tuần này</option>
                            <option>Hôm nay</option>
                        </select>
                    </div>
                    <div class="chart-container">
                        <div id="orderChartLoading" class="flex items-center justify-center h-full">
                            <div class="loading-spinner"></div>
                        </div>
                        <canvas id="orderChart" style="display: none;"></canvas>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Recent Orders -->
                <div class="bg-white rounded-2xl shadow-lg p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-bold text-gray-900">Đơn hàng gần đây</h3>
                        <span class="bg-red-100 text-red-800 px-3 py-1 rounded-full text-sm font-medium">
                            <?php echo $stats['pendingOrders']; ?> chờ xử lý
                        </span>
                    </div>
                    
                    <div class="space-y-4">
                        <?php foreach ($recentOrders as $order): ?>
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl hover:bg-gray-100 transition-colors duration-200">
                            <div class="flex items-center space-x-3">
                                <div class="bg-blue-100 p-2 rounded-lg">
                                    <?php echo getStatusIcon($order['status']); ?>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900"><?php echo htmlspecialchars($order['order_code']); ?></p>
                                    <p class="text-sm text-gray-600"><?php echo htmlspecialchars($order['username']); ?> • <?php echo formatCurrency($order['amount']); ?></p>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="px-3 py-1 rounded-full text-xs font-medium <?php echo getStatusColor($order['status']); ?>">
                                    <?php echo getStatusText($order['status']); ?>
                                </span>
                                <p class="text-xs text-gray-500 mt-1">
                                    <?php echo date('d/m/Y', strtotime($order['created_at'])); ?>
                                </p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="mt-6">
                        <a href="orders.php" class="text-blue-600 hover:text-blue-700 font-medium text-sm flex items-center">
                            Xem tất cả đơn hàng 
                            <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                </div>

                <!-- Pending Top-ups -->
                <div class="bg-white rounded-2xl shadow-lg p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-bold text-gray-900">Yêu cầu nạp tiền</h3>
                        <span class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-sm font-medium">
                            <?php echo $stats['pendingTopUps']; ?> chờ duyệt
                        </span>
                    </div>
                    
                    <div class="space-y-4">
                        <?php foreach ($pendingTopUps as $topup): ?>
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl hover:bg-gray-100 transition-colors duration-200">
                            <div class="flex items-center space-x-3">
                                <div class="bg-green-100 p-2 rounded-lg">
                                    <i class="fas fa-dollar-sign text-green-600"></i>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900"><?php echo htmlspecialchars($topup['username']); ?></p>
                                    <p class="text-sm text-gray-600"><?php echo formatCurrency($topup['amount']); ?> • <?php echo htmlspecialchars($topup['payment_method']); ?></p>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="px-3 py-1 rounded-full text-xs font-medium <?php echo getStatusColor($topup['status']); ?>">
                                    <?php echo getStatusText($topup['status']); ?>
                                </span>
                                <p class="text-xs text-gray-500 mt-1">
                                    <?php echo date('d/m/Y', strtotime($topup['created_at'])); ?>
                                </p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="mt-6">
                        <a href="topups.php" class="text-blue-600 hover:text-blue-700 font-medium text-sm flex items-center">
                            Xem tất cả yêu cầu 
                            <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="mt-8 bg-white rounded-2xl shadow-lg p-6">
                <h3 class="text-xl font-bold text-gray-900 mb-6">Thao tác nhanh</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <a href="create-order.php" class="flex items-center space-x-3 p-4 bg-blue-50 hover:bg-blue-100 rounded-xl transition-colors duration-200">
                        <div class="bg-blue-600 p-3 rounded-lg">
                            <i class="fas fa-plus text-white"></i>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">Tạo đơn hàng</p>
                            <p class="text-sm text-gray-600">Tạo đơn hàng mới</p>
                        </div>
                    </a>

                    <a href="vps-packages.php" class="flex items-center space-x-3 p-4 bg-green-50 hover:bg-green-100 rounded-xl transition-colors duration-200">
                        <div class="bg-green-600 p-3 rounded-lg">
                            <i class="fas fa-server text-white"></i>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">Quản lý VPS</p>
                            <p class="text-sm text-gray-600">Gói VPS & cấu hình</p>
                        </div>
                    </a>

                    <a href="users.php" class="flex items-center space-x-3 p-4 bg-purple-50 hover:bg-purple-100 rounded-xl transition-colors duration-200">
                        <div class="bg-purple-600 p-3 rounded-lg">
                            <i class="fas fa-users text-white"></i>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">Người dùng</p>
                            <p class="text-sm text-gray-600">Quản lý tài khoản</p>
                        </div>
                    </a>

                    <a href="settings.php" class="flex items-center space-x-3 p-4 bg-orange-50 hover:bg-orange-100 rounded-xl transition-colors duration-200">
                        <div class="bg-orange-600 p-3 rounded-lg">
                            <i class="fas fa-cog text-white"></i>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">Cài đặt</p>
                            <p class="text-sm text-gray-600">Cấu hình hệ thống</p>
                        </div>
                    </a>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Simulate loading delay for better UX
        setTimeout(() => {
            initializeCharts();
        }, 500);

        function initializeCharts() {
            // Revenue Chart
            const revenueCtx = document.getElementById('revenueChart').getContext('2d');
            document.getElementById('revenueChartLoading').style.display = 'none';
            document.getElementById('revenueChart').style.display = 'block';
            
            new Chart(revenueCtx, {
                type: 'line',
                data: {
                    labels: ['T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'CN'],
                    datasets: [{
                        label: 'Doanh thu',
                        data: [12000000, 15000000, 8000000, 22000000, 18000000, 25000000, 20000000],
                        borderColor: '#3B82F6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#3B82F6',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            },
                            ticks: {
                                callback: function(value) {
                                    return new Intl.NumberFormat('vi-VN', {
                                        style: 'currency',
                                        currency: 'VND',
                                        notation: 'compact'
                                    }).format(value);
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    },
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    }
                }
            });

            // Order Status Chart
            const orderCtx = document.getElementById('orderChart').getContext('2d');
            document.getElementById('orderChartLoading').style.display = 'none';
            document.getElementById('orderChart').style.display = 'block';
            
            new Chart(orderCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Hoàn thành', 'Đang xử lý', 'Chờ xử lý', 'Đã hủy'],
                    datasets: [{
                        data: [<?php echo $stats['activeServices']; ?>, 15, <?php echo $stats['pendingOrders']; ?>, 5],
                        backgroundColor: ['#10B981', '#F59E0B', '#3B82F6', '#EF4444'],
                        borderWidth: 0,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 20,
                                usePointStyle: true,
                                font: {
                                    size: 12
                                }
                            }
                        }
                    }
                }
            });
        }
    </script>
</body>
</html>