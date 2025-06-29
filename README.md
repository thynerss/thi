# VPS Việt Nam Pro - Professional VPS & Proxy Marketplace

Hệ thống marketplace VPS và Proxy chuyên nghiệp được viết bằng PHP với MySQL database.

## 🚀 Tính năng chính

### 👥 Người dùng
- ✅ Đăng ký/Đăng nhập tài khoản
- ✅ Xem danh sách gói VPS Trial và VPS Chính hãng
- ✅ Xem danh sách gói Proxy SOCKS5
- ✅ Đặt hàng VPS và Proxy với quản lý tồn kho
- ✅ Nạp tiền vào tài khoản qua chuyển khoản ngân hàng
- ✅ Xem lịch sử đơn hàng và giao dịch
- ✅ Quản lý hồ sơ cá nhân
- ✅ Hệ thống đánh giá sản phẩm
- ✅ Hệ thống support tickets

### 🔧 Admin
- ✅ Dashboard thống kê tổng quan với báo cáo lợi nhuận
- ✅ Quản lý người dùng và phân quyền
- ✅ Quản lý đơn hàng VPS và Proxy
- ✅ Duyệt yêu cầu nạp tiền
- ✅ Quản lý gói VPS Trial và VPS Chính hãng
- ✅ Quản lý gói Proxy SOCKS5
- ✅ Quản lý nhà cung cấp (Suppliers)
- ✅ Quản lý tồn kho (Inventory)
- ✅ Quản lý đánh giá khách hàng
- ✅ Hệ thống Affiliate marketing
- ✅ Cấu hình hệ thống và mạng xã hội

### 🛡️ Bảo mật
- ✅ Mã hóa mật khẩu bằng PHP password_hash()
- ✅ Prepared statements chống SQL injection
- ✅ Input validation và sanitization
- ✅ Session management an toàn

## 📋 Yêu cầu hệ thống

- **PHP**: 7.4 hoặc cao hơn
- **MySQL**: 5.7 hoặc cao hơn
- **Apache/Nginx**: Web server
- **Extensions**: PDO, PDO_MySQL

## 🔧 Cài đặt

### 1. Chuẩn bị database

```sql
CREATE DATABASE vps_marketplace CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 2. Import database

Chạy file `database_update.sql` trong phpMyAdmin hoặc MySQL command line:

```bash
mysql -u username -p vps_marketplace < database_update.sql
```

### 3. Cấu hình database

Chỉnh sửa file `config/database.php`:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'vps_marketplace');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
```

### 4. Upload files

Upload tất cả files lên web server của bạn.

## 👤 Tài khoản mặc định

### Admin
- **Email**: `thynerss@admin.com`
- **Password**: `Thi12121704t@`

## 📁 Cấu trúc thư mục

```
/
├── config/
│   └── database.php          # Cấu hình database
├── includes/
│   ├── functions.php         # Các hàm tiện ích
│   ├── header.php           # Header chung
│   └── footer.php           # Footer chung
├── api/
│   ├── get_social_links.php # API lấy link mạng xã hội
│   ├── get_package.php      # API lấy thông tin gói
│   ├── create_order.php     # API tạo đơn hàng
│   └── get_order_details.php # API chi tiết đơn hàng
├── admin/
│   ├── index.php            # Dashboard admin
│   ├── orders.php           # Quản lý đơn hàng
│   ├── topups.php           # Quản lý nạp tiền
│   ├── users.php            # Quản lý người dùng
│   └── settings.php         # Cài đặt hệ thống
├── index.php                # Trang chủ
├── login.php               # Đăng nhập
├── register.php            # Đăng ký
├── packages.php            # Danh sách gói VPS & Proxy
├── orders.php              # Đơn hàng của user
├── topup.php               # Nạp tiền
├── profile.php             # Hồ sơ user
├── support.php             # Hỗ trợ kỹ thuật
├── logout.php              # Đăng xuất
└── database_update.sql     # File SQL cập nhật database
```

## 🎯 Gói dịch vụ

### VPS Trial (từ nhà phân phối)
- **VPS Trial DO Basic**: 1 vCPU, 1GB RAM, 25GB SSD - 89.000 VND/7 ngày
- **VPS Trial Vultr Starter**: 1 vCPU, 1GB RAM, 25GB SSD - 95.000 VND/7 ngày
- **VPS Trial Linode Nano**: 1 vCPU, 1GB RAM, 25GB SSD - 110.000 VND/7 ngày

### VPS Chính hãng
- **VPS Official DO Standard**: 2 vCPU, 2GB RAM, 50GB SSD - 180.000 VND/tháng
- **VPS Official Vultr Performance**: 2 vCPU, 4GB RAM, 80GB NVMe - 320.000 VND/tháng
- **VPS Official Linode Dedicated**: 4 vCPU, 8GB RAM, 160GB SSD - 650.000 VND/tháng
- **VPS Official Enterprise**: 8 vCPU, 16GB RAM, 320GB NVMe - 1.200.000 VND/tháng

