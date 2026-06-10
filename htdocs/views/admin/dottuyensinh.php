<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}
include '../../config/database.php';

if (isset($_POST['add'])) {
    $ten = mysqli_real_escape_string($conn, $_POST['ten']);
    $start = $_POST['start'];
    $end = $_POST['end'];
    mysqli_query($conn, "INSERT INTO dot_tuyensinh (ten_dot, ngay_bat_dau, ngay_ket_thuc) VALUES ('$ten', '$start', '$end')");
}

$res = mysqli_query($conn, "SELECT * FROM dot_tuyensinh ORDER BY ngay_bat_dau DESC");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Đợt Tuyển Sinh - Admin</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        body { display: flex; background: #f1f5f9; }
        .sidebar { width: 260px; height: 100vh; background: #0f172a; color: white; position: fixed; padding: 30px 20px; }
        .main-content { margin-left: 260px; flex: 1; padding: 40px; }
        .nav-item { display: block; padding: 12px 15px; color: rgba(255,255,255,0.7); text-decoration: none; border-radius: 10px; margin-bottom: 5px; }
        .nav-item:hover, .nav-item.active { background: rgba(255,255,255,0.1); color: white; }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>🛡️ ADMIN PANEL</h2>
        <nav>
            <a href="dashboard.php" class="nav-item">📊 Dashboard</a>
            <a href="hoso.php" class="nav-item">📄 Quản lý hồ sơ</a>
            <a href="nganhhoc.php" class="nav-item">🎓 Quản lý ngành</a>
            <a href="dottuyensinh.php" class="nav-item active">📅 Đợt tuyển sinh</a>
            <a href="tohop.php" class="nav-item">🧩 Tổ hợp môn</a>
            <a href="thisinh.php" class="nav-item">👥 Quản lý thí sinh</a>
            <a href="xettuyen.php" class="nav-item">⚖️ Xét tuyển</a>
            <a href="logout.php" class="nav-item" style="margin-top: 50px; color: #ef4444;">🚪 Đăng xuất</a>
        </nav>
    </div>

    <div class="main-content">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <h2 style="font-weight: 700;">📅 Quản lý các đợt tuyển sinh</h2>
            <button onclick="document.getElementById('addModal').style.display='flex'" class="btn btn-primary">➕ Thêm đợt mới</button>
        </div>

        <div class="card fade-in">
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Tên đợt tuyển sinh</th>
                            <th>Ngày bắt đầu</th>
                            <th>Ngày kết thúc</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($d = mysqli_fetch_assoc($res)): ?>
                            <tr>
                                <td><strong><?php echo $d['ten_dot']; ?></strong></td>
                                <td><?php echo date('d/m/Y', strtotime($d['ngay_bat_dau'])); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($d['ngay_ket_thuc'])); ?></td>
                                <td>
                                    <?php 
                                        $color = $d['trang_thai'] == 'Dang mo' ? '#10b981' : '#ef4444';
                                        echo "<span style='color: $color; font-weight: 600;'>● {$d['trang_thai']}</span>";
                                    ?>
                                </td>
                                <td>
                                    <a href="#" style="color: var(--primary); margin-right: 15px;">Sửa</a>
                                    <a href="#" style="color: #64748b;">Đóng đợt</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="addModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1001; justify-content: center; align-items: center;">
        <div class="auth-box" style="max-width: 500px;">
            <h3 style="margin-bottom: 20px;">Thêm đợt tuyển sinh mới</h3>
            <form method="POST">
                <div class="form-group">
                    <label>Tên đợt tuyển sinh</label>
                    <input type="text" name="ten" required placeholder="VD: Tuyển sinh học bạ Đợt 1 - 2026">
                </div>
                <div class="cards-grid" style="padding: 0; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label>Ngày bắt đầu</label>
                        <input type="date" name="start" required>
                    </div>
                    <div class="form-group">
                        <label>Ngày kết thúc</label>
                        <input type="date" name="end" required>
                    </div>
                </div>
                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="submit" name="add" class="btn btn-primary" style="flex: 2;">💾 Tạo đợt</button>
                    <button type="button" onclick="document.getElementById('addModal').style.display='none'" class="btn btn-secondary" style="flex: 1;">Hủy</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
