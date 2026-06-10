<?php
include '../config/database.php';

$username = 'admin';
$new_password = 'admin123';
$hash = password_hash($new_password, PASSWORD_DEFAULT);

$sql = "UPDATE users SET password = '$hash' WHERE username = '$username' AND role = 'admin'";

echo "<h2>Cập nhật mật khẩu Admin</h2>";

if (mysqli_query($conn, $sql)) {
    if (mysqli_affected_rows($conn) > 0) {
        echo "<p style='color: green;'>✅ Thành công! Mật khẩu cho tài khoản <strong>admin</strong> đã được đổi thành: <strong>admin123</strong></p>";
        echo "<p>Bây giờ bạn có thể quay lại trang <a href='admin/login.php'>Đăng nhập Admin</a>.</p>";
        echo "<p style='color: red;'><strong>Lưu ý:</strong> Hãy xóa file <code>reset_admin.php</code> này sau khi hoàn tất để đảm bảo bảo mật!</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ Không tìm thấy tài khoản admin hoặc mật khẩu đã giống hệt mật khẩu mới.</p>";
        
        // Thử tạo mới nếu không tồn tại
        $check = mysqli_query($conn, "SELECT id FROM users WHERE username='admin'");
        if (mysqli_num_rows($check) == 0) {
            $insert = "INSERT INTO users (username, password, ho_ten, email, role) VALUES ('admin', '$hash', 'Quản trị viên', 'admin@tuyensinh.edu.vn', 'admin')";
            if (mysqli_query($conn, $insert)) {
                echo "<p style='color: green;'>✅ Đã tạo mới tài khoản admin thành công!</p>";
            }
        }
    }
} else {
    echo "<p style='color: red;'>❌ Lỗi: " . mysqli_error($conn) . "</p>";
}
?>
