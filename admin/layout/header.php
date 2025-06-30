<?php
$user = getUserById($_SESSION['user_id']);
?>
<header class="bg-white shadow-sm border-b border-gray-200 lg:ml-64">
    <div class="px-6 py-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <h1 class="text-2xl font-bold text-gray-900"><?php echo $pageTitle ?? 'Admin Panel'; ?></h1>
            </div>
            
            <div class="flex items-center space-x-4">
                <!-- Notifications -->
                <div class="relative">
                    <button class="p-2 text-gray-400 hover:text-gray-600 relative">
                        <i class="fas fa-bell text-xl"></i>
                        <?php
                        $stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE status = 'pending'");
                        $stmt->execute();
                        $notifications = $stmt->fetchColumn();
                        if ($notifications > 0):
                        ?>
                        <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center"><?php echo $notifications; ?></span>
                        <?php endif; ?>
                    </button>
                </div>

                <!-- User Menu -->
                <div class="relative group">
                    <button class="flex items-center space-x-3 p-2 rounded-lg hover:bg-gray-100 transition-colors duration-200">
                        <div class="bg-gradient-to-r from-blue-600 to-purple-600 p-2 rounded-full">
                            <i class="fas fa-user text-white"></i>
                        </div>
                        <div class="text-left">
                            <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($user['username']); ?></p>
                            <p class="text-xs text-gray-500">Administrator</p>
                        </div>
                        <i class="fas fa-chevron-down text-gray-400"></i>
                    </button>
                    
                    <div class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-100 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                        <div class="py-1">
                            <a href="../profile.php" class="flex items-center space-x-2 px-4 py-2 text-gray-700 hover:bg-gray-50">
                                <i class="fas fa-user"></i>
                                <span>Hồ sơ</span>
                            </a>
                            <a href="settings.php" class="flex items-center space-x-2 px-4 py-2 text-gray-700 hover:bg-gray-50">
                                <i class="fas fa-cog"></i>
                                <span>Cài đặt</span>
                            </a>
                            <hr class="my-1">
                            <a href="../logout.php" class="flex items-center space-x-2 px-4 py-2 text-red-600 hover:bg-red-50">
                                <i class="fas fa-sign-out-alt"></i>
                                <span>Đăng xuất</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>