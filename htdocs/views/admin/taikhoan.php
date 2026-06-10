<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
if (!isset($_SESSION['admin'])) { header("Location: login.php"); exit(); }
include '../../config/database.php';

// Xử lý đổi quyền/khóa tài khoản
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $act = $_GET['action'];
    if ($act == 'make_admin') mysqli_query($conn, "UPDATE users SET role='admin' WHERE id=$id");
    if ($act == 'make_user')  mysqli_query($conn, "UPDATE users SET role='user' WHERE id=$id");
    if ($act == 'toggle_status') {
        $u = mysqli_fetch_assoc(mysqli_query($conn, "SELECT status FROM users WHERE id=$id"));
        $new_st = ($u['status'] == 'Active' || $u['status'] == '') ? 'Locked' : 'Active';
        mysqli_query($conn, "UPDATE users SET status='$new_st' WHERE id=$id");
    }
    header("Location: taikhoan.php"); exit();
}

$res = mysqli_query($conn, "SELECT * FROM users ORDER BY role ASC, id DESC");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản Lý Tài Khoản - Admin</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        body { display: flex; background: #f1f5f9; min-height: 100vh; }
        .sidebar { width: 260px; background: #0f172a; color: white; padding: 30px 20px; position: fixed; height: 100vh; }
        .main { margin-left: 260px; flex: 1; padding: 40px; }
        .nav-item { display: block; padding: 12px 15px; color: rgba(255,255,255,0.7); text-decoration: none; border-radius: 10px; margin-bottom: 5px; }
        .nav-item.active { background: rgba(255,255,255,0.1); color: white; }
        .card { background: white; padding: 30px; border-radius: 20px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { text-align: left; padding: 15px; border-bottom: 2px solid #f1f5f9; color: #64748b; font-size: 0.85rem; text-transform: uppercase; }
        td { padding: 15px; border-bottom: 1px solid #f1f5f9; font-size: 0.95rem; }
        .badge { padding: 4px 10px; border-radius: 50px; font-size: 0.75rem; font-weight: 700; }
        .badge-admin { background: #fee2e2; color: #ef4444; }
        .badge-user { background: #dcfce7; color: #10b981; }
        .btn-act { padding: 6px 12px; border-radius: 8px; text-decoration: none; font-size: 0.8rem; margin-right: 5px; border: 1px solid #e2e8f0; color: #475569; }
        .btn-act:hover { background: #f8fafc; }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>🛡️ ADMIN PANEL</h2>
        <nav>
            <a href="dashboard.php" class="nav-item">📊 Dashboard</a>
            <a href="hoso.php" class="nav-item">📄 Quản lý hồ sơ</a>
            <a href="thisinh.php" class="nav-item">👥 Quản lý thí sinh</a>
            <a href="taikhoan.php" class="nav-item active">🔑 Quản lý tài khoản</a>
            <a href="logout.php" class="nav-item" style="margin-top:50px; color:#f87171;">🚪 Đăng xuất</a>
        </nav>
    </div>
    <div class="main">
        <div class="card">
            <h2 style="margin-bottom: 10px;">🔑 Quản lý Tài khoản & Phân quyền</h2>
            <p style="color: #64748b; margin-bottom: 30px;">Danh sách tất cả người dùng và quản trị viên trong hệ thống.</p>
            <table>
                <thead>
                    <tr>
                        <th>Người dùng</th>
                        <th>Vai trò</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($u = mysqli_fetch_assoc($res)): ?>
                    <tr>
                        <td>
                            <strong><?php echo $u['username']; ?></strong><br>
                            <small style="color: #94a3b8;"><?php echo $u['ho_ten']; ?></small>
                        </td>
                        <td>
                            <span class="badge <?php echo ($u['role']=='admin')?'badge-admin':'badge-user'; ?>">
                                <?php echo strtoupper($u['role']); ?>
                            </span>
                        </td>
                        <td>
                            <?php if($u['status'] == 'Locked'): ?>
                                <span style="color: #ef4444;">🔴 Đã khóa</span>
                            <?php else: ?>
                                <span style="color: #10b981;">🟢 Đang hoạt động</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if($u['role'] == 'user'): ?>
                                <a href="?action=make_admin&id=<?php echo $u['id']; ?>" class="btn-act">Cấp quyền Admin</a>
                            <?php else: ?>
                                <a href="?action=make_user&id=<?php echo $u['id']; ?>" class="btn-act">Hạ quyền User</a>
                            <?php endif; ?>
                            
                            <a href="?action=toggle_status&id=<?php echo $u['id']; ?>" class="btn-act">
                                <?php echo ($u['status']=='Locked')?'Mở khóa':'Khóa tài khoản'; ?>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
