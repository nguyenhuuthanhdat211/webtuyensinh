<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
include '../../config/database.php';

$error = '';
$success = '';

if (isset($_POST['register'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];
    $ho_ten = trim($_POST['ho_ten']);
    $email = trim($_POST['email']);

    if (empty($username) || empty($password) || empty($ho_ten)) {
        $error = 'Vui lòng điền đầy đủ thông tin bắt buộc!';
    } elseif (strlen($password) < 6) {
        $error = 'Mật khẩu phải có ít nhất 6 ký tự!';
    } elseif ($password !== $confirm) {
        $error = 'Mật khẩu xác nhận không khớp!';
    } else {
        $check = mysqli_query($conn, "SELECT id FROM users WHERE username='" . mysqli_real_escape_string($conn, $username) . "'");
        if (mysqli_num_rows($check) > 0) {
            $error = 'Tên đăng nhập đã tồn tại, vui lòng chọn tên khác!';
        } else {
            $sql = "INSERT INTO users(username, password, ho_ten, email, role) 
                    VALUES('$username', '$password', '$ho_ten', '$email', 'user')";
            if (mysqli_query($conn, $sql)) {
                $success = 'Đăng ký thành công! Bạn có thể đăng nhập ngay.';
            } else {
                $error = 'Có lỗi xảy ra, vui lòng thử lại!';
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
    <title>Đăng Ký - Hệ Thống Tuyển Sinh</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>

<body>
    <div class="auth-container">
        <div class="auth-box fade-in">
            <div class="auth-logo">
                <div class="logo-icon">🎓</div>
                <h2>Tạo tài khoản</h2>
                <p>Đăng ký để bắt đầu nộp hồ sơ xét tuyển</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger">⚠️ <?php echo $error; ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success">✅ <?php echo $success; ?>
                    <br><a href="../../views/user/login.php" class="text-link">→ Đăng nhập ngay</a>
                </div>
            <?php endif; ?>

            <?php if (!$success): ?>
                <form method="POST" id="registerForm">
                    <div class="form-group">
                        <label for="ho_ten">Họ và tên <span style="color:#ef4444">*</span></label>
                        <input type="text" id="ho_ten" name="ho_ten" placeholder="Nguyễn Văn A" required
                            value="<?php echo isset($_POST['ho_ten']) ? htmlspecialchars($_POST['ho_ten']) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label for="username">Tên đăng nhập <span style="color:#ef4444">*</span></label>
                        <input type="text" id="username" name="username" placeholder="nguyenvana" required
                            value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" placeholder="example@gmail.com"
                            value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label for="password">Mật khẩu <span style="color:#ef4444">*</span></label>
                        <input type="password" id="password" name="password" placeholder="Tối thiểu 6 ký tự" required>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Xác nhận mật khẩu <span style="color:#ef4444">*</span></label>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Nhập lại mật khẩu"
                            required>
                    </div>

                    <button type="submit" name="register" class="btn btn-primary btn-full" style="margin-top:8px">
                        🚀 Đăng ký tài khoản
                    </button>
                </form>
            <?php endif; ?>

            <div class="divider-text" style="margin-top:24px">Đã có tài khoản?</div>
            <div style="text-align:center">
                <a href="../../views/user/login.php" class="text-link">→ Đăng nhập ngay</a>
            </div>
            <div style="text-align:center; margin-top:12px">
                <a href="../../index.php" class="text-link">← Về trang chủ</a>
            </div>
        </div>
    </div>
</body>

</html>