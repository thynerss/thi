<?php
require_once __DIR__ . '/../config/database.php';

// User functions
function getUserById($id) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    } catch (Exception $e) {
        error_log("Error in getUserById: " . $e->getMessage());
        return false;
    }
}

function getUserByEmail($email) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    } catch (Exception $e) {
        error_log("Error in getUserByEmail: " . $e->getMessage());
        return false;
    }
}

function getUserByUsername($username) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetch();
    } catch (Exception $e) {
        error_log("Error in getUserByUsername: " . $e->getMessage());
        return false;
    }
}

function createUser($username, $email, $password) {
    global $pdo;
    try {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        return $stmt->execute([$username, $email, $hashedPassword]);
    } catch (Exception $e) {
        error_log("Error in createUser: " . $e->getMessage());
        return false;
    }
}

function loginUser($email, $password) {
    try {
        $user = getUserByEmail($email);
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            return $user;
        }
        return false;
    } catch (Exception $e) {
        error_log("Error in loginUser: " . $e->getMessage());
        return false;
    }
}

function logoutUser() {
    session_destroy();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    if (!isLoggedIn()) return false;
    $user = getUserById($_SESSION['user_id']);
    return $user && $user['role'] === 'admin';
}

// VPS Package functions
function getAllVPSPackages($type = null) {
    global $pdo;
    try {
        $sql = "SELECT * FROM vps_packages WHERE status = 'active'";
        $params = [];
        
        if ($type) {
            $sql .= " AND type = ?";
            $params[] = $type;
        }
        
        $sql .= " ORDER BY FIELD(type, 'trial', 'official'), price ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("Error in getAllVPSPackages: " . $e->getMessage());
        return [];
    }
}

function getTrialVPSPackages() {
    return getAllVPSPackages('trial');
}

function getOfficialVPSPackages() {
    return getAllVPSPackages('official');
}

function getFeaturedPackages() {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT * FROM vps_packages WHERE status = 'active' AND type = 'official' ORDER BY profit_margin DESC LIMIT 3");
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("Error in getFeaturedPackages: " . $e->getMessage());
        return [];
    }
}

function getVPSPackageById($id) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT * FROM vps_packages WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    } catch (Exception $e) {
        error_log("Error in getVPSPackageById: " . $e->getMessage());
        return false;
    }
}

// Proxy Package functions
function getAllProxyPackages() {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT * FROM proxy_packages WHERE status = 'active' ORDER BY price ASC");
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("Error in getAllProxyPackages: " . $e->getMessage());
        return [];
    }
}

function getProxyPackageById($id) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT * FROM proxy_packages WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    } catch (Exception $e) {
        error_log("Error in getProxyPackageById: " . $e->getMessage());
        return false;
    }
}

// Order functions
function createOrder($userId, $packageId, $packageType, $email) {
    global $pdo;
    
    try {
        if ($packageType === 'vps') {
            $package = getVPSPackageById($packageId);
        } else {
            $package = getProxyPackageById($packageId);
        }
        
        if (!$package || $package['status'] !== 'active') return false;
        
        $user = getUserById($userId);
        if (!$user || $user['balance'] < $package['price']) return false;
        
        $pdo->beginTransaction();
        
        // Generate order code
        $orderCode = strtoupper($packageType) . date('Y') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        // Calculate profit
        $costPrice = $package['original_price'] ?? 0;
        $profit = $package['price'] - $costPrice;
        
        // Create order
        $stmt = $pdo->prepare("INSERT INTO orders (user_id, package_id, package_type, customer_email, amount, cost_price, profit, order_code) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$userId, $packageId, $packageType, $email, $package['price'], $costPrice, $profit, $orderCode]);
        $orderId = $pdo->lastInsertId();
        
        // Deduct balance
        $stmt = $pdo->prepare("UPDATE users SET balance = balance - ? WHERE id = ?");
        $stmt->execute([$package['price'], $userId]);
        
        // Update stock
        if ($packageType === 'vps') {
            $stmt = $pdo->prepare("UPDATE vps_packages SET stock_quantity = stock_quantity - 1 WHERE id = ? AND stock_quantity > 0");
        } else {
            $stmt = $pdo->prepare("UPDATE proxy_packages SET stock_quantity = stock_quantity - 1 WHERE id = ? AND stock_quantity > 0");
        }
        $stmt->execute([$packageId]);
        
        // Create transaction record
        $transactionId = 'TXN' . time() . mt_rand(100, 999);
        $description = 'Mua ' . ($packageType === 'vps' ? 'VPS' : 'Proxy') . ': ' . $package['name'];
        $stmt = $pdo->prepare("INSERT INTO transactions (user_id, amount, type, status, payment_method, transaction_id, description) VALUES (?, ?, 'purchase', 'completed', 'Số dư tài khoản', ?, ?)");
        $stmt->execute([$userId, -$package['price'], $transactionId, $description]);
        
        $pdo->commit();
        return $orderId;
    } catch (Exception $e) {
        $pdo->rollback();
        error_log("Error in createOrder: " . $e->getMessage());
        return false;
    }
}

function getUserOrders($userId) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("
            SELECT o.*, 
                   CASE 
                       WHEN o.package_type = 'vps' THEN vp.name
                       WHEN o.package_type = 'proxy' THEN pp.name
                   END as package_name,
                   CASE 
                       WHEN o.package_type = 'vps' THEN vp.type
                       ELSE NULL
                   END as package_tier,
                   CASE 
                       WHEN o.package_type = 'vps' THEN vp.provider
                       WHEN o.package_type = 'proxy' THEN pp.provider
                   END as provider,
                   CASE 
                       WHEN o.package_type = 'vps' THEN vp.cpu
                       ELSE NULL
                   END as cpu,
                   CASE 
                       WHEN o.package_type = 'vps' THEN vp.ram
                       ELSE NULL
                   END as ram,
                   CASE 
                       WHEN o.package_type = 'vps' THEN vp.storage
                       ELSE NULL
                   END as storage,
                   CASE 
                       WHEN o.package_type = 'vps' THEN vp.bandwidth
                       ELSE NULL
                   END as bandwidth,
                   CASE 
                       WHEN o.package_type = 'proxy' THEN pp.location
                       ELSE NULL
                   END as location,
                   CASE 
                       WHEN o.package_type = 'proxy' THEN pp.speed
                       ELSE NULL
                   END as speed,
                   CASE 
                       WHEN o.package_type = 'proxy' THEN pp.concurrent_connections
                       ELSE NULL
                   END as concurrent_connections,
                   CASE 
                       WHEN o.package_type = 'proxy' THEN pp.type
                       ELSE NULL
                   END as type
            FROM orders o 
            LEFT JOIN vps_packages vp ON o.package_id = vp.id AND o.package_type = 'vps'
            LEFT JOIN proxy_packages pp ON o.package_id = pp.id AND o.package_type = 'proxy'
            WHERE o.user_id = ? 
            ORDER BY o.created_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("Error in getUserOrders: " . $e->getMessage());
        return [];
    }
}

