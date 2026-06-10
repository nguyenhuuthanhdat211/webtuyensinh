<?php
// Tự động thiết lập lại toàn bộ hệ thống Database
$host = "localhost";
$user = "root";
$pass = "";

// 1. Kết nối MySQL
$conn = mysqli_connect($host, $user, $pass);
if (!$conn) {
    die("Kết nối thất bại: " . mysqli_connect_error());
}

echo "<h2>🚀 Bắt đầu thiết lập lại hệ thống...</h2>";

// 2. Tạo Database
mysqli_query($conn, "DROP DATABASE IF EXISTS tuyensinh");
if (mysqli_query($conn, "CREATE DATABASE tuyensinh CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci")) {
    echo "<p>✅ Đã tạo mới Database 'tuyensinh'.</p>";
}
mysqli_select_db($conn, "tuyensinh");

// 3. Tạo các bảng
$sqls = [
    "CREATE TABLE users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        ho_ten VARCHAR(255),
        email VARCHAR(100),
        role ENUM('admin','user') DEFAULT 'user',
        status ENUM('Active', 'Locked') DEFAULT 'Active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    "CREATE TABLE dot_tuyensinh (
        id INT AUTO_INCREMENT PRIMARY KEY,
        ten_dot VARCHAR(255) NOT NULL,
        ngay_bat_dau DATE,
        ngay_ket_thuc DATE,
        trang_thai ENUM('Dang mo', 'Da dong') DEFAULT 'Dang mo'
    )",
    "CREATE TABLE tohop_xettuyen (
        id INT AUTO_INCREMENT PRIMARY KEY,
        ma_tohop VARCHAR(10) NOT NULL UNIQUE,
        ten_tohop VARCHAR(255) NOT NULL
    )",
    "CREATE TABLE nganhhoc (
        id INT AUTO_INCREMENT PRIMARY KEY,
        tennganh VARCHAR(255) NOT NULL,
        ma_nganh VARCHAR(20) UNIQUE,
        chitieu INT DEFAULT 0,
        mo_ta TEXT
    )",
    "CREATE TABLE nganh_tohop (
        nganh_id INT,
        tohop_id INT,
        PRIMARY KEY (nganh_id, tohop_id),
        FOREIGN KEY (nganh_id) REFERENCES nganhhoc(id) ON DELETE CASCADE,
        FOREIGN KEY (tohop_id) REFERENCES tohop_xettuyen(id) ON DELETE CASCADE
    )",
    "CREATE TABLE thisinh (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        hoten VARCHAR(255) NOT NULL,
        ngaysinh DATE,
        gioitinh VARCHAR(10),
        diachi TEXT,
        sdt VARCHAR(20),
        email VARCHAR(100),
        cccd VARCHAR(20) UNIQUE,
        que_quan VARCHAR(255),
        truong_thpt VARCHAR(255),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    )",
    "CREATE TABLE hosoxettuyen (
        id INT AUTO_INCREMENT PRIMARY KEY,
        thisinh_id INT NOT NULL,
        nganh_id INT NOT NULL,
        dot_id INT,
        tohop_id INT,
        diem_mon1 FLOAT DEFAULT 0,
        diem_mon2 FLOAT DEFAULT 0,
        diem_mon3 FLOAT DEFAULT 0,
        diem_tong FLOAT DEFAULT 0,
        file_hocba VARCHAR(255),
        trangthai ENUM('Cho duyet','Da duyet','Tu choi','Trung tuyen','Khong trung tuyen') DEFAULT 'Cho duyet',
        ghi_chu TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (thisinh_id) REFERENCES thisinh(id) ON DELETE CASCADE,
        FOREIGN KEY (nganh_id) REFERENCES nganhhoc(id) ON DELETE CASCADE,
        FOREIGN KEY (dot_id) REFERENCES dot_tuyensinh(id) ON DELETE SET NULL,
        FOREIGN KEY (tohop_id) REFERENCES tohop_xettuyen(id) ON DELETE SET NULL
    )"
];

foreach ($sqls as $sql) {
    if (mysqli_query($conn, $sql)) {
        // success
    } else {
        echo "<p style='color: red;'>❌ Lỗi tạo bảng: " . mysqli_error($conn) . "</p>";
    }
}
echo "<p>✅ Đã tạo xong 7 bảng dữ liệu chuẩn.</p>";

// 4. Chèn dữ liệu mẫu
$admin_pass = password_hash("admin123", PASSWORD_DEFAULT);
mysqli_query($conn, "INSERT INTO users (username, password, ho_ten, email, role) VALUES ('admin', '$admin_pass', 'Quản trị viên', 'admin@tuyensinh.edu.vn', 'admin')");

mysqli_query($conn, "INSERT INTO dot_tuyensinh (ten_dot, ngay_bat_dau, ngay_ket_thuc) VALUES ('Xét tuyển Đợt 1 - 2026', '2026-05-01', '2026-08-30')");

mysqli_query($conn, "INSERT INTO tohop_xettuyen (ma_tohop, ten_tohop) VALUES ('A00', 'Toán, Lý, Hóa'), ('A01', 'Toán, Lý, Anh'), ('D01', 'Toán, Văn, Anh')");

mysqli_query($conn, "INSERT INTO nganhhoc (tennganh, ma_nganh, chitieu, mo_ta) VALUES 
('Công nghệ thông tin', 'CNTT', 150, 'Đào tạo kỹ sư lập trình...'),
('Quản trị kinh doanh', 'QTKD', 200, 'Đào tạo cử nhân kinh tế...'),
('Ngôn ngữ Anh', 'NNA', 100, 'Đào tạo biên phiên dịch...')");

// Liên kết ngành - tổ hợp
mysqli_query($conn, "INSERT INTO nganh_tohop VALUES (1,1), (1,2), (2,3), (3,3)");

echo "<div style='background: #ecfdf5; border: 1px solid #10b981; padding: 25px; border-radius: 15px; margin-top: 30px;'>";
echo "<h3 style='color: #10b981;'>🎉 HOÀN TẤT THIẾT LẬP!</h3>";
echo "<p>Toàn bộ Code và Database đã được đồng bộ hóa.</p>";
echo "<h4>Thông tin đăng nhập của bạn:</h4>";
echo "<ul>";
echo "<li>Đường dẫn Admin: <a href='admin/login.php'><strong>admin/login.php</strong></a></li>";
echo "<li>Tên đăng nhập: <strong>admin</strong></li>";
echo "<li>Mật khẩu: <strong>admin123</strong></li>";
echo "</ul>";
echo "<p><strong>Bước tiếp theo:</strong> Bạn hãy xóa file <code>setup.php</code> này đi và bắt đầu sử dụng.</p>";
echo "</div>";
?>
