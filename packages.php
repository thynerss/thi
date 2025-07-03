<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Check if user is logged in
$user = null;
if (isset($_SESSION['user_id'])) {
    $user = getUserById($_SESSION['user_id']);
}

$activeTab = $_GET['tab'] ?? 'vps';
$packageType = $_GET['type'] ?? 'all'; // all, trial, official
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gói dịch vụ - VPS Việt Nam Pro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .gradient-bg { background: linear-gradient(135deg, #1e3a8a 0%, #3730a3 50%, #7c3aed 100%); }
        .card-hover { transition: all 0.3s ease; }
        .card-hover:hover { transform: translateY(-8px); box-shadow: 0 20px 40px rgba(0,0,0,0.1); }
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }
        .loading-spinner {
            border: 4px solid #f3f4f6;
            border-top: 4px solid #3b82f6;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <?php include 'includes/header.php'; ?>

    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="loading-overlay hidden">
        <div class="bg-white rounded-2xl p-8 text-center">
            <div class="loading-spinner mx-auto mb-4"></div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Đang xử lý đơn hàng...</h3>
            <p class="text-gray-600">Vui lòng chờ trong giây lát</p>
        </div>
    </div>

    <!-- Hero Section -->
    <section class="gradient-bg text-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">Gói dịch vụ chuyên nghiệp</h1>
            <p class="text-xl text-blue-100 max-w-3xl mx-auto">
                VPS chính hãng và VPS trial từ các nhà cung cấp hàng đầu thế giới
            </p>
        </div>
    </section>

    <!-- Service Tabs -->
    <section class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-center mb-8">
                <div class="bg-white rounded-xl shadow-lg p-2 flex space-x-2">
                    <button onclick="switchTab('vps')" id="vps-tab" class="px-6 py-3 rounded-lg font-semibold transition-all duration-200 <?php echo $activeTab === 'vps' ? 'bg-gradient-to-r from-blue-600 to-purple-600 text-white' : 'text-gray-600 hover:text-gray-800'; ?>">
                        <i class="fas fa-server mr-2"></i>
                        VPS Hosting
                    </button>
                    <button onclick="switchTab('proxy')" id="proxy-tab" class="px-6 py-3 rounded-lg font-semibold transition-all duration-200 <?php echo $activeTab === 'proxy' ? 'bg-gradient-to-r from-blue-600 to-purple-600 text-white' : 'text-gray-600 hover:text-gray-800'; ?>">
                        <i class="fas fa-shield-alt mr-2"></i>
                        Proxy SOCKS5
                    </button>
                </div>
            </div>

            <!-- VPS Packages -->
            <div id="vps-content" class="<?php echo $activeTab === 'vps' ? '' : 'hidden'; ?>">
                <!-- VPS Type Filter -->
                <div class="flex justify-center mb-8">
                    <div class="bg-white rounded-lg shadow-md p-1 flex space-x-1">
                        <button onclick="switchVPSType('all')" id="all-btn" class="px-4 py-2 rounded-md font-medium transition-all duration-200 <?php echo $packageType === 'all' ? 'bg-blue-600 text-white' : 'text-gray-600 hover:text-gray-800'; ?>">
                            Tất cả
                        </button>
                        <button onclick="switchVPSType('trial')" id="trial-btn" class="px-4 py-2 rounded-md font-medium transition-all duration-200 <?php echo $packageType === 'trial' ? 'bg-orange-600 text-white' : 'text-gray-600 hover:text-gray-800'; ?>">
                            <i class="fas fa-gift mr-1"></i>
                            VPS Trial
                        </button>
                        <button onclick="switchVPSType('official')" id="official-btn" class="px-4 py-2 rounded-md font-medium transition-all duration-200 <?php echo $packageType === 'official' ? 'bg-blue-600 text-white' : 'text-gray-600 hover:text-gray-800'; ?>">
                            <i class="fas fa-crown mr-1"></i>
                            VPS Chính hãng
                        </button>
                    </div>
                </div>

                <!-- Trial VPS Section -->
                <div id="trial-section" class="<?php echo $packageType === 'trial' || $packageType === 'all' ? '' : 'hidden'; ?>">
                    <div class="text-center mb-12">
                        <h2 class="text-3xl font-bold text-gray-900 mb-4">
                            <i class="fas fa-gift text-orange-600 mr-2"></i>
                            VPS Trial từ nhà phân phối
                        </h2>
                        <p class="text-xl text-gray-600">VPS trial chất lượng cao từ các nhà phân phối uy tín</p>
                        <div class="bg-orange-50 border border-orange-200 rounded-lg p-4 mt-4 max-w-2xl mx-auto">
                            <p class="text-orange-800 text-sm">
                                <i class="fas fa-info-circle mr-1"></i>
                                VPS trial từ các nhà phân phối chính thức của DigitalOcean, Vultr, Linode với cấu hình thực tế
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-16">
                        <?php
                        $trialPackages = getTrialVPSPackages();
                        foreach ($trialPackages as $package):
                        ?>
                        <div class="relative bg-white rounded-2xl shadow-lg border-2 border-orange-200 overflow-hidden card-hover">
                            <div class="absolute top-0 left-0 right-0 bg-gradient-to-r from-orange-600 to-red-600 text-white text-center py-2 text-sm font-semibold">
                                <i class="fas fa-gift mr-1"></i>
                                VPS TRIAL - <?php echo $package['trial_duration']; ?> NGÀY
                            </div>
                            
                            <div class="p-6 pt-12">
                                <div class="text-center mb-6">
                                    <h3 class="text-2xl font-bold text-gray-900 mb-2"><?php echo htmlspecialchars($package['name']); ?></h3>
                                    <p class="text-orange-600 font-semibold mb-2"><?php echo htmlspecialchars($package['provider']); ?></p>
                                    <div class="flex items-baseline justify-center">
                                        <span class="text-4xl font-bold text-orange-600"><?php echo formatCurrency($package['price']); ?></span>
                                        <span class="text-gray-500 ml-1">/<?php echo $package['trial_duration']; ?> ngày</span>
                                    </div>
                                </div>

                                <div class="space-y-4 mb-8">
                                    <div class="flex items-center space-x-3">
                                        <div class="bg-blue-100 p-2 rounded-lg">
                                            <i class="fas fa-microchip text-blue-600"></i>
                                        </div>
                                        <div>
                                            <span class="text-gray-600 text-sm">CPU</span>
                                            <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($package['cpu']); ?></p>
                                        </div>
                                    </div>

                                    <div class="flex items-center space-x-3">
                                        <div class="bg-green-100 p-2 rounded-lg">
                                            <i class="fas fa-memory text-green-600"></i>
                                        </div>
                                        <div>
                                            <span class="text-gray-600 text-sm">RAM</span>
                                            <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($package['ram']); ?></p>
                                        </div>
                                    </div>

                                    <div class="flex items-center space-x-3">
                                        <div class="bg-purple-100 p-2 rounded-lg">
                                            <i class="fas fa-hdd text-purple-600"></i>
                                        </div>
                                        <div>
                                            <span class="text-gray-600 text-sm">Lưu trữ</span>
                                            <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($package['storage']); ?></p>
                                        </div>
                                    </div>

                                    <div class="flex items-center space-x-3">
                                        <div class="bg-orange-100 p-2 rounded-lg">
                                            <i class="fas fa-wifi text-orange-600"></i>
                                        </div>
                                        <div>
                                            <span class="text-gray-600 text-sm">Băng thông</span>
                                            <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($package['bandwidth']); ?></p>
                                        </div>
                                    </div>
                                </div>

                                <div class="space-y-3 mb-8">
                                    <div class="flex items-center space-x-2">
                                        <i class="fas fa-check text-orange-500"></i>
                                        <span class="text-gray-600 text-sm">Từ nhà phân phối chính thức</span>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <i class="fas fa-check text-orange-500"></i>
                                        <span class="text-gray-600 text-sm">Cấu hình thực tế</span>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <i class="fas fa-check text-orange-500"></i>
                                        <span class="text-gray-600 text-sm">Root access đầy đủ</span>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <i class="fas fa-check text-orange-500"></i>
                                        <span class="text-gray-600 text-sm">Hỗ trợ kỹ thuật</span>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="text-gray-600">Tồn kho:</span>
                                        <span class="font-semibold <?php echo $package['stock_quantity'] > 0 ? 'text-green-600' : 'text-red-600'; ?>">
                                            <?php echo $package['stock_quantity'] > 0 ? $package['stock_quantity'] . ' VPS' : 'Hết hàng'; ?>
                                        </span>
                                    </div>
                                </div>

                                <?php if ($package['stock_quantity'] > 0): ?>
                                <button onclick="selectPackage(<?php echo $package['id']; ?>, 'vps')" class="w-full py-3 px-6 rounded-xl font-semibold transition-all duration-200 bg-gradient-to-r from-orange-600 to-red-600 text-white hover:from-orange-700 hover:to-red-700 shadow-lg hover:shadow-xl">
                                    <i class="fas fa-shopping-cart mr-2"></i>
                                    Mua VPS Trial
                                </button>
                                <?php else: ?>
                                <button disabled class="w-full py-3 px-6 rounded-xl font-semibold bg-gray-300 text-gray-500 cursor-not-allowed">
                                    <i class="fas fa-times mr-2"></i>
                                    Hết hàng
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Official VPS Section -->
                <div id="official-section" class="<?php echo $packageType === 'official' || $packageType === 'all' ? '' : 'hidden'; ?>">
                    <div class="text-center mb-12">
                        <h2 class="text-3xl font-bold text-gray-900 mb-4">
                            <i class="fas fa-crown text-blue-600 mr-2"></i>
                            VPS Chính hãng
                        </h2>
                        <p class="text-xl text-gray-600">VPS chính hãng trực tiếp từ các nhà cung cấp hàng đầu</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                        <?php
                        $officialPackages = getOfficialVPSPackages();
                        foreach ($officialPackages as $index => $package):
                            $featured = $package['profit_margin'] > 25;
                        ?>
                        <div class="relative bg-white rounded-2xl shadow-lg border-2 <?php echo $featured ? 'border-blue-500 shadow-2xl' : 'border-gray-100'; ?> overflow-hidden card-hover">
                            <?php if ($featured): ?>
                            <div class="absolute top-0 left-0 right-0 bg-gradient-to-r from-blue-600 to-purple-600 text-white text-center py-2 text-sm font-semibold">
                                <i class="fas fa-star mr-1"></i>
                                KHUYẾN MÃI
                            </div>
                            <?php endif; ?>
                            
                            <div class="p-6 <?php echo $featured ? 'pt-12' : ''; ?>">
                                <div class="text-center mb-6">
                                    <div class="flex items-center justify-center space-x-2 mb-2">
                                        <h3 class="text-xl font-bold text-gray-900"><?php echo htmlspecialchars($package['name']); ?></h3>
                                        <span class="px-2 py-1 rounded-full text-xs font-medium <?php echo getPackageTypeColor($package['type']); ?>">
                                            <?php echo getPackageTypeText($package['type']); ?>
                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-600 mb-2"><?php echo htmlspecialchars($package['provider']); ?></p>
                                    <div class="flex items-baseline justify-center">
                                        <span class="text-3xl font-bold text-gray-900"><?php echo formatCurrency($package['price']); ?></span>
                                        <span class="text-gray-500 ml-1">/tháng</span>
                                    </div>
                                    <?php if ($package['original_price'] > 0 && $package['original_price'] != $package['price']): ?>
                                    <p class="text-sm text-gray-500 line-through">Giá gốc: <?php echo formatCurrency($package['original_price']); ?></p>
                                    <?php endif; ?>
                                </div>

                                <div class="space-y-3 mb-6">
                                    <div class="flex items-center space-x-3">
                                        <div class="bg-blue-100 p-1 rounded">
                                            <i class="fas fa-microchip text-blue-600 text-sm"></i>
                                        </div>
                                        <div class="flex-1">
                                            <span class="text-gray-600 text-xs">CPU</span>
                                            <p class="font-semibold text-gray-900 text-sm"><?php echo htmlspecialchars($package['cpu']); ?></p>
                                        </div>
                                    </div>

                                    <div class="flex items-center space-x-3">
                                        <div class="bg-green-100 p-1 rounded">
                                            <i class="fas fa-memory text-green-600 text-sm"></i>
                                        </div>
                                        <div class="flex-1">
                                            <span class="text-gray-600 text-xs">RAM</span>
                                            <p class="font-semibold text-gray-900 text-sm"><?php echo htmlspecialchars($package['ram']); ?></p>
                                        </div>
                                    </div>

                                    <div class="flex items-center space-x-3">
                                        <div class="bg-purple-100 p-1 rounded">
                                            <i class="fas fa-hdd text-purple-600 text-sm"></i>
                                        </div>
                                        <div class="flex-1">
                                            <span class="text-gray-600 text-xs">Lưu trữ</span>
                                            <p class="font-semibold text-gray-900 text-sm"><?php echo htmlspecialchars($package['storage']); ?></p>
                                        </div>
                                    </div>

                                    <div class="flex items-center space-x-3">
                                        <div class="bg-orange-100 p-1 rounded">
                                            <i class="fas fa-wifi text-orange-600 text-sm"></i>
                                        </div>
                                        <div class="flex-1">
                                            <span class="text-gray-600 text-xs">Băng thông</span>
                                            <p class="font-semibold text-gray-900 text-sm"><?php echo htmlspecialchars($package['bandwidth']); ?></p>
                                        </div>
                                    </div>
                                </div>

                                <div class="space-y-2 mb-6">
                                    <div class="flex items-center space-x-2">
                                        <i class="fas fa-check text-green-500 text-xs"></i>
                                        <span class="text-gray-600 text-xs">Uptime 99.9%</span>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <i class="fas fa-check text-green-500 text-xs"></i>
                                        <span class="text-gray-600 text-xs">Hỗ trợ 24/7</span>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <i class="fas fa-check text-green-500 text-xs"></i>
                                        <span class="text-gray-600 text-xs">Root access</span>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <i class="fas fa-check text-green-500 text-xs"></i>
                                        <span class="text-gray-600 text-xs">Enterprise SLA</span>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="text-gray-600">Tồn kho:</span>
                                        <span class="font-semibold <?php echo $package['stock_quantity'] > 0 ? 'text-green-600' : 'text-red-600'; ?>">
                                            <?php echo $package['stock_quantity'] > 0 ? $package['stock_quantity'] . ' VPS' : 'Hết hàng'; ?>
                                        </span>
                                    </div>
                                </div>

                                <?php if ($package['stock_quantity'] > 0): ?>
                                <button onclick="selectPackage(<?php echo $package['id']; ?>, 'vps')" class="w-full py-2 px-4 rounded-lg font-semibold transition-all duration-200 text-sm <?php echo $featured ? 'bg-gradient-to-r from-blue-600 to-purple-600 text-white hover:from-blue-700 hover:to-purple-700 shadow-lg hover:shadow-xl' : 'bg-gray-900 text-white hover:bg-gray-800 shadow-md hover:shadow-lg'; ?>">
                                    Chọn gói này
                                </button>
                                <?php else: ?>
                                <button disabled class="w-full py-2 px-4 rounded-lg font-semibold bg-gray-300 text-gray-500 cursor-not-allowed text-sm">
                                    Hết hàng
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Proxy Packages -->
            <div id="proxy-content" class="<?php echo $activeTab === 'proxy' ? '' : 'hidden'; ?>">
                <div class="text-center mb-12">
                    <h2 class="text-3xl font-bold text-gray-900 mb-4">Gói Proxy SOCKS5 chuyên nghiệp</h2>
                    <p class="text-xl text-gray-600">Proxy chất lượng cao từ các nhà cung cấp uy tín</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <?php
                    $proxyPackages = getAllProxyPackages();
                    foreach ($proxyPackages as $index => $package):
                        $featured = $package['location'] === 'Việt Nam';
                    ?>
                    <div class="relative bg-white rounded-2xl shadow-lg border-2 <?php echo $featured ? 'border-green-500 shadow-2xl' : 'border-gray-100'; ?> overflow-hidden card-hover">
                        <?php if ($featured): ?>
                        <div class="absolute top-0 left-0 right-0 bg-gradient-to-r from-green-600 to-blue-600 text-white text-center py-2 text-sm font-semibold">
                            <i class="fas fa-flag mr-1"></i>
                            Việt Nam
                        </div>
                        <?php endif; ?>
                        
                        <div class="p-6 <?php echo $featured ? 'pt-12' : ''; ?>">
                            <div class="text-center mb-6">
                                <h3 class="text-2xl font-bold text-gray-900 mb-2"><?php echo htmlspecialchars($package['name']); ?></h3>
                                <p class="text-sm text-gray-600 mb-2"><?php echo htmlspecialchars($package['provider']); ?></p>
                                <div class="flex items-baseline justify-center">
                                    <span class="text-4xl font-bold text-gray-900"><?php echo formatCurrency($package['price']); ?></span>
                                    <span class="text-gray-500 ml-1">/tháng</span>
                                </div>
                                <?php if ($package['original_price'] > 0 && $package['original_price'] != $package['price']): ?>
                                <p class="text-sm text-gray-500 line-through">Giá gốc: <?php echo formatCurrency($package['original_price']); ?></p>
                                <?php endif; ?>
                            </div>

                            <div class="space-y-4 mb-8">
                                <div class="flex items-center space-x-3">
                                    <div class="bg-blue-100 p-2 rounded-lg">
                                        <i class="fas fa-globe text-blue-600"></i>
                                    </div>
                                    <div>
                                        <span class="text-gray-600 text-sm">Vị trí</span>
                                        <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($package['location']); ?></p>
                                    </div>
                                </div>

                                <div class="flex items-center space-x-3">
                                    <div class="bg-green-100 p-2 rounded-lg">
                                        <i class="fas fa-tachometer-alt text-green-600"></i>
                                    </div>
                                    <div>
                                        <span class="text-gray-600 text-sm">Tốc độ</span>
                                        <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($package['speed']); ?></p>
                                    </div>
                                </div>

                                <div class="flex items-center space-x-3">
                                    <div class="bg-purple-100 p-2 rounded-lg">
                                        <i class="fas fa-link text-purple-600"></i>
                                    </div>
                                    <div>
                                        <span class="text-gray-600 text-sm">Kết nối đồng thời</span>
                                        <p class="font-semibold text-gray-900"><?php echo $package['concurrent_connections']; ?> kết nối</p>
                                    </div>
                                </div>

                                <div class="flex items-center space-x-3">
                                    <div class="bg-orange-100 p-2 rounded-lg">
                                        <i class="fas fa-shield-alt text-orange-600"></i>
                                    </div>
                                    <div>
                                        <span class="text-gray-600 text-sm">Giao thức</span>
                                        <p class="font-semibold text-gray-900"><?php echo strtoupper($package['type']); ?></p>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-3 mb-8">
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-check text-green-500"></i>
                                    <span class="text-gray-600 text-sm">IP riêng biệt</span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-check text-green-500"></i>
                                    <span class="text-gray-600 text-sm">Không giới hạn băng thông</span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-check text-green-500"></i>
                                    <span class="text-gray-600 text-sm">Hỗ trợ 24/7</span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-check text-green-500"></i>
                                    <span class="text-gray-600 text-sm">Thay đổi IP miễn phí</span>
                                </div>
                            </div>

                            <div class="mb-4">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-600">Tồn kho:</span>
                                    <span class="font-semibold <?php echo $package['stock_quantity'] > 0 ? 'text-green-600' : 'text-red-600'; ?>">
                                        <?php echo $package['stock_quantity'] > 0 ? $package['stock_quantity'] . ' Proxy' : 'Hết hàng'; ?>
                                    </span>
                                </div>
                            </div>

                            <?php if ($package['stock_quantity'] > 0): ?>
                            <button onclick="selectPackage(<?php echo $package['id']; ?>, 'proxy')" class="w-full py-3 px-6 rounded-xl font-semibold transition-all duration-200 <?php echo $featured ? 'bg-gradient-to-r from-green-600 to-blue-600 text-white hover:from-green-700 hover:to-blue-700 shadow-lg hover:shadow-xl' : 'bg-gray-900 text-white hover:bg-gray-800 shadow-md hover:shadow-lg'; ?>">
                                Chọn gói này
                            </button>
                            <?php else: ?>
                            <button disabled class="w-full py-3 px-6 rounded-xl font-semibold bg-gray-300 text-gray-500 cursor-not-allowed">
                                Hết hàng
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Features Section -->
            <div class="mt-16 bg-white rounded-2xl shadow-lg p-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-6 text-center">
                    Tất cả gói đều bao gồm
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="text-center">
                        <div class="bg-blue-100 w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-bolt text-blue-600"></i>
                        </div>
                        <h3 class="font-semibold text-gray-900 mb-1">Thiết lập nhanh</h3>
                        <p class="text-gray-600 text-sm">Kích hoạt trong 15-30 phút</p>
                    </div>
                    <div class="text-center">
                        <div class="bg-green-100 w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-shield-alt text-green-600"></i>
                        </div>
                        <h3 class="font-semibold text-gray-900 mb-1">Bảo mật cao</h3>
                        <p class="text-gray-600 text-sm">Mã hóa dữ liệu và bảo vệ quyền riêng tư</p>
                    </div>
                    <div class="text-center">
                        <div class="bg-purple-100 w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-headset text-purple-600"></i>
                        </div>
                        <h3 class="font-semibold text-gray-900 mb-1">Hỗ trợ 24/7</h3>
                        <p class="text-gray-600 text-sm">Đội ngũ kỹ thuật luôn sẵn sàng hỗ trợ</p>
                    </div>
                    <div class="text-center">
                        <div class="bg-orange-100 w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-sync-alt text-orange-600"></i>
                        </div>
                        <h3 class="font-semibold text-gray-900 mb-1">Đảm bảo uptime</h3>
                        <p class="text-gray-600 text-sm">Cam kết 99.9% thời gian hoạt động</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <!-- Order Modal -->
    <div id="orderModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50 hidden">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-900">Đặt hàng dịch vụ</h2>
                    <button onclick="closeOrderModal()" class="p-2 hover:bg-gray-100 rounded-lg transition-colors duration-200">
                        <i class="fas fa-times text-gray-500"></i>
                    </button>
                </div>

                <div id="packageInfo" class="bg-gradient-to-r from-blue-50 to-purple-50 rounded-xl p-4 mb-6">
                    <!-- Package info will be loaded here -->
                </div>

                <form id="orderForm" onsubmit="submitOrder(event)">
                    <div class="mb-6">
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            Địa chỉ Email
                        </label>
                        <div class="relative">
                            <i class="fas fa-envelope absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            <input type="email" id="email" name="email" value="<?php echo $user ? htmlspecialchars($user['email']) : ''; ?>" class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Nhập email của bạn" required>
                        </div>
                        <p class="text-sm text-gray-500 mt-1">
                            Thông tin dịch vụ sẽ được gửi đến địa chỉ email này
                        </p>
                    </div>

                    <div class="flex space-x-3">
                        <button type="button" onclick="closeOrderModal()" class="flex-1 py-3 px-4 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors duration-200">
                            Hủy
                        </button>
                        <button type="submit" class="flex-1 bg-gradient-to-r from-blue-600 to-purple-600 text-white py-3 px-4 rounded-lg hover:from-blue-700 hover:to-purple-700 transition-all duration-200 flex items-center justify-center space-x-2">
                            <i class="fas fa-credit-card"></i>
                            <span>Đặt hàng ngay</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div id="successModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50 hidden">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full">
            <div class="p-6 text-center">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-check text-2xl text-green-600"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-900 mb-2">Đặt hàng thành công!</h2>
                <p class="text-gray-600 mb-6">
                    Đơn hàng của bạn đã được tạo và đang được xử lý. 
                    Thông tin dịch vụ sẽ được gửi đến email trong vòng 15-30 phút.
                </p>
                <div class="flex space-x-3">
                    <button onclick="closeSuccessModal()" class="flex-1 py-3 px-4 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors duration-200">
                        Đóng
                    </button>
                    <a href="orders.php" class="flex-1 bg-gradient-to-r from-blue-600 to-purple-600 text-white py-3 px-4 rounded-lg hover:from-blue-700 hover:to-purple-700 transition-all duration-200 text-center">
                        Xem đơn hàng
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        let selectedPackageId = null;
        let selectedPackageType = null;
        
        function switchTab(tab) {
            // Update tab buttons
            document.getElementById('vps-tab').className = tab === 'vps' 
                ? 'px-6 py-3 rounded-lg font-semibold transition-all duration-200 bg-gradient-to-r from-blue-600 to-purple-600 text-white'
                : 'px-6 py-3 rounded-lg font-semibold transition-all duration-200 text-gray-600 hover:text-gray-800';
            
            document.getElementById('proxy-tab').className = tab === 'proxy' 
                ? 'px-6 py-3 rounded-lg font-semibold transition-all duration-200 bg-gradient-to-r from-blue-600 to-purple-600 text-white'
                : 'px-6 py-3 rounded-lg font-semibold transition-all duration-200 text-gray-600 hover:text-gray-800';
            
            // Update content
            document.getElementById('vps-content').className = tab === 'vps' ? '' : 'hidden';
            document.getElementById('proxy-content').className = tab === 'proxy' ? '' : 'hidden';
            
            // Update URL
            const url = new URL(window.location);
            url.searchParams.set('tab', tab);
            window.history.pushState({}, '', url);
        }

        function switchVPSType(type) {
            // Update buttons
            ['all', 'trial', 'official'].forEach(t => {
                const btn = document.getElementById(t + '-btn');
                if (t === type) {
                    btn.className = btn.className.replace('text-gray-600 hover:text-gray-800', 
                        t === 'trial' ? 'bg-orange-600 text-white' : 
                        t === 'official' ? 'bg-blue-600 text-white' : 'bg-blue-600 text-white');
                } else {
                    btn.className = btn.className.replace(/bg-\w+-600 text-white/, 'text-gray-600 hover:text-gray-800');
                }
            });
            
            // Update content
            document.getElementById('trial-section').className = (type === 'trial' || type === 'all') ? '' : 'hidden';
            document.getElementById('official-section').className = (type === 'official' || type === 'all') ? '' : 'hidden';
            
            // Update URL
            const url = new URL(window.location);
            url.searchParams.set('type', type);
            window.history.pushState({}, '', url);
        }

        function selectPackage(packageId, packageType) {
            <?php if (!$user): ?>
                window.location.href = 'login.php';
                return;
            <?php endif; ?>
            
            selectedPackageId = packageId;
            selectedPackageType = packageType;
            
            // Load package info
            fetch(`api/get_package.php?id=${packageId}&type=${packageType}`)
                .then(response => response.json())
                .then(package => {
                    let packageInfo = '';
                    if (packageType === 'vps') {
                        packageInfo = `
                            <div class="flex items-center space-x-3 mb-3">
                                <i class="fas fa-server text-2xl text-blue-600"></i>
                                <div>
                                    <h3 class="font-semibold text-gray-900">${package.name}</h3>
                                    <p class="text-sm text-gray-600">${package.provider}</p>
                                </div>
                                <span class="px-2 py-1 rounded-full text-xs font-medium ${package.type === 'trial' ? 'bg-orange-100 text-orange-800' : 'bg-blue-100 text-blue-800'}">
                                    ${package.type === 'trial' ? 'VPS Trial' : 'VPS Chính hãng'}
                                </span>
                            </div>
                            <div class="grid grid-cols-2 gap-2 text-sm text-gray-600">
                                <div>CPU: ${package.cpu}</div>
                                <div>RAM: ${package.ram}</div>
                                <div>Lưu trữ: ${package.storage}</div>
                                <div>Băng thông: ${package.bandwidth}</div>
                            </div>
                        `;
                    } else {
                        packageInfo = `
                            <div class="flex items-center space-x-3 mb-3">
                                <i class="fas fa-shield-alt text-2xl text-green-600"></i>
                                <div>
                                    <h3 class="font-semibold text-gray-900">${package.name}</h3>
                                    <p class="text-sm text-gray-600">${package.provider}</p>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-2 text-sm text-gray-600">
                                <div>Vị trí: ${package.location}</div>
                                <div>Tốc độ: ${package.speed}</div>
                                <div>Kết nối: ${package.concurrent_connections}</div>
                                <div>Giao thức: ${package.type.toUpperCase()}</div>
                            </div>
                        `;
                    }
                    
                    packageInfo += `
                        <div class="mt-3 pt-3 border-t border-gray-200">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Tổng cộng:</span>
                                <span class="text-2xl font-bold text-gray-900">${formatCurrency(package.price)}/tháng</span>
                            </div>
                        </div>
                    `;
                    
                    document.getElementById('packageInfo').innerHTML = packageInfo;
                    document.getElementById('orderModal').classList.remove('hidden');
                });
        }

        function closeOrderModal() {
            document.getElementById('orderModal').classList.add('hidden');
        }

        function closeSuccessModal() {
            document.getElementById('successModal').classList.add('hidden');
        }

        function submitOrder(event) {
            event.preventDefault();
            
            // Show loading overlay
            document.getElementById('loadingOverlay').classList.remove('hidden');
            
            const formData = new FormData(event.target);
            formData.append('package_id', selectedPackageId);
            formData.append('package_type', selectedPackageType);
            
            fetch('api/create_order.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                // Hide loading overlay
                document.getElementById('loadingOverlay').classList.add('hidden');
                
                if (data.success) {
                    closeOrderModal();
                    document.getElementById('successModal').classList.remove('hidden');
                } else {
                    alert('Lỗi: ' + data.message);
                }
            })
            .catch(error => {
                // Hide loading overlay
                document.getElementById('loadingOverlay').classList.add('hidden');
                alert('Có lỗi xảy ra. Vui lòng thử lại.');
            });
        }

        function formatCurrency(amount) {
            return new Intl.NumberFormat('vi-VN', {
                style: 'currency',
                currency: 'VND'
            }).format(amount);
        }

        // Initialize from URL parameters
        const urlParams = new URLSearchParams(window.location.search);
        const tab = urlParams.get('tab') || 'vps';
        const type = urlParams.get('type') || 'all';
        switchTab(tab);
        if (tab === 'vps') {
            switchVPSType(type);
        }
    </script>
</body>
</html>