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

// Email functions
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
            <title>Thông tin VPS</title>
        </head>
        <body>
            <h2>Thông tin VPS của bạn</h2>
            <p>Xin chào {$order['username']},</p>
            <p>VPS của bạn đã được thiết lập thành công. Dưới đây là thông tin truy cập:</p>
            
            <table border='1' style='border-collapse: collapse; width: 100%;'>
                <tr><td><strong>Mã đơn hàng:</strong></td><td>{$order['order_code']}</td></tr>
                <tr><td><strong>IP Server:</strong></td><td>{$vpsInfo['server_ip']}</td></tr>
                <tr><td><strong>Username:</strong></td><td>{$vpsInfo['username']}</td></tr>
                <tr><td><strong>Password:</strong></td><td>{$vpsInfo['password']}</td></tr>
                <tr><td><strong>SSH Port:</strong></td><td>{$vpsInfo['ssh_port']}</td></tr>
                <tr><td><strong>OS:</strong></td><td>{$vpsInfo['os_template']}</td></tr>
                <tr><td><strong>Datacenter:</strong></td><td>{$vpsInfo['datacenter']}</td></tr>
            </table>
            
            " . ($vpsInfo['control_panel_url'] ? "<p><strong>Control Panel:</strong> <a href='{$vpsInfo['control_panel_url']}'>{$vpsInfo['control_panel_url']}</a></p>" : "") . "
            " . ($vpsInfo['control_panel_user'] ? "<p><strong>Panel User:</strong> {$vpsInfo['control_panel_user']}</p>" : "") . "
            " . ($vpsInfo['control_panel_pass'] ? "<p><strong>Panel Password:</strong> {$vpsInfo['control_panel_pass']}</p>" : "") . "
            
            <p><strong>Lưu ý quan trọng:</strong></p>
            <ul>
                <li>Vui lòng thay đổi mật khẩu sau lần đăng nhập đầu tiên</li>
                <li>Backup dữ liệu quan trọng thường xuyên</li>
                <li>Liên hệ support nếu cần hỗ trợ</li>
            </ul>
            
            " . ($vpsInfo['additional_info'] ? "<p><strong>Thông tin bổ sung:</strong><br>{$vpsInfo['additional_info']}</p>" : "") . "
            
            <p>Cảm ơn bạn đã sử dụng dịch vụ của chúng tôi!</p>
            <p>VPS Việt Nam Pro Team</p>
        </body>
        </html>
        ";
        
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: " . getSystemSetting('contact_email') . "\r\n";
        
        return mail($to, $subject, $message, $headers);
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
            <title>Thông tin Proxy</title>
        </head>
        <body>
            <h2>Thông tin Proxy của bạn</h2>
            <p>Xin chào {$order['username']},</p>
            <p>Proxy của bạn đã được thiết lập thành công. Dưới đây là thông tin truy cập:</p>
            
            <table border='1' style='border-collapse: collapse; width: 100%;'>
                <tr><td><strong>Mã đơn hàng:</strong></td><td>{$order['order_code']}</td></tr>
                <tr><td><strong>Proxy IP:</strong></td><td>{$proxyInfo['proxy_ip']}</td></tr>
                <tr><td><strong>Port:</strong></td><td>{$proxyInfo['proxy_port']}</td></tr>
                <tr><td><strong>Username:</strong></td><td>{$proxyInfo['username']}</td></tr>
                <tr><td><strong>Password:</strong></td><td>{$proxyInfo['password']}</td></tr>
                <tr><td><strong>Protocol:</strong></td><td>" . strtoupper($proxyInfo['protocol']) . "</td></tr>
                <tr><td><strong>Location:</strong></td><td>{$proxyInfo['location']}</td></tr>
            </table>
            
            <p><strong>Cách sử dụng:</strong></p>
            <ul>
                <li>Cấu hình proxy trong trình duyệt hoặc ứng dụng</li>
                <li>Sử dụng thông tin trên để kết nối</li>
                <li>Kiểm tra IP sau khi kết nối để đảm bảo proxy hoạt động</li>
            </ul>
            
            " . ($proxyInfo['additional_info'] ? "<p><strong>Thông tin bổ sung:</strong><br>{$proxyInfo['additional_info']}</p>" : "") . "
            
            <p>Cảm ơn bạn đã sử dụng dịch vụ của chúng tôi!</p>
            <p>VPS Việt Nam Pro Team</p>
        </body>
        </html>
        ";
        
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: " . getSystemSetting('contact_email') . "\r\n";
        
        return mail($to, $subject, $message, $headers);
    } catch (Exception $e) {
        error_log("Error in sendProxyInfoEmail: " . $e->getMessage());
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
    return htmlspecialchars(strip_tags(trim($input)));
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