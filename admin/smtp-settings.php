<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is admin
if (!isAdmin()) {
    redirectTo('../login.php');
}

$pageTitle = 'Cài đặt SMTP';
$success = '';
$error = '';

// Handle SMTP settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $settings = [
        'smtp_host' => sanitizeInput($_POST['smtp_host']),
        'smtp_port' => (int)$_POST['smtp_port'],
        'smtp_username' => sanitizeInput($_POST['smtp_username']),
        'smtp_password' => sanitizeInput($_POST['smtp_password']),
        'smtp_encryption' => $_POST['smtp_encryption'],
        'smtp_from_email' => sanitizeInput($_POST['smtp_from_email']),
        'smtp_from_name' => sanitizeInput($_POST['smtp_from_name']),
        'smtp_enabled' => isset($_POST['smtp_enabled']) ? '1' : '0'
    ];
    
    $allSuccess = true;
    foreach ($settings as $key => $value) {
        if (!setSystemSetting($key, $value)) {
            $allSuccess = false;
        }
    }
    
    if ($allSuccess) {
        $success = 'Cập nhật cài đặt SMTP thành công!';
    } else {
        $error = 'Có lỗi xảy ra khi cập nhật cài đặt SMTP!';
    }
}

// Get current SMTP settings
$currentSettings = [
    'smtp_host' => getSystemSetting('smtp_host') ?: '',
    'smtp_port' => getSystemSetting('smtp_port') ?: '587',
    'smtp_username' => getSystemSetting('smtp_username') ?: '',
    'smtp_password' => getSystemSetting('smtp_password') ?: '',
    'smtp_encryption' => getSystemSetting('smtp_encryption') ?: 'tls',
    'smtp_from_email' => getSystemSetting('smtp_from_email') ?: '',
    'smtp_from_name' => getSystemSetting('smtp_from_name') ?: 'VPS Việt Nam Pro',
    'smtp_enabled' => getSystemSetting('smtp_enabled') === '1'
];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cài đặt SMTP - VPS & Proxy Việt Nam</title>
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

                <!-- SMTP Settings Form -->
                <div class="bg-white rounded-2xl shadow-lg p-8">
                    <div class="flex items-center space-x-3 mb-8">
                        <div class="bg-gradient-to-r from-blue-600 to-purple-600 p-3 rounded-xl">
                            <i class="fas fa-envelope text-white text-xl"></i>
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900">Cài đặt SMTP</h2>
                            <p class="text-gray-600">Cấu hình máy chủ email để gửi thông báo</p>
                        </div>
                    </div>

                    <form method="POST" class="space-y-6">
                        <!-- Enable SMTP -->
                        <div class="flex items-center justify-between p-4 bg-blue-50 rounded-lg">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Kích hoạt SMTP</h3>
                                <p class="text-sm text-gray-600">Bật/tắt gửi email qua SMTP</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="smtp_enabled" class="sr-only peer" <?php echo $currentSettings['smtp_enabled'] ? 'checked' : ''; ?>>
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div>

                        <!-- SMTP Server Settings -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="smtp_host" class="block text-sm font-medium text-gray-700 mb-2">
                                    SMTP Host *
                                </label>
                                <input type="text" name="smtp_host" id="smtp_host" required
                                       value="<?php echo htmlspecialchars($currentSettings['smtp_host']); ?>"
                                       class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                       placeholder="smtp.gmail.com">
                            </div>

                            <div>
                                <label for="smtp_port" class="block text-sm font-medium text-gray-700 mb-2">
                                    SMTP Port *
                                </label>
                                <input type="number" name="smtp_port" id="smtp_port" required
                                       value="<?php echo htmlspecialchars($currentSettings['smtp_port']); ?>"
                                       class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                       placeholder="587">
                            </div>

                            <div>
                                <label for="smtp_username" class="block text-sm font-medium text-gray-700 mb-2">
                                    SMTP Username *
                                </label>
                                <input type="text" name="smtp_username" id="smtp_username" required
                                       value="<?php echo htmlspecialchars($currentSettings['smtp_username']); ?>"
                                       class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                       placeholder="your-email@gmail.com">
                            </div>

                            <div>
                                <label for="smtp_password" class="block text-sm font-medium text-gray-700 mb-2">
                                    SMTP Password *
                                </label>
                                <div class="relative">
                                    <input type="password" name="smtp_password" id="smtp_password" required
                                           value="<?php echo htmlspecialchars($currentSettings['smtp_password']); ?>"
                                           class="w-full px-3 py-3 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                           placeholder="App Password">
                                    <button type="button" onclick="togglePassword('smtp_password')" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                        <i class="fas fa-eye" id="smtp_password_icon"></i>
                                    </button>
                                </div>
                            </div>

                            <div>
                                <label for="smtp_encryption" class="block text-sm font-medium text-gray-700 mb-2">
                                    Mã hóa
                                </label>
                                <select name="smtp_encryption" id="smtp_encryption"
                                        class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="tls" <?php echo $currentSettings['smtp_encryption'] === 'tls' ? 'selected' : ''; ?>>TLS</option>
                                    <option value="ssl" <?php echo $currentSettings['smtp_encryption'] === 'ssl' ? 'selected' : ''; ?>>SSL</option>
                                    <option value="none" <?php echo $currentSettings['smtp_encryption'] === 'none' ? 'selected' : ''; ?>>Không</option>
                                </select>
                            </div>
                        </div>

                        <!-- From Settings -->
                        <div class="border-t pt-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Thông tin người gửi</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="smtp_from_email" class="block text-sm font-medium text-gray-700 mb-2">
                                        Email người gửi *
                                    </label>
                                    <input type="email" name="smtp_from_email" id="smtp_from_email" required
                                           value="<?php echo htmlspecialchars($currentSettings['smtp_from_email']); ?>"
                                           class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                           placeholder="noreply@vpsvietnam.com">
                                </div>

                                <div>
                                    <label for="smtp_from_name" class="block text-sm font-medium text-gray-700 mb-2">
                                        Tên người gửi *
                                    </label>
                                    <input type="text" name="smtp_from_name" id="smtp_from_name" required
                                           value="<?php echo htmlspecialchars($currentSettings['smtp_from_name']); ?>"
                                           class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                           placeholder="VPS Việt Nam Pro">
                                </div>
                            </div>
                        </div>

                        <!-- SMTP Presets -->
                        <div class="border-t pt-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Cài đặt nhanh</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <button type="button" onclick="setGmailPreset()" class="p-4 border border-gray-300 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition-colors duration-200">
                                    <div class="flex items-center space-x-3">
                                        <i class="fab fa-google text-red-500 text-xl"></i>
                                        <div class="text-left">
                                            <p class="font-medium text-gray-900">Gmail</p>
                                            <p class="text-sm text-gray-600">smtp.gmail.com:587</p>
                                        </div>
                                    </div>
                                </button>

                                <button type="button" onclick="setOutlookPreset()" class="p-4 border border-gray-300 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition-colors duration-200">
                                    <div class="flex items-center space-x-3">
                                        <i class="fab fa-microsoft text-blue-500 text-xl"></i>
                                        <div class="text-left">
                                            <p class="font-medium text-gray-900">Outlook</p>
                                            <p class="text-sm text-gray-600">smtp-mail.outlook.com:587</p>
                                        </div>
                                    </div>
                                </button>

                                <button type="button" onclick="setYahooPreset()" class="p-4 border border-gray-300 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition-colors duration-200">
                                    <div class="flex items-center space-x-3">
                                        <i class="fab fa-yahoo text-purple-500 text-xl"></i>
                                        <div class="text-left">
                                            <p class="font-medium text-gray-900">Yahoo</p>
                                            <p class="text-sm text-gray-600">smtp.mail.yahoo.com:587</p>
                                        </div>
                                    </div>
                                </button>
                            </div>
                        </div>

                        <!-- Test Email -->
                        <div class="border-t pt-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">Kiểm tra cấu hình</h3>
                                    <p class="text-sm text-gray-600">Gửi email thử nghiệm để kiểm tra cấu hình SMTP</p>
                                </div>
                                <button type="button" onclick="sendTestEmail()" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors duration-200">
                                    <i class="fas fa-paper-plane mr-2"></i>
                                    Gửi email thử
                                </button>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex space-x-4">
                            <button type="submit" class="flex-1 bg-gradient-to-r from-blue-600 to-purple-600 text-white py-3 px-6 rounded-lg font-semibold hover:from-blue-700 hover:to-purple-700 transition-all duration-200 flex items-center justify-center space-x-2">
                                <i class="fas fa-save"></i>
                                <span>Lưu cài đặt</span>
                            </button>
                            <a href="settings.php" class="flex-1 bg-gray-600 text-white py-3 px-6 rounded-lg font-semibold hover:bg-gray-700 transition-all duration-200 flex items-center justify-center space-x-2">
                                <i class="fas fa-arrow-left"></i>
                                <span>Quay lại</span>
                            </a>
                        </div>
                    </form>
                </div>

                <!-- SMTP Guide -->
                <div class="mt-8 bg-blue-50 rounded-2xl p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                        Hướng dẫn cài đặt SMTP
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h4 class="font-semibold text-gray-900 mb-2">Gmail</h4>
                            <ol class="list-decimal list-inside space-y-1 text-sm text-gray-600">
                                <li>Bật xác thực 2 bước cho tài khoản Gmail</li>
                                <li>Tạo App Password trong cài đặt bảo mật</li>
                                <li>Sử dụng App Password thay vì mật khẩu thường</li>
                                <li>Host: smtp.gmail.com, Port: 587, TLS</li>
                            </ol>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900 mb-2">Outlook/Hotmail</h4>
                            <ol class="list-decimal list-inside space-y-1 text-sm text-gray-600">
                                <li>Đăng nhập vào tài khoản Microsoft</li>
                                <li>Vào Security > App passwords</li>
                                <li>Tạo mật khẩu ứng dụng mới</li>
                                <li>Host: smtp-mail.outlook.com, Port: 587, TLS</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const icon = document.getElementById(fieldId + '_icon');
            
            if (field.type === 'password') {
                field.type = 'text';
                icon.className = 'fas fa-eye-slash';
            } else {
                field.type = 'password';
                icon.className = 'fas fa-eye';
            }
        }

        function setGmailPreset() {
            document.getElementById('smtp_host').value = 'smtp.gmail.com';
            document.getElementById('smtp_port').value = '587';
            document.getElementById('smtp_encryption').value = 'tls';
        }

        function setOutlookPreset() {
            document.getElementById('smtp_host').value = 'smtp-mail.outlook.com';
            document.getElementById('smtp_port').value = '587';
            document.getElementById('smtp_encryption').value = 'tls';
        }

        function setYahooPreset() {
            document.getElementById('smtp_host').value = 'smtp.mail.yahoo.com';
            document.getElementById('smtp_port').value = '587';
            document.getElementById('smtp_encryption').value = 'tls';
        }

        function sendTestEmail() {
            const testEmail = prompt('Nhập email để gửi thử nghiệm:');
            if (testEmail) {
                // Here you would implement the test email functionality
                alert('Tính năng gửi email thử nghiệm sẽ được triển khai trong phiên bản tiếp theo.');
            }
        }
    </script>
</body>
</html>