<?php
header('Content-Type: text/html; charset=utf-8');
include '../config/database.php';
mysqli_report(MYSQLI_REPORT_OFF);

$res = mysqli_query($conn, "SELECT id, username, password, role, ho_ten FROM users");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Danh sách tài khoản</title>
    <style>
        body { font-family: Arial; padding: 30px; background: #f4f7f6; }
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; }
        th, td { padding: 12px 15px; border: 1px solid #ddd; text-align: left; }
        th { background: #3498db; color: white; }
        tr:nth-child(even) { background: #f2f2f2; }
        .role-admin { color: #e74c3c; font-weight: bold; }
        .role-user { color: #2ecc71; font-weight: bold; }
    </style>
</head>
<body>
    <h2>👥 Danh sách tài khoản trong hệ thống</h2>
    <p>Dùng thông tin này để đăng nhập (Mật khẩu hiện tại đều là mật khẩu thường):</p>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Tên đăng nhập (Username)</th>
                <th>Mật khẩu (Password)</th>
                <th>Vai trò (Role)</th>
                <th>Họ tên</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = mysqli_fetch_assoc($res)): ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><strong><?php echo $row['username']; ?></strong></td>
                <td style="color:red;"><?php echo $row['password']; ?></td>
                <td class="<?php echo ($row['role'] == 'admin') ? 'role-admin' : 'role-user'; ?>">
                    <?php echo $row['role']; ?>
                </td>
                <td><?php echo $row['ho_ten']; ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <br>
    <a href="login.php" style="padding:10px 20px; background:#3498db; color:white; text-decoration:none; border-radius:5px;">Quay lại trang Đăng nhập</a>
</body>
</html>
