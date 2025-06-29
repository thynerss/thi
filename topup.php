<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirectTo('login.php');
}

$user = getUserById($_SESSION['user_id']);
$banks = getActiveBanks();
$topupHistory = getUserTopUps($_SESSION['user_id']);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = (int)$_POST['amount'];
    $bankId = (int)$_POST['bank_id'];
    $transferContent = sanitizeInput($_POST['transfer_content']);
    
    if ($amount < 50000) {
        $error = 'Số tiền nạp tối thiểu là 50.000 VND';
    } elseif ($amount > 50000000) {
        $error = 'Số tiền nạp tối đa là 50.000.000 VND';
    } elseif (empty($transferContent)) {
        $error = 'Vui lòng nhập nội dung chuyển khoản';
    } else {
        $selectedBank = null;
        foreach ($banks as $bank) {
            if ($bank['id'] == $bankId) {
                $selectedBank = $bank;
                break;
            }
        }
        
        if ($selectedBank && createTopUpRequest($_SESSION['user_id'], $amount, 'Chuyển khoản ' . $selectedBank['bank_name'], $transferContent)) {
            $success = 'Yêu cầu nạp tiền đã được gửi thành công! Chúng tôi sẽ xử lý trong vòng 24 giờ.';
            $topupHistory = getUserTopUps($_SESSION['user_id']); // Refresh history
        } else {
            $error = 'Có lỗi xảy ra. Vui lòng thử lại.';
        }
    }
}

