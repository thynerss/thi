<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirectTo('index.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username']);
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    
    if (empty($username) || empty($email) || empty($password) || empty($confirmPassword)) {
        $error = 'Vui lòng nhập đầy đủ thông tin';
    } elseif ($password !== $confirmPassword) {
        $error = 'Mật khẩu xác nhận không khớp';
    } elseif (strlen($password) < 6) {
        $error = 'Mật khẩu phải có ít nhất 6 ký tự';
    } elseif (getUserByEmail($email)) {
        $error = 'Email đã được sử dụng';
    } elseif (getUserByUsername($username)) {
        $error = 'Tên đăng nhập đã được sử dụng';
    } else {
        if (createUser($username, $email, $password)) {
            $success = 'Đăng ký thành công! Bạn có thể đăng nhập ngay bây giờ.';
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
    <title>Đăng ký - VPS & Proxy Việt Nam</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .gradient-bg { background: linear-gradient(135deg, #1e3a8a 0%, #3730a3 50%, #7c3aed 100%); }
    </style>
</head>
<body class="gradient-bg min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div class="bg-white rounded-2xl shadow-2xl p-8">
            <div class="text-center mb-8">
                <div class="flex justify-center mb-4">
                    <div class="bg-gradient-to-r from-blue-600 to-purple-600 p-3 rounded-2xl">
                        <i class="fas fa-server text-2xl text-white"></i>
                    </div>
                </div>
                <h2 class="text-3xl font-bold text-gray-900">Tạo tài khoản</h2>
                <p class="text-gray-600 mt-2">Tham gia VPS & Proxy Việt Nam ngay hôm nay</p>
            </div>

            <?php if ($error): ?>
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                <p class="text-red-600 text-sm"><?php echo htmlspecialchars($error); ?></p>
            </div>
            <?php endif; ?>

            <?php if ($success): ?>
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                <p class="text-green-600 text-sm"><?php echo htmlspecialchars($success); ?></p>
            </div>
            <?php endif; ?>

            <form method="POST" class="space-y-6">
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-2">
                        Tên đăng nhập
                    </label>
                    <div class="relative">
                        <i class="fas fa-user absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input id="username" name="username" type="text" required 
                               class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                               placeholder="Chọn tên đăng nhập" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                    </div>
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        Địa chỉ Email
                    </label>
                    <div class="relative">
                        <i class="fas fa-envelope absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input id="email" name="email" type="email" required 
                               class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                               placeholder="Nhập email của bạn" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        Mật khẩu
                    </label>
                    <div class="relative">
                        <i class="fas fa-lock absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input id="password" name="password" type="password" required
                               class="w-full pl-10 pr-12 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                               placeholder="Tạo mật khẩu">
                        <button type="button" onclick="togglePassword('password')" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <i id="passwordIcon" class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div>
                    <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-2">
                        Xác nhận mật khẩu
                    </label>
                    <div class="relative">
                        <i class="fas fa-lock absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input id="confirm_password" name="confirm_password" type="password" required
                               class="w-full pl-10 pr-12 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                               placeholder="Xác nhận mật khẩu">
                        <button type="button" onclick="togglePassword('confirm_password')" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <i id="confirmPasswordIcon" class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="flex items-center">
                    <input id="terms" name="terms" type="checkbox" required class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="terms" class="ml-2 block text-sm text-gray-700">
                        Tôi đồng ý với 
                        <a href="#" class="text-blue-600 hover:text-blue-500">Điều khoản dịch vụ</a>
                        và 
                        <a href="#" class="text-blue-600 hover:text-blue-500">Chính sách bảo mật</a>
                    </label>
                </div>

                <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-purple-600 text-white py-3 px-4 rounded-lg font-semibold hover:from-blue-700 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl">
                    Tạo tài khoản
                </button>
            </form>

            <div class="mt-8 text-center">
                <p class="text-gray-600">
                    Đã có tài khoản? 
                    <a href="login.php" class="text-blue-600 hover:text-blue-500 font-medium transition-colors duration-200">
                        Đăng nhập tại đây
                    </a>
                </p>
            </div>
        </div>
    </div>

    <script>
        function togglePassword(fieldId) {
            const passwordInput = document.getElementById(fieldId);
            const passwordIcon = document.getElementById(fieldId + 'Icon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                passwordIcon.className = 'fas fa-eye-slash';
            } else {
                passwordInput.type = 'password';
                passwordIcon.className = 'fas fa-eye';
            }
        }
    </script>
</body>
</html>