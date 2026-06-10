<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

// Tắt exception mode để tự xử lý lỗi
mysqli_report(MYSQLI_REPORT_OFF);

include '../../config/database.php';

// Kiểm tra bảng cần thiết có tồn tại không
$tables_required = ['hosoxettuyen', 'thisinh', 'nganhhoc', 'dot_tuyensinh', 'tohop_xettuyen'];
$missing = [];
foreach ($tables_required as $tbl) {
    $chk = mysqli_query($conn, "SHOW TABLES LIKE '$tbl'");
    if (!$chk || mysqli_num_rows($chk) == 0) {
        $missing[] = $tbl;
    }
}

if (!empty($missing)) {
    // Chuyển sang init_database để tạo bảng
    header('Location: ../../tools/init_database.php');
    exit();
}

// Xử lý duyệt/từ chối
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $status = $_GET['action'] == 'approve' ? 'Da duyet' : 'Tu choi';
    mysqli_query($conn, "UPDATE hosoxettuyen SET trangthai = '$status' WHERE id = $id");
    header("Location: hoso.php");
    exit();
}

$sql = "SELECT h.*, t.hoten, 
               COALESCE(t.cccd, '') AS cccd,
               n.tennganh, 
               COALESCE(d.ten_dot, 'Chưa xác định') AS ten_dot, 
               COALESCE(th.ma_tohop, 'N/A') AS ma_tohop
        FROM hosoxettuyen h
        LEFT JOIN thisinh t ON h.thisinh_id = t.id
        LEFT JOIN nganhhoc n ON h.nganh_id = n.id
        LEFT JOIN dot_tuyensinh d ON h.dot_id = d.id
        LEFT JOIN tohop_xettuyen th ON h.tohop_id = th.id
        ORDER BY h.created_at DESC";
$res = mysqli_query($conn, $sql);
if (!$res) {
    die("<div style='padding:30px;font-family:Arial'>
        <h3 style='color:red'>❌ Lỗi truy vấn: " . mysqli_error($conn) . "</h3>
        <p><a href='../../tools/fix_columns.php' style='background:#3b82f6;color:white;padding:10px 20px;border-radius:6px;text-decoration:none'>→ Nhấn đây để tự động sửa cấu trúc bảng</a></p>
    </div>");
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Hồ Sơ - Hệ Thống Tuyển Sinh</title>
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
            <a href="hoso.php" class="nav-item active">📄 Quản lý hồ sơ</a>
            <a href="nganhhoc.php" class="nav-item">🎓 Quản lý ngành</a>
            <a href="dottuyensinh.php" class="nav-item">📅 Đợt tuyển sinh</a>
            <a href="tohop.php" class="nav-item">🧩 Tổ hợp môn</a>
            <a href="thisinh.php" class="nav-item">👥 Quản lý thí sinh</a>
            <a href="xettuyen.php" class="nav-item">⚖️ Xét tuyển</a>
            <a href="logout.php" class="nav-item" style="margin-top: 50px; color: #ef4444;">🚪 Đăng xuất</a>
        </nav>
    </div>

    <div class="main-content">
        <h2 style="margin-bottom: 30px; font-weight: 700;">📄 Tiếp nhận hồ sơ xét tuyển</h2>

        <div class="card fade-in">
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Thí sinh</th>
                            <th>Ngành học</th>
                            <th>Tổ hợp</th>
                            <th>Điểm</th>
                            <th>Trạng thái</th>
                            <th>Tài liệu</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $has_rows = false;
                        while($h = mysqli_fetch_assoc($res)):
                            $has_rows = true;
                        ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($h['hoten'] ?? 'N/A'); ?></strong><br>
                                    <small style="color: var(--text-muted);"><?php echo htmlspecialchars($h['cccd'] ?? ''); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($h['tennganh'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($h['ma_tohop'] ?? 'N/A'); ?></td>
                                <td><strong style="color: var(--primary);"><?php echo number_format($h['diem_tong'], 1); ?></strong></td>
                                <td>
                                    <?php 
                                        $st = $h['trangthai'];
                                        $color = "#64748b";
                                        if ($st == 'Da duyet' || $st == 'Trung tuyen') $color = "#10b981";
                                        if ($st == 'Tu choi' || $st == 'Khong trung tuyen') $color = "#ef4444";
                                        if ($st == 'Cho duyet') $color = "#f59e0b";
                                        echo "<span style='background: $color; color: white; padding: 4px 10px; border-radius: 50px; font-size: 0.8rem;'>$st</span>";
                                    ?>
                                </td>
                                <td>
                                    <?php if ($h['file_hocba']): ?>
                                        <a href="/uploads/hocba/<?php echo $h['file_hocba']; ?>" target="_blank" style="color: var(--primary); text-decoration: none;">👁️ Xem học bạ</a>
                                    <?php else: ?>
                                        <span style="color: var(--text-muted);">Không có file</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($st == 'Cho duyet'): ?>
                                        <a href="hoso.php?action=approve&id=<?php echo $h['id']; ?>" class="btn" style="padding: 5px 12px; font-size: 0.8rem; background: #10b981; color: white; text-decoration: none; border-radius: 5px; margin-right:4px;">✅ Duyệt</a><br>
                                        <a href="hoso.php?action=reject&id=<?php echo $h['id']; ?>" class="btn" onclick="return confirm('Xác nhận từ chối hồ sơ này?')" style="padding: 5px 12px; font-size: 0.8rem; background: #ef4444; color: white; text-decoration: none; border-radius: 5px;">❌ Từ chối</a>
                                    <?php else: ?>
                                        <span style="color: var(--text-muted);">Đã xử lý</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        <?php if (!$has_rows): ?>
                            <tr>
                                <td colspan="7" style="text-align:center; padding: 50px 20px; color: var(--text-muted);">
                                    <div style="font-size:3rem;">📭</div>
                                    <div style="margin-top:10px; font-size:1.1rem;">Chưa có hồ sơ xét tuyển nào</div>
                                    <div style="font-size:0.85rem; margin-top:5px;">Hồ sơ sẽ xuất hiện khi thí sinh nộp đăng ký</div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
