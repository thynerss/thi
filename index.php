<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Check if user is logged in
$user = null;
if (isset($_SESSION['user_id'])) {
    $user = getUserById($_SESSION['user_id']);
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VPS Việt Nam Pro - VPS Chính Hãng & VPS Trial Chất Lượng Cao</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .gradient-bg { background: linear-gradient(135deg, #1e3a8a 0%, #3730a3 50%, #7c3aed 100%); }
        .card-hover { transition: all 0.3s ease; }
        .card-hover:hover { transform: translateY(-8px); box-shadow: 0 20px 40px rgba(0,0,0,0.1); }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <?php include 'includes/header.php'; ?>

    <!-- Hero Section -->
    <section class="gradient-bg text-white overflow-hidden relative">
        <div class="absolute inset-0 bg-black opacity-20"></div>
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24">
            <div class="text-center">
                <h1 class="text-5xl md:text-7xl font-bold mb-6">
                    VPS Việt Nam Pro
                    <span class="block bg-gradient-to-r from-blue-400 to-purple-400 bg-clip-text text-transparent">
                        Chính hãng & Trial
                    </span>
                </h1>
                
                <p class="text-xl md:text-2xl text-blue-100 mb-8 max-w-3xl mx-auto">
                    VPS chính hãng từ các nhà cung cấp hàng đầu thế giới và VPS trial chất lượng cao. 
                    Hạ tầng đám mây đáng tin cậy cho mọi nhu cầu doanh nghiệp.
                </p>
                
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="packages.php?type=trial" class="bg-orange-600 text-white px-8 py-4 rounded-xl font-semibold hover:bg-orange-700 transition-all duration-200 shadow-lg hover:shadow-xl inline-flex items-center justify-center space-x-2">
                        <i class="fas fa-gift"></i>
                        <span>VPS Trial</span>
                    </a>
                    <a href="packages.php?type=official" class="bg-white text-blue-900 px-8 py-4 rounded-xl font-semibold hover:bg-blue-50 transition-all duration-200 shadow-lg hover:shadow-xl inline-flex items-center justify-center space-x-2">
                        <i class="fas fa-crown"></i>
                        <span>VPS Chính hãng</span>
                    </a>
                    <button onclick="handleContactClick()" class="border-2 border-white text-white px-8 py-4 rounded-xl font-semibold hover:bg-white hover:text-blue-900 transition-all duration-200">
                        Liên hệ tư vấn
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-900 mb-4">
                    Tại sao chọn VPS Việt Nam Pro?
                </h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Đối tác chính thức của các nhà cung cấp VPS hàng đầu thế giới
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <div class="bg-white p-8 rounded-2xl shadow-lg card-hover">
                    <div class="bg-blue-100 w-16 h-16 rounded-2xl flex items-center justify-center mb-6">
                        <i class="fas fa-certificate text-2xl text-blue-600"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-4">VPS Chính hãng</h3>
                    <p class="text-gray-600">
                        Đối tác chính thức của DigitalOcean, Vultr, Linode và các nhà cung cấp hàng đầu.
                    </p>
                </div>

                <div class="bg-white p-8 rounded-2xl shadow-lg card-hover">
                    <div class="bg-orange-100 w-16 h-16 rounded-2xl flex items-center justify-center mb-6">
                        <i class="fas fa-gift text-2xl text-orange-600"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-4">VPS Trial Chất lượng</h3>
                    <p class="text-gray-600">
                        VPS trial từ các nhà phân phối uy tín với cấu hình thực tế, không giới hạn.
                    </p>
                </div>

                <div class="bg-white p-8 rounded-2xl shadow-lg card-hover">
                    <div class="bg-green-100 w-16 h-16 rounded-2xl flex items-center justify-center mb-6">
                        <i class="fas fa-shield-alt text-2xl text-green-600"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-4">Bảo mật & Tin cậy</h3>
                    <p class="text-gray-600">
                        Bảo mật cấp doanh nghiệp với bảo vệ DDoS và đảm bảo uptime 99.9%.
                    </p>
                </div>

                <div class="bg-white p-8 rounded-2xl shadow-lg card-hover">
                    <div class="bg-purple-100 w-16 h-16 rounded-2xl flex items-center justify-center mb-6">
                        <i class="fas fa-headset text-2xl text-purple-600"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-4">Hỗ trợ 24/7</h3>
                    <p class="text-gray-600">
                        Hỗ trợ kỹ thuật chuyên gia có sẵn suốt ngày đêm để giúp bạn thành công.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- VPS Categories -->
    <section class="py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-900 mb-4">
                    Danh mục VPS chuyên nghiệp
                </h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Lựa chọn phù hợp cho mọi nhu cầu từ cá nhân đến doanh nghiệp
                </p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-16">
                <!-- VPS Trial -->
                <div class="bg-gradient-to-br from-orange-50 to-red-50 rounded-2xl shadow-lg overflow-hidden card-hover">
                    <div class="p-8">
                        <div class="flex items-center space-x-4 mb-6">
                            <div class="bg-orange-600 p-4 rounded-2xl">
                                <i class="fas fa-gift text-2xl text-white"></i>
                            </div>
                            <div>
                                <h3 class="text-2xl font-bold text-gray-900">VPS Trial</h3>
                                <p class="text-orange-600 font-semibold">Từ các nhà phân phối uy tín</p>
                            </div>
                        </div>
                        
                        <p class="text-gray-700 mb-6">
                            VPS trial chất lượng cao từ các nhà phân phối chính thức của DigitalOcean, Vultr, Linode. 
                            Cấu hình thực tế, thời gian sử dụng 7 ngày, phù hợp cho testing và phát triển.
                        </p>
                        
                        <div class="space-y-3 mb-8">
                            <div class="flex items-center space-x-2">
                                <i class="fas fa-check text-orange-500"></i>
                                <span class="text-gray-700">Từ các nhà phân phối chính thức</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <i class="fas fa-check text-orange-500"></i>
                                <span class="text-gray-700">Cấu hình thực tế, không giới hạn</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <i class="fas fa-check text-orange-500"></i>
                                <span class="text-gray-700">Thời gian sử dụng 7 ngày</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <i class="fas fa-check text-orange-500"></i>
                                <span class="text-gray-700">Giá cả phải chăng</span>
                            </div>
                        </div>
                        
                        <a href="packages.php?type=trial" class="bg-orange-600 text-white px-6 py-3 rounded-xl font-semibold hover:bg-orange-700 transition-all duration-200 inline-flex items-center space-x-2">
                            <span>Xem VPS Trial</span>
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>

                <!-- VPS Chính hãng -->
                <div class="bg-gradient-to-br from-blue-50 to-purple-50 rounded-2xl shadow-lg overflow-hidden card-hover">
                    <div class="p-8">
                        <div class="flex items-center space-x-4 mb-6">
                            <div class="bg-blue-600 p-4 rounded-2xl">
                                <i class="fas fa-crown text-2xl text-white"></i>
                            </div>
                            <div>
                                <h3 class="text-2xl font-bold text-gray-900">VPS Chính hãng</h3>
                                <p class="text-blue-600 font-semibold">Đối tác chính thức</p>
                            </div>
                        </div>
                        
                        <p class="text-gray-700 mb-6">
                            VPS chính hãng trực tiếp từ DigitalOcean, Vultr, Linode với đầy đủ tính năng enterprise. 
                            Đảm bảo chất lượng, hiệu suất và hỗ trợ tốt nhất.
                        </p>
                        
                        <div class="space-y-3 mb-8">
                            <div class="flex items-center space-x-2">
                                <i class="fas fa-check text-blue-500"></i>
                                <span class="text-gray-700">Đối tác chính thức của các nhà cung cấp</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <i class="fas fa-check text-blue-500"></i>
                                <span class="text-gray-700">Đầy đủ tính năng enterprise</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <i class="fas fa-check text-blue-500"></i>
                                <span class="text-gray-700">SLA 99.9% uptime</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <i class="fas fa-check text-blue-500"></i>
                                <span class="text-gray-700">Hỗ trợ 24/7 chuyên nghiệp</span>
                            </div>
                        </div>
                        
                        <a href="packages.php?type=official" class="bg-blue-600 text-white px-6 py-3 rounded-xl font-semibold hover:bg-blue-700 transition-all duration-200 inline-flex items-center space-x-2">
                            <span>Xem VPS Chính hãng</span>
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Featured Packages -->
            <div class="text-center mb-12">
                <h3 class="text-3xl font-bold text-gray-900 mb-4">Gói VPS nổi bật</h3>
                <p class="text-xl text-gray-600">Được khách hàng lựa chọn nhiều nhất</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <?php
                $packages = getFeaturedPackages();
                foreach ($packages as $index => $package):
                    $featured = $index === 1;
                ?>
                <div class="relative bg-white rounded-2xl shadow-lg border-2 <?php echo $featured ? 'border-blue-500 shadow-2xl' : 'border-gray-100'; ?> overflow-hidden card-hover">
                    <?php if ($featured): ?>
                    <div class="absolute top-0 left-0 right-0 bg-gradient-to-r from-blue-600 to-purple-600 text-white text-center py-2 text-sm font-semibold">
                        <i class="fas fa-star mr-1"></i>
                        PHỔ BIẾN NHẤT
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
                        </div>

                        <button onclick="selectPackage(<?php echo $package['id']; ?>, 'vps')" class="w-full py-2 px-4 rounded-lg font-semibold transition-all duration-200 text-sm <?php echo $featured ? 'bg-gradient-to-r from-blue-600 to-purple-600 text-white hover:from-blue-700 hover:to-purple-700 shadow-lg hover:shadow-xl' : 'bg-gray-900 text-white hover:bg-gray-800 shadow-md hover:shadow-lg'; ?>">
                            Chọn gói này
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="text-center mt-12">
                <a href="packages.php" class="inline-flex items-center space-x-2 bg-gradient-to-r from-blue-600 to-purple-600 text-white px-8 py-4 rounded-xl font-semibold hover:from-blue-700 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl">
                    <span>Xem tất cả gói VPS</span>
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- Proxy Section -->
    <section class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-900 mb-4">
                    Proxy SOCKS5 chuyên nghiệp
                </h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Proxy SOCKS5 chất lượng cao từ các nhà cung cấp uy tín
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <?php
                $proxyPackages = array_slice(getAllProxyPackages(), 0, 3);
                foreach ($proxyPackages as $index => $package):
                    $featured = $index === 0;
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
                            <h3 class="text-xl font-bold text-gray-900 mb-2"><?php echo htmlspecialchars($package['name']); ?></h3>
                            <p class="text-sm text-gray-600 mb-2"><?php echo htmlspecialchars($package['provider']); ?></p>
                            <div class="flex items-baseline justify-center">
                                <span class="text-3xl font-bold text-gray-900"><?php echo formatCurrency($package['price']); ?></span>
                                <span class="text-gray-500 ml-1">/tháng</span>
                            </div>
                        </div>

                        <div class="space-y-3 mb-6">
                            <div class="flex items-center space-x-3">
                                <div class="bg-blue-100 p-1 rounded">
                                    <i class="fas fa-globe text-blue-600 text-sm"></i>
                                </div>
                                <div class="flex-1">
                                    <span class="text-gray-600 text-xs">Vị trí</span>
                                    <p class="font-semibold text-gray-900 text-sm"><?php echo htmlspecialchars($package['location']); ?></p>
                                </div>
                            </div>

                            <div class="flex items-center space-x-3">
                                <div class="bg-green-100 p-1 rounded">
                                    <i class="fas fa-tachometer-alt text-green-600 text-sm"></i>
                                </div>
                                <div class="flex-1">
                                    <span class="text-gray-600 text-xs">Tốc độ</span>
                                    <p class="font-semibold text-gray-900 text-sm"><?php echo htmlspecialchars($package['speed']); ?></p>
                                </div>
                            </div>

                            <div class="flex items-center space-x-3">
                                <div class="bg-purple-100 p-1 rounded">
                                    <i class="fas fa-link text-purple-600 text-sm"></i>
                                </div>
                                <div class="flex-1">
                                    <span class="text-gray-600 text-xs">Kết nối</span>
                                    <p class="font-semibold text-gray-900 text-sm"><?php echo $package['concurrent_connections']; ?></p>
                                </div>
                            </div>
                        </div>

                        <button onclick="selectPackage(<?php echo $package['id']; ?>, 'proxy')" class="w-full py-2 px-4 rounded-lg font-semibold transition-all duration-200 text-sm <?php echo $featured ? 'bg-gradient-to-r from-green-600 to-blue-600 text-white hover:from-green-700 hover:to-blue-700 shadow-lg hover:shadow-xl' : 'bg-gray-900 text-white hover:bg-gray-800 shadow-md hover:shadow-lg'; ?>">
                            Chọn gói này
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="text-center mt-12">
                <a href="packages.php?tab=proxy" class="inline-flex items-center space-x-2 bg-gradient-to-r from-green-600 to-blue-600 text-white px-8 py-4 rounded-xl font-semibold hover:from-green-700 hover:to-blue-700 transition-all duration-200 shadow-lg hover:shadow-xl">
                    <span>Xem tất cả Proxy</span>
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="py-20 gradient-bg text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 text-center">
                <div>
                    <i class="fas fa-handshake text-5xl mb-4 text-blue-300"></i>
                    <div class="text-4xl font-bold mb-2">6+</div>
                    <div class="text-blue-200">Nhà cung cấp đối tác</div>
                </div>

                <div>
                    <i class="fas fa-server text-5xl mb-4 text-blue-300"></i>
                    <div class="text-4xl font-bold mb-2">1,000+</div>
                    <div class="text-blue-200">VPS đã bán</div>
                </div>

                <div>
                    <i class="fas fa-clock text-5xl mb-4 text-blue-300"></i>
                    <div class="text-4xl font-bold mb-2">99.9%</div>
                    <div class="text-blue-200">Đảm bảo Uptime</div>
                </div>

                <div>
                    <i class="fas fa-headset text-5xl mb-4 text-blue-300"></i>
                    <div class="text-4xl font-bold mb-2">24/7</div>
                    <div class="text-blue-200">Hỗ trợ chuyên gia</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Reviews Section -->
    <section class="py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-900 mb-4">
                    Khách hàng nói gì về chúng tôi
                </h2>
                <p class="text-xl text-gray-600">Đánh giá thực tế từ khách hàng</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <?php
                $reviews = getApprovedReviews(null, 3);
                if (empty($reviews)):
                ?>
                <!-- Sample reviews -->
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <div class="flex items-center space-x-1 mb-4">
                        <?php for($i = 0; $i < 5; $i++): ?>
                        <i class="fas fa-star text-yellow-400"></i>
                        <?php endfor; ?>
                    </div>
                    <p class="text-gray-700 mb-4">"VPS trial chất lượng tuyệt vời, cấu hình thực tế như mô tả. Sẽ mua VPS chính hãng ở đây."</p>
                    <div class="flex items-center space-x-3">
                        <div class="bg-blue-600 p-2 rounded-full">
                            <i class="fas fa-user text-white"></i>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900">Nguyễn Văn A</p>
                            <p class="text-gray-600 text-sm">VPS Trial DigitalOcean</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-lg p-6">
                    <div class="flex items-center space-x-1 mb-4">
                        <?php for($i = 0; $i < 5; $i++): ?>
                        <i class="fas fa-star text-yellow-400"></i>
                        <?php endfor; ?>
                    </div>
                    <p class="text-gray-700 mb-4">"VPS chính hãng từ Vultr rất ổn định, hỗ trợ nhiệt tình. Giá cả hợp lý."</p>
                    <div class="flex items-center space-x-3">
                        <div class="bg-green-600 p-2 rounded-full">
                            <i class="fas fa-user text-white"></i>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900">Trần Thị B</p>
                            <p class="text-gray-600 text-sm">VPS Official Vultr</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-lg p-6">
                    <div class="flex items-center space-x-1 mb-4">
                        <?php for($i = 0; $i < 5; $i++): ?>
                        <i class="fas fa-star text-yellow-400"></i>
                        <?php endfor; ?>
                    </div>
                    <p class="text-gray-700 mb-4">"Proxy SOCKS5 tốc độ cao, ổn định. Phù hợp cho công việc của tôi."</p>
                    <div class="flex items-center space-x-3">
                        <div class="bg-purple-600 p-2 rounded-full">
                            <i class="fas fa-user text-white"></i>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900">Lê Văn C</p>
                            <p class="text-gray-600 text-sm">Proxy SOCKS5 Singapore</p>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                <?php foreach($reviews as $review): ?>
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <div class="flex items-center space-x-1 mb-4">
                        <?php for($i = 0; $i < 5; $i++): ?>
                        <i class="fas fa-star <?php echo $i < $review['rating'] ? 'text-yellow-400' : 'text-gray-300'; ?>"></i>
                        <?php endfor; ?>
                    </div>
                    <h4 class="font-semibold text-gray-900 mb-2"><?php echo htmlspecialchars($review['title']); ?></h4>
                    <p class="text-gray-700 mb-4"><?php echo htmlspecialchars($review['content']); ?></p>
                    <div class="flex items-center space-x-3">
                        <div class="bg-blue-600 p-2 rounded-full">
                            <i class="fas fa-user text-white"></i>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($review['username']); ?></p>
                            <p class="text-gray-600 text-sm"><?php echo ucfirst($review['package_type']); ?> Package</p>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
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

    <script>
        let selectedPackageId = null;
        let selectedPackageType = null;
        
        function handleContactClick() {
            fetch('api/get_social_links.php')
                .then(response => response.json())
                .then(data => {
                    const contactUrl = data.facebook || data.twitter || data.youtube || data.instagram;
                    if (contactUrl) {
                        window.open(contactUrl, '_blank', 'noopener,noreferrer');
                    } else {
                        alert('Vui lòng liên hệ qua email: support@vpsvietnam.com');
                    }
                })
                .catch(() => {
                    alert('Vui lòng liên hệ qua email: support@vpsvietnam.com');
                });
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

        function submitOrder(event) {
            event.preventDefault();
            
            const formData = new FormData(event.target);
            formData.append('package_id', selectedPackageId);
            formData.append('package_type', selectedPackageType);
            
            fetch('api/create_order.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Đặt hàng thành công! Chúng tôi sẽ xử lý đơn hàng và gửi thông tin dịch vụ đến email của bạn trong vòng 15-30 phút.');
                    closeOrderModal();
                    window.location.href = 'orders.php';
                } else {
                    alert('Lỗi: ' + data.message);
                }
            })
            .catch(error => {
                alert('Có lỗi xảy ra. Vui lòng thử lại.');
            });
        }

        function formatCurrency(amount) {
            return new Intl.NumberFormat('vi-VN', {
                style: 'currency',
                currency: 'VND'
            }).format(amount);
        }
    </script>
</body>
</html>