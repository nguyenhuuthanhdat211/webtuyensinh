<?php
include '../config/database.php';

echo "<h2>🔧 Đang sửa lỗi đăng nhập Admin...</h2>";

// 1. Kiểm tra kết nối
if (!$conn) {
    die("<p style='color: red;'>❌ Lỗi kết nối Database: " . mysqli_connect_error() . "</p>");
}

// 2. Kiểm tra bảng users
$check_table = mysqli_query($conn, "SHOW TABLES LIKE 'users'");
if (mysqli_num_rows($check_table) == 0) {
    // Nếu chưa có bảng, tạo bảng mới theo chuẩn mới nhất
    $create_sql = "CREATE TABLE users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        ho_ten VARCHAR(255),
        email VARCHAR(100),
        role ENUM('admin','user') DEFAULT 'user',
        status ENUM('Active', 'Locked') DEFAULT 'Active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    if (mysqli_query($conn, $create_sql)) {
        echo "<p style='color: green;'>✅ Đã tạo bảng 'users' mới.</p>";
    } else {
        die("<p style='color: red;'>❌ Không thể tạo bảng users: " . mysqli_error($conn) . "</p>");
    }
}

// 3. Reset tài khoản admin
$username = 'admin';
$password = 'admin123';
$hash = password_hash($password, PASSWORD_DEFAULT);
$role = 'admin';

// Xóa cũ để tránh lỗi dữ liệu cũ không tương thích
mysqli_query($conn, "DELETE FROM users WHERE username = '$username'");

$sql = "INSERT INTO users (username, password, ho_ten, email, role) 
        VALUES ('$username', '$hash', 'Quản trị viên', 'admin@tuyensinh.edu.vn', '$role')";

if (mysqli_query($conn, $sql)) {
    echo "<div style='background: #ecfdf5; border: 1px solid #10b981; padding: 20px; border-radius: 10px; margin-top: 20px;'>";
    echo "<h3 style='color: #10b981; margin: 0;'>🎉 SỬA LỖI THÀNH CÔNG!</h3>";
    echo "<p>Tài khoản quản trị của bạn đã được thiết lập lại:</p>";
    echo "<ul>";
    echo "<li>URL đăng nhập: <a href='admin/login.php'><strong>admin/login.php</strong></a></li>";
    echo "<li>User: <strong>admin</strong></li>";
    echo "<li>Pass: <strong>admin123</strong></li>";
    echo "</ul>";
    echo "<p style='color: #ef4444;'><strong>Chú ý:</strong> Sau khi đăng nhập được, bạn hãy XÓA file <code>force_fix_admin.php</code> này đi.</p>";
    echo "</div>";
} else {
    echo "<p style='color: red;'>❌ Lỗi khi tạo tài khoản: " . mysqli_error($conn) . "</p>";
}
?>
