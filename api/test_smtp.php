<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json; charset=UTF-8');

// Check if user is admin
if (!isAdmin()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $testEmail = sanitizeInput($_POST['test_email'] ?? '');
    
    if (empty($testEmail) || !filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Email không hợp lệ']);
        exit;
    }
    
    $subject = "Test SMTP - VPS Việt Nam Pro";
    $message = "
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #1e3a8a 0%, #7c3aed 100%); color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
            .content { background: #f8f9fa; padding: 20px; border-radius: 0 0 8px 8px; }
            .success { background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; color: #155724; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>✅ SMTP Test Successful!</h1>
                <p>VPS Việt Nam Pro - Email System</p>
            </div>
            
            <div class='content'>
                <div class='success'>
                    <h3>🎉 Chúc mừng!</h3>
                    <p>Hệ thống SMTP đã được cấu hình thành công và hoạt động bình thường.</p>
                </div>
                
                <h3>📧 Thông tin test:</h3>
                <ul>
                    <li><strong>Thời gian:</strong> " . date('d/m/Y H:i:s') . "</li>
                    <li><strong>Email nhận:</strong> $testEmail</li>
                    <li><strong>Trạng thái:</strong> Gửi thành công</li>
                </ul>
                
                <p>Hệ thống email của bạn đã sẵn sàng để gửi thông báo đơn hàng, thông tin VPS/Proxy và các email khác.</p>
                
                <hr style='margin: 20px 0; border: none; border-top: 1px solid #ddd;'>
                
                <p style='text-align: center; color: #666; font-size: 14px;'>
                    <strong>VPS Việt Nam Pro</strong><br>
                    Email: support@vpsvietnam.com<br>
                    Website: vpsvietnam.com
                </p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    $result = sendSMTPEmail($testEmail, $subject, $message, true);
    
    if ($result) {
        echo json_encode([
            'success' => true, 
            'message' => 'Email test đã được gửi thành công đến ' . $testEmail
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Không thể gửi email. Vui lòng kiểm tra cấu hình SMTP.'
        ]);
    }
    
} catch (Exception $e) {
    error_log("SMTP test error: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Lỗi hệ thống: ' . $e->getMessage()
    ]);
}
?>