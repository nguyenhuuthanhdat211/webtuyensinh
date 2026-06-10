<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}
include '../../config/database.php';

if (isset($_POST['add'])) {
    $ma = mysqli_real_escape_string($conn, $_POST['ma']);
    $ten = mysqli_real_escape_string($conn, $_POST['ten']);
    $mota = mysqli_real_escape_string($conn, $_POST['mota']);
    mysqli_query($conn, "INSERT INTO tohop_xettuyen (ma_tohop, ten_tohop, mo_ta) VALUES ('$ma', '$ten', '$mota')");
}

$res = mysqli_query($conn, "SELECT * FROM tohop_xettuyen");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Tổ Hợp - Admin</title>
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
            <a href="dottuyensinh.php" class="nav-item">📅 Đợt tuyển sinh</a>
            <a href="tohop.php" class="nav-item active">🧩 Tổ hợp môn</a>
            <a href="thisinh.php" class="nav-item">👥 Quản lý thí sinh</a>
            <a href="xettuyen.php" class="nav-item">⚖️ Xét tuyển</a>
            <a href="logout.php" class="nav-item" style="margin-top: 50px; color: #ef4444;">🚪 Đăng xuất</a>
        </nav>
    </div>

    <div class="main-content">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <h2 style="font-weight: 700;">🧩 Quản lý tổ hợp môn xét tuyển</h2>
            <button onclick="document.getElementById('addModal').style.display='flex'" class="btn btn-primary">➕ Thêm tổ hợp</button>
        </div>

        <div class="card fade-in">
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Mã Tổ Hợp</th>
                            <th>Tên Các Môn</th>
                            <th>Ghi chú</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($t = mysqli_fetch_assoc($res)): ?>
                            <tr>
                                <td><span style="background: var(--bg-light); padding: 5px 12px; border-radius: 5px; font-weight: 700; color: var(--primary);"><?php echo $t['ma_tohop']; ?></span></td>
                                <td><?php echo $t['ten_tohop']; ?></td>
                                <td><?php echo $t['mo_ta']; ?></td>
                                <td>
                                    <a href="#" style="color: var(--primary); margin-right: 15px;">Sửa</a>
                                    <a href="#" style="color: #ef4444;">Xóa</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="addModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1001; justify-content: center; align-items: center;">
        <div class="auth-box" style="max-width: 450px;">
            <h3 style="margin-bottom: 20px;">Thêm tổ hợp mới</h3>
            <form method="POST">
                <div class="form-group">
                    <label>Mã tổ hợp</label>
                    <input type="text" name="ma" required placeholder="VD: A00">
                </div>
                <div class="form-group">
                    <label>Tên các môn</label>
                    <input type="text" name="ten" required placeholder="VD: Toán, Lý, Hóa">
                </div>
                <div class="form-group">
                    <label>Mô tả</label>
                    <input type="text" name="mota">
                </div>
                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="submit" name="add" class="btn btn-primary" style="flex: 2;">💾 Lưu</button>
                    <button type="button" onclick="document.getElementById('addModal').style.display='none'" class="btn btn-secondary" style="flex: 1;">Hủy</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