function getOrderById($id) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("
            SELECT o.*, 
                   CASE 
                       WHEN o.package_type = 'vps' THEN vp.name
                       WHEN o.package_type = 'proxy' THEN pp.name
                   END as package_name,
                   CASE 
                       WHEN o.package_type = 'vps' THEN vp.provider
                       WHEN o.package_type = 'proxy' THEN pp.provider
                   END as provider,
                   u.username, u.email as user_email
            FROM orders o 
            LEFT JOIN vps_packages vp ON o.package_id = vp.id AND o.package_type = 'vps'
            LEFT JOIN proxy_packages pp ON o.package_id = pp.id AND o.package_type = 'proxy'
            JOIN users u ON o.user_id = u.id
            WHERE o.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    } catch (Exception $e) {
        error_log("Error in getOrderById: " . $e->getMessage());
        return false;
    }
}

// VPS Info functions
function addVPSInfo($orderId, $serverIp, $username, $password, $sshPort, $controlPanelUrl, $controlPanelUser, $controlPanelPass, $osTemplate, $datacenter, $additionalInfo) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("INSERT INTO vps_info (order_id, server_ip, username, password, ssh_port, control_panel_url, control_panel_user, control_panel_pass, os_template, datacenter, additional_info) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        return $stmt->execute([$orderId, $serverIp, $username, $password, $sshPort, $controlPanelUrl, $controlPanelUser, $controlPanelPass, $osTemplate, $datacenter, $additionalInfo]);
    } catch (Exception $e) {
        error_log("Error in addVPSInfo: " . $e->getMessage());
        return false;
    }
}

function getVPSInfo($orderId) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT * FROM vps_info WHERE order_id = ?");
        $stmt->execute([$orderId]);
        return $stmt->fetch();
    } catch (Exception $e) {
        error_log("Error in getVPSInfo: " . $e->getMessage());
        return false;
    }
}

// Proxy Info functions
function addProxyInfo($orderId, $proxyIp, $proxyPort, $username, $password, $protocol, $location, $additionalInfo) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("INSERT INTO proxy_info (order_id, proxy_ip, proxy_port, username, password, protocol, location, additional_info) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        return $stmt->execute([$orderId, $proxyIp, $proxyPort, $username, $password, $protocol, $location, $additionalInfo]);
    } catch (Exception $e) {
        error_log("Error in addProxyInfo: " . $e->getMessage());
        return false;
    }
}

function getProxyInfo($orderId) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT * FROM proxy_info WHERE order_id = ?");
        $stmt->execute([$orderId]);
        return $stmt->fetch();
    } catch (Exception $e) {
        error_log("Error in getProxyInfo: " . $e->getMessage());
        return false;
    }
}

