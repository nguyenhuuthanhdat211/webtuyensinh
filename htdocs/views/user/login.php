<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
include '../../config/database.php';

if (isset($_SESSION['user'])) {
    header('Location: ../../controllers/user/dangky_xettuyen.php');
    exit;
}

$error = '';

if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $u = mysqli_real_escape_string($conn, $username);

    $sql = "SELECT * FROM users WHERE username='$u' AND role='user'";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        if ($password === $row['password']) {
            $_SESSION['user'] = $row;
            header('Location: ../../controllers/user/dangky_xettuyen.php');
            exit;
        } else {
            $error = 'Sai tên đăng nhập hoặc mật khẩu!';
        }
    } else {
        $error = 'Sai tên đăng nhập hoặc mật khẩu!';
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Nhập - Hệ Thống Tuyển Sinh</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body style="background: var(--grad); min-height: 100vh; display: flex; align-items: center; justify-content: center;">

    <div class="auth-box fade-in">
        <div style="text-align: center; margin-bottom: 30px;">
            <div style="font-size: 3rem; margin-bottom: 15px;">🔐</div>
            <h2 style="font-weight: 700; color: var(--text-main);">ĐĂNG NHẬP</h2>
            <p style="color: var(--text-muted); font-size: 0.9rem;">Chào mừng bạn trở lại hệ thống tuyển sinh</p>
        </div>

        <?php if ($error): ?>
            <div style="background: #fee2e2; color: #ef4444; padding: 12px; border-radius: 12px; margin-bottom: 20px; text-align: center; font-size: 0.9rem;">
                ⚠️ <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Tên đăng nhập</label>
                <input type="text" name="username" placeholder="Nhập tên đăng nhập" required 
                       value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
            </div>
            <div class="form-group">
                <label>Mật khẩu</label>
                <input type="password" name="password" placeholder="Nhập mật khẩu" required>
            </div>
            <button type="submit" name="login" class="btn btn-primary btn-full" style="margin-top: 10px;">
                🔓 Đăng nhập ngay
            </button>
        </form>

        <div style="text-align: center; margin-top: 25px;">
            <p style="font-size: 0.9rem; color: var(--text-muted);">Chưa có tài khoản? <a href="../../controllers/user/register.php" style="color: var(--primary); font-weight: 600; text-decoration: none;">Đăng ký ngay</a></p>
            <div style="margin-top: 15px;">
                <a href="../../index.php" style="text-decoration: none; color: var(--text-muted); font-size: 0.85rem;">← Quay về trang chủ</a>
            </div>
        </div>
    </div>

</body>
</html>