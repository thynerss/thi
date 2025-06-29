<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirectTo('index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = 'Vui lòng nhập đầy đủ thông tin';
    } else {
        $user = loginUser($email, $password);
        if ($user) {
            showAlert('Đăng nhập thành công!', 'success');
            redirectTo($user['role'] === 'admin' ? 'admin/index.php' : 'index.php');
        } else {
            $error = 'Email hoặc mật khẩu không đúng';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - VPS Việt Nam Pro</title>
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
                <h2 class="text-3xl font-bold text-gray-900">Chào mừng trở lại</h2>
                <p class="text-gray-600 mt-2">Đăng nhập vào VPS Việt Nam Pro</p>
            </div>

            <?php if ($error): ?>
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                <p class="text-red-600 text-sm"><?php echo htmlspecialchars($error); ?></p>
            </div>
            <?php endif; ?>

            <form method="POST" class="space-y-6">
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
                               placeholder="Nhập mật khẩu">
                        <button type="button" onclick="togglePassword()" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <i id="passwordIcon" class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember-me" name="remember-me" type="checkbox" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="remember-me" class="ml-2 block text-sm text-gray-700">
                            Ghi nhớ đăng nhập
                        </label>
                    </div>
                    <a href="forgot-password.php" class="text-sm text-blue-600 hover:text-blue-500 transition-colors duration-200">
                        Quên mật khẩu?
                    </a>
                </div>

                <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-purple-600 text-white py-3 px-4 rounded-lg font-semibold hover:from-blue-700 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl">
                    Đăng nhập
                </button>
            </form>

            <div class="mt-8 text-center">
                <p class="text-gray-600">
                    Chưa có tài khoản? 
                    <a href="register.php" class="text-blue-600 hover:text-blue-500 font-medium transition-colors duration-200">
                        Đăng ký tại đây
                    </a>
                </p>
            </div>

            <!-- Admin Login Info -->
            <div class="mt-6 p-4 bg-blue-50 rounded-lg">
                <h4 class="font-semibold text-blue-900 mb-2">Tài khoản Admin</h4>
                <p class="text-blue-800 text-sm">
                    Email: <code>thynerss@admin.com</code><br>
                    Password: <code>Thi12121704t@</code>
                </p>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const passwordIcon = document.getElementById('passwordIcon');
            
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