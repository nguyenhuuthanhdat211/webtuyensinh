<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
mysqli_report(MYSQLI_REPORT_OFF);
include 'config/database.php';

// Đã đăng nhập rồi thì redirect
if (isset($_SESSION['admin'])) { header('Location: views/admin/dashboard.php'); exit; }
if (isset($_SESSION['user']))  { header('Location: controllers/user/dangky_xettuyen.php'); exit; }

$error_admin = '';
$error_user  = '';
$active_tab  = isset($_POST['tab']) ? $_POST['tab'] : (isset($_GET['tab']) ? $_GET['tab'] : 'user');

// ===== XỬ LÝ ĐĂNG NHẬP ADMIN =====
if (isset($_POST['login_admin'])) {
    $active_tab = 'admin';
    $username = trim($_POST['admin_username']);
    $password = trim($_POST['admin_password']);

    if (empty($username) || empty($password)) {
        $error_admin = "Vui lòng nhập đầy đủ thông tin!";
    } else {
        $u   = mysqli_real_escape_string($conn, $username);
        $res = mysqli_query($conn, "SELECT * FROM users WHERE username='$u' AND role='admin'");
        if ($res && mysqli_num_rows($res) > 0) {
            $admin = mysqli_fetch_assoc($res);
            // Kiểm tra cả 2 trường hợp: Khớp chữ thường HOẶC Khớp mã hóa Bcrypt
            if ($password === $admin['password'] || password_verify($password, $admin['password'])) {
                $_SESSION['admin'] = $admin;
                header('Location: views/admin/dashboard.php');
                exit;
            } else {
                $error_admin = "Sai mật khẩu quản trị viên!";
            }
        } else {
            $error_admin = "Tài khoản quản trị viên không tồn tại!";
        }
    }
}

