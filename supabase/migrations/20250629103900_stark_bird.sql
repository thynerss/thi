-- VPS Marketplace Database Complete Fix
-- Run this single SQL file to fix all database issues

-- Create database if not exists
CREATE DATABASE IF NOT EXISTS vps_marketplace CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE vps_marketplace;

-- Disable foreign key checks temporarily
SET FOREIGN_KEY_CHECKS = 0;

-- Users table
CREATE TABLE IF NOT EXISTS users (
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
);

-- Add missing columns to users if they don't exist
SET @sql = CONCAT('ALTER TABLE users ADD COLUMN IF NOT EXISTS phone VARCHAR(20)');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = CONCAT('ALTER TABLE users ADD COLUMN IF NOT EXISTS company VARCHAR(200)');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = CONCAT('ALTER TABLE users ADD COLUMN IF NOT EXISTS address TEXT');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = CONCAT('ALTER TABLE users ADD COLUMN IF NOT EXISTS verification_status ENUM(\'unverified\', \'pending\', \'verified\') DEFAULT \'unverified\'');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = CONCAT('ALTER TABLE users ADD COLUMN IF NOT EXISTS verification_documents TEXT');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- VPS Packages table
CREATE TABLE IF NOT EXISTS vps_packages (
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
);

-- Add missing columns to vps_packages
SET @sql = CONCAT('ALTER TABLE vps_packages ADD COLUMN IF NOT EXISTS type ENUM(\'trial\', \'official\') DEFAULT \'official\'');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = CONCAT('ALTER TABLE vps_packages ADD COLUMN IF NOT EXISTS provider VARCHAR(100) NOT NULL DEFAULT \'Unknown\'');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = CONCAT('ALTER TABLE vps_packages ADD COLUMN IF NOT EXISTS original_price DECIMAL(15,2) DEFAULT 0');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = CONCAT('ALTER TABLE vps_packages ADD COLUMN IF NOT EXISTS profit_margin DECIMAL(5,2) DEFAULT 0');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = CONCAT('ALTER TABLE vps_packages ADD COLUMN IF NOT EXISTS trial_duration INT DEFAULT 0');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = CONCAT('ALTER TABLE vps_packages ADD COLUMN IF NOT EXISTS features JSON');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = CONCAT('ALTER TABLE vps_packages ADD COLUMN IF NOT EXISTS os_templates JSON');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = CONCAT('ALTER TABLE vps_packages ADD COLUMN IF NOT EXISTS datacenter_location VARCHAR(100)');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = CONCAT('ALTER TABLE vps_packages ADD COLUMN IF NOT EXISTS provider_info JSON');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = CONCAT('ALTER TABLE vps_packages ADD COLUMN IF NOT EXISTS stock_quantity INT DEFAULT 0');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = CONCAT('ALTER TABLE vps_packages ADD COLUMN IF NOT EXISTS auto_delivery BOOLEAN DEFAULT FALSE');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Proxy Packages table
CREATE TABLE IF NOT EXISTS proxy_packages (
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
);

-- Add missing columns to proxy_packages
SET @sql = CONCAT('ALTER TABLE proxy_packages ADD COLUMN IF NOT EXISTS original_price DECIMAL(15,2) DEFAULT 0');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = CONCAT('ALTER TABLE proxy_packages ADD COLUMN IF NOT EXISTS profit_margin DECIMAL(5,2) DEFAULT 0');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = CONCAT('ALTER TABLE proxy_packages ADD COLUMN IF NOT EXISTS provider VARCHAR(100) NOT NULL DEFAULT \'Unknown\'');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = CONCAT('ALTER TABLE proxy_packages ADD COLUMN IF NOT EXISTS features JSON');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = CONCAT('ALTER TABLE proxy_packages ADD COLUMN IF NOT EXISTS stock_quantity INT DEFAULT 0');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = CONCAT('ALTER TABLE proxy_packages ADD COLUMN IF NOT EXISTS auto_delivery BOOLEAN DEFAULT FALSE');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Orders table
CREATE TABLE IF NOT EXISTS orders (
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
);

