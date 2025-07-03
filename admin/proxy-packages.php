<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is admin
if (!isAdmin()) {
    redirectTo('../login.php');
}

$pageTitle = 'Quản lý gói Proxy';
$success = '';
$error = '';

// Handle package actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add_package') {
            $name = sanitizeInput($_POST['name']);
            $type = $_POST['type'];
            $location = sanitizeInput($_POST['location']);
            $speed = sanitizeInput($_POST['speed']);
            $concurrentConnections = (int)$_POST['concurrent_connections'];
            $price = (float)$_POST['price'];
            $originalPrice = (float)$_POST['original_price'];
            $provider = sanitizeInput($_POST['provider']);
            $description = sanitizeInput($_POST['description']);
            $stockQuantity = (int)$_POST['stock_quantity'];
            
            $profitMargin = $originalPrice > 0 ? (($price - $originalPrice) / $originalPrice) * 100 : 0;
            
            try {
                $stmt = $pdo->prepare("INSERT INTO proxy_packages (name, type, location, speed, concurrent_connections, price, original_price, profit_margin, provider, description, stock_quantity) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                if ($stmt->execute([$name, $type, $location, $speed, $concurrentConnections, $price, $originalPrice, $profitMargin, $provider, $description, $stockQuantity])) {
                    $success = 'Thêm gói Proxy thành công!';
                } else {
                    $error = 'Có lỗi xảy ra khi thêm gói Proxy!';
                }
            } catch (Exception $e) {
                $error = 'Có lỗi xảy ra: ' . $e->getMessage();
            }
        } elseif ($_POST['action'] === 'update_package') {
            $id = (int)$_POST['package_id'];
            $name = sanitizeInput($_POST['name']);
            $type = $_POST['type'];
            $location = sanitizeInput($_POST['location']);
            $speed = sanitizeInput($_POST['speed']);
            $concurrentConnections = (int)$_POST['concurrent_connections'];
            $price = (float)$_POST['price'];
            $originalPrice = (float)$_POST['original_price'];
            $provider = sanitizeInput($_POST['provider']);
            $description = sanitizeInput($_POST['description']);
            $stockQuantity = (int)$_POST['stock_quantity'];
            $status = $_POST['status'];
            
            $profitMargin = $originalPrice > 0 ? (($price - $originalPrice) / $originalPrice) * 100 : 0;
            
            try {
                $stmt = $pdo->prepare("UPDATE proxy_packages SET name = ?, type = ?, location = ?, speed = ?, concurrent_connections = ?, price = ?, original_price = ?, profit_margin = ?, provider = ?, description = ?, stock_quantity = ?, status = ? WHERE id = ?");
                if ($stmt->execute([$name, $type, $location, $speed, $concurrentConnections, $price, $originalPrice, $profitMargin, $provider, $description, $stockQuantity, $status, $id])) {
                    $success = 'Cập nhật gói Proxy thành công!';
                } else {
                    $error = 'Có lỗi xảy ra khi cập nhật gói Proxy!';
                }
            } catch (Exception $e) {
                $error = 'Có lỗi xảy ra: ' . $e->getMessage();
            }
        }
    }
}

