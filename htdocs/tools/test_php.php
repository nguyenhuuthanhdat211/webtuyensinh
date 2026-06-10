<?php
$pass = 'admin123';
$hash = password_hash($pass, PASSWORD_DEFAULT);

echo "<h2>Kiểm tra môi trường PHP</h2>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Mật khẩu test: " . $pass . "<br>";
echo "Hash tạo ra: <code>" . $hash . "</code><br>";

if (password_verify($pass, $hash)) {
    echo "<p style='color: green;'>✅ password_verify hoạt động bình thường!</p>";
} else {
    echo "<p style='color: red;'>❌ password_verify THẤT BẠI trên server này!</p>";
}

// Kiểm tra MD5 để dự phòng
$md5 = md5($pass);
echo "MD5 Hash: <code>" . $md5 . "</code><br>";
?>
