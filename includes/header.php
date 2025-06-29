<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$alert = getAlert();
?>

<header class="bg-white shadow-lg border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <!-- Logo -->
            <a href="index.php" class="flex items-center space-x-2">
                <div class="bg-gradient-to-r from-blue-600 to-purple-600 p-2 rounded-lg">
                    <i class="fas fa-server text-white"></i>
                </div>
                <span class="text-xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
                    VPS Việt Nam
                </span>
            </a>

            <!-- Navigation -->
            <nav class="hidden md:flex items-center space-x-8">
                <a href="index.php" class="text-gray-700 hover:text-blue-600 transition-colors duration-200 font-medium <?php echo $currentPage === 'index.php' ? 'text-blue-600' : ''; ?>">
                    Trang chủ
                </a>
                <a href="packages.php" class="text-gray-700 hover:text-blue-600 transition-colors duration-200 font-medium <?php echo $currentPage === 'packages.php' ? 'text-blue-600' : ''; ?>">
                    Gói VPS
                </a>
                <?php if ($user): ?>
                <a href="orders.php" class="text-gray-700 hover:text-blue-600 transition-colors duration-200 font-medium <?php echo $currentPage === 'orders.php' ? 'text-blue-600' : ''; ?>">
                    Đơn hàng của tôi
                </a>
                <?php endif; ?>
            </nav>

            <!-- User Actions -->
            <div class="flex items-center space-x-4">
                <?php if ($user): ?>
                    <div class="flex items-center space-x-2 bg-green-50 px-3 py-1 rounded-full">
                        <i class="fas fa-wallet text-green-600"></i>
                        <span class="text-green-700 font-semibold">
                            <?php echo formatCurrency($user['balance']); ?>
                        </span>
                    </div>
                    
                    <div class="relative group">
                        <button class="flex items-center space-x-2 bg-gray-50 hover:bg-gray-100 px-3 py-2 rounded-lg transition-colors duration-200">
                            <i class="fas fa-user text-gray-600"></i>
                            <span class="text-gray-700 font-medium"><?php echo htmlspecialchars($user['username']); ?></span>
                            <i class="fas fa-chevron-down text-gray-400"></i>
                        </button>
                        
                        <div class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-100 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                            <div class="py-1">
                                <a href="profile.php" class="flex items-center space-x-2 px-4 py-2 text-gray-700 hover:bg-gray-50 transition-colors duration-200">
                                    <i class="fas fa-user"></i>
                                    <span>Hồ sơ</span>
                                </a>
                                <a href="orders.php" class="flex items-center space-x-2 px-4 py-2 text-gray-700 hover:bg-gray-50 transition-colors duration-200">
                                    <i class="fas fa-shopping-cart"></i>
                                    <span>Đơn hàng</span>
                                </a>
                                <a href="topup.php" class="flex items-center space-x-2 px-4 py-2 text-gray-700 hover:bg-gray-50 transition-colors duration-200">
                                    <i class="fas fa-credit-card"></i>
                                    <span>Nạp tiền</span>
                                </a>
                                <?php if ($user['role'] === 'admin'): ?>
                                <a href="admin/index.php" class="flex items-center space-x-2 px-4 py-2 text-gray-700 hover:bg-gray-50 transition-colors duration-200">
                                    <i class="fas fa-cog"></i>
                                    <span>Quản trị</span>
                                </a>
                                <?php endif; ?>
                                <a href="logout.php" class="flex items-center space-x-2 px-4 py-2 text-gray-700 hover:bg-gray-50 transition-colors duration-200">
                                    <i class="fas fa-sign-out-alt"></i>
                                    <span>Đăng xuất</span>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="flex items-center space-x-3">
                        <a href="login.php" class="text-gray-700 hover:text-blue-600 transition-colors duration-200 font-medium">
                            Đăng nhập
                        </a>
                        <a href="register.php" class="bg-gradient-to-r from-blue-600 to-purple-600 text-white px-4 py-2 rounded-lg hover:from-blue-700 hover:to-purple-700 transition-all duration-200 font-medium shadow-md hover:shadow-lg">
                            Đăng ký
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>

<!-- Alert Messages -->
<?php if ($alert): ?>
<div class="bg-<?php echo $alert['type'] === 'success' ? 'green' : ($alert['type'] === 'error' ? 'red' : 'blue'); ?>-50 border border-<?php echo $alert['type'] === 'success' ? 'green' : ($alert['type'] === 'error' ? 'red' : 'blue'); ?>-200 rounded-lg p-4 mx-4 mt-4">
    <div class="flex items-center space-x-2">
        <i class="fas fa-<?php echo $alert['type'] === 'success' ? 'check-circle' : ($alert['type'] === 'error' ? 'exclamation-circle' : 'info-circle'); ?> text-<?php echo $alert['type'] === 'success' ? 'green' : ($alert['type'] === 'error' ? 'red' : 'blue'); ?>-500"></i>
        <span class="text-<?php echo $alert['type'] === 'success' ? 'green' : ($alert['type'] === 'error' ? 'red' : 'blue'); ?>-700 font-medium"><?php echo htmlspecialchars($alert['message']); ?></span>
    </div>
</div>
<?php endif; ?>