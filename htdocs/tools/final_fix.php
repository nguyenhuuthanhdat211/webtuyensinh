<?php
header('Content-Type: text/html; charset=utf-8');
include '../config/database.php';
mysqli_report(MYSQLI_REPORT_OFF);

// 1. Ép mật khẩu admin về 123456 (chữ thường)
$sql = "UPDATE users SET password = '123456' WHERE username = 'admin'";
$res = mysqli_query($conn, $sql);

// 2. Lấy thông tin để hiển thị kiểm tra
$check = mysqli_query($conn, "SELECT * FROM users WHERE username = 'admin'");
$admin = mysqli_fetch_assoc($check);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Xác nhận mật khẩu thường</title>
    <style>
        body { font-family: Arial; background: #f4f7f6; display: flex; justify-content: center; padding-top: 50px; }
        .card { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); max-width: 500px; width: 100%; }
        h2 { color: #2c3e50; border-bottom: 2px solid #eee; padding-bottom: 10px; }
        .success { color: #27ae60; font-weight: bold; font-size: 1.2rem; }
        .info { background: #f9f9f9; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 5px solid #3498db; }
        .btn { display: inline-block; background: #3498db; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="card">
        <h2>✅ Đã gỡ bỏ mã hóa</h2>
        <p class="success">Mật khẩu đã được đưa về dạng chữ thường thành công!</p>
        
        <div class="info">
            <p><strong>Tên đăng nhập:</strong> <?php echo $admin['username']; ?></p>
            <p><strong>Mật khẩu hiện tại trong DB:</strong> <span style="color:red; font-size:1.5rem;"><?php echo $admin['password']; ?></span></p>
            <p><strong>Vai trò:</strong> <?php echo $admin['role']; ?></p>
        </div>

        <p>Bây giờ bạn hãy dùng mật khẩu <strong>123456</strong> để đăng nhập.</p>
        <a href="login.php" class="btn">Đến trang đăng nhập ngay</a>
    </div>
</body>
</html>
