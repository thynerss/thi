<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is admin
if (!isAdmin()) {
    redirectTo('../login.php');
}

$topups = getAllTopUps();

// Handle topup actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'approve') {
        $topupId = (int)$_POST['topup_id'];
        $adminNote = sanitizeInput($_POST['admin_note']);
        
        if (approveTopUp($topupId, $adminNote)) {
            showAlert('Duyệt nạp tiền thành công!', 'success');
        } else {
            showAlert('Có lỗi xảy ra khi duyệt nạp tiền!', 'error');
        }
        redirectTo('topups.php');
    } elseif ($_POST['action'] === 'reject') {
        $topupId = (int)$_POST['topup_id'];
        $adminNote = sanitizeInput($_POST['admin_note']);
        
        if (rejectTopUp($topupId, $adminNote)) {
            showAlert('Từ chối nạp tiền thành công!', 'success');
        } else {
            showAlert('Có lỗi xảy ra khi từ chối nạp tiền!', 'error');
        }
        redirectTo('topups.php');
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý nạp tiền - VPS & Proxy Việt Nam</title>
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
                    <a href="orders.php" class="text-gray-700 hover:text-blue-600 transition-colors duration-200 font-medium">Đơn hàng</a>
                    <a href="topups.php" class="text-blue-600 font-medium">Nạp tiền</a>
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
            <h1 class="text-3xl font-bold text-gray-900">Quản lý nạp tiền</h1>
            <p class="text-gray-600 mt-2">Duyệt và quản lý các yêu cầu nạp tiền</p>
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

        <!-- Topups Table -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-900">Danh sách yêu cầu nạp tiền</h2>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Khách hàng</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Số tiền</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phương thức</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nội dung CK</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trạng thái</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ngày tạo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($topups as $topup): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div>
                                    <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($topup['username']); ?></div>
                                    <div class="text-sm text-gray-500"><?php echo htmlspecialchars($topup['user_email']); ?></div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?php echo formatCurrency($topup['amount']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo htmlspecialchars($topup['payment_method']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if ($topup['bank_transfer_content']): ?>
                                <span class="font-mono text-sm bg-gray-100 px-2 py-1 rounded"><?php echo htmlspecialchars($topup['bank_transfer_content']); ?></span>
                                <?php else: ?>
                                <span class="text-gray-500">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 rounded-full text-xs font-medium <?php echo getStatusColor($topup['status']); ?>">
                                    <?php echo getStatusText($topup['status']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo date('d/m/Y H:i', strtotime($topup['created_at'])); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <?php if ($topup['status'] === 'pending'): ?>
                                <button onclick="approveTopup(<?php echo $topup['id']; ?>)" 
                                        class="text-green-600 hover:text-green-900 mr-3">
                                    <i class="fas fa-check"></i>
                                </button>
                                <button onclick="rejectTopup(<?php echo $topup['id']; ?>)" 
                                        class="text-red-600 hover:text-red-900">
                                    <i class="fas fa-times"></i>
                                </button>
                                <?php else: ?>
                                <span class="text-gray-400">Đã xử lý</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Approve Modal -->
    <div id="approveModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50 hidden">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full">
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-gray-900">Duyệt nạp tiền</h2>
                    <button onclick="closeApproveModal()" class="p-2 hover:bg-gray-100 rounded-lg">
                        <i class="fas fa-times text-gray-500"></i>
                    </button>
                </div>

                <form method="POST">
                    <input type="hidden" name="action" value="approve">
                    <input type="hidden" name="topup_id" id="approve_topup_id">
                    
                    <div class="mb-6">
                        <label for="admin_note" class="block text-sm font-medium text-gray-700 mb-2">Ghi chú (tùy chọn)</label>
                        <textarea name="admin_note" id="approve_admin_note" rows="3" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                  placeholder="Ghi chú cho việc duyệt nạp tiền..."></textarea>
                    </div>

                    <div class="flex space-x-3">
                        <button type="button" onclick="closeApproveModal()" class="flex-1 py-2 px-4 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                            Hủy
                        </button>
                        <button type="submit" class="flex-1 bg-green-600 text-white py-2 px-4 rounded-lg hover:bg-green-700">
                            Duyệt
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div id="rejectModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50 hidden">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full">
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-gray-900">Từ chối nạp tiền</h2>
                    <button onclick="closeRejectModal()" class="p-2 hover:bg-gray-100 rounded-lg">
                        <i class="fas fa-times text-gray-500"></i>
                    </button>
                </div>

                <form method="POST">
                    <input type="hidden" name="action" value="reject">
                    <input type="hidden" name="topup_id" id="reject_topup_id">
                    
                    <div class="mb-6">
                        <label for="admin_note" class="block text-sm font-medium text-gray-700 mb-2">Lý do từ chối</label>
                        <textarea name="admin_note" id="reject_admin_note" rows="3" required
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                  placeholder="Nhập lý do từ chối nạp tiền..."></textarea>
                    </div>

                    <div class="flex space-x-3">
                        <button type="button" onclick="closeRejectModal()" class="flex-1 py-2 px-4 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                            Hủy
                        </button>
                        <button type="submit" class="flex-1 bg-red-600 text-white py-2 px-4 rounded-lg hover:bg-red-700">
                            Từ chối
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function approveTopup(topupId) {
            document.getElementById('approve_topup_id').value = topupId;
            document.getElementById('approveModal').classList.remove('hidden');
        }

        function rejectTopup(topupId) {
            document.getElementById('reject_topup_id').value = topupId;
            document.getElementById('rejectModal').classList.remove('hidden');
        }

        function closeApproveModal() {
            document.getElementById('approveModal').classList.add('hidden');
        }

        function closeRejectModal() {
            document.getElementById('rejectModal').classList.add('hidden');
        }
    </script>
</body>
</html>