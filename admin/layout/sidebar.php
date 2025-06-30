<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<div id="sidebar" class="fixed left-0 top-0 h-full w-64 bg-gradient-to-b from-gray-900 to-gray-800 text-white transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out z-50">
    <!-- Logo -->
    <div class="flex items-center justify-center h-16 bg-gradient-to-r from-blue-600 to-purple-600">
        <div class="flex items-center space-x-2">
            <div class="bg-white p-2 rounded-lg">
                <i class="fas fa-server text-blue-600"></i>
            </div>
            <span class="text-xl font-bold">VPS Admin</span>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="mt-8 px-4">
        <div class="space-y-2">
            <!-- Dashboard -->
            <a href="index.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-all duration-200 <?php echo $currentPage === 'index.php' ? 'bg-blue-600 text-white shadow-lg' : 'text-gray-300 hover:bg-gray-700 hover:text-white'; ?>">
                <i class="fas fa-tachometer-alt w-5"></i>
                <span class="font-medium">Dashboard</span>
            </a>

            <!-- Orders -->
            <a href="orders.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-all duration-200 <?php echo $currentPage === 'orders.php' ? 'bg-blue-600 text-white shadow-lg' : 'text-gray-300 hover:bg-gray-700 hover:text-white'; ?>">
                <i class="fas fa-shopping-cart w-5"></i>
                <span class="font-medium">Đơn hàng</span>
                <?php
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE status = 'pending'");
                $stmt->execute();
                $pendingCount = $stmt->fetchColumn();
                if ($pendingCount > 0):
                ?>
                <span class="bg-red-500 text-white text-xs px-2 py-1 rounded-full ml-auto"><?php echo $pendingCount; ?></span>
                <?php endif; ?>
            </a>

            <!-- Create Order -->
            <a href="create-order.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-all duration-200 <?php echo $currentPage === 'create-order.php' ? 'bg-blue-600 text-white shadow-lg' : 'text-gray-300 hover:bg-gray-700 hover:text-white'; ?>">
                <i class="fas fa-plus-circle w-5"></i>
                <span class="font-medium">Tạo đơn hàng</span>
            </a>

            <!-- Package Management -->
            <div class="space-y-1">
                <div class="text-gray-400 text-xs uppercase tracking-wider px-4 py-2 font-semibold">Quản lý gói</div>
                <a href="vps-packages.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-all duration-200 <?php echo $currentPage === 'vps-packages.php' ? 'bg-blue-600 text-white shadow-lg' : 'text-gray-300 hover:bg-gray-700 hover:text-white'; ?>">
                    <i class="fas fa-server w-5"></i>
                    <span class="font-medium">Gói VPS</span>
                </a>
                <a href="proxy-packages.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-all duration-200 <?php echo $currentPage === 'proxy-packages.php' ? 'bg-blue-600 text-white shadow-lg' : 'text-gray-300 hover:bg-gray-700 hover:text-white'; ?>">
                    <i class="fas fa-shield-alt w-5"></i>
                    <span class="font-medium">Gói Proxy</span>
                </a>
            </div>

            <!-- Top-ups -->
            <a href="topups.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-all duration-200 <?php echo $currentPage === 'topups.php' ? 'bg-blue-600 text-white shadow-lg' : 'text-gray-300 hover:bg-gray-700 hover:text-white'; ?>">
                <i class="fas fa-credit-card w-5"></i>
                <span class="font-medium">Nạp tiền</span>
                <?php
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM topup_requests WHERE status = 'pending'");
                $stmt->execute();
                $pendingTopups = $stmt->fetchColumn();
                if ($pendingTopups > 0):
                ?>
                <span class="bg-yellow-500 text-white text-xs px-2 py-1 rounded-full ml-auto"><?php echo $pendingTopups; ?></span>
                <?php endif; ?>
            </a>

            <!-- Users -->
            <a href="users.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-all duration-200 <?php echo $currentPage === 'users.php' ? 'bg-blue-600 text-white shadow-lg' : 'text-gray-300 hover:bg-gray-700 hover:text-white'; ?>">
                <i class="fas fa-users w-5"></i>
                <span class="font-medium">Người dùng</span>
            </a>

            <!-- Banks -->
            <a href="banks.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-all duration-200 <?php echo $currentPage === 'banks.php' ? 'bg-blue-600 text-white shadow-lg' : 'text-gray-300 hover:bg-gray-700 hover:text-white'; ?>">
                <i class="fas fa-university w-5"></i>
                <span class="font-medium">Ngân hàng</span>
            </a>

            <!-- Settings -->
            <div class="space-y-1">
                <div class="text-gray-400 text-xs uppercase tracking-wider px-4 py-2 font-semibold">Cài đặt</div>
                <a href="settings.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-all duration-200 <?php echo $currentPage === 'settings.php' ? 'bg-blue-600 text-white shadow-lg' : 'text-gray-300 hover:bg-gray-700 hover:text-white'; ?>">
                    <i class="fas fa-cog w-5"></i>
                    <span class="font-medium">Cài đặt</span>
                </a>
                <a href="smtp-settings.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-all duration-200 <?php echo $currentPage === 'smtp-settings.php' ? 'bg-blue-600 text-white shadow-lg' : 'text-gray-300 hover:bg-gray-700 hover:text-white'; ?>">
                    <i class="fas fa-envelope w-5"></i>
                    <span class="font-medium">SMTP</span>
                </a>
            </div>
        </div>
    </nav>

    <!-- Bottom Actions -->
    <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-gray-700">
        <div class="space-y-2">
            <a href="../index.php" class="flex items-center space-x-3 px-4 py-2 rounded-lg text-gray-300 hover:bg-gray-700 hover:text-white transition-all duration-200">
                <i class="fas fa-external-link-alt w-5"></i>
                <span class="font-medium">Xem trang web</span>
            </a>
            <a href="../logout.php" class="flex items-center space-x-3 px-4 py-2 rounded-lg text-red-300 hover:bg-red-600 hover:text-white transition-all duration-200">
                <i class="fas fa-sign-out-alt w-5"></i>
                <span class="font-medium">Đăng xuất</span>
            </a>
        </div>
    </div>
</div>

<!-- Mobile sidebar overlay -->
<div id="sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden hidden"></div>

<!-- Mobile menu button -->
<button id="mobile-menu-btn" class="lg:hidden fixed top-4 left-4 z-50 bg-gray-900 text-white p-2 rounded-lg">
    <i class="fas fa-bars"></i>
</button>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    const mobileBtn = document.getElementById('mobile-menu-btn');

    mobileBtn.addEventListener('click', function() {
        sidebar.classList.toggle('-translate-x-full');
        overlay.classList.toggle('hidden');
    });

    overlay.addEventListener('click', function() {
        sidebar.classList.add('-translate-x-full');
        overlay.classList.add('hidden');
    });
});
</script>