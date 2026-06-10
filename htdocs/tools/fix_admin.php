<?php
include '../config/database.php';

$username = 'admin';
$password = 'admin123';
$role = 'admin';

echo "<h2>Sửa lỗi đăng nhập Admin khẩn cấp</h2>";

// Xóa tài khoản admin cũ nếu có để tránh trùng lặp hoặc sai role
mysqli_query($conn, "DELETE FROM users WHERE username = '$username'");

// Chèn lại tài khoản admin mới với mật khẩu plain text và role chuẩn
$sql = "INSERT INTO users (username, password, ho_ten, email, role) 
        VALUES ('$username', '$password', 'Quản trị viên', 'admin@tuyensinh.edu.vn', '$role')";

if (mysqli_query($conn, $sql)) {
    echo "<p style='color: green;'>✅ Đã làm mới tài khoản Admin thành công!</p>";
    echo "<ul>";
    echo "<li>Tên đăng nhập: <strong>admin</strong></li>";
    echo "<li>Mật khẩu: <strong>admin123</strong></li>";
    echo "<li>Quyền: <strong>admin</strong></li>";
    echo "</ul>";
    echo "<p>Bây giờ bạn hãy thử đăng nhập lại tại đây: <a href='admin/login.php'>Trang đăng nhập Admin</a></p>";
} else {
    echo "<p style='color: red;'>❌ Lỗi khi tạo tài khoản: " . mysqli_error($conn) . "</p>";
}
?>
