<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is admin
if (!isAdmin()) {
    redirectTo('../login.php');
}

$users = getAllUsers();

// Handle user actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_user') {
        $userId = (int)$_POST['user_id'];
        $username = sanitizeInput($_POST['username']);
        $email = sanitizeInput($_POST['email']);
        $balance = (float)$_POST['balance'];
        $status = $_POST['status'];
        $role = $_POST['role'];
        
        if (updateUser($userId, $username, $email, $balance, $status, $role)) {
            showAlert('Cập nhật thông tin người dùng thành công!', 'success');
        } else {
            showAlert('Có lỗi xảy ra khi cập nhật thông tin!', 'error');
        }
        redirectTo('users.php');
    } elseif ($_POST['action'] === 'add_balance') {
        $userId = (int)$_POST['user_id'];
        $amount = (float)$_POST['amount'];
        $note = sanitizeInput($_POST['note']);
        
        if (addUserBalance($userId, $amount, $note)) {
            showAlert('Thêm số dư thành công!', 'success');
        } else {
            showAlert('Có lỗi xảy ra khi thêm số dư!', 'error');
        }
        redirectTo('users.php');
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý người dùng - VPS & Proxy Việt Nam</title>
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
                    <a href="topups.php" class="text-gray-700 hover:text-blue-600 transition-colors duration-200 font-medium">Nạp tiền</a>
                    <a href="users.php" class="text-blue-600 font-medium">Người dùng</a>
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
            <h1 class="text-3xl font-bold text-gray-900">Quản lý người dùng</h1>
            <p class="text-gray-600 mt-2">Quản lý tất cả người dùng trong hệ thống</p>
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

        <!-- Users Table -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-900">Danh sách người dùng</h2>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Thông tin</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Số dư</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vai trò</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trạng thái</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ngày tham gia</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($users as $user): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-medium text-gray-900">#<?php echo $user['id']; ?></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div>
                                    <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($user['username']); ?></div>
                                    <div class="text-sm text-gray-500"><?php echo htmlspecialchars($user['email']); ?></div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?php echo formatCurrency($user['balance']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 rounded-full text-xs font-medium <?php echo $user['role'] === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800'; ?>">
                                    <?php echo $user['role'] === 'admin' ? 'Admin' : 'User'; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 rounded-full text-xs font-medium <?php echo $user['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                    <?php echo $user['status'] === 'active' ? 'Hoạt động' : 'Không hoạt động'; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo date('d/m/Y', strtotime($user['created_at'])); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <button onclick="viewUserDetails(<?php echo $user['id']; ?>)" 
                                            class="text-blue-600 hover:text-blue-900" title="Xem chi tiết">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <?php if ($user['role'] !== 'admin'): ?>
                                    <button onclick="editUser(<?php echo $user['id']; ?>)" 
                                            class="text-green-600 hover:text-green-900" title="Chỉnh sửa">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="addBalance(<?php echo $user['id']; ?>)" 
                                            class="text-purple-600 hover:text-purple-900" title="Thêm số dư">
                                        <i class="fas fa-wallet"></i>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- User Statistics -->
        <div class="mt-8 grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white rounded-xl shadow-lg p-6">
                <div class="flex items-center">
                    <div class="bg-blue-100 p-3 rounded-lg">
                        <i class="fas fa-users text-blue-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-600">Tổng người dùng</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo count($users); ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6">
                <div class="flex items-center">
                    <div class="bg-green-100 p-3 rounded-lg">
                        <i class="fas fa-user-check text-green-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-600">Đang hoạt động</p>
                        <p class="text-2xl font-bold text-gray-900">
                            <?php echo count(array_filter($users, function($u) { return $u['status'] === 'active'; })); ?>
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6">
                <div class="flex items-center">
                    <div class="bg-purple-100 p-3 rounded-lg">
                        <i class="fas fa-crown text-purple-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-600">Admin</p>
                        <p class="text-2xl font-bold text-gray-900">
                            <?php echo count(array_filter($users, function($u) { return $u['role'] === 'admin'; })); ?>
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6">
                <div class="flex items-center">
                    <div class="bg-yellow-100 p-3 rounded-lg">
                        <i class="fas fa-wallet text-yellow-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-600">Tổng số dư</p>
                        <p class="text-2xl font-bold text-gray-900">
                            <?php echo formatCurrency(array_sum(array_column($users, 'balance'))); ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div id="editUserModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50 hidden">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full">
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-gray-900">Chỉnh sửa người dùng</h2>
                    <button onclick="closeEditUserModal()" class="p-2 hover:bg-gray-100 rounded-lg">
                        <i class="fas fa-times text-gray-500"></i>
                    </button>
                </div>

                <form method="POST" id="editUserForm">
                    <input type="hidden" name="action" value="update_user">
                    <input type="hidden" name="user_id" id="edit_user_id">
                    
                    <div class="space-y-4">
                        <div>
                            <label for="edit_username" class="block text-sm font-medium text-gray-700 mb-2">Tên đăng nhập</label>
                            <input type="text" name="username" id="edit_username" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        
                        <div>
                            <label for="edit_email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                            <input type="email" name="email" id="edit_email" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        
                        <div>
                            <label for="edit_balance" class="block text-sm font-medium text-gray-700 mb-2">Số dư</label>
                            <input type="number" step="0.01" name="balance" id="edit_balance" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        
                        <div>
                            <label for="edit_status" class="block text-sm font-medium text-gray-700 mb-2">Trạng thái</label>
                            <select name="status" id="edit_status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="active">Hoạt động</option>
                                <option value="inactive">Không hoạt động</option>
                                <option value="suspended">Tạm khóa</option>
                            </select>
                        </div>
                        
                        <div>
                            <label for="edit_role" class="block text-sm font-medium text-gray-700 mb-2">Vai trò</label>
                            <select name="role" id="edit_role" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="user">User</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex space-x-3 mt-6">
                        <button type="button" onclick="closeEditUserModal()" class="flex-1 py-2 px-4 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
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

    <!-- Add Balance Modal -->
    <div id="addBalanceModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50 hidden">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full">
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-gray-900">Thêm số dư</h2>
                    <button onclick="closeAddBalanceModal()" class="p-2 hover:bg-gray-100 rounded-lg">
                        <i class="fas fa-times text-gray-500"></i>
                    </button>
                </div>

                <form method="POST">
                    <input type="hidden" name="action" value="add_balance">
                    <input type="hidden" name="user_id" id="balance_user_id">
                    
                    <div class="space-y-4">
                        <div>
                            <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">Số tiền (VND)</label>
                            <input type="number" step="1000" name="amount" id="amount" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="100000">
                        </div>
                        
                        <div>
                            <label for="note" class="block text-sm font-medium text-gray-700 mb-2">Ghi chú</label>
                            <textarea name="note" id="note" rows="3"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                      placeholder="Lý do thêm số dư..."></textarea>
                        </div>
                    </div>

                    <div class="flex space-x-3 mt-6">
                        <button type="button" onclick="closeAddBalanceModal()" class="flex-1 py-2 px-4 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                            Hủy
                        </button>
                        <button type="submit" class="flex-1 bg-green-600 text-white py-2 px-4 rounded-lg hover:bg-green-700">
                            Thêm số dư
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- User Details Modal -->
    <div id="userDetailsModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50 hidden">
        <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-gray-900">Chi tiết người dùng</h2>
                    <button onclick="closeUserDetailsModal()" class="p-2 hover:bg-gray-100 rounded-lg">
                        <i class="fas fa-times text-gray-500"></i>
                    </button>
                </div>

                <div id="userDetailsContent">
                    <!-- User details will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <script>
        function viewUserDetails(userId) {
            // Load user details via AJAX
            fetch('../api/get_user_details.php?id=' + userId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('userDetailsContent').innerHTML = data.html;
                        document.getElementById('userDetailsModal').classList.remove('hidden');
                    } else {
                        alert('Không thể tải thông tin người dùng');
                    }
                })
                .catch(error => {
                    alert('Có lỗi xảy ra khi tải thông tin');
                });
        }

        function editUser(userId) {
            // Find user data
            const users = <?php echo json_encode($users); ?>;
            const user = users.find(u => u.id == userId);
            
            if (user) {
                document.getElementById('edit_user_id').value = user.id;
                document.getElementById('edit_username').value = user.username;
                document.getElementById('edit_email').value = user.email;
                document.getElementById('edit_balance').value = user.balance;
                document.getElementById('edit_status').value = user.status;
                document.getElementById('edit_role').value = user.role;
                document.getElementById('editUserModal').classList.remove('hidden');
            }
        }

        function addBalance(userId) {
            document.getElementById('balance_user_id').value = userId;
            document.getElementById('addBalanceModal').classList.remove('hidden');
        }

        function closeEditUserModal() {
            document.getElementById('editUserModal').classList.add('hidden');
        }

        function closeAddBalanceModal() {
            document.getElementById('addBalanceModal').classList.add('hidden');
        }

        function closeUserDetailsModal() {
            document.getElementById('userDetailsModal').classList.add('hidden');
        }
    </script>
</body>
</html>