// Enhanced SMTP Email functions
function sendSMTPEmail($to, $subject, $message, $isHTML = true) {
    try {
        // Get SMTP settings
        $smtpEnabled = getSystemSetting('smtp_enabled') === '1';
        
        if (!$smtpEnabled) {
            // Fallback to PHP mail()
            return sendBasicEmail($to, $subject, $message, $isHTML);
        }
        
        $smtpHost = getSystemSetting('smtp_host');
        $smtpPort = getSystemSetting('smtp_port') ?: 587;
        $smtpUsername = getSystemSetting('smtp_username');
        $smtpPassword = getSystemSetting('smtp_password');
        $smtpEncryption = getSystemSetting('smtp_encryption') ?: 'tls';
        $fromEmail = getSystemSetting('smtp_from_email') ?: 'noreply@vpsvietnam.com';
        $fromName = getSystemSetting('smtp_from_name') ?: 'VPS Việt Nam Pro';
        
        if (empty($smtpHost) || empty($smtpUsername) || empty($smtpPassword)) {
            error_log("SMTP settings incomplete");
            return sendBasicEmail($to, $subject, $message, $isHTML);
        }
        
        // Create socket connection
        $socket = fsockopen($smtpHost, $smtpPort, $errno, $errstr, 30);
        if (!$socket) {
            error_log("SMTP connection failed: $errstr ($errno)");
            return sendBasicEmail($to, $subject, $message, $isHTML);
        }
        
        // Read server response
        $response = fgets($socket, 515);
        if (substr($response, 0, 3) != '220') {
            error_log("SMTP server not ready: $response");
            fclose($socket);
            return sendBasicEmail($to, $subject, $message, $isHTML);
        }
        
        // Send EHLO
        fputs($socket, "EHLO " . $_SERVER['HTTP_HOST'] . "\r\n");
        $response = fgets($socket, 515);
        
        // Start TLS if required
        if ($smtpEncryption === 'tls') {
            fputs($socket, "STARTTLS\r\n");
            $response = fgets($socket, 515);
            if (substr($response, 0, 3) != '220') {
                error_log("STARTTLS failed: $response");
                fclose($socket);
                return sendBasicEmail($to, $subject, $message, $isHTML);
            }
            
            // Enable crypto
            if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                error_log("TLS encryption failed");
                fclose($socket);
                return sendBasicEmail($to, $subject, $message, $isHTML);
            }
            
            // Send EHLO again after TLS
            fputs($socket, "EHLO " . $_SERVER['HTTP_HOST'] . "\r\n");
            $response = fgets($socket, 515);
        }
        
        // Authenticate
        fputs($socket, "AUTH LOGIN\r\n");
        $response = fgets($socket, 515);
        if (substr($response, 0, 3) != '334') {
            error_log("AUTH LOGIN failed: $response");
            fclose($socket);
            return sendBasicEmail($to, $subject, $message, $isHTML);
        }
        
        fputs($socket, base64_encode($smtpUsername) . "\r\n");
        $response = fgets($socket, 515);
        if (substr($response, 0, 3) != '334') {
            error_log("Username authentication failed: $response");
            fclose($socket);
            return sendBasicEmail($to, $subject, $message, $isHTML);
        }
        
        fputs($socket, base64_encode($smtpPassword) . "\r\n");
        $response = fgets($socket, 515);
        if (substr($response, 0, 3) != '235') {
            error_log("Password authentication failed: $response");
            fclose($socket);
            return sendBasicEmail($to, $subject, $message, $isHTML);
        }
        
        // Send email
        fputs($socket, "MAIL FROM: <$fromEmail>\r\n");
        $response = fgets($socket, 515);
        
        fputs($socket, "RCPT TO: <$to>\r\n");
        $response = fgets($socket, 515);
        
        fputs($socket, "DATA\r\n");
        $response = fgets($socket, 515);
        
        // Email headers
        $headers = "From: $fromName <$fromEmail>\r\n";
        $headers .= "Reply-To: $fromEmail\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        if ($isHTML) {
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        } else {
            $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        }
        $headers .= "Content-Transfer-Encoding: 8bit\r\n";
        $headers .= "Date: " . date('r') . "\r\n";
        $headers .= "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=\r\n";
        
        // Send email content
        fputs($socket, $headers . "\r\n" . $message . "\r\n.\r\n");
        $response = fgets($socket, 515);
        
        // Quit
        fputs($socket, "QUIT\r\n");
        fclose($socket);
        
        return substr($response, 0, 3) == '250';
        
    } catch (Exception $e) {
        error_log("SMTP Error: " . $e->getMessage());
        return sendBasicEmail($to, $subject, $message, $isHTML);
    }
}

function sendBasicEmail($to, $subject, $message, $isHTML = true) {
    try {
        $fromEmail = getSystemSetting('smtp_from_email') ?: 'noreply@vpsvietnam.com';
        $fromName = getSystemSetting('smtp_from_name') ?: 'VPS Việt Nam Pro';
        
        $headers = "From: $fromName <$fromEmail>\r\n";
        $headers .= "Reply-To: $fromEmail\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        if ($isHTML) {
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        } else {
            $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        }
        $headers .= "Content-Transfer-Encoding: 8bit\r\n";
        
        return mail($to, $subject, $message, $headers);
    } catch (Exception $e) {
        error_log("Basic email error: " . $e->getMessage());
        return false;
    }
}

