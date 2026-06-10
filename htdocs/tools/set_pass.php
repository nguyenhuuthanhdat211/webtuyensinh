<?php
header('Content-Type: text/html; charset=utf-8');
// Chạy file này 1 lần để đặt mật khẩu plain text, sau đó XÓA ĐI
mysqli_report(MYSQLI_REPORT_OFF);
include '../config/database.php';

// Cập nhật thẳng mật khẩu plain text vào DB - không mã hóa
$r1 = mysqli_query($conn, "UPDATE users SET password='123456' WHERE username='admin' AND role='admin'");
$affected = mysqli_affected_rows($conn);

// Kiểm tra lại
$check = mysqli_query($conn, "SELECT username, password, role FROM users WHERE username='admin'");
$row = $check ? mysqli_fetch_assoc($check) : null;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Đặt mật khẩu</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: Arial, sans-serif; background: #0f172a; min-height: 100vh;
       display: flex; align-items: center; justify-content: center; }
.box { background: white; border-radius: 20px; padding: 40px; width: 420px; text-align: center; }
.ok  { color: #16a34a; font-size: 3rem; margin-bottom: 16px; }
.err { color: #ef4444; font-size: 3rem; margin-bottom: 16px; }
h2   { font-size: 1.4rem; margin-bottom: 10px; color: #0f172a; }
.info { background: #f0fdf4; border: 2px solid #22c55e; border-radius: 12px;
        padding: 20px; margin: 20px 0; text-align: left; }
.row { display: flex; justify-content: space-between; padding: 8px 0;
       border-bottom: 1px solid #dcfce7; font-size: .95rem; }
.row:last-child { border-bottom: none; }
.lbl { color: #64748b; font-weight: 600; }
.val { font-weight: 800; color: #16a34a; font-size: 1.1rem; }
.btn { display: block; margin: 10px 0; padding: 14px; border-radius: 12px;
       text-decoration: none; font-weight: 700; font-size: .95rem; }
.btn-go { background: linear-gradient(135deg,#6d28d9,#7c3aed); color: white; }
.warn { color: #ef4444; font-size: .8rem; margin-top: 16px; }
</style>
</head>
<body>
<div class="box">
<?php if ($affected > 0 || ($row && $row['password'] === '123456')): ?>
    <div class="ok">✅</div>
    <h2>Đặt mật khẩu thành công!</h2>
    <div class="info">
        <div class="row"><span class="lbl">Tài khoản</span><span class="val">admin</span></div>
        <div class="row"><span class="lbl">Mật khẩu</span><span class="val">123456</span></div>
        <div class="row"><span class="lbl">Trong DB</span><span class="val"><?= htmlspecialchars($row['password'] ?? '') ?></span></div>
    </div>
    <a href="login.php?tab=admin" class="btn btn-go">🔓 Đăng nhập ngay →</a>
    <p class="warn">⚠️ Hãy XÓA file <strong>set_pass.php</strong> này sau khi đăng nhập!</p>
<?php else: ?>
    <div class="err">❌</div>
    <h2>Thất bại! Không tìm thấy tài khoản admin</h2>
    <p style="color:#64748b;margin:16px 0">Tài khoản admin chưa tồn tại trong database.<br>
    Hãy chạy: <a href="init_database.php">init_database.php</a> trước.</p>
    <p>Lỗi: <?= mysqli_error($conn) ?></p>
    <?php if($row): ?>
    <p>Tìm thấy: <?= $row['username'] ?> / role: <?= $row['role'] ?></p>
    <?php endif; ?>
<?php endif; ?>
</div>
</body>
</html>
