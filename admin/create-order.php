<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is admin
if (!isAdmin()) {
    redirectTo('../login.php');
}

$pageTitle = 'Tạo đơn hàng';
$success = '';
$error = '';

// Get all users and packages
$users = getAllUsers();
$vpsPackages = getAllVPSPackages();
$proxyPackages = getAllProxyPackages();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = (int)$_POST['user_id'];
    $packageId = (int)$_POST['package_id'];
    $packageType = $_POST['package_type'];
    $customerEmail = sanitizeInput($_POST['customer_email']);
    $notes = sanitizeInput($_POST['notes']);
    
    if (empty($userId) || empty($packageId) || empty($packageType) || empty($customerEmail)) {
        $error = 'Vui lòng nhập đầy đủ thông tin';
    } else {
        // Get package info
        if ($packageType === 'vps') {
            $package = getVPSPackageById($packageId);
        } else {
            $package = getProxyPackageById($packageId);
        }
        
        if (!$package) {
            $error = 'Gói dịch vụ không tồn tại';
        } else {
            // Create order directly (admin bypass balance check)
            try {
                $pdo->beginTransaction();
                
                // Generate order code
                $orderCode = strtoupper($packageType) . date('Y') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
                
                // Calculate profit
                $costPrice = $package['original_price'] ?? 0;
                $profit = $package['price'] - $costPrice;
                
                // Create order
                $stmt = $pdo->prepare("INSERT INTO orders (user_id, package_id, package_type, customer_email, amount, cost_price, profit, order_code, status, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?)");
                $stmt->execute([$userId, $packageId, $packageType, $customerEmail, $package['price'], $costPrice, $profit, $orderCode, $notes]);
                $orderId = $pdo->lastInsertId();
                
                // Update stock
                if ($packageType === 'vps') {
                    $stmt = $pdo->prepare("UPDATE vps_packages SET stock_quantity = stock_quantity - 1 WHERE id = ? AND stock_quantity > 0");
                } else {
                    $stmt = $pdo->prepare("UPDATE proxy_packages SET stock_quantity = stock_quantity - 1 WHERE id = ? AND stock_quantity > 0");
                }
                $stmt->execute([$packageId]);
                
                // Create transaction record
                $transactionId = 'TXN' . time() . mt_rand(100, 999);
                $description = 'Đơn hàng tạo bởi admin: ' . $package['name'];
                $stmt = $pdo->prepare("INSERT INTO transactions (user_id, amount, type, status, payment_method, transaction_id, description) VALUES (?, ?, 'purchase', 'completed', 'Admin tạo', ?, ?)");
                $stmt->execute([$userId, $package['price'], $transactionId, $description]);
                
                $pdo->commit();
                $success = 'Tạo đơn hàng thành công! Mã đơn hàng: ' . $orderCode;
            } catch (Exception $e) {
                $pdo->rollback();
                $error = 'Có lỗi xảy ra: ' . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tạo đơn hàng - VPS & Proxy Việt Nam</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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
            <div class="max-w-4xl mx-auto">
                <!-- Alert Messages -->
                <?php if ($success): ?>
                <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                    <div class="flex items-center space-x-2">
                        <i class="fas fa-check-circle text-green-500"></i>
                        <span class="text-green-700 font-medium"><?php echo htmlspecialchars($success); ?></span>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($error): ?>
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                    <div class="flex items-center space-x-2">
                        <i class="fas fa-exclamation-circle text-red-500"></i>
                        <span class="text-red-700 font-medium"><?php echo htmlspecialchars($error); ?></span>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Create Order Form -->
                <div class="bg-white rounded-2xl shadow-lg p-8">
                    <div class="flex items-center space-x-3 mb-8">
                        <div class="bg-gradient-to-r from-blue-600 to-purple-600 p-3 rounded-xl">
                            <i class="fas fa-plus-circle text-white text-xl"></i>
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900">Tạo đơn hàng mới</h2>
                            <p class="text-gray-600">Tạo đơn hàng cho khách hàng</p>
                        </div>
                    </div>

                    <form method="POST" class="space-y-6">
                        <!-- Customer Selection -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="user_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    Khách hàng *
                                </label>
                                <select name="user_id" id="user_id" required class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="">Chọn khách hàng</option>
                                    <?php foreach ($users as $user): ?>
                                    <?php if ($user['role'] !== 'admin'): ?>
                                    <option value="<?php echo $user['id']; ?>" <?php echo (isset($_POST['user_id']) && $_POST['user_id'] == $user['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($user['username']); ?> (<?php echo htmlspecialchars($user['email']); ?>)
                                    </option>
                                    <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div>
                                <label for="customer_email" class="block text-sm font-medium text-gray-700 mb-2">
                                    Email nhận thông tin *
                                </label>
                                <input type="email" name="customer_email" id="customer_email" required
                                       class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                       placeholder="Email để gửi thông tin dịch vụ" value="<?php echo isset($_POST['customer_email']) ? htmlspecialchars($_POST['customer_email']) : ''; ?>">
                            </div>
                        </div>

                        <!-- Package Type Selection -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-3">Loại dịch vụ *</label>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <label class="cursor-pointer">
                                    <input type="radio" name="package_type" value="vps" class="sr-only" required onchange="updatePackageList()">
                                    <div class="p-4 border-2 rounded-lg transition-all duration-200 hover:border-blue-300 package-type-option">
                                        <div class="flex items-center space-x-3">
                                            <i class="fas fa-server text-blue-600 text-xl"></i>
                                            <div>
                                                <h3 class="font-semibold text-gray-900">VPS Hosting</h3>
                                                <p class="text-sm text-gray-600">Virtual Private Server</p>
                                            </div>
                                        </div>
                                    </div>
                                </label>

                                <label class="cursor-pointer">
                                    <input type="radio" name="package_type" value="proxy" class="sr-only" required onchange="updatePackageList()">
                                    <div class="p-4 border-2 rounded-lg transition-all duration-200 hover:border-blue-300 package-type-option">
                                        <div class="flex items-center space-x-3">
                                            <i class="fas fa-shield-alt text-green-600 text-xl"></i>
                                            <div>
                                                <h3 class="font-semibold text-gray-900">Proxy SOCKS5</h3>
                                                <p class="text-sm text-gray-600">Proxy Server</p>
                                            </div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Package Selection -->
                        <div>
                            <label for="package_id" class="block text-sm font-medium text-gray-700 mb-2">
                                Gói dịch vụ *
                            </label>
                            <select name="package_id" id="package_id" required class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">Chọn loại dịch vụ trước</option>
                            </select>
                        </div>

                        <!-- Package Details -->
                        <div id="package-details" class="hidden bg-gray-50 rounded-lg p-4">
                            <h4 class="font-semibold text-gray-900 mb-3">Chi tiết gói dịch vụ</h4>
                            <div id="package-info"></div>
                        </div>

                        <!-- Notes -->
                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                                Ghi chú
                            </label>
                            <textarea name="notes" id="notes" rows="3"
                                      class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                      placeholder="Ghi chú thêm cho đơn hàng..."><?php echo isset($_POST['notes']) ? htmlspecialchars($_POST['notes']) : ''; ?></textarea>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex space-x-4">
                            <button type="submit" class="flex-1 bg-gradient-to-r from-blue-600 to-purple-600 text-white py-3 px-6 rounded-lg font-semibold hover:from-blue-700 hover:to-purple-700 transition-all duration-200 flex items-center justify-center space-x-2">
                                <i class="fas fa-plus-circle"></i>
                                <span>Tạo đơn hàng</span>
                            </button>
                            <a href="orders.php" class="flex-1 bg-gray-600 text-white py-3 px-6 rounded-lg font-semibold hover:bg-gray-700 transition-all duration-200 flex items-center justify-center space-x-2">
                                <i class="fas fa-list"></i>
                                <span>Danh sách đơn hàng</span>
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script>
        const vpsPackages = <?php echo json_encode($vpsPackages); ?>;
        const proxyPackages = <?php echo json_encode($proxyPackages); ?>;

        function updatePackageList() {
            const packageType = document.querySelector('input[name="package_type"]:checked').value;
            const packageSelect = document.getElementById('package_id');
            const packageDetails = document.getElementById('package-details');
            
            // Clear existing options
            packageSelect.innerHTML = '<option value="">Chọn gói dịch vụ</option>';
            packageDetails.classList.add('hidden');
            
            // Add packages based on type
            const packages = packageType === 'vps' ? vpsPackages : proxyPackages;
            packages.forEach(package => {
                const option = document.createElement('option');
                option.value = package.id;
                option.textContent = `${package.name} - ${formatCurrency(package.price)}`;
                option.dataset.package = JSON.stringify(package);
                packageSelect.appendChild(option);
            });
            
            // Update package type option styles
            document.querySelectorAll('.package-type-option').forEach(option => {
                option.classList.remove('border-blue-500', 'bg-blue-50');
                option.classList.add('border-gray-200');
            });
            
            const selectedOption = document.querySelector(`input[value="${packageType}"]`).closest('label').querySelector('.package-type-option');
            selectedOption.classList.remove('border-gray-200');
            selectedOption.classList.add('border-blue-500', 'bg-blue-50');
        }

        // Handle package selection
        document.getElementById('package_id').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const packageDetails = document.getElementById('package-details');
            const packageInfo = document.getElementById('package-info');
            
            if (selectedOption.dataset.package) {
                const package = JSON.parse(selectedOption.dataset.package);
                const packageType = document.querySelector('input[name="package_type"]:checked').value;
                
                let html = '';
                if (packageType === 'vps') {
                    html = `
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div>
                                <span class="text-sm text-gray-600">CPU:</span>
                                <p class="font-medium">${package.cpu}</p>
                            </div>
                            <div>
                                <span class="text-sm text-gray-600">RAM:</span>
                                <p class="font-medium">${package.ram}</p>
                            </div>
                            <div>
                                <span class="text-sm text-gray-600">Storage:</span>
                                <p class="font-medium">${package.storage}</p>
                            </div>
                            <div>
                                <span class="text-sm text-gray-600">Bandwidth:</span>
                                <p class="font-medium">${package.bandwidth}</p>
                            </div>
                        </div>
                        <div class="mt-4 flex justify-between items-center">
                            <span class="text-lg font-semibold text-gray-900">Giá: ${formatCurrency(package.price)}</span>
                            <span class="text-sm text-gray-600">Tồn kho: ${package.stock_quantity}</span>
                        </div>
                    `;
                } else {
                    html = `
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div>
                                <span class="text-sm text-gray-600">Vị trí:</span>
                                <p class="font-medium">${package.location}</p>
                            </div>
                            <div>
                                <span class="text-sm text-gray-600">Tốc độ:</span>
                                <p class="font-medium">${package.speed}</p>
                            </div>
                            <div>
                                <span class="text-sm text-gray-600">Kết nối:</span>
                                <p class="font-medium">${package.concurrent_connections}</p>
                            </div>
                            <div>
                                <span class="text-sm text-gray-600">Giao thức:</span>
                                <p class="font-medium">${package.type.toUpperCase()}</p>
                            </div>
                        </div>
                        <div class="mt-4 flex justify-between items-center">
                            <span class="text-lg font-semibold text-gray-900">Giá: ${formatCurrency(package.price)}</span>
                            <span class="text-sm text-gray-600">Tồn kho: ${package.stock_quantity}</span>
                        </div>
                    `;
                }
                
                packageInfo.innerHTML = html;
                packageDetails.classList.remove('hidden');
            } else {
                packageDetails.classList.add('hidden');
            }
        });

        // Auto-fill email when user is selected
        document.getElementById('user_id').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption.value) {
                const email = selectedOption.textContent.match(/\(([^)]+)\)/)[1];
                document.getElementById('customer_email').value = email;
            }
        });

        function formatCurrency(amount) {
            return new Intl.NumberFormat('vi-VN', {
                style: 'currency',
                currency: 'VND'
            }).format(amount);
        }
    </script>
</body>
</html>