function sendVPSInfoEmail($orderId) {
    try {
        $order = getOrderById($orderId);
        $vpsInfo = getVPSInfo($orderId);
        
        if (!$order || !$vpsInfo) return false;
        
        $to = $order['customer_email'];
        $subject = "Thông tin VPS - Đơn hàng " . $order['order_code'];
        
        $message = "
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Thông tin VPS</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #1e3a8a 0%, #7c3aed 100%); color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
                .content { background: #f8f9fa; padding: 20px; border-radius: 0 0 8px 8px; }
                .info-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
                .info-table td { padding: 12px; border: 1px solid #ddd; }
                .info-table td:first-child { background: #e9ecef; font-weight: bold; width: 30%; }
                .alert { background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 20px 0; }
                .footer { text-align: center; margin-top: 30px; color: #666; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🎉 VPS của bạn đã sẵn sàng!</h1>
                    <p>Cảm ơn bạn đã tin tưởng VPS Việt Nam Pro</p>
                </div>
                
                <div class='content'>
                    <h2>Xin chào {$order['username']},</h2>
                    <p>VPS của bạn đã được thiết lập thành công và sẵn sàng sử dụng. Dưới đây là thông tin truy cập:</p>
                    
                    <table class='info-table'>
                        <tr><td>Mã đơn hàng</td><td>{$order['order_code']}</td></tr>
                        <tr><td>IP Server</td><td><strong>{$vpsInfo['server_ip']}</strong></td></tr>
                        <tr><td>Username</td><td><strong>{$vpsInfo['username']}</strong></td></tr>
                        <tr><td>Password</td><td><strong>{$vpsInfo['password']}</strong></td></tr>
                        <tr><td>SSH Port</td><td>{$vpsInfo['ssh_port']}</td></tr>
                        <tr><td>Hệ điều hành</td><td>{$vpsInfo['os_template']}</td></tr>
                        <tr><td>Datacenter</td><td>{$vpsInfo['datacenter']}</td></tr>
                    </table>
                    
                    " . ($vpsInfo['control_panel_url'] ? "
                    <h3>🎛️ Control Panel</h3>
                    <table class='info-table'>
                        <tr><td>URL</td><td><a href='{$vpsInfo['control_panel_url']}'>{$vpsInfo['control_panel_url']}</a></td></tr>
                        " . ($vpsInfo['control_panel_user'] ? "<tr><td>Username</td><td>{$vpsInfo['control_panel_user']}</td></tr>" : "") . "
                        " . ($vpsInfo['control_panel_pass'] ? "<tr><td>Password</td><td>{$vpsInfo['control_panel_pass']}</td></tr>" : "") . "
                    </table>
                    " : "") . "
                    
                    <div class='alert'>
                        <h3>⚠️ Lưu ý quan trọng:</h3>
                        <ul>
                            <li>Vui lòng thay đổi mật khẩu sau lần đăng nhập đầu tiên</li>
                            <li>Backup dữ liệu quan trọng thường xuyên</li>
                            <li>Cập nhật hệ điều hành và phần mềm định kỳ</li>
                            <li>Liên hệ support nếu cần hỗ trợ kỹ thuật</li>
                        </ul>
                    </div>
                    
                    " . ($vpsInfo['additional_info'] ? "
                    <h3>📋 Thông tin bổ sung:</h3>
                    <p>" . nl2br(htmlspecialchars($vpsInfo['additional_info'])) . "</p>
                    " : "") . "
                    
                    <div class='footer'>
                        <p>Cảm ơn bạn đã sử dụng dịch vụ của chúng tôi!</p>
                        <p><strong>VPS Việt Nam Pro Team</strong></p>
                        <p>Email: support@vpsvietnam.com | Website: vpsvietnam.com</p>
                    </div>
                </div>
            </div>
        </body>
        </html>
        ";
        
        return sendSMTPEmail($to, $subject, $message, true);
    } catch (Exception $e) {
        error_log("Error in sendVPSInfoEmail: " . $e->getMessage());
        return false;
    }
}

function sendProxyInfoEmail($orderId) {
    try {
        $order = getOrderById($orderId);
        $proxyInfo = getProxyInfo($orderId);
        
        if (!$order || !$proxyInfo) return false;
        
        $to = $order['customer_email'];
        $subject = "Thông tin Proxy - Đơn hàng " . $order['order_code'];
        
        $message = "
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Thông tin Proxy</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #059669 0%, #3b82f6 100%); color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
                .content { background: #f8f9fa; padding: 20px; border-radius: 0 0 8px 8px; }
                .info-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
                .info-table td { padding: 12px; border: 1px solid #ddd; }
                .info-table td:first-child { background: #e9ecef; font-weight: bold; width: 30%; }
                .usage-guide { background: #e7f3ff; border: 1px solid #b3d9ff; padding: 15px; border-radius: 5px; margin: 20px 0; }
                .footer { text-align: center; margin-top: 30px; color: #666; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🛡️ Proxy của bạn đã sẵn sàng!</h1>
                    <p>Cảm ơn bạn đã tin tưởng VPS Việt Nam Pro</p>
                </div>
                
                <div class='content'>
                    <h2>Xin chào {$order['username']},</h2>
                    <p>Proxy của bạn đã được thiết lập thành công và sẵn sàng sử dụng. Dưới đây là thông tin truy cập:</p>
                    
                    <table class='info-table'>
                        <tr><td>Mã đơn hàng</td><td>{$order['order_code']}</td></tr>
                        <tr><td>Proxy IP</td><td><strong>{$proxyInfo['proxy_ip']}</strong></td></tr>
                        <tr><td>Port</td><td><strong>{$proxyInfo['proxy_port']}</strong></td></tr>
                        <tr><td>Username</td><td><strong>{$proxyInfo['username']}</strong></td></tr>
                        <tr><td>Password</td><td><strong>{$proxyInfo['password']}</strong></td></tr>
                        <tr><td>Protocol</td><td>" . strtoupper($proxyInfo['protocol']) . "</td></tr>
                        <tr><td>Location</td><td>{$proxyInfo['location']}</td></tr>
                    </table>
                    
                    <div class='usage-guide'>
                        <h3>📖 Hướng dẫn sử dụng:</h3>
                        <ol>
                            <li>Mở cài đặt proxy trong trình duyệt hoặc ứng dụng</li>
                            <li>Chọn loại proxy: " . strtoupper($proxyInfo['protocol']) . "</li>
                            <li>Nhập địa chỉ IP: <strong>{$proxyInfo['proxy_ip']}</strong></li>
                            <li>Nhập Port: <strong>{$proxyInfo['proxy_port']}</strong></li>
                            <li>Nhập Username và Password như trên</li>
                            <li>Lưu cài đặt và kiểm tra kết nối</li>
                        </ol>
                    </div>
                    
                    " . ($proxyInfo['additional_info'] ? "
                    <h3>📋 Thông tin bổ sung:</h3>
                    <p>" . nl2br(htmlspecialchars($proxyInfo['additional_info'])) . "</p>
                    " : "") . "
                    
                    <div class='footer'>
                        <p>Cảm ơn bạn đã sử dụng dịch vụ của chúng tôi!</p>
                        <p><strong>VPS Việt Nam Pro Team</strong></p>
                        <p>Email: support@vpsvietnam.com | Website: vpsvietnam.com</p>
                    </div>
                </div>
            </div>
        </body>
        </html>
        ";
        
        return sendSMTPEmail($to, $subject, $message, true);
    } catch (Exception $e) {
        error_log("Error in sendProxyInfoEmail: " . $e->getMessage());
        return false;
    }
}

// Test SMTP function
function testSMTPConnection() {
    try {
        $testEmail = getSystemSetting('smtp_from_email') ?: 'test@vpsvietnam.com';
        $subject = "Test SMTP Connection - " . date('Y-m-d H:i:s');
        $message = "
        <html>
        <body>
            <h2>SMTP Test Email</h2>
            <p>This is a test email to verify SMTP configuration.</p>
            <p>Sent at: " . date('Y-m-d H:i:s') . "</p>
            <p>From: VPS Việt Nam Pro</p>
        </body>
        </html>
        ";
        
        return sendSMTPEmail($testEmail, $subject, $message, true);
    } catch (Exception $e) {
        error_log("SMTP test error: " . $e->getMessage());
        return false;
    }
}

// Supplier functions
function getAllSuppliers() {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT * FROM suppliers WHERE status = 'active' ORDER BY name ASC");
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("Error in getAllSuppliers: " . $e->getMessage());
        return [];
    }
}

function getSupplierById($id) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT * FROM suppliers WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    } catch (Exception $e) {
        error_log("Error in getSupplierById: " . $e->getMessage());
        return false;
    }
}

// Inventory functions
function updateInventory($packageId, $packageType, $supplierId, $quantity, $costPrice, $sellingPrice) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("
            INSERT INTO inventory (package_id, package_type, supplier_id, available_quantity, cost_price, selling_price) 
            VALUES (?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE 
            available_quantity = available_quantity + VALUES(available_quantity),
            cost_price = VALUES(cost_price),
            selling_price = VALUES(selling_price)
        ");
        return $stmt->execute([$packageId, $packageType, $supplierId, $quantity, $costPrice, $sellingPrice]);
    } catch (Exception $e) {
        error_log("Error in updateInventory: " . $e->getMessage());
        return false;
    }
}

// Review functions
function createReview($userId, $orderId, $packageType, $rating, $title, $content, $pros = '', $cons = '') {
    global $pdo;
    try {
        $stmt = $pdo->prepare("INSERT INTO reviews (user_id, order_id, package_type, rating, title, content, pros, cons) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        return $stmt->execute([$userId, $orderId, $packageType, $rating, $title, $content, $pros, $cons]);
    } catch (Exception $e) {
        error_log("Error in createReview: " . $e->getMessage());
        return false;
    }
}

function getApprovedReviews($packageType = null, $limit = 10) {
    global $pdo;
    try {
        $sql = "SELECT r.*, u.username FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.status = 'approved'";
        $params = [];
        
        if ($packageType) {
            $sql .= " AND r.package_type = ?";
            $params[] = $packageType;
        }
        
        $sql .= " ORDER BY r.created_at DESC LIMIT ?";
        $params[] = $limit;
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("Error in getApprovedReviews: " . $e->getMessage());
        return [];
    }
}

// Affiliate functions
function createAffiliate($userId) {
    global $pdo;
    try {
        $affiliateCode = 'AFF' . strtoupper(substr(md5($userId . time()), 0, 8));
        $stmt = $pdo->prepare("INSERT INTO affiliates (user_id, affiliate_code) VALUES (?, ?)");
        return $stmt->execute([$userId, $affiliateCode]) ? $affiliateCode : false;
    } catch (Exception $e) {
        error_log("Error in createAffiliate: " . $e->getMessage());
        return false;
    }
}

function getAffiliateByCode($code) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT * FROM affiliates WHERE affiliate_code = ? AND status = 'active'");
        $stmt->execute([$code]);
        return $stmt->fetch();
    } catch (Exception $e) {
        error_log("Error in getAffiliateByCode: " . $e->getMessage());
        return false;
    }
}

function getAffiliateByUserId($userId) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT * FROM affiliates WHERE user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    } catch (Exception $e) {
        error_log("Error in getAffiliateByUserId: " . $e->getMessage());
        return false;
    }
}

function addAffiliateCommission($affiliateId, $referredUserId, $orderId, $commissionAmount, $commissionRate) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("INSERT INTO affiliate_commissions (affiliate_id, referred_user_id, order_id, commission_amount, commission_rate) VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute([$affiliateId, $referredUserId, $orderId, $commissionAmount, $commissionRate]);
    } catch (Exception $e) {
        error_log("Error in addAffiliateCommission: " . $e->getMessage());
        return false;
    }
}

// Support ticket functions
function createSupportTicket($userId, $subject, $message, $priority = 'medium', $category = 'general') {
    global $pdo;
    
    try {
        $pdo->beginTransaction();
        
        // Create ticket
        $stmt = $pdo->prepare("INSERT INTO support_tickets (user_id, subject, priority, category) VALUES (?, ?, ?, ?)");
        $stmt->execute([$userId, $subject, $priority, $category]);
        $ticketId = $pdo->lastInsertId();
        
        // Add first message
        $stmt = $pdo->prepare("INSERT INTO support_messages (ticket_id, user_id, message) VALUES (?, ?, ?)");
        $stmt->execute([$ticketId, $userId, $message]);
        
        $pdo->commit();
        return $ticketId;
    } catch (Exception $e) {
        $pdo->rollback();
        error_log("Error in createSupportTicket: " . $e->getMessage());
        return false;
    }
}

function getUserSupportTickets($userId) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT * FROM support_tickets WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("Error in getUserSupportTickets: " . $e->getMessage());
        return [];
    }
}