$proxyPackages = getAllProxyPackages();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý gói Proxy - VPS & Proxy Việt Nam</title>
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

            <!-- Header Actions -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Quản lý gói Proxy</h2>
                    <p class="text-gray-600 mt-1">Quản lý tất cả gói Proxy SOCKS5</p>
                </div>
                <div class="mt-4 sm:mt-0">
                    <button onclick="openAddPackageModal()" class="bg-gradient-to-r from-green-600 to-blue-600 text-white px-4 py-2 rounded-lg hover:from-green-700 hover:to-blue-700 transition-all duration-200 flex items-center space-x-2">
                        <i class="fas fa-plus"></i>
                        <span>Thêm gói Proxy</span>
                    </button>
                </div>
            </div>

            <!-- Packages Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($proxyPackages as $package): ?>
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden hover:shadow-xl transition-shadow duration-300">
                    <!-- Package Header -->
                    <div class="p-6 bg-gradient-to-r from-green-500 to-blue-500 text-white">
                        <div class="flex items-center justify-between mb-2">
                            <span class="px-3 py-1 bg-white bg-opacity-20 rounded-full text-sm font-medium">
                                Proxy <?php echo strtoupper($package['type']); ?>
                            </span>
                            <span class="px-2 py-1 bg-white bg-opacity-20 rounded text-xs">
                                <?php echo $package['status'] === 'active' ? 'Hoạt động' : 'Tạm dừng'; ?>
                            </span>
                        </div>
                        <h3 class="text-xl font-bold mb-1"><?php echo htmlspecialchars($package['name']); ?></h3>
                        <p class="text-sm opacity-90"><?php echo htmlspecialchars($package['provider']); ?></p>
                    </div>

                    <!-- Package Details -->
                    <div class="p-6">
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <span class="text-xs text-gray-500">Vị trí</span>
                                <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($package['location']); ?></p>
                            </div>
                            <div>
                                <span class="text-xs text-gray-500">Tốc độ</span>
                                <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($package['speed']); ?></p>
                            </div>
                            <div>
                                <span class="text-xs text-gray-500">Kết nối</span>
                                <p class="font-semibold text-gray-900"><?php echo $package['concurrent_connections']; ?></p>
                            </div>
                            <div>
                                <span class="text-xs text-gray-500">Giao thức</span>
                                <p class="font-semibold text-gray-900"><?php echo strtoupper($package['type']); ?></p>
                            </div>
                        </div>

                        <!-- Pricing -->
                        <div class="mb-4">
                            <div class="flex items-baseline space-x-2">
                                <span class="text-2xl font-bold text-gray-900"><?php echo formatCurrency($package['price']); ?></span>
                                <span class="text-gray-500 ml-1">/tháng</span>
                            </div>
                            <?php if ($package['original_price'] > 0): ?>
                            <div class="flex items-center space-x-2 mt-1">
                                <span class="text-sm text-gray-500 line-through"><?php echo formatCurrency($package['original_price']); ?></span>
                                <span class="text-sm text-green-600 font-medium">Lãi: <?php echo number_format($package['profit_margin'], 1); ?>%</span>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Stock -->
                        <div class="mb-4">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-600">Tồn kho:</span>
                                <span class="font-semibold <?php echo $package['stock_quantity'] > 0 ? 'text-green-600' : 'text-red-600'; ?>">
                                    <?php echo $package['stock_quantity']; ?> Proxy
                                </span>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex space-x-2">
                            <button onclick="editPackage(<?php echo htmlspecialchars(json_encode($package)); ?>)" 
                                    class="flex-1 bg-green-600 text-white py-2 px-3 rounded-lg hover:bg-green-700 transition-colors duration-200 text-sm">
                                <i class="fas fa-edit mr-1"></i>
                                Chỉnh sửa
                            </button>
                            <button onclick="togglePackageStatus(<?php echo $package['id']; ?>, '<?php echo $package['status']; ?>')" 
                                    class="bg-gray-600 text-white py-2 px-3 rounded-lg hover:bg-gray-700 transition-colors duration-200 text-sm">
                                <i class="fas fa-<?php echo $package['status'] === 'active' ? 'pause' : 'play'; ?>"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>

    <!-- Add/Edit Package Modal -->
    <div id="packageModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50 hidden">
        <div class="bg-white rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 id="modalTitle" class="text-2xl font-bold text-gray-900">Thêm gói Proxy</h2>
                    <button onclick="closePackageModal()" class="p-2 hover:bg-gray-100 rounded-lg">
                        <i class="fas fa-times text-gray-500"></i>
                    </button>
                </div>

                <form method="POST" id="packageForm">
                    <input type="hidden" name="action" id="formAction" value="add_package">
                    <input type="hidden" name="package_id" id="packageId">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Basic Info -->
                        <div class="space-y-4">
                            <h3 class="text-lg font-semibold text-gray-900">Thông tin cơ bản</h3>
                            
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Tên gói *</label>
                                <input type="text" name="name" id="name" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>

                            <div>
                                <label for="type" class="block text-sm font-medium text-gray-700 mb-2">Giao thức *</label>
                                <select name="type" id="type" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="socks5">SOCKS5</option>
                                    <option value="http">HTTP</option>
                                    <option value="https">HTTPS</option>
                                </select>
                            </div>

                            <div>
                                <label for="provider" class="block text-sm font-medium text-gray-700 mb-2">Nhà cung cấp *</label>
                                <input type="text" name="provider" id="provider" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                       placeholder="ProxyMesh, SmartProxy, BrightData...">
                            </div>

                            <div>
                                <label for="location" class="block text-sm font-medium text-gray-700 mb-2">Vị trí *</label>
                                <select name="location" id="location" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="">Chọn vị trí</option>
                                    <option value="Việt Nam">Việt Nam</option>
                                    <option value="Singapore">Singapore</option>
                                    <option value="United States">United States</option>
                                    <option value="Germany">Germany</option>
                                    <option value="Japan">Japan</option>
                                    <option value="United Kingdom">United Kingdom</option>
                                    <option value="Canada">Canada</option>
                                    <option value="Australia">Australia</option>
                                    <option value="Multiple">Multiple Locations</option>
                                </select>
                            </div>

                            <div>
                                <label for="stock_quantity" class="block text-sm font-medium text-gray-700 mb-2">Số lượng tồn kho *</label>
                                <input type="number" name="stock_quantity" id="stock_quantity" required min="0"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                        </div>

                        <!-- Specifications -->
                        <div class="space-y-4">
                            <h3 class="text-lg font-semibold text-gray-900">Thông số kỹ thuật</h3>
                            
                            <div>
                                <label for="speed" class="block text-sm font-medium text-gray-700 mb-2">Tốc độ *</label>
                                <select name="speed" id="speed" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="">Chọn tốc độ</option>
                                    <option value="100 Mbps">100 Mbps</option>
                                    <option value="500 Mbps">500 Mbps</option>
                                    <option value="1 Gbps">1 Gbps</option>
                                    <option value="2 Gbps">2 Gbps</option>
                                    <option value="5 Gbps">5 Gbps</option>
                                    <option value="10 Gbps">10 Gbps</option>
                                </select>
                            </div>

                            <div>
                                <label for="concurrent_connections" class="block text-sm font-medium text-gray-700 mb-2">Kết nối đồng thời *</label>
                                <input type="number" name="concurrent_connections" id="concurrent_connections" required min="1"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                       placeholder="Số kết nối đồng thời">
                            </div>

                            <div>
                                <label for="original_price" class="block text-sm font-medium text-gray-700 mb-2">Giá vốn (VND)</label>
                                <input type="number" name="original_price" id="original_price" min="0" step="1000"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                       onchange="calculateProfit()">
                            </div>

                            <div>
                                <label for="price" class="block text-sm font-medium text-gray-700 mb-2">Giá bán (VND) *</label>
                                <input type="number" name="price" id="price" required min="0" step="1000"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                       onchange="calculateProfit()">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Lợi nhuận (%)</label>
                                <input type="text" id="profit_display" readonly
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-600">
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="mt-6">
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Mô tả</label>
                        <textarea name="description" id="description" rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                  placeholder="Mô tả chi tiết về gói Proxy..."></textarea>
                    </div>

                    <!-- Status (for edit mode) -->
                    <div class="mt-6 hidden" id="statusDiv">
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Trạng thái</label>
                        <select name="status" id="status"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="active">Hoạt động</option>
                            <option value="inactive">Tạm dừng</option>
                            <option value="out_of_stock">Hết hàng</option>
                        </select>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex space-x-3 mt-8">
                        <button type="button" onclick="closePackageModal()" class="flex-1 py-3 px-4 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                            Hủy
                        </button>
                        <button type="submit" class="flex-1 bg-gradient-to-r from-green-600 to-blue-600 text-white py-3 px-4 rounded-lg hover:from-green-700 hover:to-blue-700 transition-all duration-200">
                            <span id="submitText">Thêm gói Proxy</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openAddPackageModal() {
            document.getElementById('modalTitle').textContent = 'Thêm gói Proxy';
            document.getElementById('formAction').value = 'add_package';
            document.getElementById('submitText').textContent = 'Thêm gói Proxy';
            document.getElementById('statusDiv').classList.add('hidden');
            document.getElementById('packageForm').reset();
            document.getElementById('packageModal').classList.remove('hidden');
        }

        function editPackage(package) {
            document.getElementById('modalTitle').textContent = 'Chỉnh sửa gói Proxy';
            document.getElementById('formAction').value = 'update_package';
            document.getElementById('submitText').textContent = 'Cập nhật gói Proxy';
            document.getElementById('statusDiv').classList.remove('hidden');
            
            // Fill form with package data
            document.getElementById('packageId').value = package.id;
            document.getElementById('name').value = package.name;
            document.getElementById('type').value = package.type;
            document.getElementById('location').value = package.location;
            document.getElementById('speed').value = package.speed;
            document.getElementById('concurrent_connections').value = package.concurrent_connections;
            document.getElementById('price').value = package.price;
            document.getElementById('original_price').value = package.original_price || '';
            document.getElementById('provider').value = package.provider;
            document.getElementById('description').value = package.description || '';
            document.getElementById('stock_quantity').value = package.stock_quantity;
            document.getElementById('status').value = package.status;
            
            calculateProfit();
            document.getElementById('packageModal').classList.remove('hidden');
        }

        function closePackageModal() {
            document.getElementById('packageModal').classList.add('hidden');
        }

        function calculateProfit() {
            const originalPrice = parseFloat(document.getElementById('original_price').value) || 0;
            const price = parseFloat(document.getElementById('price').value) || 0;
            
            if (originalPrice > 0 && price > originalPrice) {
                const profit = ((price - originalPrice) / originalPrice) * 100;
                document.getElementById('profit_display').value = profit.toFixed(1) + '%';
            } else {
                document.getElementById('profit_display').value = '0%';
            }
        }

        function togglePackageStatus(packageId, currentStatus) {
            const newStatus = currentStatus === 'active' ? 'inactive' : 'active';
            
            if (confirm(`Bạn có chắc muốn ${newStatus === 'active' ? 'kích hoạt' : 'tạm dừng'} gói Proxy này?`)) {
                // Create a form to submit the status change
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="update_package">
                    <input type="hidden" name="package_id" value="${packageId}">
                    <input type="hidden" name="status" value="${newStatus}">
                `;
                
                // Get current package data and submit
                fetch(`../api/get_package.php?id=${packageId}&type=proxy`)
                    .then(response => response.json())
                    .then(package => {
                        // Add all required fields
                        const fields = ['name', 'type', 'location', 'speed', 'concurrent_connections', 'price', 'original_price', 'provider', 'description', 'stock_quantity'];
                        fields.forEach(field => {
                            const input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = field;
                            input.value = package[field] || '';
                            form.appendChild(input);
                        });
                        
                        document.body.appendChild(form);
                        form.submit();
                    });
            }
        }
    </script>
</body>
</html>