// ===== XỬ LÝ ĐĂNG NHẬP THÍ SINH =====
if (isset($_POST['login_user'])) {
    $active_tab = 'user';
    $username = trim($_POST['user_username']);
    $password = trim($_POST['user_password']);

    if (empty($username) || empty($password)) {
        $error_user = "Vui lòng nhập đầy đủ thông tin!";
    } else {
        $u   = mysqli_real_escape_string($conn, $username);
        $res = mysqli_query($conn, "SELECT * FROM users WHERE username='$u' AND role='user'");
        if ($res && mysqli_num_rows($res) > 0) {
            $row = mysqli_fetch_assoc($res);
            // Kiểm tra cả 2 trường hợp: Khớp chữ thường HOẶC Khớp mã hóa Bcrypt
            if ($password === $row['password'] || password_verify($password, $row['password'])) {
                $_SESSION['user'] = $row;
                header('Location: controllers/user/dangky_xettuyen.php');
                exit;
            } else {
                $error_user = "Sai mật khẩu!";
            }
        } else {
            $error_user = "Tài khoản thí sinh không tồn tại!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Nhập - Hệ Thống Tuyển Sinh 2026</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            background: #0f172a;
            overflow: hidden;
        }

        /* ===== LEFT PANEL ===== */
        .left-panel {
            flex: 1;
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 40%, #06b6d4 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 60px 50px;
            position: relative;
            overflow: hidden;
        }
        .left-panel::before {
            content: '';
            position: absolute; inset: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Ccircle cx='30' cy='30' r='4'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
        .left-panel::after {
            content: '';
            position: absolute;
            width: 400px; height: 400px;
            background: rgba(255,255,255,0.06);
            border-radius: 50%;
            bottom: -100px; left: -100px;
        }
        .left-content { position: relative; z-index: 1; text-align: center; color: white; max-width: 420px; }
        .left-logo { font-size: 5rem; margin-bottom: 20px; filter: drop-shadow(0 4px 20px rgba(0,0,0,0.3)); }
        .left-title { font-size: 2.4rem; font-weight: 800; line-height: 1.2; margin-bottom: 16px; }
        .left-sub { font-size: 1rem; opacity: 0.85; line-height: 1.7; margin-bottom: 40px; }
        .left-features { text-align: left; }
        .feat-item {
            display: flex; align-items: center; gap: 12px;
            padding: 12px 0; border-bottom: 1px solid rgba(255,255,255,0.15);
            font-size: 0.9rem; opacity: 0.9;
        }
        .feat-item:last-child { border-bottom: none; }
        .feat-icon { width: 36px; height: 36px; background: rgba(255,255,255,0.15);
                     border-radius: 8px; display: flex; align-items: center; justify-content: center;
                     font-size: 1.1rem; flex-shrink: 0; }

        /* ===== RIGHT PANEL ===== */
        .right-panel {
            width: 500px;
            background: #ffffff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 50px 48px;
            overflow-y: auto;
        }
        .brand-top { display: flex; align-items: center; gap: 10px; margin-bottom: 40px; }
        .brand-dot { width: 10px; height: 10px; background: #3b82f6; border-radius: 50%; }
        .brand-name { font-size: 0.85rem; font-weight: 700; color: #1e293b; letter-spacing: .08em; text-transform: uppercase; }

        /* ===== TAB SWITCHER ===== */
        .tab-switcher {
            display: flex;
            background: #f1f5f9;
            border-radius: 14px;
            padding: 5px;
            margin-bottom: 36px;
            gap: 4px;
        }
        .tab-btn {
            flex: 1; padding: 12px 10px;
            border: none; border-radius: 10px;
            font-family: 'Inter', sans-serif;
            font-size: 0.88rem; font-weight: 600;
            cursor: pointer; transition: all 0.25s;
            display: flex; align-items: center; justify-content: center; gap: 7px;
            color: #64748b; background: transparent;
        }
        .tab-btn.active {
            background: white;
            color: #1e293b;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .tab-btn.active.admin-tab { color: #7c3aed; }
        .tab-btn.active.user-tab  { color: #2563eb; }

        /* ===== FORM PANELS ===== */
        .form-panel { display: none; animation: fadeSlide .3s ease; }
        .form-panel.show { display: block; }
        @keyframes fadeSlide {
            from { opacity: 0; transform: translateY(10px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .form-heading { margin-bottom: 28px; }
        .form-heading h2 { font-size: 1.65rem; font-weight: 800; color: #0f172a; margin-bottom: 6px; }
        .form-heading p  { font-size: 0.88rem; color: #64748b; }

        .form-group { margin-bottom: 18px; }
        .form-group label {
            display: block; font-size: 0.82rem; font-weight: 600;
            color: #374151; margin-bottom: 7px; letter-spacing: .01em;
        }
        .form-group .input-wrap { position: relative; }
        .form-group .input-icon {
            position: absolute; left: 14px; top: 50%;
            transform: translateY(-50%); font-size: 1rem;
            color: #94a3b8; pointer-events: none;
        }
        .form-group input {
            width: 100%; padding: 13px 16px 13px 44px;
            border: 2px solid #e2e8f0; border-radius: 12px;
            font-family: 'Inter', sans-serif; font-size: 0.92rem;
            color: #0f172a; background: #f8fafc;
            outline: none; transition: all 0.2s;
        }
        .form-group input:focus { border-color: #3b82f6; background: white; box-shadow: 0 0 0 4px rgba(59,130,246,0.08); }
        .form-panel.admin-panel .form-group input:focus { border-color: #7c3aed; box-shadow: 0 0 0 4px rgba(124,58,237,0.08); }

        /* Password toggle */
        .pass-toggle {
            position: absolute; right: 14px; top: 50%;
            transform: translateY(-50%); cursor: pointer;
            color: #94a3b8; font-size: 1.1rem; user-select: none;
            background: none; border: none; padding: 0;
        }
        .pass-toggle:hover { color: #475569; }

        /* Error */
        .error-box {
            background: #fef2f2; border: 1px solid #fecaca;
            color: #b91c1c; padding: 12px 16px; border-radius: 10px;
            font-size: 0.85rem; margin-bottom: 18px;
            display: flex; align-items: center; gap: 8px;
        }

        /* Submit button */
        .btn-submit {
            width: 100%; padding: 14px;
            border: none; border-radius: 12px;
            font-family: 'Inter', sans-serif;
            font-size: 0.95rem; font-weight: 700;
            cursor: pointer; transition: all 0.2s;
            display: flex; align-items: center; justify-content: center; gap: 8px;
            margin-top: 8px;
        }
        .btn-user-submit {
            background: linear-gradient(135deg, #2563eb, #3b82f6);
            color: white;
            box-shadow: 0 4px 14px rgba(59,130,246,0.4);
        }
        .btn-user-submit:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(59,130,246,0.5); }
        .btn-admin-submit {
            background: linear-gradient(135deg, #6d28d9, #7c3aed);
            color: white;
            box-shadow: 0 4px 14px rgba(124,58,237,0.4);
        }
        .btn-admin-submit:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(124,58,237,0.5); }

        /* Footer links */
        .form-footer { margin-top: 24px; text-align: center; font-size: 0.85rem; color: #64748b; }
        .form-footer a { color: #3b82f6; font-weight: 600; text-decoration: none; }
        .form-footer a:hover { text-decoration: underline; }
        .admin-panel .form-footer a { color: #7c3aed; }

        .divider { display: flex; align-items: center; gap: 12px; margin: 20px 0; color: #cbd5e1; font-size: 0.82rem; }
        .divider::before, .divider::after { content: ''; flex: 1; height: 1px; background: #e2e8f0; }

        .back-home { text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #f1f5f9; }
        .back-home a { color: #94a3b8; font-size: 0.82rem; text-decoration: none; }
        .back-home a:hover { color: #475569; }

        /* Admin badge */
        .admin-badge {
            display: inline-flex; align-items: center; gap: 6px;
            background: #f3f0ff; color: #6d28d9;
            padding: 6px 14px; border-radius: 20px;
            font-size: 0.78rem; font-weight: 600;
            margin-bottom: 20px;
        }

        @media (max-width: 900px) {
            .left-panel { display: none; }
            .right-panel { width: 100%; padding: 40px 30px; }
        }
    </style>
</head>
<body>

<!-- ===== LEFT PANEL ===== -->
<div class="left-panel">
    <div class="left-content">
        <div class="left-logo">🎓</div>
        <h1 class="left-title">Cổng Tuyển Sinh<br>Trực Tuyến 2026</h1>
        <p class="left-sub">Hệ thống tiếp nhận hồ sơ, xét tuyển và công bố kết quả hiện đại, minh bạch và nhanh chóng.</p>
        <div class="left-features">
            <div class="feat-item">
                <div class="feat-icon">📝</div>
                <div>Nộp hồ sơ xét tuyển trực tuyến dễ dàng</div>
            </div>
            <div class="feat-item">
                <div class="feat-icon">⚡</div>
                <div>Xét duyệt nhanh chóng, thông báo tức thì</div>
            </div>
            <div class="feat-item">
                <div class="feat-icon">📊</div>
                <div>Tra cứu kết quả minh bạch, rõ ràng</div>
            </div>
            <div class="feat-item">
                <div class="feat-icon">🔒</div>
                <div>Bảo mật thông tin thí sinh tuyệt đối</div>
            </div>
        </div>
    </div>
</div>

<!-- ===== RIGHT PANEL ===== -->
<div class="right-panel">
    <div class="brand-top">
        <div class="brand-dot"></div>
        <div class="brand-name">Hệ Thống Tuyển Sinh</div>
    </div>

    <!-- TAB SWITCHER -->
    <div class="tab-switcher">
        <button class="tab-btn user-tab  <?= $active_tab == 'user'  ? 'active' : '' ?>"
                onclick="switchTab('user')" type="button">
            🎓 Thí sinh
        </button>
        <button class="tab-btn admin-tab <?= $active_tab == 'admin' ? 'active' : '' ?>"
                onclick="switchTab('admin')" type="button">
            🛡️ Quản trị viên
        </button>
    </div>

    <!-- ===== FORM THÍ SINH ===== -->
    <div class="form-panel user-panel <?= $active_tab == 'user' ? 'show' : '' ?>" id="panel-user">
        <div class="form-heading">
            <h2>Chào mừng trở lại! 👋</h2>
            <p>Đăng nhập để nộp và theo dõi hồ sơ xét tuyển của bạn</p>
        </div>

        <?php if ($error_user): ?>
            <div class="error-box">⚠️ <?= htmlspecialchars($error_user) ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="tab" value="user">
            <div class="form-group">
                <label for="user_username">Tên đăng nhập</label>
                <div class="input-wrap">
                    <span class="input-icon">👤</span>
                    <input type="text" id="user_username" name="user_username"
                           placeholder="Nhập tên đăng nhập" required autocomplete="username"
                           value="<?= isset($_POST['user_username']) ? htmlspecialchars($_POST['user_username']) : '' ?>">
                </div>
            </div>
            <div class="form-group">
                <label for="user_password">Mật khẩu</label>
                <div class="input-wrap">
                    <span class="input-icon">🔑</span>
                    <input type="password" id="user_password" name="user_password"
                           placeholder="Nhập mật khẩu" required autocomplete="current-password">
                    <button type="button" class="pass-toggle" onclick="togglePass('user_password', this)">👁️</button>
                </div>
            </div>
            <button type="submit" name="login_user" class="btn-submit btn-user-submit">
                🚀 Đăng nhập thí sinh
            </button>
        </form>

        <div class="divider">hoặc</div>

        <div style="text-align:center">
            <a href="controllers/user/register.php" style="display:inline-flex;align-items:center;gap:8px;
               padding:12px 24px;border:2px solid #e2e8f0;border-radius:12px;
               text-decoration:none;color:#374151;font-size:0.88rem;font-weight:600;
               transition:all .2s" onmouseover="this.style.borderColor='#3b82f6'" onmouseout="this.style.borderColor='#e2e8f0'">
                ✏️ Chưa có tài khoản? Đăng ký ngay
            </a>
        </div>
    </div>

    <!-- ===== FORM QUẢN TRỊ VIÊN ===== -->
    <div class="form-panel admin-panel <?= $active_tab == 'admin' ? 'show' : '' ?>" id="panel-admin">
        <div class="form-heading">
            <div class="admin-badge">🛡️ Khu vực quản trị viên</div>
            <h2>Đăng nhập Admin</h2>
            <p>Truy cập hệ thống quản lý tuyển sinh 2026</p>
        </div>

        <?php if ($error_admin): ?>
            <div class="error-box">⚠️ <?= htmlspecialchars($error_admin) ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="tab" value="admin">
            <div class="form-group">
                <label for="admin_username">Tài khoản quản trị</label>
                <div class="input-wrap">
                    <span class="input-icon">👤</span>
                    <input type="text" id="admin_username" name="admin_username"
                           placeholder="Nhập username admin" required autocomplete="username"
                           value="<?= isset($_POST['admin_username']) ? htmlspecialchars($_POST['admin_username']) : '' ?>">
                </div>
            </div>
            <div class="form-group">
                <label for="admin_password">Mật khẩu quản trị</label>
                <div class="input-wrap">
                    <span class="input-icon">🔑</span>
                    <input type="password" id="admin_password" name="admin_password"
                           placeholder="Nhập mật khẩu" required autocomplete="current-password">
                    <button type="button" class="pass-toggle" onclick="togglePass('admin_password', this)">👁️</button>
                </div>
            </div>
            <button type="submit" name="login_admin" class="btn-submit btn-admin-submit">
                🔓 Vào hệ thống quản trị
            </button>
        </form>

        <div class="form-footer" style="margin-top:20px">
            <span style="color:#94a3b8;font-size:0.8rem">🔒 Khu vực này chỉ dành cho quản trị viên được ủy quyền</span>
        </div>
    </div>

    <!-- Back to home -->
    <div class="back-home">
        <a href="index.php">← Quay về trang chủ</a>
    </div>
</div>

<script>
function switchTab(tab) {
    // Update tab buttons
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    document.querySelector('.' + tab + '-tab').classList.add('active');

    // Update panels
    document.querySelectorAll('.form-panel').forEach(p => p.classList.remove('show'));
    document.getElementById('panel-' + tab).classList.add('show');
}

function togglePass(inputId, btn) {
    const inp = document.getElementById(inputId);
    if (inp.type === 'password') {
        inp.type = 'text';
        btn.textContent = '🙈';
    } else {
        inp.type = 'password';
        btn.textContent = '👁️';
    }
}

// Focus vào input đầu tiên của tab active
window.addEventListener('DOMContentLoaded', () => {
    const activePanel = document.querySelector('.form-panel.show');
    if (activePanel) {
        const first = activePanel.querySelector('input');
        if (first) setTimeout(() => first.focus(), 100);
    }
});
</script>
</body>
</html>