-- Add missing columns to orders
SET @sql = CONCAT('ALTER TABLE orders ADD COLUMN IF NOT EXISTS cost_price DECIMAL(15,2) DEFAULT 0');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = CONCAT('ALTER TABLE orders ADD COLUMN IF NOT EXISTS profit DECIMAL(15,2) DEFAULT 0');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = CONCAT('ALTER TABLE orders ADD COLUMN IF NOT EXISTS provider_order_id VARCHAR(100)');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = CONCAT('ALTER TABLE orders ADD COLUMN IF NOT EXISTS delivery_status ENUM(\'pending\', \'delivered\', \'failed\') DEFAULT \'pending\'');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = CONCAT('ALTER TABLE orders ADD COLUMN IF NOT EXISTS notes TEXT');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- VPS Info table
CREATE TABLE IF NOT EXISTS vps_info (
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
);

-- Add missing columns to vps_info
SET @sql = CONCAT('ALTER TABLE vps_info ADD COLUMN IF NOT EXISTS os_template VARCHAR(100)');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = CONCAT('ALTER TABLE vps_info ADD COLUMN IF NOT EXISTS datacenter VARCHAR(100)');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = CONCAT('ALTER TABLE vps_info ADD COLUMN IF NOT EXISTS provider_panel_url VARCHAR(255)');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = CONCAT('ALTER TABLE vps_info ADD COLUMN IF NOT EXISTS provider_credentials TEXT');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = CONCAT('ALTER TABLE vps_info ADD COLUMN IF NOT EXISTS backup_enabled BOOLEAN DEFAULT FALSE');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = CONCAT('ALTER TABLE vps_info ADD COLUMN IF NOT EXISTS monitoring_enabled BOOLEAN DEFAULT TRUE');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = CONCAT('ALTER TABLE vps_info ADD COLUMN IF NOT EXISTS firewall_enabled BOOLEAN DEFAULT TRUE');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = CONCAT('ALTER TABLE vps_info ADD COLUMN IF NOT EXISTS expires_at TIMESTAMP NULL');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = CONCAT('ALTER TABLE vps_info ADD COLUMN IF NOT EXISTS renewal_price DECIMAL(15,2) DEFAULT 0');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Proxy Info table
CREATE TABLE IF NOT EXISTS proxy_info (
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
);

-- Add missing columns to proxy_info
SET @sql = CONCAT('ALTER TABLE proxy_info ADD COLUMN IF NOT EXISTS renewal_price DECIMAL(15,2) DEFAULT 0');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Suppliers table
CREATE TABLE IF NOT EXISTS suppliers (
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
);

-- Inventory table
CREATE TABLE IF NOT EXISTS inventory (
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
);

-- Reviews table
CREATE TABLE IF NOT EXISTS reviews (
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
);

-- Affiliates table
CREATE TABLE IF NOT EXISTS affiliates (
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
);

-- Affiliate commissions table
CREATE TABLE IF NOT EXISTS affiliate_commissions (
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
);

-- Top-up requests table
CREATE TABLE IF NOT EXISTS topup_requests (
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
);

