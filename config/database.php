<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'vps_marketplace');
define('DB_USER', 'root');
define('DB_PASS', '');

// Set proper encoding
header('Content-Type: text/html; charset=UTF-8');
ini_set('default_charset', 'UTF-8');
mb_internal_encoding('UTF-8');

// Error reporting for debugging (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS, [
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
    ]);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    
    // Set connection charset
    $pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
} catch(PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    die("Kết nối database thất bại. Vui lòng thử lại sau.");
}

// Create database tables if they don't exist
function createTables() {
    global $pdo;
    
    try {
        // Users table
        $pdo->exec("CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            balance DECIMAL(15,2) DEFAULT 0.00,
            role ENUM('user', 'admin') DEFAULT 'user',
            status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
            phone VARCHAR(20),
            company VARCHAR(200),
            address TEXT,
            verification_status ENUM('unverified', 'pending', 'verified') DEFAULT 'unverified',
            verification_documents TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        
        // VPS Packages table - Phân biệt VPS Trial và VPS Chính hãng
        $pdo->exec("CREATE TABLE IF NOT EXISTS vps_packages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            type ENUM('trial', 'official') DEFAULT 'official',
            provider VARCHAR(100) NOT NULL DEFAULT 'Unknown',
            cpu VARCHAR(50) NOT NULL,
            ram VARCHAR(50) NOT NULL,
            storage VARCHAR(50) NOT NULL,
            bandwidth VARCHAR(50) NOT NULL,
            price DECIMAL(15,2) NOT NULL,
            original_price DECIMAL(15,2) DEFAULT 0,
            profit_margin DECIMAL(5,2) DEFAULT 0,
            trial_duration INT DEFAULT 0,
            description TEXT,
            features JSON,
            os_templates JSON,
            datacenter_location VARCHAR(100),
            provider_info JSON,
            stock_quantity INT DEFAULT 0,
            auto_delivery BOOLEAN DEFAULT FALSE,
            status ENUM('active', 'inactive', 'out_of_stock') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        
        // Proxy Packages table
        $pdo->exec("CREATE TABLE IF NOT EXISTS proxy_packages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            type ENUM('socks5', 'http', 'https') DEFAULT 'socks5',
            location VARCHAR(100) NOT NULL,
            speed VARCHAR(50) NOT NULL,
            concurrent_connections INT NOT NULL,
            price DECIMAL(15,2) NOT NULL,
            original_price DECIMAL(15,2) DEFAULT 0,
            profit_margin DECIMAL(5,2) DEFAULT 0,
            provider VARCHAR(100) NOT NULL DEFAULT 'Unknown',
            description TEXT,
            features JSON,
            stock_quantity INT DEFAULT 0,
            auto_delivery BOOLEAN DEFAULT FALSE,
            status ENUM('active', 'inactive', 'out_of_stock') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        
        // Orders table
        $pdo->exec("CREATE TABLE IF NOT EXISTS orders (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            package_id INT NOT NULL,
            package_type ENUM('vps', 'proxy') NOT NULL,
            customer_email VARCHAR(100) NOT NULL,
            amount DECIMAL(15,2) NOT NULL,
            cost_price DECIMAL(15,2) DEFAULT 0,
            profit DECIMAL(15,2) DEFAULT 0,
            status ENUM('pending', 'processing', 'completed', 'cancelled', 'expired', 'refunded') DEFAULT 'pending',
            order_code VARCHAR(50) UNIQUE NOT NULL,
            provider_order_id VARCHAR(100),
            delivery_status ENUM('pending', 'delivered', 'failed') DEFAULT 'pending',
            notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            completed_at TIMESTAMP NULL,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        
        // VPS Info table
        $pdo->exec("CREATE TABLE IF NOT EXISTS vps_info (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT NOT NULL,
            server_ip VARCHAR(45) NOT NULL,
            username VARCHAR(50) NOT NULL,
            password VARCHAR(255) NOT NULL,
            ssh_port INT DEFAULT 22,
            control_panel_url VARCHAR(255),
            control_panel_user VARCHAR(50),
            control_panel_pass VARCHAR(255),
            os_template VARCHAR(100),
            datacenter VARCHAR(100),
            provider_panel_url VARCHAR(255),
            provider_credentials TEXT,
            backup_enabled BOOLEAN DEFAULT FALSE,
            monitoring_enabled BOOLEAN DEFAULT TRUE,
            firewall_enabled BOOLEAN DEFAULT TRUE,
            expires_at TIMESTAMP NULL,
            renewal_price DECIMAL(15,2) DEFAULT 0,
            additional_info TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        
        // Proxy Info table
        $pdo->exec("CREATE TABLE IF NOT EXISTS proxy_info (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT NOT NULL,
            proxy_ip VARCHAR(45) NOT NULL,
            proxy_port INT NOT NULL,
            username VARCHAR(50) NOT NULL,
            password VARCHAR(255) NOT NULL,
            protocol ENUM('socks5', 'http', 'https') DEFAULT 'socks5',
            location VARCHAR(100),
            expires_at TIMESTAMP NULL,
            renewal_price DECIMAL(15,2) DEFAULT 0,
            additional_info TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        
        // Suppliers table
        $pdo->exec("CREATE TABLE IF NOT EXISTS suppliers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            type ENUM('vps', 'proxy', 'both') NOT NULL,
            contact_email VARCHAR(100),
            contact_phone VARCHAR(20),
            website VARCHAR(255),
            api_endpoint VARCHAR(255),
            api_key VARCHAR(255),
            api_secret VARCHAR(255),
            commission_rate DECIMAL(5,2) DEFAULT 0,
            payment_terms TEXT,
            notes TEXT,
            status ENUM('active', 'inactive') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        
        // Inventory table
        $pdo->exec("CREATE TABLE IF NOT EXISTS inventory (
            id INT AUTO_INCREMENT PRIMARY KEY,
            package_id INT NOT NULL,
            package_type ENUM('vps', 'proxy') NOT NULL,
            supplier_id INT NOT NULL,
            available_quantity INT DEFAULT 0,
            reserved_quantity INT DEFAULT 0,
            sold_quantity INT DEFAULT 0,
            cost_price DECIMAL(15,2) NOT NULL,
            selling_price DECIMAL(15,2) NOT NULL,
            last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        
        // Customer reviews table
        $pdo->exec("CREATE TABLE IF NOT EXISTS reviews (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            order_id INT NOT NULL,
            package_type ENUM('vps', 'proxy') NOT NULL,
            rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
            title VARCHAR(200),
            content TEXT,
            pros TEXT,
            cons TEXT,
            status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
            admin_reply TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        
        // Affiliate system table
        $pdo->exec("CREATE TABLE IF NOT EXISTS affiliates (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            affiliate_code VARCHAR(50) UNIQUE NOT NULL,
            commission_rate DECIMAL(5,2) DEFAULT 5.00,
            total_referrals INT DEFAULT 0,
            total_earnings DECIMAL(15,2) DEFAULT 0.00,
            pending_earnings DECIMAL(15,2) DEFAULT 0.00,
            paid_earnings DECIMAL(15,2) DEFAULT 0.00,
            status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        
        // Affiliate commissions table
        $pdo->exec("CREATE TABLE IF NOT EXISTS affiliate_commissions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            affiliate_id INT NOT NULL,
            referred_user_id INT NOT NULL,
            order_id INT NOT NULL,
            commission_amount DECIMAL(15,2) NOT NULL,
            commission_rate DECIMAL(5,2) NOT NULL,
            status ENUM('pending', 'approved', 'paid') DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            paid_at TIMESTAMP NULL,
            FOREIGN KEY (affiliate_id) REFERENCES affiliates(id) ON DELETE CASCADE,
            FOREIGN KEY (referred_user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        
        // Top-up requests table
        $pdo->exec("CREATE TABLE IF NOT EXISTS topup_requests (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            amount DECIMAL(15,2) NOT NULL,
            payment_method VARCHAR(100) NOT NULL,
            payment_proof VARCHAR(255),
            bank_transfer_content VARCHAR(100),
            status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
            admin_note TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            processed_at TIMESTAMP NULL,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        
        // Bank info table
        $pdo->exec("CREATE TABLE IF NOT EXISTS bank_info (
            id INT AUTO_INCREMENT PRIMARY KEY,
            bank_name VARCHAR(100) NOT NULL,
            account_number VARCHAR(50) NOT NULL,
            account_name VARCHAR(200) NOT NULL,
            branch VARCHAR(200),
            bank_id VARCHAR(50) DEFAULT NULL,
            status ENUM('active', 'inactive') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        
        // System settings table
        $pdo->exec("CREATE TABLE IF NOT EXISTS system_settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            setting_key VARCHAR(100) UNIQUE NOT NULL,
            setting_value TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        
        // Transactions table
        $pdo->exec("CREATE TABLE IF NOT EXISTS transactions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            amount DECIMAL(15,2) NOT NULL,
            type ENUM('deposit', 'withdraw', 'purchase', 'refund', 'commission') NOT NULL,
            status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
            payment_method VARCHAR(100),
            transaction_id VARCHAR(100) UNIQUE NOT NULL,
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        
        // Support tickets table
        $pdo->exec("CREATE TABLE IF NOT EXISTS support_tickets (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            subject VARCHAR(255) NOT NULL,
            priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
            status ENUM('open', 'in_progress', 'resolved', 'closed') DEFAULT 'open',
            category ENUM('technical', 'billing', 'general', 'abuse') DEFAULT 'general',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        
        // Support ticket messages table
        $pdo->exec("CREATE TABLE IF NOT EXISTS support_messages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            ticket_id INT NOT NULL,
            user_id INT,
            is_admin BOOLEAN DEFAULT FALSE,
            message TEXT NOT NULL,
            attachments JSON,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (ticket_id) REFERENCES support_tickets(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        
        // Server monitoring table
        $pdo->exec("CREATE TABLE IF NOT EXISTS server_monitoring (
            id INT AUTO_INCREMENT PRIMARY KEY,
            vps_info_id INT NOT NULL,
            cpu_usage DECIMAL(5,2),
            memory_usage DECIMAL(5,2),
            disk_usage DECIMAL(5,2),
            network_in BIGINT,
            network_out BIGINT,
            uptime INT,
            status ENUM('online', 'offline', 'maintenance') DEFAULT 'online',
            checked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (vps_info_id) REFERENCES vps_info(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        
    } catch (Exception $e) {
        error_log("Database creation error: " . $e->getMessage());
    }
}

// Insert default data if tables are empty
function insertDefaultData() {
    global $pdo;
    
    try {
        // Check if admin user exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role = 'admin'");
        $stmt->execute();
        if ($stmt->fetchColumn() == 0) {
            // Create admin user
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->execute(['thynerss', 'thynerss@admin.com', password_hash('Thi12121704t@', PASSWORD_DEFAULT), 'admin']);
        }
        
        // Insert suppliers
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM suppliers");
        $stmt->execute();
        if ($stmt->fetchColumn() == 0) {
            $suppliers = [
                ['DigitalOcean Reseller', 'vps', 'reseller@digitalocean.com', '+1-555-0001', 'https://digitalocean.com', '', '', '', 15.00, 'Net 30', 'VPS Trial và Official'],
                ['Vultr Partner', 'vps', 'partner@vultr.com', '+1-555-0002', 'https://vultr.com', '', '', '', 12.00, 'Net 15', 'VPS chất lượng cao'],
                ['Linode Distributor', 'vps', 'dist@linode.com', '+1-555-0003', 'https://linode.com', '', '', '', 18.00, 'Net 30', 'VPS enterprise'],
                ['ProxyMesh', 'proxy', 'sales@proxymesh.com', '+1-555-0004', 'https://proxymesh.com', '', '', '', 25.00, 'Net 7', 'Proxy SOCKS5 chuyên nghiệp'],
                ['SmartProxy', 'proxy', 'b2b@smartproxy.com', '+1-555-0005', 'https://smartproxy.com', '', '', '', 20.00, 'Net 15', 'Proxy residential'],
                ['BrightData', 'proxy', 'enterprise@brightdata.com', '+1-555-0006', 'https://brightdata.com', '', '', '', 30.00, 'Net 30', 'Proxy enterprise']
            ];
            
            $stmt = $pdo->prepare("INSERT INTO suppliers (name, type, contact_email, contact_phone, website, api_endpoint, api_key, api_secret, commission_rate, payment_terms, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            foreach ($suppliers as $supplier) {
                $stmt->execute($supplier);
            }
        }
        
        // Check if packages exist
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM vps_packages");
        $stmt->execute();
        if ($stmt->fetchColumn() == 0) {
            // Insert VPS packages
            $packages = [
                // VPS Trial từ các nhà phân phối
                ['VPS Trial DO Basic', 'trial', 'DigitalOcean', '1 vCPU', '1 GB', '25 GB SSD', '1 TB', 89000, 75000, 18.67, 7, 'VPS Trial từ DigitalOcean - 7 ngày', '["Root access", "1 IP", "Basic support", "SSD Storage"]', '["Ubuntu 20.04", "CentOS 8", "Debian 11"]', 'Singapore', '{"panel_url": "https://cloud.digitalocean.com", "api_docs": "https://docs.digitalocean.com/reference/api/"}', 50],
                ['VPS Trial Vultr Starter', 'trial', 'Vultr', '1 vCPU', '1 GB', '25 GB SSD', '1 TB', 95000, 80000, 18.75, 7, 'VPS Trial từ Vultr - 7 ngày', '["Root access", "1 IP", "Basic support", "NVMe SSD"]', '["Ubuntu 20.04", "CentOS 8", "Debian 11", "Windows Server"]', 'Tokyo', '{"panel_url": "https://my.vultr.com", "api_docs": "https://www.vultr.com/api/"}', 30],
                ['VPS Trial Linode Nano', 'trial', 'Linode', '1 vCPU', '1 GB', '25 GB SSD', '1 TB', 110000, 90000, 22.22, 7, 'VPS Trial từ Linode - 7 ngày', '["Root access", "1 IP", "24/7 support", "SSD Storage"]', '["Ubuntu 20.04", "CentOS 8", "Debian 11", "Arch Linux"]', 'Singapore', '{"panel_url": "https://cloud.linode.com", "api_docs": "https://www.linode.com/api/docs/v4"}', 25],
                
                // VPS Chính hãng
                ['VPS Official DO Standard', 'official', 'DigitalOcean', '2 vCPU', '2 GB', '50 GB SSD', '3 TB', 180000, 150000, 20.00, 0, 'VPS Chính hãng DigitalOcean', '["Root access", "2 IP", "24/7 support", "Backup", "Monitoring"]', '["Ubuntu 20.04", "CentOS 8", "Debian 11", "Windows Server 2019"]', 'Singapore', '{"panel_url": "https://cloud.digitalocean.com", "features": ["Load Balancer", "Firewall", "VPC"]}', 100],
                ['VPS Official Vultr Performance', 'official', 'Vultr', '2 vCPU', '4 GB', '80 GB NVMe', '3 TB', 320000, 260000, 23.08, 0, 'VPS Chính hãng Vultr hiệu suất cao', '["Root access", "2 IP", "Priority support", "NVMe SSD", "DDoS Protection"]', '["Ubuntu 20.04", "CentOS 8", "Debian 11", "Windows Server 2019"]', 'Tokyo', '{"panel_url": "https://my.vultr.com", "features": ["Block Storage", "Load Balancer", "Private Network"]}', 80],
                ['VPS Official Linode Dedicated', 'official', 'Linode', '4 vCPU', '8 GB', '160 GB SSD', '5 TB', 650000, 520000, 25.00, 0, 'VPS Chính hãng Linode Dedicated CPU', '["Root access", "4 IP", "Premium support", "Dedicated CPU", "Enterprise SLA"]', '["Ubuntu 20.04", "CentOS 8", "Debian 11", "Windows Server 2019"]', 'Singapore', '{"panel_url": "https://cloud.linode.com", "features": ["NodeBalancer", "Block Storage", "Private VLAN"]}', 60],
                ['VPS Official Enterprise', 'official', 'Multiple Providers', '8 vCPU', '16 GB', '320 GB NVMe', '10 TB', 1200000, 950000, 26.32, 0, 'VPS Enterprise từ nhiều nhà cung cấp', '["Root access", "8 IP", "Dedicated support", "Enterprise SLA", "Custom OS"]', '["Ubuntu 20.04", "CentOS 8", "Debian 11", "Windows Server 2019", "Custom OS"]', 'Multiple', '{"panel_url": "Custom", "features": ["Dedicated Support", "Custom Configuration", "Enterprise SLA"]}', 40]
            ];
            
            $stmt = $pdo->prepare("INSERT INTO vps_packages (name, type, provider, cpu, ram, storage, bandwidth, price, original_price, profit_margin, trial_duration, description, features, os_templates, datacenter_location, provider_info, stock_quantity) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            foreach ($packages as $package) {
                $stmt->execute($package);
            }
        }
        
        // Check if proxy packages exist
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM proxy_packages");
        $stmt->execute();
        if ($stmt->fetchColumn() == 0) {
            // Insert proxy packages
            $proxyPackages = [
                ['Proxy SOCKS5 Việt Nam Premium', 'socks5', 'Việt Nam', '100 Mbps', 10, 120000, 95000, 26.32, 'ProxyMesh VN', 'Proxy SOCKS5 chất lượng cao tại Việt Nam', '["Dedicated IP", "24/7 uptime", "No logs", "High anonymity"]', 100],
                ['Proxy SOCKS5 Singapore Business', 'socks5', 'Singapore', '500 Mbps', 25, 280000, 220000, 27.27, 'SmartProxy', 'Proxy SOCKS5 doanh nghiệp tại Singapore', '["Dedicated IP", "99.9% uptime", "No logs", "Business support"]', 80],
                ['Proxy SOCKS5 USA Enterprise', 'socks5', 'United States', '1 Gbps', 50, 450000, 350000, 28.57, 'BrightData', 'Proxy SOCKS5 enterprise tại Mỹ', '["Dedicated IP", "Enterprise SLA", "No logs", "Multiple locations"]', 60],
                ['Proxy SOCKS5 Europe Pro', 'socks5', 'Germany', '500 Mbps', 30, 380000, 300000, 26.67, 'ProxyMesh EU', 'Proxy SOCKS5 chuyên nghiệp tại châu Âu', '["Dedicated IP", "GDPR compliant", "No logs", "EU servers"]', 70],
                ['Proxy SOCKS5 Japan Gaming', 'socks5', 'Japan', '1 Gbps', 20, 520000, 400000, 30.00, 'SmartProxy JP', 'Proxy SOCKS5 tối ưu cho gaming tại Nhật', '["Dedicated IP", "Low latency", "Gaming optimized", "24/7 support"]', 50],
                ['Proxy SOCKS5 Global Network', 'socks5', 'Multiple', '2 Gbps', 100, 850000, 650000, 30.77, 'BrightData Global', 'Mạng proxy SOCKS5 toàn cầu', '["Multiple IPs", "Global network", "Enterprise grade", "API access"]', 30]
            ];
            
            $stmt = $pdo->prepare("INSERT INTO proxy_packages (name, type, location, speed, concurrent_connections, price, original_price, profit_margin, provider, description, features, stock_quantity) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            foreach ($proxyPackages as $package) {
                $stmt->execute($package);
            }
        }
        
        // Check if bank info exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM bank_info");
        $stmt->execute();
        if ($stmt->fetchColumn() == 0) {
            // Insert default bank info with VietQR bank IDs - FIX ENCODING
            $banks = [
                ['Vietcombank', '0123456789', 'CONG TY VPS VIET NAM', 'Chi nhánh Hà Nội', 'vietcombank'],
                ['Techcombank', '9876543210', 'CONG TY VPS VIET NAM', 'Chi nhánh TP.HCM', 'techcombank'],
                ['BIDV', '1122334455', 'CONG TY VPS VIET NAM', 'Chi nhánh Đà Nẵng', 'bidv'],
                ['VietinBank', '5566778899', 'CONG TY VPS VIET NAM', 'Chi nhánh Cần Thơ', 'vietinbank']
            ];
            
            $stmt = $pdo->prepare("INSERT INTO bank_info (bank_name, account_number, account_name, branch, bank_id) VALUES (?, ?, ?, ?, ?)");
            foreach ($banks as $bank) {
                $stmt->execute($bank);
            }
        }
        
        // Insert default system settings
        $settings = [
            ['site_name', 'VPS Việt Nam Pro'],
            ['site_description', 'Dịch vụ VPS chính hãng và VPS trial chất lượng cao'],
            ['contact_email', 'support@vpsvietnam.com'],
            ['min_topup_amount', '50000'],
            ['max_topup_amount', '50000000'],
            ['facebook_url', 'https://facebook.com/vpsvietnam'],
            ['twitter_url', 'https://twitter.com/vpsvietnam'],
            ['youtube_url', 'https://youtube.com/vpsvietnam'],
            ['instagram_url', 'https://instagram.com/vpsvietnam'],
            ['telegram_url', 'https://t.me/vpsvietnam'],
            ['affiliate_commission_rate', '5.00'],
            ['auto_delivery_enabled', '1'],
            ['review_moderation', '1'],
            ['maintenance_mode', '0'],
            ['vietqr_enabled', '1'],
            ['smtp_enabled', '0'],
            ['smtp_host', ''],
            ['smtp_port', '587'],
            ['smtp_username', ''],
            ['smtp_password', ''],
            ['smtp_encryption', 'tls'],
            ['smtp_from_email', 'noreply@vpsvietnam.com'],
            ['smtp_from_name', 'VPS Việt Nam Pro']
        ];
        
        foreach ($settings as $setting) {
            $stmt = $pdo->prepare("INSERT IGNORE INTO system_settings (setting_key, setting_value) VALUES (?, ?)");
            $stmt->execute($setting);
        }
        
    } catch (Exception $e) {
        error_log("Default data insertion error: " . $e->getMessage());
    }
}

// Initialize database
createTables();
insertDefaultData();
?>