// Generate transfer content
$transferContent = generateTransferContent($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nạp tiền - VPS Việt Nam Pro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .card-hover { transition: all 0.3s ease; }
        .card-hover:hover { transform: translateY(-4px); box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        .gradient-bg { background: linear-gradient(135deg, #1e3a8a 0%, #3730a3 50%, #7c3aed 100%); }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <?php include 'includes/header.php'; ?>

    <div class="min-h-screen py-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Nạp tiền vào tài khoản</h1>
                <p class="text-gray-600 mt-2">Nạp tiền để mua các dịch vụ VPS và Proxy</p>
            </div>

            <?php if ($success): ?>
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                <div class="flex items-center space-x-2">
                    <i class="fas fa-check-circle text-green-500"></i>
                    <span class="text-green-700 font-medium"><?php echo htmlspecialchars($success); ?></span>
                </div>
            </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Top-up Form -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white rounded-xl shadow-lg p-6">
                        <div class="flex items-center space-x-3 mb-6">
                            <div class="bg-gradient-to-r from-blue-600 to-purple-600 p-3 rounded-lg">
                                <i class="fas fa-credit-card text-white"></i>
                            </div>
                            <div>
                                <h2 class="text-xl font-bold text-gray-900">Nạp tiền qua chuyển khoản</h2>
                                <p class="text-gray-600">Số dư hiện tại: <span class="font-semibold text-green-600"><?php echo formatCurrency($user['balance']); ?></span></p>
                            </div>
                        </div>

                        <?php if ($error): ?>
                        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                            <p class="text-red-600 text-sm"><?php echo htmlspecialchars($error); ?></p>
                        </div>
                        <?php endif; ?>

                        <form method="POST" class="space-y-6">
                            <div>
                                <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">
                                    Số tiền nạp (VND)
                                </label>
                                <div class="relative">
                                    <i class="fas fa-dollar-sign absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                                    <input type="number" id="amount" name="amount" min="50000" max="50000000" step="1000" required
                                           class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                           placeholder="0" value="<?php echo isset($_POST['amount']) ? $_POST['amount'] : ''; ?>" onchange="updateQRCode()">
                                </div>
                                <p class="text-sm text-gray-500 mt-1">Số tiền nạp: 50.000 VND - 50.000.000 VND</p>
                                
                                <!-- Quick amount buttons -->
                                <div class="grid grid-cols-3 gap-2 mt-3">
                                    <button type="button" onclick="setAmount(100000)" class="px-3 py-2 text-sm bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors duration-200">
                                        100.000 VND
                                    </button>
                                    <button type="button" onclick="setAmount(500000)" class="px-3 py-2 text-sm bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors duration-200">
                                        500.000 VND
                                    </button>
                                    <button type="button" onclick="setAmount(1000000)" class="px-3 py-2 text-sm bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors duration-200">
                                        1.000.000 VND
                                    </button>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Chọn ngân hàng
                                </label>
                                <div class="grid grid-cols-1 gap-3">
                                    <?php foreach ($banks as $bank): ?>
                                    <label class="cursor-pointer">
                                        <input type="radio" name="bank_id" value="<?php echo $bank['id']; ?>" class="sr-only" required onchange="updateQRCode()">
                                        <div class="p-4 border-2 rounded-lg transition-all duration-200 hover:border-blue-300 bank-option" data-bank-id="<?php echo $bank['bank_id'] ?? 'vietcombank'; ?>" data-account="<?php echo htmlspecialchars($bank['account_number']); ?>" data-name="<?php echo htmlspecialchars($bank['account_name']); ?>">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center space-x-3">
                                                    <i class="fas fa-university text-blue-600"></i>
                                                    <div>
                                                        <h3 class="font-semibold text-gray-900"><?php echo htmlspecialchars($bank['bank_name']); ?></h3>
                                                        <p class="text-sm text-gray-600"><?php echo htmlspecialchars($bank['branch']); ?></p>
                                                    </div>
                                                </div>
                                                <div class="text-right">
                                                    <p class="font-mono text-sm font-medium"><?php echo htmlspecialchars($bank['account_number']); ?></p>
                                                    <p class="text-xs text-gray-500"><?php echo htmlspecialchars($bank['account_name']); ?></p>
                                                </div>
                                            </div>
                                        </div>
                                    </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <div>
                                <label for="transfer_content" class="block text-sm font-medium text-gray-700 mb-2">
                                    Nội dung chuyển khoản
                                </label>
                                <div class="relative">
                                    <input type="text" id="transfer_content" name="transfer_content" value="<?php echo $transferContent; ?>" readonly
                                           class="w-full pr-10 py-3 border border-gray-300 rounded-lg bg-gray-50 font-mono">
                                    <button type="button" onclick="copyToClipboard('<?php echo $transferContent; ?>')" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                                <p class="text-sm text-yellow-600 mt-1">
                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                    Vui lòng chuyển khoản đúng nội dung này để hệ thống tự động xử lý
                                </p>
                            </div>

                            <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-purple-600 text-white py-3 px-4 rounded-lg font-semibold hover:from-blue-700 hover:to-purple-700 transition-all duration-200 flex items-center justify-center space-x-2">
                                <i class="fas fa-credit-card"></i>
                                <span>Gửi yêu cầu nạp tiền</span>
                            </button>
                        </form>
                    </div>

                    <!-- VietQR Section -->
                    <div class="bg-white rounded-xl shadow-lg p-6">
                        <div class="flex items-center space-x-3 mb-6">
                            <div class="bg-gradient-to-r from-green-600 to-blue-600 p-3 rounded-lg">
                                <i class="fas fa-qrcode text-white"></i>
                            </div>
                            <div>
                                <h2 class="text-xl font-bold text-gray-900">Quét mã QR để chuyển khoản</h2>
                                <p class="text-gray-600">Sử dụng app ngân hàng để quét mã QR</p>
                            </div>
                        </div>

                        <div class="text-center">
                            <div id="qr-container" class="inline-block p-4 bg-gray-50 rounded-lg">
                                <img id="qr-code" src="" alt="QR Code" class="w-64 h-64 mx-auto" style="display: none;">
                                <div id="qr-placeholder" class="w-64 h-64 flex items-center justify-center bg-gray-200 rounded-lg">
                                    <div class="text-center">
                                        <i class="fas fa-qrcode text-4xl text-gray-400 mb-2"></i>
                                        <p class="text-gray-500">Chọn ngân hàng và số tiền để tạo mã QR</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-4 space-y-2">
                                <p class="text-sm text-gray-600">
                                    <i class="fas fa-info-circle text-blue-500 mr-1"></i>
                                    Quét mã QR bằng app ngân hàng để chuyển khoản nhanh chóng
                                </p>
                                <p class="text-sm text-gray-600">
                                    <i class="fas fa-mobile-alt text-green-500 mr-1"></i>
                                    Hỗ trợ tất cả app ngân hàng có tính năng quét QR
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top-up History -->
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-6">Lịch sử nạp tiền</h2>
                    
                    <div class="space-y-4">
                        <?php if (empty($topupHistory)): ?>
                        <p class="text-gray-500 text-center py-4">Chưa có lịch sử nạp tiền</p>
                        <?php else: ?>
                        <?php foreach ($topupHistory as $request): ?>
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center space-x-2">
                                    <?php echo getStatusIcon($request['status']); ?>
                                    <span class="font-semibold text-gray-900">
                                        <?php echo formatCurrency($request['amount']); ?>
                                    </span>
                                    <span class="px-2 py-1 rounded-full text-xs font-medium <?php echo getStatusColor($request['status']); ?>">
                                        <?php echo getStatusText($request['status']); ?>
                                    </span>
                                </div>
                                <span class="text-sm text-gray-500">
                                    <?php echo date('d/m/Y', strtotime($request['created_at'])); ?>
                                </span>
                            </div>
                            
                            <div class="text-sm text-gray-600 space-y-1">
                                <p>Phương thức: <?php echo htmlspecialchars($request['payment_method']); ?></p>
                                <?php if ($request['bank_transfer_content']): ?>
                                <p>Nội dung CK: <span class="font-mono bg-gray-100 px-1 rounded"><?php echo htmlspecialchars($request['bank_transfer_content']); ?></span></p>
                                <?php endif; ?>
                                <?php if ($request['processed_at']): ?>
                                <p>Xử lý lúc: <?php echo date('d/m/Y H:i', strtotime($request['processed_at'])); ?></p>
                                <?php endif; ?>
                                <?php if ($request['admin_note']): ?>
                                <p class="text-red-600 mt-1">Ghi chú: <?php echo htmlspecialchars($request['admin_note']); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Instructions Section -->
            <div class="mt-8 bg-gradient-to-r from-blue-50 to-purple-50 rounded-xl p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">
                    <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                    Hướng dẫn nạp tiền
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h4 class="font-semibold text-gray-900 mb-2">Cách 1: Quét mã QR</h4>
                        <ol class="list-decimal list-inside space-y-1 text-sm text-gray-600">
                            <li>Chọn ngân hàng và số tiền muốn nạp</li>
                            <li>Mở app ngân hàng trên điện thoại</li>
                            <li>Quét mã QR được tạo tự động</li>
                            <li>Xác nhận chuyển khoản</li>
                        </ol>
                    </div>
                    <div>
                        <h4 class="font-semibold text-gray-900 mb-2">Cách 2: Chuyển khoản thủ công</h4>
                        <ol class="list-decimal list-inside space-y-1 text-sm text-gray-600">
                            <li>Chọn ngân hàng và số tiền</li>
                            <li>Chuyển khoản đến số tài khoản</li>
                            <li>Nhập đúng nội dung chuyển khoản</li>
                            <li>Gửi yêu cầu nạp tiền</li>
                        </ol>
                    </div>
                </div>
                <div class="mt-4 p-3 bg-yellow-50 rounded-lg">
                    <p class="text-sm text-yellow-800">
                        <i class="fas fa-clock mr-1"></i>
                        Thời gian xử lý: 5-30 phút (tự động) hoặc tối đa 24 giờ (thủ công)
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <script>
        // Bank ID mapping for VietQR
        const bankMapping = {
            'Vietcombank': 'vietcombank',
            'Techcombank': 'techcombank', 
            'BIDV': 'bidv',
            'VietinBank': 'vietinbank'
        };

        function setAmount(amount) {
            document.getElementById('amount').value = amount;
            updateQRCode();
        }

        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                // Show success message
                const button = event.target.closest('button');
                const icon = button.querySelector('i');
                icon.className = 'fas fa-check';
                setTimeout(() => {
                    icon.className = 'fas fa-copy';
                }, 2000);
            });
        }

        function updateQRCode() {
            const amount = document.getElementById('amount').value;
            const selectedBank = document.querySelector('input[name="bank_id"]:checked');
            const transferContent = document.getElementById('transfer_content').value;
            
            if (amount && selectedBank && amount >= 50000) {
                const bankOption = selectedBank.closest('label').querySelector('.bank-option');
                const bankName = bankOption.closest('label').querySelector('h3').textContent.trim();
                const accountNumber = bankOption.dataset.account;
                const accountName = bankOption.dataset.name;
                
                // Get bank ID for VietQR
                const bankId = bankMapping[bankName] || 'vietcombank';
                
                // Generate VietQR URL
                const qrUrl = `https://img.vietqr.io/image/${bankId}-${accountNumber}-compact2.jpg?amount=${amount}&addInfo=${encodeURIComponent(transferContent)}&accountName=${encodeURIComponent(accountName)}`;
                
                // Update QR code
                const qrCode = document.getElementById('qr-code');
                const qrPlaceholder = document.getElementById('qr-placeholder');
                
                qrCode.src = qrUrl;
                qrCode.style.display = 'block';
                qrPlaceholder.style.display = 'none';
            } else {
                // Hide QR code if conditions not met
                const qrCode = document.getElementById('qr-code');
                const qrPlaceholder = document.getElementById('qr-placeholder');
                
                qrCode.style.display = 'none';
                qrPlaceholder.style.display = 'flex';
            }
        }

        // Handle bank selection
        document.addEventListener('DOMContentLoaded', function() {
            const bankOptions = document.querySelectorAll('.bank-option');
            const radioButtons = document.querySelectorAll('input[name="bank_id"]');
            
            radioButtons.forEach((radio, index) => {
                radio.addEventListener('change', function() {
                    bankOptions.forEach(option => {
                        option.classList.remove('border-blue-500', 'bg-blue-50');
                        option.classList.add('border-gray-200');
                    });
                    
                    if (this.checked) {
                        bankOptions[index].classList.remove('border-gray-200');
                        bankOptions[index].classList.add('border-blue-500', 'bg-blue-50');
                        updateQRCode();
                    }
                });
            });
            
            bankOptions.forEach((option, index) => {
                option.addEventListener('click', function() {
                    radioButtons[index].checked = true;
                    radioButtons[index].dispatchEvent(new Event('change'));
                });
            });
        });
    </script>
</body>
</html>