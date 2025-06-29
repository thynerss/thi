<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirectTo('login.php');
}

$user = getUserById($_SESSION['user_id']);
$transactions = getUserTransactions($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hồ sơ cá nhân - VPS & Proxy Việt Nam</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <?php include 'includes/header.php'; ?>

    <div class="min-h-screen py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Hồ sơ cá nhân</h1>
                <p class="text-gray-600 mt-2">Quản lý thông tin tài khoản và lịch sử giao dịch</p>
            </div>

            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <!-- Tab Navigation -->
                <div class="border-b border-gray-200">
                    <nav class="flex space-x-8 px-6">
                        <button onclick="switchTab('profile')" id="profile-tab" class="py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200 border-blue-500 text-blue-600">
                            Thông tin cá nhân
                        </button>
                        <button onclick="switchTab('transactions')" id="transactions-tab" class="py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200 border-transparent text-gray-500 hover:text-gray-700">
                            Lịch sử giao dịch
                        </button>
                    </nav>
                </div>

                <!-- Tab Content -->
                <div class="p-6">
                    <!-- Profile Tab -->
                    <div id="profile-content">
                        <div class="flex items-center space-x-4 mb-8">
                            <div class="bg-gradient-to-r from-blue-600 to-purple-600 p-4 rounded-full">
                                <i class="fas fa-user text-2xl text-white"></i>
                            </div>
                            <div>
                                <h2 class="text-2xl font-bold text-gray-900"><?php echo htmlspecialchars($user['username']); ?></h2>
                                <p class="text-gray-600"><?php echo htmlspecialchars($user['email']); ?></p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="bg-gray-50 rounded-lg p-4">
                                <div class="flex items-center space-x-3 mb-3">
                                    <i class="fas fa-user text-gray-600"></i>
                                    <h3 class="font-semibold text-gray-900">Tên đăng nhập</h3>
                                </div>
                                <p class="text-gray-700"><?php echo htmlspecialchars($user['username']); ?></p>
                            </div>

                            <div class="bg-gray-50 rounded-lg p-4">
                                <div class="flex items-center space-x-3 mb-3">
                                    <i class="fas fa-envelope text-gray-600"></i>
                                    <h3 class="font-semibold text-gray-900">Email</h3>
                                </div>
                                <p class="text-gray-700"><?php echo htmlspecialchars($user['email']); ?></p>
                            </div>

                            <div class="bg-gradient-to-r from-green-50 to-blue-50 rounded-lg p-4">
                                <div class="flex items-center space-x-3 mb-3">
                                    <i class="fas fa-wallet text-green-600"></i>
                                    <h3 class="font-semibold text-gray-900">Số dư tài khoản</h3>
                                </div>
                                <p class="text-3xl font-bold text-green-600"><?php echo formatCurrency($user['balance']); ?></p>
                            </div>

                            <div class="bg-gray-50 rounded-lg p-4">
                                <div class="flex items-center space-x-3 mb-3">
                                    <i class="fas fa-shield-alt text-gray-600"></i>
                                    <h3 class="font-semibold text-gray-900">Trạng thái tài khoản</h3>
                                </div>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <?php echo $user['status'] === 'active' ? 'Hoạt động' : 'Không hoạt động'; ?>
                                </span>
                            </div>

                            <div class="bg-gray-50 rounded-lg p-4">
                                <div class="flex items-center space-x-3 mb-3">
                                    <i class="fas fa-calendar text-gray-600"></i>
                                    <h3 class="font-semibold text-gray-900">Ngày tham gia</h3>
                                </div>
                                <p class="text-gray-700"><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></p>
                            </div>

                            <div class="bg-gray-50 rounded-lg p-4">
                                <div class="flex items-center space-x-3 mb-3">
                                    <i class="fas fa-crown text-gray-600"></i>
                                    <h3 class="font-semibold text-gray-900">Loại tài khoản</h3>
                                </div>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $user['role'] === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800'; ?>">
                                    <?php echo $user['role'] === 'admin' ? 'Quản trị viên' : 'Người dùng'; ?>
                                </span>
                            </div>
                        </div>

                        <div class="mt-8 flex space-x-4">
                            <a href="topup.php" class="bg-gradient-to-r from-green-600 to-blue-600 text-white px-6 py-3 rounded-lg hover:from-green-700 hover:to-blue-700 transition-all duration-200 flex items-center space-x-2">
                                <i class="fas fa-plus"></i>
                                <span>Nạp tiền</span>
                            </a>
                            <a href="packages.php" class="bg-gradient-to-r from-blue-600 to-purple-600 text-white px-6 py-3 rounded-lg hover:from-blue-700 hover:to-purple-700 transition-all duration-200 flex items-center space-x-2">
                                <i class="fas fa-shopping-cart"></i>
                                <span>Mua dịch vụ</span>
                            </a>
                        </div>
                    </div>

                    <!-- Transactions Tab -->
                    <div id="transactions-content" class="hidden">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-2xl font-bold text-gray-900">Lịch sử giao dịch</h2>
                        </div>

                        <div class="space-y-4">
                            <?php if (empty($transactions)): ?>
                            <p class="text-gray-500 text-center py-8">Chưa có giao dịch nào</p>
                            <?php else: ?>
                            <?php foreach ($transactions as $transaction): ?>
                            <div class="bg-gray-50 rounded-lg p-4 flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <i class="fas fa-<?php echo $transaction['type'] === 'deposit' ? 'plus' : ($transaction['type'] === 'purchase' ? 'shopping-cart' : 'minus'); ?> text-<?php echo $transaction['amount'] > 0 ? 'green' : 'red'; ?>-500"></i>
                                    <div>
                                        <p class="font-medium text-gray-900">
                                            <?php 
                                            switch($transaction['type']) {
                                                case 'deposit': echo 'Nạp tiền'; break;
                                                case 'purchase': echo 'Mua dịch vụ'; break;
                                                case 'withdraw': echo 'Rút tiền'; break;
                                                default: echo 'Giao dịch'; break;
                                            }
                                            ?>
                                        </p>
                                        <p class="text-sm text-gray-600">
                                            <?php echo htmlspecialchars($transaction['payment_method']); ?> • <?php echo htmlspecialchars($transaction['transaction_id']); ?>
                                        </p>
                                        <?php if ($transaction['description']): ?>
                                        <p class="text-sm text-gray-500"><?php echo htmlspecialchars($transaction['description']); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="font-semibold <?php echo $transaction['amount'] > 0 ? 'text-green-600' : 'text-red-600'; ?>">
                                        <?php echo $transaction['amount'] > 0 ? '+' : ''; ?><?php echo formatCurrency(abs($transaction['amount'])); ?>
                                    </p>
                                    <p class="text-sm text-gray-600">
                                        <?php echo date('d/m/Y H:i', strtotime($transaction['created_at'])); ?>
                                    </p>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <script>
        function switchTab(tab) {
            // Update tab buttons
            document.getElementById('profile-tab').className = tab === 'profile' 
                ? 'py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200 border-blue-500 text-blue-600'
                : 'py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200 border-transparent text-gray-500 hover:text-gray-700';
            
            document.getElementById('transactions-tab').className = tab === 'transactions' 
                ? 'py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200 border-blue-500 text-blue-600'
                : 'py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200 border-transparent text-gray-500 hover:text-gray-700';
            
            // Update content
            document.getElementById('profile-content').className = tab === 'profile' ? '' : 'hidden';
            document.getElementById('transactions-content').className = tab === 'transactions' ? '' : 'hidden';
        }
    </script>
</body>
</html>