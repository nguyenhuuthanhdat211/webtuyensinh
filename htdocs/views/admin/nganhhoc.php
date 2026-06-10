<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}
include '../../config/database.php';

// Xử lý thêm ngành
if (isset($_POST['add'])) {
    $ten = mysqli_real_escape_string($conn, $_POST['tennganh']);
    $ma = mysqli_real_escape_string($conn, $_POST['ma_nganh']);
    $chitieu = (int)$_POST['chitieu'];
    $mota = mysqli_real_escape_string($conn, $_POST['mo_ta']);
    
    mysqli_query($conn, "INSERT INTO nganhhoc (tennganh, ma_nganh, chitieu, mo_ta) VALUES ('$ten', '$ma', $chitieu, '$mota')");
}

// Xử lý xóa ngành
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM nganhhoc WHERE id = $id");
    header("Location: nganhhoc.php");
    exit();
}

$res = mysqli_query($conn, "SELECT * FROM nganhhoc ORDER BY ma_nganh");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Ngành Học - Admin</title>
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
            <a href="nganhhoc.php" class="nav-item active">🎓 Quản lý ngành</a>
            <a href="dottuyensinh.php" class="nav-item">📅 Đợt tuyển sinh</a>
            <a href="tohop.php" class="nav-item">🧩 Tổ hợp môn</a>
            <a href="thisinh.php" class="nav-item">👥 Quản lý thí sinh</a>
            <a href="xettuyen.php" class="nav-item">⚖️ Xét tuyển</a>
            <a href="logout.php" class="nav-item" style="margin-top: 50px; color: #ef4444;">🚪 Đăng xuất</a>
        </nav>
    </div>

    <div class="main-content">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <h2 style="font-weight: 700;">🎓 Quản lý danh mục ngành học</h2>
            <button onclick="document.getElementById('addModal').style.display='flex'" class="btn btn-primary">➕ Thêm ngành mới</button>
        </div>

        <div class="card fade-in">
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Mã Ngành</th>
                            <th>Tên Ngành Học</th>
                            <th>Chỉ Tiêu</th>
                            <th>Mô tả</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($n = mysqli_fetch_assoc($res)): ?>
                            <tr>
                                <td><strong><?php echo $n['ma_nganh']; ?></strong></td>
                                <td><?php echo $n['tennganh']; ?></td>
                                <td><?php echo $n['chitieu']; ?></td>
                                <td style="font-size: 0.8rem; color: var(--text-muted); max-width: 300px;"><?php echo $n['mo_ta']; ?></td>
                                <td>
                                    <a href="#" style="color: var(--primary); margin-right: 15px;">Sửa</a>
                                    <a href="nganhhoc.php?delete=<?php echo $n['id']; ?>" onclick="return confirm('Xóa ngành này?')" style="color: #ef4444;">Xóa</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Thêm ngành -->
    <div id="addModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1001; justify-content: center; align-items: center;">
        <div class="auth-box" style="max-width: 600px;">
            <h3 style="margin-bottom: 20px;">Thêm ngành học mới</h3>
            <form method="POST">
                <div class="cards-grid" style="padding: 0; grid-template-columns: 1fr 2fr; gap: 15px;">
                    <div class="form-group">
                        <label>Mã ngành</label>
                        <input type="text" name="ma_nganh" required placeholder="VD: CNTT">
                    </div>
                    <div class="form-group">
                        <label>Tên ngành</label>
                        <input type="text" name="tennganh" required placeholder="VD: Công nghệ thông tin">
                    </div>
                </div>
                <div class="form-group">
                    <label>Chỉ tiêu tuyển sinh</label>
                    <input type="number" name="chitieu" required value="50">
                </div>
                <div class="form-group">
                    <label>Mô tả ngành học</label>
                    <textarea name="mo_ta" style="width: 100%; padding: 10px; border-radius: 10px; border: 2px solid #e2e8f0;"></textarea>
                </div>
                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="submit" name="add" class="btn btn-primary" style="flex: 2;">💾 Lưu thông tin</button>
                    <button type="button" onclick="document.getElementById('addModal').style.display='none'" class="btn btn-secondary" style="flex: 1;">Hủy</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
