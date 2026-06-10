<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
include '../../config/database.php';

$error = '';
$debug_info = '';

if (isset($_SESSION['admin'])) {
    header('Location: dashboard.php');
    exit;
}

if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        $error = "Vui lòng nhập đầy đủ thông tin!";
    } else {
        // Kiểm tra kết nối DB
        if (!$conn) {
            $error = "Lỗi kết nối database!";
        } else {
            $username_safe = mysqli_real_escape_string($conn, $username);
            $sql = "SELECT * FROM users WHERE username='$username_safe' AND role='admin'";
            $result = mysqli_query($conn, $sql);

            if (!$result) {
                $error = "Lỗi truy vấn DB: " . mysqli_error($conn);
            } elseif (mysqli_num_rows($result) == 0) {
                // Thử tìm user không phân biệt role để debug
                $check = mysqli_query($conn, "SELECT username, role, LEFT(password,20) as pass_pre FROM users WHERE username='$username_safe'");
                if ($check && mysqli_num_rows($check) > 0) {
                    $u = mysqli_fetch_assoc($check);
                    $error = "Tài khoản tồn tại nhưng không phải admin (role: {$u['role']})";
                } else {
                    $error = "Không tìm thấy tài khoản admin '$username'. Hãy chạy <a href='../../tools/debug_login.php'>debug_login.php</a> để reset.";
                }
            } else {
                $admin = mysqli_fetch_assoc($result);
                $verify_bcrypt = password_verify($password, $admin['password']);
                $verify_md5    = (md5($password) === $admin['password']);
                $verify_plain  = ($password === $admin['password']);

                if ($password === $admin['password']) {
                $_SESSION['admin'] = $admin;
                header('Location: dashboard.php');
                exit;
            } else {
                $error = "Sai mật khẩu!";
            }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Nhập Admin - Hệ Thống Tuyển Sinh</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body style="background: var(--grad); min-height: 100vh; display: flex; align-items: center; justify-content: center;">

    <div class="auth-box fade-in">
        <div style="text-align: center; margin-bottom: 30px;">
            <div style="font-size: 3rem; margin-bottom: 15px;">🛡️</div>
            <h2 style="font-weight: 700; color: var(--text-main);">ADMIN LOGIN</h2>
            <p style="color: var(--text-muted); font-size: 0.9rem;">Hệ thống quản trị tuyển sinh 2026</p>
        </div>

        <?php if ($error): ?>
            <div style="background: #fee2e2; color: #ef4444; padding: 12px; border-radius: 12px; margin-bottom: 20px; text-align: center; font-size: 0.9rem;">
                ⚠️ <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Tên đăng nhập</label>
                <input type="text" name="username" placeholder="Nhập username" required 
                       value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
            </div>
            <div class="form-group">
                <label>Mật khẩu</label>
                <input type="password" name="password" placeholder="Nhập mật khẩu" required>
            </div>
            <button type="submit" name="login" class="btn btn-primary btn-full" style="margin-top: 10px;">
                🔓 Đăng nhập hệ thống
            </button>
        </form>

        <div style="text-align: center; margin-top: 25px;">
            <a href="../../index.php" style="text-decoration: none; color: var(--primary); font-weight: 500;">← Quay về trang chủ</a>
        </div>
    </div>

</body>
</html>