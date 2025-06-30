<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is admin
if (!isAdmin()) {
    redirectTo('../login.php');
}

$pageTitle = 'Quản lý gói VPS';
$success = '';
$error = '';

// Handle package actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add_package') {
            $name = sanitizeInput($_POST['name']);
            $type = $_POST['type'];
            $provider = sanitizeInput($_POST['provider']);
            $cpu = sanitizeInput($_POST['cpu']);
            $ram = sanitizeInput($_POST['ram']);
            $storage = sanitizeInput($_POST['storage']);
            $bandwidth = sanitizeInput($_POST['bandwidth']);
            $price = (float)$_POST['price'];
            $originalPrice = (float)$_POST['original_price'];
            $trialDuration = $type === 'trial' ? (int)$_POST['trial_duration'] : 0;
            $description = sanitizeInput($_POST['description']);
            $datacenterLocation = sanitizeInput($_POST['datacenter_location']);
            $stockQuantity = (int)$_POST['stock_quantity'];
            
            $profitMargin = $originalPrice > 0 ? (($price - $originalPrice) / $originalPrice) * 100 : 0;
            
            try {
                $stmt = $pdo->prepare("INSERT INTO vps_packages (name, type, provider, cpu, ram, storage, bandwidth, price, original_price, profit_margin, trial_duration, description, datacenter_location, stock_quantity) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                if ($stmt->execute([$name, $type, $provider, $cpu, $ram, $storage, $bandwidth, $price, $originalPrice, $profitMargin, $trialDuration, $description, $datacenterLocation, $stockQuantity])) {
                    $success = 'Thêm gói VPS thành công!';
                } else {
                    $error = 'Có lỗi xảy ra khi thêm gói VPS!';
                }
            } catch (Exception $e) {
                $error = 'Có lỗi xảy ra: ' . $e->getMessage();
            }
        } elseif ($_POST['action'] === 'update_package') {
            $id = (int)$_POST['package_id'];
            $name = sanitizeInput($_POST['name']);
            $type = $_POST['type'];
            $provider = sanitizeInput($_POST['provider']);
            $cpu = sanitizeInput($_POST['cpu']);
            $ram = sanitizeInput($_POST['ram']);
            $storage = sanitizeInput($_POST['storage']);
            $bandwidth = sanitizeInput($_POST['bandwidth']);
            $price = (float)$_POST['price'];
            $originalPrice = (float)$_POST['original_price'];
            $trialDuration = $type === 'trial' ? (int)$_POST['trial_duration'] : 0;
            $description = sanitizeInput($_POST['description']);
            $datacenterLocation = sanitizeInput($_POST['datacenter_location']);
            $stockQuantity = (int)$_POST['stock_quantity'];
            $status = $_POST['status'];
            
            $profitMargin = $originalPrice > 0 ? (($price - $originalPrice) / $originalPrice) * 100 : 0;
            
            try {
                $stmt = $pdo->prepare("UPDATE vps_packages SET name = ?, type = ?, provider = ?, cpu = ?, ram = ?, storage = ?, bandwidth = ?, price = ?, original_price = ?, profit_margin = ?, trial_duration = ?, description = ?, datacenter_location = ?, stock_quantity = ?, status = ? WHERE id = ?");
                if ($stmt->execute([$name, $type, $provider, $cpu, $ram, $storage, $bandwidth, $price, $originalPrice, $profitMargin, $trialDuration, $description, $datacenterLocation, $stockQuantity, $status, $id])) {
                    $success = 'Cập nhật gói VPS thành công!';
                } else {
                    $error = 'Có lỗi xảy ra khi cập nhật gói VPS!';
                }
            } catch (Exception $e) {
                $error = 'Có lỗi xảy ra: ' . $e->getMessage();
            }
        }
    }
}

