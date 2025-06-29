<footer class="bg-gray-900 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <!-- Brand -->
            <div class="col-span-1 md:col-span-2">
                <div class="flex items-center space-x-2 mb-4">
                    <div class="bg-gradient-to-r from-blue-600 to-purple-600 p-2 rounded-lg">
                        <i class="fas fa-server text-white"></i>
                    </div>
                    <span class="text-xl font-bold">VPS Việt Nam</span>
                </div>
                <p class="text-gray-400 mb-4 max-w-md">
                    Giải pháp hosting VPS cao cấp với đảm bảo uptime 99.9%. 
                    Hạ tầng đám mây nhanh, đáng tin cậy và bảo mật cho nhu cầu doanh nghiệp của bạn.
                </p>
                <div class="flex items-center space-x-2 text-gray-400">
                    <i class="fas fa-envelope"></i>
                    <span><?php echo getSystemSetting('contact_email') ?: 'support@vpsvietnam.com'; ?></span>
                </div>
            </div>

            <!-- Services -->
            <div>
                <h3 class="text-lg font-semibold mb-4">Dịch vụ</h3>
                <ul class="space-y-2 text-gray-400">
                    <li><a href="packages.php" class="hover:text-white transition-colors duration-200">VPS Hosting</a></li>
                    <li><a href="#" class="hover:text-white transition-colors duration-200">Cloud Storage</a></li>
                    <li><a href="#" class="hover:text-white transition-colors duration-200">Dedicated Server</a></li>
                    <li><a href="#" class="hover:text-white transition-colors duration-200">Đăng ký Domain</a></li>
                </ul>
            </div>

            <!-- Support & Social -->
            <div>
                <h3 class="text-lg font-semibold mb-4">Hỗ trợ & Liên hệ</h3>
                <ul class="space-y-2 text-gray-400 mb-6">
                    <li><a href="#" class="hover:text-white transition-colors duration-200">Tài liệu hướng dẫn</a></li>
                    <li><a href="#" class="hover:text-white transition-colors duration-200">Câu hỏi thường gặp</a></li>
                    <li>
                        <button onclick="handleContactClick()" class="hover:text-white transition-colors duration-200 text-left">
                            Liên hệ tư vấn
                        </button>
                    </li>
                    <li><a href="#" class="hover:text-white transition-colors duration-200">Trạng thái hệ thống</a></li>
                </ul>
                
                <h4 class="text-sm font-semibold mb-3">Theo dõi chúng tôi</h4>
                <div class="flex space-x-3">
                    <?php
                    $socialLinks = getSocialLinks();
                    if ($socialLinks['facebook']):
                    ?>
                    <a href="<?php echo htmlspecialchars($socialLinks['facebook']); ?>" target="_blank" rel="noopener noreferrer" 
                       class="bg-gray-800 p-2 rounded-lg hover:bg-blue-600 transition-colors duration-200">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <?php endif; ?>
                    
                    <?php if ($socialLinks['twitter']): ?>
                    <a href="<?php echo htmlspecialchars($socialLinks['twitter']); ?>" target="_blank" rel="noopener noreferrer"
                       class="bg-gray-800 p-2 rounded-lg hover:bg-blue-400 transition-colors duration-200">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <?php endif; ?>
                    
                    <?php if ($socialLinks['youtube']): ?>
                    <a href="<?php echo htmlspecialchars($socialLinks['youtube']); ?>" target="_blank" rel="noopener noreferrer"
                       class="bg-gray-800 p-2 rounded-lg hover:bg-red-600 transition-colors duration-200">
                        <i class="fab fa-youtube"></i>
                    </a>
                    <?php endif; ?>
                    
                    <?php if ($socialLinks['instagram']): ?>
                    <a href="<?php echo htmlspecialchars($socialLinks['instagram']); ?>" target="_blank" rel="noopener noreferrer"
                       class="bg-gray-800 p-2 rounded-lg hover:bg-pink-600 transition-colors duration-200">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="border-t border-gray-800 mt-8 pt-8 flex flex-col md:flex-row justify-between items-center">
            <p class="text-gray-400 text-sm">
                © 2024 VPS Việt Nam. Tất cả quyền được bảo lưu.
            </p>
            <div class="flex space-x-6 mt-4 md:mt-0">
                <a href="#" class="text-gray-400 hover:text-white transition-colors duration-200 text-sm">
                    Chính sách bảo mật
                </a>
                <a href="#" class="text-gray-400 hover:text-white transition-colors duration-200 text-sm">
                    Điều khoản dịch vụ
                </a>
            </div>
        </div>
    </div>
</footer>

<script>
function handleContactClick() {
    fetch('api/get_social_links.php')
        .then(response => response.json())
        .then(data => {
            const contactUrl = data.facebook || data.twitter || data.youtube || data.instagram;
            if (contactUrl) {
                window.open(contactUrl, '_blank', 'noopener,noreferrer');
            } else {
                alert('Vui lòng liên hệ qua email: <?php echo getSystemSetting('contact_email') ?: 'support@vpsvietnam.com'; ?>');
            }
        })
        .catch(() => {
            alert('Vui lòng liên hệ qua email: <?php echo getSystemSetting('contact_email') ?: 'support@vpsvietnam.com'; ?>');
        });
}
</script>