function getSupportTicketMessages($ticketId) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("
            SELECT sm.*, u.username 
            FROM support_messages sm 
            LEFT JOIN users u ON sm.user_id = u.id 
            WHERE sm.ticket_id = ? 
            ORDER BY sm.created_at ASC
        ");
        $stmt->execute([$ticketId]);
        return $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("Error in getSupportTicketMessages: " . $e->getMessage());
        return [];
    }
}

// Top-up functions
function createTopUpRequest($userId, $amount, $paymentMethod, $bankTransferContent) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("INSERT INTO topup_requests (user_id, amount, payment_method, bank_transfer_content) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$userId, $amount, $paymentMethod, $bankTransferContent]);
    } catch (Exception $e) {
        error_log("Error in createTopUpRequest: " . $e->getMessage());
        return false;
    }
}

function getUserTopUps($userId) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT * FROM topup_requests WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("Error in getUserTopUps: " . $e->getMessage());
        return [];
    }
}

function getUserTransactions($userId) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT * FROM transactions WHERE user_id = ? ORDER BY created_at DESC LIMIT 20");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("Error in getUserTransactions: " . $e->getMessage());
        return [];
    }
}

// Bank functions
function getActiveBanks() {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT * FROM bank_info WHERE status = 'active' ORDER BY id ASC");
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("Error in getActiveBanks: " . $e->getMessage());
        return [];
    }
}

