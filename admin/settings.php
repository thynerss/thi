<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is admin
if (!isAdmin()) {
    redirectTo('../login.php');
}

$success = '';
$error = '';

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $settings = [
        'site_name' => sanitizeInput($_POST['site_name']),
        'site_description' => sanitizeInput($_POST['site_description']),
        'contact_email' => sanitizeInput($_POST['contact_email']),
        'min_topup_amount' => (int)$_POST['min_topup_amount'],
        'max_topup_amount' => (int)$_POST['max_topup_amount'],
        'facebook_url' => sanitizeInput($_POST['facebook_url']),
        'twitter_url' => sanitizeInput($_POST['twitter_url']),
        'youtube_url' => sanitizeInput($_POST['youtube_url']),
        'instagram_url' => sanitizeInput($_POST['instagram_url']),
        'telegram_url' => sanitizeInput($_POST['telegram_url']),
        'affiliate_commission_rate' => (float)$_POST['affiliate_commission_rate'],
        'auto_delivery_enabled' => isset($_POST['auto_delivery_enabled']) ? '1' : '0',
        'review_moderation' => isset($_POST['review_moderation']) ? '1' : '0',
        'maintenance_mode' => isset($_POST['maintenance_mode']) ? '1' : '0',
        'vietqr_enabled' => isset($_POST['vietqr_enabled']) ? '1' : '0'
    ];
    
    $allSuccess = true;
    foreach ($settings as $key => $value) {
        if (!setSystemSetting($key, $value)) {
            $allSuccess = false;
        }
    }
    
    if ($allSuccess) {
        $success = 'Cập nhật cài đặt thành công!';
    } else {
        $error = 'Có lỗi xảy ra khi cập nhật cài đặt!';
    }
}

