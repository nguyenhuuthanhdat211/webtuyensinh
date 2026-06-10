<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}
include '../../config/database.php';

// Xử lý xác định trúng tuyển (Tự động cập nhật dựa trên chỉ tiêu)
if (isset($_POST['execute'])) {
    $nganh_id = (int)$_POST['nganh_id'];
    $chitieu = (int)$_POST['chitieu'];
    
    // 1. Lấy danh sách hồ sơ Đã duyệt của ngành này, sắp xếp giảm dần theo điểm
    $sql = "SELECT id FROM hosoxettuyen 
            WHERE nganh_id = $nganh_id AND trangthai = 'Da duyet' 
            ORDER BY diem_tong DESC";
    $res = mysqli_query($conn, $sql);
    
    $count = 0;
    while($row = mysqli_fetch_assoc($res)) {
        $h_id = $row['id'];
        $count++;
        $new_status = ($count <= $chitieu) ? 'Trung tuyen' : 'Khong trung tuyen';
        mysqli_query($conn, "UPDATE hosoxettuyen SET trangthai = '$new_status' WHERE id = $h_id");
    }
    $success = "Đã thực hiện xét tuyển tự động cho ngành này.";
}

$nganhs = mysqli_query($conn, "SELECT * FROM nganhhoc");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xét Tuyển Tự Động - Admin</title>
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
            <a href="tohop.php" class="nav-item">🧩 Tổ hợp môn</a>
            <a href="thisinh.php" class="nav-item">👥 Quản lý thí sinh</a>
            <a href="xettuyen.php" class="nav-item active">⚖️ Xét tuyển</a>
            <a href="logout.php" class="nav-item" style="margin-top: 50px; color: #ef4444;">🚪 Đăng xuất</a>
        </nav>
    </div>

    <div class="main-content">
        <h2 style="margin-bottom: 30px; font-weight: 700;">⚖️ Quy trình xét tuyển tự động</h2>

        <?php if (isset($success)): ?>
            <div class="card" style="border-left: 5px solid #10b981; margin-bottom: 20px;">
                <p style="color: #10b981;">✅ <?php echo $success; ?></p>
            </div>
        <?php endif; ?>

        <div class="cards-grid" style="padding: 0; gap: 20px;">
            <?php while($n = mysqli_fetch_assoc($nganhs)): ?>
                <?php 
                    $nid = $n['id'];
                    $stats = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM hosoxettuyen WHERE nganh_id = $nid AND trangthai = 'Da duyet'"));
                    $trungtuyen = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM hosoxettuyen WHERE nganh_id = $nid AND trangthai = 'Trung tuyen'"));
                ?>
                <div class="card fade-in">
                    <h3 style="color: var(--primary);"><?php echo $n['tennganh']; ?></h3>
                    <p style="margin: 10px 0; font-size: 0.9rem;">Mã ngành: <strong><?php echo $n['ma_nganh']; ?></strong></p>
                    <hr style="margin: 15px 0; opacity: 0.1;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                        <span>Chỉ tiêu:</span>
                        <strong><?php echo $n['chitieu']; ?></strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                        <span>Hồ sơ đã duyệt:</span>
                        <strong><?php echo $stats['total']; ?></strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
                        <span>Đã trúng tuyển:</span>
                        <strong style="color: #10b981;"><?php echo $trungtuyen['total']; ?></strong>
                    </div>
                    
                    <form method="POST">
                        <input type="hidden" name="nganh_id" value="<?php echo $nid; ?>">
                        <input type="hidden" name="chitieu" value="<?php echo $n['chitieu']; ?>">
                        <button type="submit" name="execute" class="btn btn-secondary btn-full" style="font-size: 0.85rem;" 
                                onclick="return confirm('Hệ thống sẽ tự động xác định trúng tuyển cho top <?php echo $n['chitieu']; ?> thí sinh điểm cao nhất. Tiếp tục?')">
                            ⚙️ Chạy xét tuyển
                        </button>
                    </form>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</body>
</html>
