<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is admin
if (!isAdmin()) {
    redirectTo('../login.php');
}

$pageTitle = 'Quản lý ngân hàng';
$success = '';
$error = '';

// Handle bank actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add_bank') {
            $bankName = sanitizeInput($_POST['bank_name']);
            $accountNumber = sanitizeInput($_POST['account_number']);
            $accountName = sanitizeInput($_POST['account_name']);
            $branch = sanitizeInput($_POST['branch']);
            $bankId = sanitizeInput($_POST['bank_id']);
            
            try {
                $stmt = $pdo->prepare("INSERT INTO bank_info (bank_name, account_number, account_name, branch, bank_id) VALUES (?, ?, ?, ?, ?)");
                if ($stmt->execute([$bankName, $accountNumber, $accountName, $branch, $bankId])) {
                    $success = 'Thêm ngân hàng thành công!';
                } else {
                    $error = 'Có lỗi xảy ra khi thêm ngân hàng!';
                }
            } catch (Exception $e) {
                $error = 'Có lỗi xảy ra: ' . $e->getMessage();
            }
        } elseif ($_POST['action'] === 'update_bank') {
            $id = (int)$_POST['bank_id'];
            $bankName = sanitizeInput($_POST['bank_name']);
            $accountNumber = sanitizeInput($_POST['account_number']);
            $accountName = sanitizeInput($_POST['account_name']);
            $branch = sanitizeInput($_POST['branch']);
            $bankIdCode = sanitizeInput($_POST['bank_id_code']);
            $status = $_POST['status'];
            
            try {
                $stmt = $pdo->prepare("UPDATE bank_info SET bank_name = ?, account_number = ?, account_name = ?, branch = ?, bank_id = ?, status = ? WHERE id = ?");
                if ($stmt->execute([$bankName, $accountNumber, $accountName, $branch, $bankIdCode, $status, $id])) {
                    $success = 'Cập nhật ngân hàng thành công!';
                } else {
                    $error = 'Có lỗi xảy ra khi cập nhật ngân hàng!';
                }
            } catch (Exception $e) {
                $error = 'Có lỗi xảy ra: ' . $e->getMessage();
            }
        }
    }
}

