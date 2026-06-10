<?php
header('Content-Type: text/html; charset=utf-8');
mysqli_report(MYSQLI_REPORT_OFF);
include '../config/database.php';

$new_pass = '123456';
// Lưu cả 2: bcrypt hash VÀ plain text backup
$hash = password_hash($new_pass, PASSWORD_DEFAULT);

// Xóa admin cũ
mysqli_query($conn, "DELETE FROM users WHERE username='admin'");

// Lấy cột có thực
$cols_res = mysqli_query($conn, "DESCRIBE users");
$cols = [];
while($c = mysqli_fetch_assoc($cols_res)) $cols[] = $c['Field'];

$f = "username,password,role";
$v = "'admin','$hash','admin'";
if(in_array('ho_ten',$cols)){ $f.=",ho_ten"; $v.=",'Quản trị viên'"; }
if(in_array('email',$cols)){ $f.=",email"; $v.=",'admin@ts.vn'"; }
if(in_array('status',$cols)){ $f.=",status"; $v.=",'Active'"; }

mysqli_query($conn, "INSERT INTO users($f) VALUES($v)");

// Nếu bcrypt có vấn đề → fallback lưu plain text
$check_hash = mysqli_query($conn, "SELECT password FROM users WHERE username='admin'");
if($check_hash){
    $ch = mysqli_fetch_assoc($check_hash);
    if(!password_verify($new_pass, $ch['password'])){
        // Lưu plain text để đảm bảo login được
        mysqli_query($conn, "UPDATE users SET password='$new_pass' WHERE username='admin'");
    }
}

// Xác minh
$ok = false;
$r = mysqli_query($conn, "SELECT password FROM users WHERE username='admin' AND role='admin'");
if($r && mysqli_num_rows($r)>0){
    $row = mysqli_fetch_assoc($r);
    $ok = password_verify($new_pass, $row['password']) || ($row['password'] === $new_pass);
}
?><!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Reset Admin</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:Arial,sans-serif;background:#0f172a;min-height:100vh;display:flex;align-items:center;justify-content:center;}
.box{background:white;border-radius:20px;padding:40px;max-width:440px;width:90%;text-align:center;box-shadow:0 20px 60px rgba(0,0,0,0.4);}
.icon{font-size:3.5rem;margin-bottom:16px}
h2{font-size:1.5rem;margin-bottom:8px;color:#0f172a}
p{color:#64748b;margin-bottom:24px;font-size:.9rem;line-height:1.6}
.creds{background:#f0fdf4;border:2px solid #22c55e;border-radius:12px;padding:20px;margin:20px 0;text-align:left}
.cred-row{display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid #dcfce7}
.cred-row:last-child{border-bottom:none}
.cred-label{font-size:.82rem;color:#64748b;font-weight:600}
.cred-val{font-size:1rem;font-weight:800;color:#16a34a;letter-spacing:.03em}
.btn{display:block;margin:8px 0;padding:14px 20px;border-radius:12px;text-decoration:none;font-weight:700;font-size:.95rem;transition:all .2s}
.btn-main{background:linear-gradient(135deg,#6d28d9,#7c3aed);color:white;box-shadow:0 4px 14px rgba(124,58,237,.4)}
.btn-main:hover{transform:translateY(-1px)}
.err{background:#fef2f2;border:2px solid #ef4444;border-radius:12px;padding:20px;color:#991b1b}
</style>
</head>
<body>
<div class="box">
<?php if($ok): ?>
    <div class="icon">🎉</div>
    <h2>Reset thành công!</h2>
    <p>Tài khoản quản trị viên đã được đặt lại. Hãy đăng nhập ngay với thông tin dưới đây:</p>
    <div class="creds">
        <div class="cred-row">
            <span class="cred-label">Tài khoản</span>
            <span class="cred-val">admin</span>
        </div>
        <div class="cred-row">
            <span class="cred-label">Mật khẩu</span>
            <span class="cred-val"><?= $new_pass ?></span>
        </div>
    </div>
    <a href="login.php" class="btn btn-main">🔓 Đăng nhập ngay →</a>
    <p style="margin-top:16px;font-size:.78rem;color:#ef4444">⚠️ Sau khi đăng nhập, hãy XÓA file <strong>reset_now.php</strong> này!</p>
<?php else: ?>
    <div class="err">
        <div style="font-size:2rem;margin-bottom:10px">❌</div>
        <strong>Reset thất bại!</strong><br>
        Lỗi: <?= mysqli_error($conn) ?>
    </div>
<?php endif; ?>
</div>
</body>
</html>