// Get current settings
$currentSettings = [
    'site_name' => getSystemSetting('site_name') ?: 'VPS Việt Nam Pro',
    'site_description' => getSystemSetting('site_description') ?: 'Dịch vụ VPS chính hãng và VPS trial chất lượng cao',
    'contact_email' => getSystemSetting('contact_email') ?: 'support@vpsvietnam.com',
    'min_topup_amount' => getSystemSetting('min_topup_amount') ?: '50000',
    'max_topup_amount' => getSystemSetting('max_topup_amount') ?: '50000000',
    'facebook_url' => getSystemSetting('facebook_url') ?: '',
    'twitter_url' => getSystemSetting('twitter_url') ?: '',
    'youtube_url' => getSystemSetting('youtube_url') ?: '',
    'instagram_url' => getSystemSetting('instagram_url') ?: '',
    'telegram_url' => getSystemSetting('telegram_url') ?: '',
    'affiliate_commission_rate' => getSystemSetting('affiliate_commission_rate') ?: '5.00',
    'auto_delivery_enabled' => getSystemSetting('auto_delivery_enabled') === '1',
    'review_moderation' => getSystemSetting('review_moderation') === '1',
    'maintenance_mode' => getSystemSetting('maintenance_mode') === '1',
    'vietqr_enabled' => getSystemSetting('vietqr_enabled') === '1'
];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cài đặt hệ thống - VPS & Proxy Việt Nam</title>
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
                    <a href="users.php" class="text-gray-700 hover:text-blue-600 transition-colors duration-200 font-medium">Người dùng</a>
                    <a href="settings.php" class="text-blue-600 font-medium">Cài đặt</a>
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

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Cài đặt hệ thống</h1>
            <p class="text-gray-600 mt-2">Quản lý cấu hình và thiết lập hệ thống</p>
        </div>

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

        <form method="POST" class="space-y-8">
            <!-- General Settings -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-6">Cài đặt chung</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="site_name" class="block text-sm font-medium text-gray-700 mb-2">Tên website</label>
                        <input type="text" id="site_name" name="site_name" value="<?php echo htmlspecialchars($currentSettings['site_name']); ?>" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    
                    <div>
                        <label for="contact_email" class="block text-sm font-medium text-gray-700 mb-2">Email liên hệ</label>
                        <input type="email" id="contact_email" name="contact_email" value="<?php echo htmlspecialchars($currentSettings['contact_email']); ?>" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                </div>
                
                <div class="mt-6">
                    <label for="site_description" class="block text-sm font-medium text-gray-700 mb-2">Mô tả website</label>
                    <textarea id="site_description" name="site_description" rows="3" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"><?php echo htmlspecialchars($currentSettings['site_description']); ?></textarea>
                </div>
            </div>

            <!-- Payment Settings -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-6">Cài đặt thanh toán</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="min_topup_amount" class="block text-sm font-medium text-gray-700 mb-2">Số tiền nạp tối thiểu (VND)</label>
                        <input type="number" id="min_topup_amount" name="min_topup_amount" value="<?php echo $currentSettings['min_topup_amount']; ?>" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    
                    <div>
                        <label for="max_topup_amount" class="block text-sm font-medium text-gray-700 mb-2">Số tiền nạp tối đa (VND)</label>
                        <input type="number" id="max_topup_amount" name="max_topup_amount" value="<?php echo $currentSettings['max_topup_amount']; ?>" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                </div>
                
                <div class="mt-6">
                    <label for="affiliate_commission_rate" class="block text-sm font-medium text-gray-700 mb-2">Tỷ lệ hoa hồng affiliate (%)</label>
                    <input type="number" step="0.01" id="affiliate_commission_rate" name="affiliate_commission_rate" value="<?php echo $currentSettings['affiliate_commission_rate']; ?>" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
            </div>

            <!-- Social Media Settings -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-6">Mạng xã hội</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="facebook_url" class="block text-sm font-medium text-gray-700 mb-2">Facebook URL</label>
                        <input type="url" id="facebook_url" name="facebook_url" value="<?php echo htmlspecialchars($currentSettings['facebook_url']); ?>" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    
                    <div>
                        <label for="twitter_url" class="block text-sm font-medium text-gray-700 mb-2">Twitter URL</label>
                        <input type="url" id="twitter_url" name="twitter_url" value="<?php echo htmlspecialchars($currentSettings['twitter_url']); ?>" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    
                    <div>
                        <label for="youtube_url" class="block text-sm font-medium text-gray-700 mb-2">YouTube URL</label>
                        <input type="url" id="youtube_url" name="youtube_url" value="<?php echo htmlspecialchars($currentSettings['youtube_url']); ?>" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    
                    <div>
                        <label for="instagram_url" class="block text-sm font-medium text-gray-700 mb-2">Instagram URL</label>
                        <input type="url" id="instagram_url" name="instagram_url" value="<?php echo htmlspecialchars($currentSettings['instagram_url']); ?>" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    
                    <div class="md:col-span-2">
                        <label for="telegram_url" class="block text-sm font-medium text-gray-700 mb-2">Telegram URL</label>
                        <input type="url" id="telegram_url" name="telegram_url" value="<?php echo htmlspecialchars($currentSettings['telegram_url']); ?>" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                </div>
            </div>

            <!-- System Features -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-6">Tính năng hệ thống</h2>
                
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-medium text-gray-900">Giao hàng tự động</h3>
                            <p class="text-sm text-gray-500">Tự động giao dịch vụ sau khi thanh toán</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="auto_delivery_enabled" class="sr-only peer" <?php echo $currentSettings['auto_delivery_enabled'] ? 'checked' : ''; ?>>
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-medium text-gray-900">Kiểm duyệt đánh giá</h3>
                            <p class="text-sm text-gray-500">Yêu cầu admin duyệt đánh giá trước khi hiển thị</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="review_moderation" class="sr-only peer" <?php echo $currentSettings['review_moderation'] ? 'checked' : ''; ?>>
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-medium text-gray-900">VietQR</h3>
                            <p class="text-sm text-gray-500">Bật/tắt tính năng tạo mã QR thanh toán</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="vietqr_enabled" class="sr-only peer" <?php echo $currentSettings['vietqr_enabled'] ? 'checked' : ''; ?>>
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-medium text-gray-900">Chế độ bảo trì</h3>
                            <p class="text-sm text-gray-500">Tạm thời tắt website để bảo trì</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="maintenance_mode" class="sr-only peer" <?php echo $currentSettings['maintenance_mode'] ? 'checked' : ''; ?>>
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-red-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-red-600"></div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Save Button -->
            <div class="flex justify-end">
                <button type="submit" class="bg-gradient-to-r from-blue-600 to-purple-600 text-white px-8 py-3 rounded-lg font-semibold hover:from-blue-700 hover:to-purple-700 transition-all duration-200 flex items-center space-x-2">
                    <i class="fas fa-save"></i>
                    <span>Lưu cài đặt</span>
                </button>
            </div>
        </form>
    </div>
</body>
</html>