// Get all banks
$stmt = $pdo->prepare("SELECT * FROM bank_info ORDER BY created_at DESC");
$stmt->execute();
$banks = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý ngân hàng - VPS & Proxy Việt Nam</title>
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
                    <h2 class="text-2xl font-bold text-gray-900">Quản lý ngân hàng</h2>
                    <p class="text-gray-600 mt-1">Quản lý thông tin tài khoản ngân hàng nhận thanh toán</p>
                </div>
                <div class="mt-4 sm:mt-0">
                    <button onclick="openAddBankModal()" class="bg-gradient-to-r from-blue-600 to-purple-600 text-white px-4 py-2 rounded-lg hover:from-blue-700 hover:to-purple-700 transition-all duration-200 flex items-center space-x-2">
                        <i class="fas fa-plus"></i>
                        <span>Thêm ngân hàng</span>
                    </button>
                </div>
            </div>

            <!-- Banks Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($banks as $bank): ?>
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden hover:shadow-xl transition-shadow duration-300">
                    <!-- Bank Header -->
                    <div class="p-6 bg-gradient-to-r from-blue-500 to-green-500 text-white">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center space-x-2">
                                <i class="fas fa-university text-xl"></i>
                                <span class="font-bold text-lg"><?php echo htmlspecialchars($bank['bank_name']); ?></span>
                            </div>
                            <span class="px-2 py-1 bg-white bg-opacity-20 rounded text-xs">
                                <?php echo $bank['status'] === 'active' ? 'Hoạt động' : 'Tạm dừng'; ?>
                            </span>
                        </div>
                        <p class="text-sm opacity-90"><?php echo htmlspecialchars($bank['branch']); ?></p>
                    </div>

                    <!-- Bank Details -->
                    <div class="p-6">
                        <div class="space-y-3 mb-4">
                            <div>
                                <span class="text-xs text-gray-500">Số tài khoản</span>
                                <p class="font-mono font-semibold text-gray-900"><?php echo htmlspecialchars($bank['account_number']); ?></p>
                            </div>
                            <div>
                                <span class="text-xs text-gray-500">Tên tài khoản</span>
                                <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($bank['account_name']); ?></p>
                            </div>
                            <?php if ($bank['bank_id']): ?>
                            <div>
                                <span class="text-xs text-gray-500">Mã ngân hàng (VietQR)</span>
                                <p class="font-mono text-sm text-gray-900"><?php echo htmlspecialchars($bank['bank_id']); ?></p>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- QR Code Preview -->
                        <?php if ($bank['bank_id']): ?>
                        <div class="mb-4 text-center">
                            <img src="https://img.vietqr.io/image/<?php echo $bank['bank_id']; ?>-<?php echo $bank['account_number']; ?>-compact2.jpg?amount=100000&addInfo=TEST&accountName=<?php echo urlencode($bank['account_name']); ?>" 
                                 alt="QR Code" class="w-24 h-24 mx-auto rounded-lg border">
                            <p class="text-xs text-gray-500 mt-1">QR Code mẫu</p>
                        </div>
                        <?php endif; ?>

                        <!-- Actions -->
                        <div class="flex space-x-2">
                            <button onclick="editBank(<?php echo htmlspecialchars(json_encode($bank)); ?>)" 
                                    class="flex-1 bg-blue-600 text-white py-2 px-3 rounded-lg hover:bg-blue-700 transition-colors duration-200 text-sm">
                                <i class="fas fa-edit mr-1"></i>
                                Chỉnh sửa
                            </button>
                            <button onclick="toggleBankStatus(<?php echo $bank['id']; ?>, '<?php echo $bank['status']; ?>')" 
                                    class="bg-gray-600 text-white py-2 px-3 rounded-lg hover:bg-gray-700 transition-colors duration-200 text-sm">
                                <i class="fas fa-<?php echo $bank['status'] === 'active' ? 'pause' : 'play'; ?>"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>

    <!-- Add/Edit Bank Modal -->
    <div id="bankModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50 hidden">
        <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full">
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 id="modalTitle" class="text-2xl font-bold text-gray-900">Thêm ngân hàng</h2>
                    <button onclick="closeBankModal()" class="p-2 hover:bg-gray-100 rounded-lg">
                        <i class="fas fa-times text-gray-500"></i>
                    </button>
                </div>

                <form method="POST" id="bankForm">
                    <input type="hidden" name="action" id="formAction" value="add_bank">
                    <input type="hidden" name="bank_id" id="bankIdHidden">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="bank_name" class="block text-sm font-medium text-gray-700 mb-2">Tên ngân hàng *</label>
                            <select name="bank_name" id="bank_name" required onchange="updateBankId()"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">Chọn ngân hàng</option>
                                <option value="Vietcombank" data-id="vietcombank">Vietcombank</option>
                                <option value="Techcombank" data-id="techcombank">Techcombank</option>
                                <option value="BIDV" data-id="bidv">BIDV</option>
                                <option value="VietinBank" data-id="vietinbank">VietinBank</option>
                                <option value="Agribank" data-id="agribank">Agribank</option>
                                <option value="MBBank" data-id="mbbank">MBBank</option>
                                <option value="VPBank" data-id="vpbank">VPBank</option>
                                <option value="TPBank" data-id="tpbank">TPBank</option>
                                <option value="Sacombank" data-id="sacombank">Sacombank</option>
                                <option value="ACB" data-id="acb">ACB</option>
                            </select>
                        </div>

                        <div>
                            <label for="account_number" class="block text-sm font-medium text-gray-700 mb-2">Số tài khoản *</label>
                            <input type="text" name="account_number" id="account_number" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="Nhập số tài khoản">
                        </div>

                        <div class="md:col-span-2">
                            <label for="account_name" class="block text-sm font-medium text-gray-700 mb-2">Tên tài khoản *</label>
                            <input type="text" name="account_name" id="account_name" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="Tên chủ tài khoản">
                        </div>

                        <div>
                            <label for="branch" class="block text-sm font-medium text-gray-700 mb-2">Chi nhánh</label>
                            <input type="text" name="branch" id="branch"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="Chi nhánh ngân hàng">
                        </div>

                        <div>
                            <label for="bank_id_input" class="block text-sm font-medium text-gray-700 mb-2">Mã ngân hàng (VietQR)</label>
                            <input type="text" name="bank_id" id="bank_id_input" readonly
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50"
                                   placeholder="Tự động điền">
                        </div>
                    </div>

                    <!-- Status (for edit mode) -->
                    <div class="mt-6 hidden" id="statusDiv">
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Trạng thái</label>
                        <select name="status" id="status"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="active">Hoạt động</option>
                            <option value="inactive">Tạm dừng</option>
                        </select>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex space-x-3 mt-8">
                        <button type="button" onclick="closeBankModal()" class="flex-1 py-3 px-4 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                            Hủy
                        </button>
                        <button type="submit" class="flex-1 bg-gradient-to-r from-blue-600 to-purple-600 text-white py-3 px-4 rounded-lg hover:from-blue-700 hover:to-purple-700 transition-all duration-200">
                            <span id="submitText">Thêm ngân hàng</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openAddBankModal() {
            document.getElementById('modalTitle').textContent = 'Thêm ngân hàng';
            document.getElementById('formAction').value = 'add_bank';
            document.getElementById('submitText').textContent = 'Thêm ngân hàng';
            document.getElementById('statusDiv').classList.add('hidden');
            document.getElementById('bankForm').reset();
            document.getElementById('bankModal').classList.remove('hidden');
        }

        function editBank(bank) {
            document.getElementById('modalTitle').textContent = 'Chỉnh sửa ngân hàng';
            document.getElementById('formAction').value = 'update_bank';
            document.getElementById('submitText').textContent = 'Cập nhật ngân hàng';
            document.getElementById('statusDiv').classList.remove('hidden');
            
            // Fill form with bank data
            document.getElementById('bankIdHidden').value = bank.id;
            document.getElementById('bank_name').value = bank.bank_name;
            document.getElementById('account_number').value = bank.account_number;
            document.getElementById('account_name').value = bank.account_name;
            document.getElementById('branch').value = bank.branch || '';
            document.getElementById('bank_id_input').value = bank.bank_id || '';
            document.getElementById('status').value = bank.status;
            
            // Update the hidden input name for edit mode
            document.getElementById('bank_id_input').name = 'bank_id_code';
            
            document.getElementById('bankModal').classList.remove('hidden');
        }

        function closeBankModal() {
            document.getElementById('bankModal').classList.add('hidden');
            // Reset the input name
            document.getElementById('bank_id_input').name = 'bank_id';
        }

        function updateBankId() {
            const select = document.getElementById('bank_name');
            const selectedOption = select.options[select.selectedIndex];
            const bankId = selectedOption.getAttribute('data-id');
            document.getElementById('bank_id_input').value = bankId || '';
        }

        function toggleBankStatus(bankId, currentStatus) {
            const newStatus = currentStatus === 'active' ? 'inactive' : 'active';
            
            if (confirm(`Bạn có chắc muốn ${newStatus === 'active' ? 'kích hoạt' : 'tạm dừng'} ngân hàng này?`)) {
                // Create a form to submit the status change
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="update_bank">
                    <input type="hidden" name="bank_id" value="${bankId}">
                    <input type="hidden" name="status" value="${newStatus}">
                `;
                
                // Get current bank data from the page
                const banks = <?php echo json_encode($banks); ?>;
                const bank = banks.find(b => b.id == bankId);
                
                if (bank) {
                    // Add all required fields
                    const fields = ['bank_name', 'account_number', 'account_name', 'branch'];
                    fields.forEach(field => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = field;
                        input.value = bank[field] || '';
                        form.appendChild(input);
                    });
                    
                    // Add bank_id_code
                    const bankIdInput = document.createElement('input');
                    bankIdInput.type = 'hidden';
                    bankIdInput.name = 'bank_id_code';
                    bankIdInput.value = bank.bank_id || '';
                    form.appendChild(bankIdInput);
                    
                    document.body.appendChild(form);
                    form.submit();
                }
            }
        }
    </script>
</body>
</html>