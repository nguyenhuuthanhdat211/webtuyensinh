<?php
include '../config/database.php';

$username = 'admin';
$sql = "SELECT username, password, role FROM users WHERE username = '$username'";
$result = mysqli_query($conn, $sql);

echo "<h2>Kiểm tra tài khoản Admin</h2>";

if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    echo "<ul>";
    echo "<li>Username: " . $row['username'] . "</li>";
    echo "<li>Role: " . $row['role'] . "</li>";
    echo "<li>Password Hash trong DB: <code>" . $row['password'] . "</code></li>";
    echo "</ul>";
    
    $test_pass = 'admin123';
    if (password_verify($test_pass, $row['password'])) {
        echo "<p style='color: green;'>✅ Mật khẩu <strong>admin123</strong> KHỚP với hash trong DB.</p>";
    } else {
        echo "<p style='color: red;'>❌ Mật khẩu <strong>admin123</strong> KHÔNG khớp với hash trong DB.</p>";
        echo "<p>Điều này có nghĩa là bạn chưa chạy file <code>reset_admin.php</code> hoặc hash trong DB là loại cũ (MD5 hoặc MySQL PASSWORD).</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Không tìm thấy tài khoản có username là 'admin' trong bảng 'users'.</p>";
}
?>