$vpsPackages = getAllVPSPackages();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý gói VPS - VPS & Proxy Việt Nam</title>
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
                    <h2 class="text-2xl font-bold text-gray-900">Quản lý gói VPS</h2>
                    <p class="text-gray-600 mt-1">Quản lý tất cả gói VPS Trial và VPS Chính hãng</p>
                </div>
                <div class="mt-4 sm:mt-0">
                    <button onclick="openAddPackageModal()" class="bg-gradient-to-r from-blue-600 to-purple-600 text-white px-4 py-2 rounded-lg hover:from-blue-700 hover:to-purple-700 transition-all duration-200 flex items-center space-x-2">
                        <i class="fas fa-plus"></i>
                        <span>Thêm gói VPS</span>
                    </button>
                </div>
            </div>

            <!-- Packages Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($vpsPackages as $package): ?>
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden hover:shadow-xl transition-shadow duration-300">
                    <!-- Package Header -->
                    <div class="p-6 <?php echo $package['type'] === 'trial' ? 'bg-gradient-to-r from-orange-500 to-red-500' : 'bg-gradient-to-r from-blue-500 to-purple-500'; ?> text-white">
                        <div class="flex items-center justify-between mb-2">
                            <span class="px-3 py-1 bg-white bg-opacity-20 rounded-full text-sm font-medium">
                                <?php echo $package['type'] === 'trial' ? 'VPS Trial' : 'VPS Chính hãng'; ?>
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
                                <span class="text-xs text-gray-500">CPU</span>
                                <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($package['cpu']); ?></p>
                            </div>
                            <div>
                                <span class="text-xs text-gray-500">RAM</span>
                                <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($package['ram']); ?></p>
                            </div>
                            <div>
                                <span class="text-xs text-gray-500">Storage</span>
                                <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($package['storage']); ?></p>
                            </div>
                            <div>
                                <span class="text-xs text-gray-500">Bandwidth</span>
                                <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($package['bandwidth']); ?></p>
                            </div>
                        </div>

                        <!-- Pricing -->
                        <div class="mb-4">
                            <div class="flex items-baseline space-x-2">
                                <span class="text-2xl font-bold text-gray-900"><?php echo formatCurrency($package['price']); ?></span>
                                <?php if ($package['type'] === 'trial'): ?>
                                <span class="text-sm text-gray-500">/<?php echo $package['trial_duration']; ?> ngày</span>
                                <?php else: ?>
                                <span class="text-sm text-gray-500">/tháng</span>
                                <?php endif; ?>
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
                                    <?php echo $package['stock_quantity']; ?> VPS
                                </span>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex space-x-2">
                            <button onclick="editPackage(<?php echo htmlspecialchars(json_encode($package)); ?>)" 
                                    class="flex-1 bg-blue-600 text-white py-2 px-3 rounded-lg hover:bg-blue-700 transition-colors duration-200 text-sm">
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
                    <h2 id="modalTitle" class="text-2xl font-bold text-gray-900">Thêm gói VPS</h2>
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
                                <label for="type" class="block text-sm font-medium text-gray-700 mb-2">Loại VPS *</label>
                                <select name="type" id="type" required onchange="toggleTrialDuration()"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="trial">VPS Trial</option>
                                    <option value="official">VPS Chính hãng</option>
                                </select>
                            </div>

                            <div>
                                <label for="provider" class="block text-sm font-medium text-gray-700 mb-2">Nhà cung cấp *</label>
                                <input type="text" name="provider" id="provider" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                       placeholder="DigitalOcean, Vultr, Linode...">
                            </div>

                            <div id="trialDurationDiv">
                                <label for="trial_duration" class="block text-sm font-medium text-gray-700 mb-2">Thời hạn trial (ngày)</label>
                                <input type="number" name="trial_duration" id="trial_duration" min="1" max="30" value="7"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>

                            <div>
                                <label for="datacenter_location" class="block text-sm font-medium text-gray-700 mb-2">Vị trí datacenter</label>
                                <input type="text" name="datacenter_location" id="datacenter_location"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                       placeholder="Singapore, Tokyo, New York...">
                            </div>
                        </div>

                        <!-- Specifications -->
                        <div class="space-y-4">
                            <h3 class="text-lg font-semibold text-gray-900">Cấu hình</h3>
                            
                            <div>
                                <label for="cpu" class="block text-sm font-medium text-gray-700 mb-2">CPU *</label>
                                <input type="text" name="cpu" id="cpu" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                       placeholder="1 vCPU, 2 vCPU...">
                            </div>

                            <div>
                                <label for="ram" class="block text-sm font-medium text-gray-700 mb-2">RAM *</label>
                                <input type="text" name="ram" id="ram" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                       placeholder="1 GB, 2 GB...">
                            </div>

                            <div>
                                <label for="storage" class="block text-sm font-medium text-gray-700 mb-2">Lưu trữ *</label>
                                <input type="text" name="storage" id="storage" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                       placeholder="25 GB SSD, 50 GB NVMe...">
                            </div>

                            <div>
                                <label for="bandwidth" class="block text-sm font-medium text-gray-700 mb-2">Băng thông *</label>
                                <input type="text" name="bandwidth" id="bandwidth" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                       placeholder="1 TB, 3 TB...">
                            </div>

                            <div>
                                <label for="stock_quantity" class="block text-sm font-medium text-gray-700 mb-2">Số lượng tồn kho *</label>
                                <input type="number" name="stock_quantity" id="stock_quantity" required min="0"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                        </div>
                    </div>

                    <!-- Pricing -->
                    <div class="mt-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Giá cả</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
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
                                  placeholder="Mô tả chi tiết về gói VPS..."></textarea>
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
                        <button type="submit" class="flex-1 bg-gradient-to-r from-blue-600 to-purple-600 text-white py-3 px-4 rounded-lg hover:from-blue-700 hover:to-purple-700 transition-all duration-200">
                            <span id="submitText">Thêm gói VPS</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openAddPackageModal() {
            document.getElementById('modalTitle').textContent = 'Thêm gói VPS';
            document.getElementById('formAction').value = 'add_package';
            document.getElementById('submitText').textContent = 'Thêm gói VPS';
            document.getElementById('statusDiv').classList.add('hidden');
            document.getElementById('packageForm').reset();
            document.getElementById('packageModal').classList.remove('hidden');
            toggleTrialDuration();
        }

        function editPackage(package) {
            document.getElementById('modalTitle').textContent = 'Chỉnh sửa gói VPS';
            document.getElementById('formAction').value = 'update_package';
            document.getElementById('submitText').textContent = 'Cập nhật gói VPS';
            document.getElementById('statusDiv').classList.remove('hidden');
            
            // Fill form with package data
            document.getElementById('packageId').value = package.id;
            document.getElementById('name').value = package.name;
            document.getElementById('type').value = package.type;
            document.getElementById('provider').value = package.provider;
            document.getElementById('cpu').value = package.cpu;
            document.getElementById('ram').value = package.ram;
            document.getElementById('storage').value = package.storage;
            document.getElementById('bandwidth').value = package.bandwidth;
            document.getElementById('price').value = package.price;
            document.getElementById('original_price').value = package.original_price || '';
            document.getElementById('trial_duration').value = package.trial_duration || 7;
            document.getElementById('description').value = package.description || '';
            document.getElementById('datacenter_location').value = package.datacenter_location || '';
            document.getElementById('stock_quantity').value = package.stock_quantity;
            document.getElementById('status').value = package.status;
            
            toggleTrialDuration();
            calculateProfit();
            document.getElementById('packageModal').classList.remove('hidden');
        }

        function closePackageModal() {
            document.getElementById('packageModal').classList.add('hidden');
        }

        function toggleTrialDuration() {
            const type = document.getElementById('type').value;
            const trialDiv = document.getElementById('trialDurationDiv');
            
            if (type === 'trial') {
                trialDiv.style.display = 'block';
            } else {
                trialDiv.style.display = 'none';
            }
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
            
            if (confirm(`Bạn có chắc muốn ${newStatus === 'active' ? 'kích hoạt' : 'tạm dừng'} gói VPS này?`)) {
                // Create a form to submit the status change
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="update_package">
                    <input type="hidden" name="package_id" value="${packageId}">
                    <input type="hidden" name="status" value="${newStatus}">
                `;
                
                // Get current package data and submit
                fetch(`../api/get_package.php?id=${packageId}&type=vps`)
                    .then(response => response.json())
                    .then(package => {
                        // Add all required fields
                        const fields = ['name', 'type', 'provider', 'cpu', 'ram', 'storage', 'bandwidth', 'price', 'original_price', 'trial_duration', 'description', 'datacenter_location', 'stock_quantity'];
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