// System settings functions
function getSystemSetting($key) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT setting_value FROM system_settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $result = $stmt->fetch();
        return $result ? $result['setting_value'] : null;
    } catch (Exception $e) {
        error_log("Error in getSystemSetting: " . $e->getMessage());
        return null;
    }
}

function setSystemSetting($key, $value) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("INSERT INTO system_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
        return $stmt->execute([$key, $value, $value]);
    } catch (Exception $e) {
        error_log("Error in setSystemSetting: " . $e->getMessage());
        return false;
    }
}

function getSocialLinks() {
    return [
        'facebook' => getSystemSetting('facebook_url'),
        'twitter' => getSystemSetting('twitter_url'),
        'youtube' => getSystemSetting('youtube_url'),
        'instagram' => getSystemSetting('instagram_url'),
        'telegram' => getSystemSetting('telegram_url')
    ];
}

// Utility functions
function formatCurrency($amount) {
    return number_format($amount, 0, ',', '.') . ' VND';
}

function generateTransferContent($userId) {
    return 'NAPVPS' . $userId . substr(time(), -4);
}

function sanitizeInput($input) {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

function redirectTo($url) {
    header("Location: $url");
    exit();
}

function showAlert($message, $type = 'info') {
    $_SESSION['alert'] = ['message' => $message, 'type' => $type];
}

function getAlert() {
    if (isset($_SESSION['alert'])) {
        $alert = $_SESSION['alert'];
        unset($_SESSION['alert']);
        return $alert;
    }
    return null;
}

function getStatusIcon($status) {
    switch ($status) {
        case 'completed':
        case 'approved':
            return '<i class="fas fa-check-circle text-green-500"></i>';
        case 'processing':
            return '<i class="fas fa-clock text-yellow-500"></i>';
        case 'pending':
            return '<i class="fas fa-clock text-blue-500"></i>';
        case 'cancelled':
        case 'rejected':
            return '<i class="fas fa-times-circle text-red-500"></i>';
        case 'expired':
            return '<i class="fas fa-exclamation-triangle text-orange-500"></i>';
        default:
            return '<i class="fas fa-clock text-gray-500"></i>';
    }
}

function getStatusColor($status) {
    switch ($status) {
        case 'completed':
        case 'approved':
            return 'bg-green-100 text-green-800';
        case 'processing':
            return 'bg-yellow-100 text-yellow-800';
        case 'pending':
            return 'bg-blue-100 text-blue-800';
        case 'cancelled':
        case 'rejected':
            return 'bg-red-100 text-red-800';
        case 'expired':
            return 'bg-orange-100 text-orange-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
}

function getStatusText($status) {
    switch ($status) {
        case 'completed':
            return 'Hoàn thành';
        case 'approved':
            return 'Đã duyệt';
        case 'processing':
            return 'Đang xử lý';
        case 'pending':
            return 'Chờ xử lý';
        case 'cancelled':
            return 'Đã hủy';
        case 'rejected':
            return 'Từ chối';
        case 'expired':
            return 'Hết hạn';
        default:
            return 'Không xác định';
    }
}

function getPackageTypeText($type) {
    switch ($type) {
        case 'trial':
            return 'VPS Trial';
        case 'official':
            return 'VPS Chính hãng';
        default:
            return 'VPS';
    }
}

function getPackageTypeColor($type) {
    switch ($type) {
        case 'trial':
            return 'bg-orange-100 text-orange-800';
        case 'official':
            return 'bg-blue-100 text-blue-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
}

// Admin functions
function getAllUsers() {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT * FROM users ORDER BY created_at DESC");
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("Error in getAllUsers: " . $e->getMessage());
        return [];
    }
}

function getAllOrders() {
    global $pdo;
    try {
        $stmt = $pdo->prepare("
            SELECT o.*, 
                   CASE 
                       WHEN o.package_type = 'vps' THEN vp.name
                       WHEN o.package_type = 'proxy' THEN pp.name
                   END as package_name,
                   CASE 
                       WHEN o.package_type = 'vps' THEN vp.provider
                       WHEN o.package_type = 'proxy' THEN pp.provider
                   END as provider,
                   u.username, u.email as user_email
            FROM orders o 
            LEFT JOIN vps_packages vp ON o.package_id = vp.id AND o.package_type = 'vps'
            LEFT JOIN proxy_packages pp ON o.package_id = pp.id AND o.package_type = 'proxy'
            JOIN users u ON o.user_id = u.id
            ORDER BY o.created_at DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("Error in getAllOrders: " . $e->getMessage());
        return [];
    }
}

function getAllTopUps() {
    global $pdo;
    try {
        $stmt = $pdo->prepare("
            SELECT t.*, u.username, u.email as user_email
            FROM topup_requests t 
            JOIN users u ON t.user_id = u.id
            ORDER BY t.created_at DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("Error in getAllTopUps: " . $e->getMessage());
        return [];
    }
}

function updateOrderStatus($orderId, $status) {
    global $pdo;
    try {
        $completedAt = $status === 'completed' ? date('Y-m-d H:i:s') : null;
        $stmt = $pdo->prepare("UPDATE orders SET status = ?, completed_at = ? WHERE id = ?");
        return $stmt->execute([$status, $completedAt, $orderId]);
    } catch (Exception $e) {
        error_log("Error in updateOrderStatus: " . $e->getMessage());
        return false;
    }
}

function updateUser($userId, $username, $email, $balance, $status, $role) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, balance = ?, status = ?, role = ? WHERE id = ?");
        return $stmt->execute([$username, $email, $balance, $status, $role, $userId]);
    } catch (Exception $e) {
        error_log("Error in updateUser: " . $e->getMessage());
        return false;
    }
}

function addUserBalance($userId, $amount, $note) {
    global $pdo;
    try {
        $pdo->beginTransaction();
        
        // Update user balance
        $stmt = $pdo->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
        $stmt->execute([$amount, $userId]);
        
        // Create transaction record
        $transactionId = 'ADM' . time() . mt_rand(100, 999);
        $stmt = $pdo->prepare("INSERT INTO transactions (user_id, amount, type, status, payment_method, transaction_id, description) VALUES (?, ?, 'deposit', 'completed', 'Admin thêm', ?, ?)");
        $stmt->execute([$userId, $amount, $transactionId, $note ?: 'Admin thêm số dư']);
        
        $pdo->commit();
        return true;
    } catch (Exception $e) {
        $pdo->rollback();
        error_log("Error in addUserBalance: " . $e->getMessage());
        return false;
    }
}

function approveTopUp($topupId, $adminNote = '') {
    global $pdo;
    
    try {
        $pdo->beginTransaction();
        
        // Get topup request
        $stmt = $pdo->prepare("SELECT * FROM topup_requests WHERE id = ?");
        $stmt->execute([$topupId]);
        $topup = $stmt->fetch();
        
        if (!$topup || $topup['status'] !== 'pending') {
            throw new Exception('Invalid topup request');
        }
        
        // Update user balance
        $stmt = $pdo->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
        $stmt->execute([$topup['amount'], $topup['user_id']]);
        
        // Update topup status
        $stmt = $pdo->prepare("UPDATE topup_requests SET status = 'approved', admin_note = ?, processed_at = NOW() WHERE id = ?");
        $stmt->execute([$adminNote, $topupId]);
        
        // Create transaction record
        $transactionId = 'TXN' . time() . mt_rand(100, 999);
        $stmt = $pdo->prepare("INSERT INTO transactions (user_id, amount, type, status, payment_method, transaction_id, description) VALUES (?, ?, 'deposit', 'completed', ?, ?, 'Nạp tiền được duyệt')");
        $stmt->execute([$topup['user_id'], $topup['amount'], $topup['payment_method'], $transactionId]);
        
        $pdo->commit();
        return true;
    } catch (Exception $e) {
        $pdo->rollback();
        error_log("Error in approveTopUp: " . $e->getMessage());
        return false;
    }
}

function rejectTopUp($topupId, $adminNote = '') {
    global $pdo;
    try {
        $stmt = $pdo->prepare("UPDATE topup_requests SET status = 'rejected', admin_note = ?, processed_at = NOW() WHERE id = ?");
        return $stmt->execute([$adminNote, $topupId]);
    } catch (Exception $e) {
        error_log("Error in rejectTopUp: " . $e->getMessage());
        return false;
    }
}

function getDashboardStats() {
    global $pdo;
    
    $stats = [];
    
    try {
        // Total users
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role = 'user'");
        $stmt->execute();
        $stats['totalUsers'] = $stmt->fetchColumn();
        
        // Total orders
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM orders");
        $stmt->execute();
        $stats['totalOrders'] = $stmt->fetchColumn();
        
        // Total revenue
        $stmt = $pdo->prepare("SELECT SUM(amount) FROM orders WHERE status = 'completed'");
        $stmt->execute();
        $stats['totalRevenue'] = $stmt->fetchColumn() ?: 0;
        
        // Total profit
        $stmt = $pdo->prepare("SELECT SUM(profit) FROM orders WHERE status = 'completed'");
        $stmt->execute();
        $stats['totalProfit'] = $stmt->fetchColumn() ?: 0;
        
        // Pending orders
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE status = 'pending'");
        $stmt->execute();
        $stats['pendingOrders'] = $stmt->fetchColumn();
        
        // Pending topups
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM topup_requests WHERE status = 'pending'");
        $stmt->execute();
        $stats['pendingTopUps'] = $stmt->fetchColumn();
        
        // Active services
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE status = 'completed'");
        $stmt->execute();
        $stats['activeServices'] = $stmt->fetchColumn();
        
        // Trial orders
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM orders o JOIN vps_packages vp ON o.package_id = vp.id WHERE o.package_type = 'vps' AND vp.type = 'trial'");
        $stmt->execute();
        $stats['trialOrders'] = $stmt->fetchColumn();
        
        // Official orders
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM orders o JOIN vps_packages vp ON o.package_id = vp.id WHERE o.package_type = 'vps' AND vp.type = 'official'");
        $stmt->execute();
        $stats['officialOrders'] = $stmt->fetchColumn();
        
    } catch (Exception $e) {
        error_log("Error in getDashboardStats: " . $e->getMessage());
        // Return default values on error
        $stats = [
            'totalUsers' => 0,
            'totalOrders' => 0,
            'totalRevenue' => 0,
            'totalProfit' => 0,
            'pendingOrders' => 0,
            'pendingTopUps' => 0,
            'activeServices' => 0,
            'trialOrders' => 0,
            'officialOrders' => 0
        ];
    }
    
    return $stats;
}
?>