<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirectTo('login.php');
}

$user = getUserById($_SESSION['user_id']);
$tickets = getUserSupportTickets($_SESSION['user_id']);

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = sanitizeInput($_POST['subject']);
    $message = sanitizeInput($_POST['message']);
    $priority = sanitizeInput($_POST['priority']);
    $category = sanitizeInput($_POST['category']);
    
    if (empty($subject) || empty($message)) {
        $error = 'Vui lòng nhập đầy đủ thông tin';
    } else {
        $ticketId = createSupportTicket($_SESSION['user_id'], $subject, $message, $priority, $category);
        if ($ticketId) {
            $success = 'Ticket hỗ trợ đã được tạo thành công! Mã ticket: #' . $ticketId;
            $tickets = getUserSupportTickets($_SESSION['user_id']); // Refresh list
        } else {
            $error = 'Có lỗi xảy ra. Vui lòng thử lại.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hỗ trợ kỹ thuật - VPS Việt Nam Pro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <?php include 'includes/header.php'; ?>

    <div class="min-h-screen py-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Hỗ trợ kỹ thuật</h1>
                <p class="text-gray-600 mt-2">Gửi yêu cầu hỗ trợ và theo dõi trạng thái xử lý</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Create Ticket Form -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-xl shadow-lg p-6">
                        <div class="flex items-center space-x-3 mb-6">
                            <div class="bg-gradient-to-r from-blue-600 to-purple-600 p-3 rounded-lg">
                                <i class="fas fa-headset text-white"></i>
                            </div>
                            <div>
                                <h2 class="text-xl font-bold text-gray-900">Tạo ticket hỗ trợ</h2>
                                <p class="text-gray-600">Mô tả chi tiết vấn đề bạn gặp phải</p>
                            </div>
                        </div>

                        <?php if ($success): ?>
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                            <p class="text-green-600 text-sm"><?php echo htmlspecialchars($success); ?></p>
                        </div>
                        <?php endif; ?>

                        <?php if ($error): ?>
                        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                            <p class="text-red-600 text-sm"><?php echo htmlspecialchars($error); ?></p>
                        </div>
                        <?php endif; ?>

                        <form method="POST" class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="category" class="block text-sm font-medium text-gray-700 mb-2">
                                        Danh mục
                                    </label>
                                    <select id="category" name="category" required class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        <option value="technical">Kỹ thuật</option>
                                        <option value="billing">Thanh toán</option>
                                        <option value="general">Chung</option>
                                        <option value="abuse">Báo cáo vi phạm</option>
                                    </select>
                                </div>

                                <div>
                                    <label for="priority" class="block text-sm font-medium text-gray-700 mb-2">
                                        Độ ưu tiên
                                    </label>
                                    <select id="priority" name="priority" required class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        <option value="low">Thấp</option>
                                        <option value="medium" selected>Trung bình</option>
                                        <option value="high">Cao</option>
                                        <option value="urgent">Khẩn cấp</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label for="subject" class="block text-sm font-medium text-gray-700 mb-2">
                                    Tiêu đề
                                </label>
                                <input type="text" id="subject" name="subject" required 
                                       class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                       placeholder="Mô tả ngắn gọn vấn đề">
                            </div>

                            <div>
                                <label for="message" class="block text-sm font-medium text-gray-700 mb-2">
                                    Nội dung chi tiết
                                </label>
                                <textarea id="message" name="message" rows="6" required
                                          class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                          placeholder="Mô tả chi tiết vấn đề bạn gặp phải, bao gồm các bước đã thực hiện và thông báo lỗi (nếu có)"></textarea>
                            </div>

                            <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-purple-600 text-white py-3 px-4 rounded-lg font-semibold hover:from-blue-700 hover:to-purple-700 transition-all duration-200 flex items-center justify-center space-x-2">
                                <i class="fas fa-paper-plane"></i>
                                <span>Gửi yêu cầu hỗ trợ</span>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Support Info -->
                <div class="space-y-6">
                    <!-- Contact Info -->
                    <div class="bg-white rounded-xl shadow-lg p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Thông tin liên hệ</h3>
                        <div class="space-y-4">
                            <div class="flex items-center space-x-3">
                                <i class="fas fa-envelope text-blue-600"></i>
                                <div>
                                    <p class="font-medium text-gray-900">Email</p>
                                    <p class="text-gray-600 text-sm">support@vpsvietnam.com</p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-3">
                                <i class="fas fa-clock text-green-600"></i>
                                <div>
                                    <p class="font-medium text-gray-900">Thời gian hỗ trợ</p>
                                    <p class="text-gray-600 text-sm">24/7 - Tất cả các ngày</p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-3">
                                <i class="fas fa-reply text-purple-600"></i>
                                <div>
                                    <p class="font-medium text-gray-900">Thời gian phản hồi</p>
                                    <p class="text-gray-600 text-sm">Trong vòng 2-4 giờ</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- FAQ -->
                    <div class="bg-white rounded-xl shadow-lg p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Câu hỏi thường gặp</h3>
                        <div class="space-y-3">
                            <details class="group">
                                <summary class="flex justify-between items-center cursor-pointer p-3 bg-gray-50 rounded-lg">
                                    <span class="font-medium text-gray-900">Làm sao để reset mật khẩu VPS?</span>
                                    <i class="fas fa-chevron-down group-open:rotate-180 transition-transform"></i>
                                </summary>
                                <div class="mt-2 p-3 text-gray-600 text-sm">
                                    Bạn có thể reset mật khẩu VPS thông qua control panel hoặc gửi ticket hỗ trợ để được hỗ trợ.
                                </div>
                            </details>
                            
                            <details class="group">
                                <summary class="flex justify-between items-center cursor-pointer p-3 bg-gray-50 rounded-lg">
                                    <span class="font-medium text-gray-900">VPS trial có giới hạn gì?</span>
                                    <i class="fas fa-chevron-down group-open:rotate-180 transition-transform"></i>
                                </summary>
                                <div class="mt-2 p-3 text-gray-600 text-sm">
                                    VPS trial có thời hạn 7 ngày, mỗi tài khoản chỉ được sử dụng 1 lần và có cấu hình cơ bản.
                                </div>
                            </details>
                            
                            <details class="group">
                                <summary class="flex justify-between items-center cursor-pointer p-3 bg-gray-50 rounded-lg">
                                    <span class="font-medium text-gray-900">Làm sao để nâng cấp VPS?</span>
                                    <i class="fas fa-chevron-down group-open:rotate-180 transition-transform"></i>
                                </summary>
                                <div class="mt-2 p-3 text-gray-600 text-sm">
                                    Bạn có thể nâng cấp VPS bằng cách gửi ticket hỗ trợ hoặc liên hệ trực tiếp với team support.
                                </div>
                            </details>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Support Tickets History -->
            <div class="mt-8 bg-white rounded-xl shadow-lg p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-6">Lịch sử ticket hỗ trợ</h2>
                
                <?php if (empty($tickets)): ?>
                <div class="text-center py-8">
                    <i class="fas fa-ticket-alt text-4xl text-gray-300 mb-4"></i>
                    <p class="text-gray-500">Chưa có ticket hỗ trợ nào</p>
                </div>
                <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ticket ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tiêu đề</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Danh mục</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Độ ưu tiên</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trạng thái</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ngày tạo</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($tickets as $ticket): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="font-mono text-sm font-medium text-gray-900">#<?php echo $ticket['id']; ?></span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($ticket['subject']); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        <?php echo ucfirst($ticket['category']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 rounded-full text-xs font-medium <?php 
                                        echo $ticket['priority'] === 'urgent' ? 'bg-red-100 text-red-800' : 
                                             ($ticket['priority'] === 'high' ? 'bg-orange-100 text-orange-800' : 
                                              ($ticket['priority'] === 'medium' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800')); 
                                    ?>">
                                        <?php echo ucfirst($ticket['priority']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 rounded-full text-xs font-medium <?php 
                                        echo $ticket['status'] === 'resolved' ? 'bg-green-100 text-green-800' : 
                                             ($ticket['status'] === 'in_progress' ? 'bg-blue-100 text-blue-800' : 
                                              ($ticket['status'] === 'closed' ? 'bg-gray-100 text-gray-800' : 'bg-yellow-100 text-yellow-800')); 
                                    ?>">
                                        <?php 
                                        switch($ticket['status']) {
                                            case 'open': echo 'Mở'; break;
                                            case 'in_progress': echo 'Đang xử lý'; break;
                                            case 'resolved': echo 'Đã giải quyết'; break;
                                            case 'closed': echo 'Đã đóng'; break;
                                            default: echo 'Không xác định'; break;
                                        }
                                        ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo date('d/m/Y H:i', strtotime($ticket['created_at'])); ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>
</body>
</html>