### Proxy SOCKS5
- **Proxy SOCKS5 Việt Nam Premium**: 100 Mbps, 10 kết nối - 120.000 VND/tháng
- **Proxy SOCKS5 Singapore Business**: 500 Mbps, 25 kết nối - 280.000 VND/tháng
- **Proxy SOCKS5 USA Enterprise**: 1 Gbps, 50 kết nối - 450.000 VND/tháng
- **Proxy SOCKS5 Europe Pro**: 500 Mbps, 30 kết nối - 380.000 VND/tháng
- **Proxy SOCKS5 Japan Gaming**: 1 Gbps, 20 kết nối - 520.000 VND/tháng
- **Proxy SOCKS5 Global Network**: 2 Gbps, 100 kết nối - 850.000 VND/tháng

## 💳 Thanh toán

### Ngân hàng hỗ trợ
- **Vietcombank**: 0123456789 - CONG TY VPS VIET NAM
- **Techcombank**: 9876543210 - CONG TY VPS VIET NAM
- **BIDV**: 1122334455 - CONG TY VPS VIET NAM
- **VietinBank**: 5566778899 - CONG TY VPS VIET NAM

### Quy trình nạp tiền
1. Chọn số tiền (50.000 - 50.000.000 VND)
2. Chọn ngân hàng
3. Chuyển khoản với nội dung được cung cấp
4. Hệ thống tự động xử lý trong 24h

## 🏢 Business Model

### VPS Trial
- Nguồn hàng từ các nhà phân phối chính thức
- Lợi nhuận: 15-25% trên giá vốn
- Thời hạn: 7 ngày sử dụng
- Cấu hình thực tế, không giới hạn

### VPS Chính hãng
- Đối tác chính thức của DigitalOcean, Vultr, Linode
- Lợi nhuận: 20-30% trên giá vốn
- Đầy đủ tính năng enterprise
- SLA 99.9% uptime

### Proxy SOCKS5
- Từ các nhà cung cấp uy tín
- Lợi nhuận: 25-35% trên giá vốn
- Chất lượng cao, tốc độ nhanh
- Hỗ trợ nhiều vị trí địa lý

## 🔧 Tính năng nâng cao

### Quản lý nhà cung cấp
- Thông tin liên hệ và API
- Tỷ lệ hoa hồng và điều khoản thanh toán
- Theo dõi hiệu suất và chất lượng

### Quản lý tồn kho
- Theo dõi số lượng có sẵn
- Quản lý giá vốn và giá bán
- Cảnh báo hết hàng

### Hệ thống đánh giá
- Khách hàng đánh giá sau khi sử dụng
- Kiểm duyệt và phản hồi admin
- Hiển thị đánh giá công khai

### Affiliate Marketing
- Chương trình giới thiệu khách hàng
- Tính hoa hồng tự động
- Theo dõi thu nhập và thống kê

### Support System
- Hệ thống ticket hỗ trợ
- Phân loại theo mức độ ưu tiên
- Theo dõi trạng thái xử lý

## 📊 Database Schema

### Bảng chính
- **users**: Thông tin người dùng và admin
- **vps_packages**: Gói VPS (trial/official)
- **proxy_packages**: Gói Proxy SOCKS5
- **orders**: Đơn hàng với tracking lợi nhuận
- **suppliers**: Nhà cung cấp VPS/Proxy
- **inventory**: Quản lý tồn kho
- **reviews**: Đánh giá khách hàng
- **affiliates**: Hệ thống affiliate
- **support_tickets**: Hỗ trợ kỹ thuật

## 🛠️ API Endpoints

- `GET /api/get_social_links.php` - Lấy link mạng xã hội
- `GET /api/get_package.php?id=1&type=vps` - Lấy thông tin gói
- `POST /api/create_order.php` - Tạo đơn hàng
- `GET /api/get_order_details.php?id=1` - Chi tiết đơn hàng

## 🔍 Troubleshooting

### Lỗi database
1. **Table already exists**: Chạy file `database_update.sql` thay vì tạo mới
2. **Connection failed**: Kiểm tra thông tin database trong `config/database.php`
3. **Missing columns**: File `database_update.sql` sẽ tự động thêm cột mới

### Lỗi thường gặp
1. **404 errors**: Đảm bảo mod_rewrite được bật trên Apache
2. **Permission denied**: Cấu hình permissions 755 cho thư mục, 644 cho files
3. **Session errors**: Đảm bảo thư mục session có quyền ghi

## 📞 Hỗ trợ

Nếu gặp vấn đề:
1. Kiểm tra PHP error logs
2. Kiểm tra MySQL error logs
3. Đảm bảo file permissions đúng
4. Kiểm tra cấu hình database

## 📄 License

MIT License - Xem file LICENSE để biết thêm chi tiết.

---

**🎉 Chúc bạn triển khai thành công!**

Developed with ❤️ by VPS Việt Nam Pro Team