-- Bank info table with VietQR support
CREATE TABLE IF NOT EXISTS bank_info (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bank_name VARCHAR(100) NOT NULL,
    account_number VARCHAR(50) NOT NULL,
    account_name VARCHAR(200) NOT NULL,
    branch VARCHAR(200),
    bank_id VARCHAR(50) DEFAULT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Add VietQR support column to bank_info
SET @sql = CONCAT('ALTER TABLE bank_info ADD COLUMN IF NOT EXISTS bank_id VARCHAR(50) DEFAULT NULL');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- System settings table
CREATE TABLE IF NOT EXISTS system_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Transactions table
CREATE TABLE IF NOT EXISTS transactions (
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
);

-- Support tickets table
CREATE TABLE IF NOT EXISTS support_tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    subject VARCHAR(255) NOT NULL,
    priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
    status ENUM('open', 'in_progress', 'resolved', 'closed') DEFAULT 'open',
    category ENUM('technical', 'billing', 'general', 'abuse') DEFAULT 'general',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Support messages table
CREATE TABLE IF NOT EXISTS support_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id INT NOT NULL,
    user_id INT,
    is_admin BOOLEAN DEFAULT FALSE,
    message TEXT NOT NULL,
    attachments JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ticket_id) REFERENCES support_tickets(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Server monitoring table
CREATE TABLE IF NOT EXISTS server_monitoring (
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
);

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- Insert default admin user
INSERT IGNORE INTO users (username, email, password, role) VALUES 
('thynerss', 'thynerss@admin.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Insert suppliers
INSERT IGNORE INTO suppliers (id, name, type, contact_email, contact_phone, website, commission_rate, payment_terms, notes) VALUES
(1, 'DigitalOcean Reseller', 'vps', 'reseller@digitalocean.com', '+1-555-0001', 'https://digitalocean.com', 15.00, 'Net 30', 'VPS Trial và Official'),
(2, 'Vultr Partner', 'vps', 'partner@vultr.com', '+1-555-0002', 'https://vultr.com', 12.00, 'Net 15', 'VPS chất lượng cao'),
(3, 'Linode Distributor', 'vps', 'dist@linode.com', '+1-555-0003', 'https://linode.com', 18.00, 'Net 30', 'VPS enterprise'),
(4, 'ProxyMesh', 'proxy', 'sales@proxymesh.com', '+1-555-0004', 'https://proxymesh.com', 25.00, 'Net 7', 'Proxy SOCKS5 chuyên nghiệp'),
(5, 'SmartProxy', 'proxy', 'b2b@smartproxy.com', '+1-555-0005', 'https://smartproxy.com', 20.00, 'Net 15', 'Proxy residential'),
(6, 'BrightData', 'proxy', 'enterprise@brightdata.com', '+1-555-0006', 'https://brightdata.com', 30.00, 'Net 30', 'Proxy enterprise');

-- Insert VPS packages
INSERT IGNORE INTO vps_packages (id, name, type, provider, cpu, ram, storage, bandwidth, price, original_price, profit_margin, trial_duration, description, features, os_templates, datacenter_location, provider_info, stock_quantity) VALUES
(1, 'VPS Trial DO Basic', 'trial', 'DigitalOcean', '1 vCPU', '1 GB', '25 GB SSD', '1 TB', 89000, 75000, 18.67, 7, 'VPS Trial từ DigitalOcean - 7 ngày', '["Root access", "1 IP", "Basic support", "SSD Storage"]', '["Ubuntu 20.04", "CentOS 8", "Debian 11"]', 'Singapore', '{"panel_url": "https://cloud.digitalocean.com", "api_docs": "https://docs.digitalocean.com/reference/api/"}', 50),
(2, 'VPS Trial Vultr Starter', 'trial', 'Vultr', '1 vCPU', '1 GB', '25 GB SSD', '1 TB', 95000, 80000, 18.75, 7, 'VPS Trial từ Vultr - 7 ngày', '["Root access", "1 IP", "Basic support", "NVMe SSD"]', '["Ubuntu 20.04", "CentOS 8", "Debian 11", "Windows Server"]', 'Tokyo', '{"panel_url": "https://my.vultr.com", "api_docs": "https://www.vultr.com/api/"}', 30),
(3, 'VPS Trial Linode Nano', 'trial', 'Linode', '1 vCPU', '1 GB', '25 GB SSD', '1 TB', 110000, 90000, 22.22, 7, 'VPS Trial từ Linode - 7 ngày', '["Root access", "1 IP", "24/7 support", "SSD Storage"]', '["Ubuntu 20.04", "CentOS 8", "Debian 11", "Arch Linux"]', 'Singapore', '{"panel_url": "https://cloud.linode.com", "api_docs": "https://www.linode.com/api/docs/v4"}', 25),
(4, 'VPS Official DO Standard', 'official', 'DigitalOcean', '2 vCPU', '2 GB', '50 GB SSD', '3 TB', 180000, 150000, 20.00, 0, 'VPS Chính hãng DigitalOcean', '["Root access", "2 IP", "24/7 support", "Backup", "Monitoring"]', '["Ubuntu 20.04", "CentOS 8", "Debian 11", "Windows Server 2019"]', 'Singapore', '{"panel_url": "https://cloud.digitalocean.com", "features": ["Load Balancer", "Firewall", "VPC"]}', 100),
(5, 'VPS Official Vultr Performance', 'official', 'Vultr', '2 vCPU', '4 GB', '80 GB NVMe', '3 TB', 320000, 260000, 23.08, 0, 'VPS Chính hãng Vultr hiệu suất cao', '["Root access", "2 IP", "Priority support", "NVMe SSD", "DDoS Protection"]', '["Ubuntu 20.04", "CentOS 8", "Debian 11", "Windows Server 2019"]', 'Tokyo', '{"panel_url": "https://my.vultr.com", "features": ["Block Storage", "Load Balancer", "Private Network"]}', 80),
(6, 'VPS Official Linode Dedicated', 'official', 'Linode', '4 vCPU', '8 GB', '160 GB SSD', '5 TB', 650000, 520000, 25.00, 0, 'VPS Chính hãng Linode Dedicated CPU', '["Root access", "4 IP", "Premium support", "Dedicated CPU", "Enterprise SLA"]', '["Ubuntu 20.04", "CentOS 8", "Debian 11", "Windows Server 2019"]', 'Singapore', '{"panel_url": "https://cloud.linode.com", "features": ["NodeBalancer", "Block Storage", "Private VLAN"]}', 60),
(7, 'VPS Official Enterprise', 'official', 'Multiple Providers', '8 vCPU', '16 GB', '320 GB NVMe', '10 TB', 1200000, 950000, 26.32, 0, 'VPS Enterprise từ nhiều nhà cung cấp', '["Root access", "8 IP", "Dedicated support", "Enterprise SLA", "Custom OS"]', '["Ubuntu 20.04", "CentOS 8", "Debian 11", "Windows Server 2019", "Custom OS"]', 'Multiple', '{"panel_url": "Custom", "features": ["Dedicated Support", "Custom Configuration", "Enterprise SLA"]}', 40);

-- Insert proxy packages
INSERT IGNORE INTO proxy_packages (id, name, type, location, speed, concurrent_connections, price, original_price, profit_margin, provider, description, features, stock_quantity) VALUES
(1, 'Proxy SOCKS5 Việt Nam Premium', 'socks5', 'Việt Nam', '100 Mbps', 10, 120000, 95000, 26.32, 'ProxyMesh VN', 'Proxy SOCKS5 chất lượng cao tại Việt Nam', '["Dedicated IP", "24/7 uptime", "No logs", "High anonymity"]', 100),
(2, 'Proxy SOCKS5 Singapore Business', 'socks5', 'Singapore', '500 Mbps', 25, 280000, 220000, 27.27, 'SmartProxy', 'Proxy SOCKS5 doanh nghiệp tại Singapore', '["Dedicated IP", "99.9% uptime", "No logs", "Business support"]', 80),
(3, 'Proxy SOCKS5 USA Enterprise', 'socks5', 'United States', '1 Gbps', 50, 450000, 350000, 28.57, 'BrightData', 'Proxy SOCKS5 enterprise tại Mỹ', '["Dedicated IP", "Enterprise SLA", "No logs", "Multiple locations"]', 60),
(4, 'Proxy SOCKS5 Europe Pro', 'socks5', 'Germany', '500 Mbps', 30, 380000, 300000, 26.67, 'ProxyMesh EU', 'Proxy SOCKS5 chuyên nghiệp tại châu Âu', '["Dedicated IP", "GDPR compliant", "No logs", "EU servers"]', 70),
(5, 'Proxy SOCKS5 Japan Gaming', 'socks5', 'Japan', '1 Gbps', 20, 520000, 400000, 30.00, 'SmartProxy JP', 'Proxy SOCKS5 tối ưu cho gaming tại Nhật', '["Dedicated IP", "Low latency", "Gaming optimized", "24/7 support"]', 50),
(6, 'Proxy SOCKS5 Global Network', 'socks5', 'Multiple', '2 Gbps', 100, 850000, 650000, 30.77, 'BrightData Global', 'Mạng proxy SOCKS5 toàn cầu', '["Multiple IPs", "Global network", "Enterprise grade", "API access"]', 30);

-- Insert bank info with VietQR support
INSERT IGNORE INTO bank_info (id, bank_name, account_number, account_name, branch, bank_id) VALUES
(1, 'Vietcombank', '0123456789', 'CONG TY VPS VIET NAM', 'Chi nhánh Hà Nội', 'vietcombank'),
(2, 'Techcombank', '9876543210', 'CONG TY VPS VIET NAM', 'Chi nhánh TP.HCM', 'techcombank'),
(3, 'BIDV', '1122334455', 'CONG TY VPS VIET NAM', 'Chi nhánh Đà Nẵng', 'bidv'),
(4, 'VietinBank', '5566778899', 'CONG TY VPS VIET NAM', 'Chi nhánh Cần Thơ', 'vietinbank');

-- Insert system settings
INSERT IGNORE INTO system_settings (setting_key, setting_value) VALUES
('site_name', 'VPS Việt Nam Pro'),
('site_description', 'Dịch vụ VPS chính hãng và VPS trial chất lượng cao'),
('contact_email', 'support@vpsvietnam.com'),
('min_topup_amount', '50000'),
('max_topup_amount', '50000000'),
('facebook_url', 'https://facebook.com/vpsvietnam'),
('twitter_url', 'https://twitter.com/vpsvietnam'),
('youtube_url', 'https://youtube.com/vpsvietnam'),
('instagram_url', 'https://instagram.com/vpsvietnam'),
('telegram_url', 'https://t.me/vpsvietnam'),
('affiliate_commission_rate', '5.00'),
('auto_delivery_enabled', '1'),
('review_moderation', '1'),
('maintenance_mode', '0'),
('vietqr_enabled', '1');

-- Update existing data - Fixed syntax
UPDATE vps_packages SET type = 'official' WHERE type IS NULL;
UPDATE vps_packages SET provider = 'Unknown' WHERE provider IS NULL OR provider = '';
UPDATE vps_packages SET stock_quantity = 50 WHERE stock_quantity = 0;
UPDATE vps_packages SET original_price = ROUND(price * 0.8) WHERE original_price = 0;
UPDATE vps_packages SET profit_margin = ROUND(((price - original_price) / original_price) * 100, 2) WHERE profit_margin = 0 AND original_price > 0;

UPDATE proxy_packages SET provider = 'Unknown' WHERE provider IS NULL OR provider = '';
UPDATE proxy_packages SET stock_quantity = 50 WHERE stock_quantity = 0;
UPDATE proxy_packages SET original_price = ROUND(price * 0.75) WHERE original_price = 0;
UPDATE proxy_packages SET profit_margin = ROUND(((price - original_price) / original_price) * 100, 2) WHERE profit_margin = 0 AND original_price > 0;

UPDATE orders SET delivery_status = 'pending' WHERE delivery_status IS NULL;
UPDATE orders SET cost_price = 0 WHERE cost_price IS NULL;
UPDATE orders SET profit = 0 WHERE profit IS NULL;

-- Update bank records with VietQR IDs
UPDATE bank_info SET bank_id = 'vietcombank' WHERE bank_name = 'Vietcombank' AND bank_id IS NULL;
UPDATE bank_info SET bank_id = 'techcombank' WHERE bank_name = 'Techcombank' AND bank_id IS NULL;
UPDATE bank_info SET bank_id = 'bidv' WHERE bank_name = 'BIDV' AND bank_id IS NULL;
UPDATE bank_info SET bank_id = 'vietinbank' WHERE bank_name = 'VietinBank' AND bank_id IS NULL;

-- Create indexes for performance
CREATE INDEX IF NOT EXISTS idx_users_email ON users(email);
CREATE INDEX IF NOT EXISTS idx_users_role ON users(role);
CREATE INDEX IF NOT EXISTS idx_orders_user_id ON orders(user_id);
CREATE INDEX IF NOT EXISTS idx_orders_status ON orders(status);
CREATE INDEX IF NOT EXISTS idx_orders_package_type ON orders(package_type);
CREATE INDEX IF NOT EXISTS idx_orders_created_at ON orders(created_at);
CREATE INDEX IF NOT EXISTS idx_topup_requests_user_id ON topup_requests(user_id);
CREATE INDEX IF NOT EXISTS idx_topup_requests_status ON topup_requests(status);
CREATE INDEX IF NOT EXISTS idx_transactions_user_id ON transactions(user_id);
CREATE INDEX IF NOT EXISTS idx_transactions_type ON transactions(type);
CREATE INDEX IF NOT EXISTS idx_vps_packages_status ON vps_packages(status);
CREATE INDEX IF NOT EXISTS idx_vps_packages_type ON vps_packages(type);
CREATE INDEX IF NOT EXISTS idx_proxy_packages_status ON proxy_packages(status);
CREATE INDEX IF NOT EXISTS idx_proxy_packages_type ON proxy_packages(type);
CREATE INDEX IF NOT EXISTS idx_reviews_status ON reviews(status);
CREATE INDEX IF NOT EXISTS idx_affiliates_code ON affiliates(affiliate_code);
CREATE INDEX IF NOT EXISTS idx_suppliers_status ON suppliers(status);
CREATE INDEX IF NOT EXISTS idx_bank_info_status ON bank_info(status);

-- Clean up orphaned records
DELETE FROM orders WHERE user_id NOT IN (SELECT id FROM users);
DELETE FROM topup_requests WHERE user_id NOT IN (SELECT id FROM users);
DELETE FROM transactions WHERE user_id NOT IN (SELECT id FROM users);

-- Final message
SELECT 'Database setup completed successfully! All tables created and data inserted.' as message;