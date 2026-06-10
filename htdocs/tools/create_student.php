<?php
header('Content-Type: text/html; charset=utf-8');
include '../config/database.php';
mysqli_report(MYSQLI_REPORT_OFF);

// 1. Xóa các tài khoản lỗi (tên đăng nhập trống)
mysqli_query($conn, "DELETE FROM users WHERE username = '' OR username IS NULL");

// 2. Tạo tài khoản thí sinh mẫu
$u = 'thisinh';
$p = '123';
$h = 'Thí Sinh Mẫu';
$e = 'thisinh@gmail.com';
$r = 'user';

// Kiểm tra xem đã tồn tại chưa
$check = mysqli_query($conn, "SELECT id FROM users WHERE username = '$u'");
if (mysqli_num_rows($check) == 0) {
    mysqli_query($conn, "INSERT INTO users (username, password, ho_ten, email, role) VALUES ('$u', '$p', '$h', '$e', '$r')");
    $msg = "✅ Đã tạo thành công tài khoản thí sinh mới!";
} else {
    mysqli_query($conn, "UPDATE users SET password = '$p' WHERE username = '$u'");
    $msg = "✅ Tài khoản 'thisinh' đã sẵn sàng với mật khẩu '123'!";
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Tạo tài khoản thí sinh</title>
    <style>
        body { font-family: Arial; background: #0f172a; display: flex; justify-content: center; padding-top: 100px; color: white; }
        .box { background: #1e293b; padding: 40px; border-radius: 20px; text-align: center; border: 1px solid #334155; }
        h2 { color: #22c55e; }
        .info { background: #0f172a; padding: 20px; border-radius: 12px; margin: 20px 0; text-align: left; border: 1px solid #334155; }
        .btn { display: inline-block; background: #3b82f6; color: white; padding: 12px 25px; text-decoration: none; border-radius: 10px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="box">
        <h2><?php echo $msg; ?></h2>
        <div class="info">
            <p><strong>Tên đăng nhập:</strong> <span style="color:#3b82f6; font-size:1.2rem;">thisinh</span></p>
            <p><strong>Mật khẩu:</strong> <span style="color:#3b82f6; font-size:1.2rem;">123</span></p>
            <p><strong>Vai trò:</strong> user</p>
        </div>
        <a href="login.php" class="btn">🔓 Đăng nhập ngay</a>
    </div>
</body>
</html>
