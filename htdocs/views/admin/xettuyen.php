<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}
include '../../config/database.php';

function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function fetch_all_rows($result) {
    $rows = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
    }
    return $rows;
}

$success = '';
$error = '';

mysqli_query($conn, "CREATE TABLE IF NOT EXISTS dot_nganh_tohop (
    id INT AUTO_INCREMENT PRIMARY KEY,
    dot_id INT NOT NULL,
    nganh_id INT NOT NULL,
    tohop_id INT NOT NULL,
    diem_san DECIMAL(4,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_dot_nganh_tohop (dot_id, nganh_id, tohop_id),
    FOREIGN KEY (dot_id) REFERENCES dot_tuyensinh(id) ON DELETE CASCADE,
    FOREIGN KEY (nganh_id) REFERENCES nganhhoc(id) ON DELETE CASCADE,
    FOREIGN KEY (tohop_id) REFERENCES tohop_xettuyen(id) ON DELETE CASCADE
) ENGINE=InnoDB CHARSET=utf8mb4");

if (isset($_POST['save_config'])) {
    $dot_id = (int)$_POST['dot_id'];
    $nganh_id = (int)$_POST['nganh_id'];
    $tohop_id = (int)$_POST['tohop_id'];
    $diem_san = (float)str_replace(',', '.', $_POST['diem_san']);

    if ($dot_id <= 0 || $nganh_id <= 0 || $tohop_id <= 0) {
        $error = "Vui lòng chọn đủ đợt tuyển sinh, ngành và tổ hợp.";
    } elseif ($diem_san < 0 || $diem_san > 30) {
        $error = "Điểm sàn phải nằm trong khoảng 0 đến 30.";
    } else {
        $sql = "INSERT INTO dot_nganh_tohop (dot_id, nganh_id, tohop_id, diem_san)
                VALUES ($dot_id, $nganh_id, $tohop_id, $diem_san)
                ON DUPLICATE KEY UPDATE diem_san = VALUES(diem_san)";
        if (mysqli_query($conn, $sql)) {
            mysqli_query($conn, "INSERT IGNORE INTO nganh_tohop (nganh_id, tohop_id) VALUES ($nganh_id, $tohop_id)");
            $success = "Đã lưu cấu hình xét tuyển.";
        } else {
            $error = "Lỗi lưu cấu hình: " . mysqli_error($conn);
        }
    }
}

if (isset($_GET['delete_config'])) {
    $id = (int)$_GET['delete_config'];
    mysqli_query($conn, "DELETE FROM dot_nganh_tohop WHERE id = $id");
    header("Location: xettuyen.php");
    exit();
}

if (isset($_POST['execute'])) {
    $dot_id = (int)$_POST['dot_id'];
    $nganh_id = (int)$_POST['nganh_id'];
    $nganh = mysqli_fetch_assoc(mysqli_query($conn, "SELECT chitieu FROM nganhhoc WHERE id = $nganh_id"));
    $chitieu = (int)($nganh['chitieu'] ?? 0);

    $below_sql = "UPDATE hosoxettuyen h
                  JOIN dot_nganh_tohop c
                    ON c.dot_id = h.dot_id AND c.nganh_id = h.nganh_id AND c.tohop_id = h.tohop_id
                  SET h.trangthai = 'Khong trung tuyen'
                  WHERE h.dot_id = $dot_id
                    AND h.nganh_id = $nganh_id
                    AND h.trangthai IN ('Da duyet','Trung tuyen','Khong trung tuyen')
                    AND h.diem_tong < c.diem_san";
    mysqli_query($conn, $below_sql);

    $sql = "SELECT h.id
            FROM hosoxettuyen h
            JOIN dot_nganh_tohop c
              ON c.dot_id = h.dot_id AND c.nganh_id = h.nganh_id AND c.tohop_id = h.tohop_id
            WHERE h.dot_id = $dot_id
              AND h.nganh_id = $nganh_id
              AND h.trangthai IN ('Da duyet','Trung tuyen','Khong trung tuyen')
              AND h.diem_tong >= c.diem_san
            ORDER BY h.diem_tong DESC, h.created_at ASC";
    $res = mysqli_query($conn, $sql);

    $count = 0;
    while ($row = mysqli_fetch_assoc($res)) {
        $count++;
        $new_status = ($count <= $chitieu) ? 'Trung tuyen' : 'Khong trung tuyen';
        mysqli_query($conn, "UPDATE hosoxettuyen SET trangthai = '$new_status' WHERE id = " . (int)$row['id']);
    }
    $success = "Đã xét tuyển theo đợt, ngành, tổ hợp và điểm sàn.";
}

$dots = fetch_all_rows(mysqli_query($conn, "SELECT * FROM dot_tuyensinh ORDER BY ngay_bat_dau DESC, id DESC"));
$nganhs = fetch_all_rows(mysqli_query($conn, "SELECT * FROM nganhhoc ORDER BY ma_nganh"));
$tohops = fetch_all_rows(mysqli_query($conn, "SELECT * FROM tohop_xettuyen ORDER BY ma_tohop"));

$configs = mysqli_query($conn, "SELECT c.*, d.ten_dot, d.trang_thai, n.ma_nganh, n.tennganh, th.ma_tohop, th.ten_tohop,
                                      COUNT(h.id) AS so_hoso,
                                      SUM(CASE WHEN h.id IS NOT NULL AND h.diem_tong >= c.diem_san THEN 1 ELSE 0 END) AS dat_san
                               FROM dot_nganh_tohop c
                               JOIN dot_tuyensinh d ON c.dot_id = d.id
                               JOIN nganhhoc n ON c.nganh_id = n.id
                               JOIN tohop_xettuyen th ON c.tohop_id = th.id
                               LEFT JOIN hosoxettuyen h
                                 ON h.dot_id = c.dot_id AND h.nganh_id = c.nganh_id AND h.tohop_id = c.tohop_id
                               GROUP BY c.id, c.dot_id, c.nganh_id, c.tohop_id, c.diem_san, c.created_at,
                                        d.ten_dot, d.trang_thai, n.ma_nganh, n.tennganh, th.ma_tohop, th.ten_tohop
                               ORDER BY d.ngay_bat_dau DESC, n.ma_nganh, th.ma_tohop");

$groups = mysqli_query($conn, "SELECT c.dot_id, c.nganh_id, d.ten_dot, n.ma_nganh, n.tennganh, n.chitieu,
                                      COUNT(DISTINCT c.tohop_id) AS so_tohop,
                                      COUNT(h.id) AS so_hoso,
                                      SUM(CASE WHEN h.trangthai = 'Da duyet' THEN 1 ELSE 0 END) AS da_duyet,
                                      SUM(CASE WHEN h.trangthai = 'Trung tuyen' THEN 1 ELSE 0 END) AS trung_tuyen
                               FROM dot_nganh_tohop c
                               JOIN dot_tuyensinh d ON c.dot_id = d.id
                               JOIN nganhhoc n ON c.nganh_id = n.id
                               LEFT JOIN hosoxettuyen h ON h.dot_id = c.dot_id AND h.nganh_id = c.nganh_id
                               GROUP BY c.dot_id, c.nganh_id, d.ten_dot, n.ma_nganh, n.tennganh, n.chitieu
                               ORDER BY d.ngay_bat_dau DESC, n.ma_nganh");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cấu hình Xét Tuyển - Admin</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        body { display: flex; background: #f1f5f9; }
        .sidebar { width: 260px; height: 100vh; background: #0f172a; color: white; position: fixed; padding: 30px 20px; }
        .main-content { margin-left: 260px; flex: 1; padding: 40px; }
        .nav-item { display: block; padding: 12px 15px; color: rgba(255,255,255,0.7); text-decoration: none; border-radius: 10px; margin-bottom: 5px; }
        .nav-item:hover, .nav-item.active { background: rgba(255,255,255,0.1); color: white; }
        .inline-form { display: grid; grid-template-columns: repeat(4, minmax(150px, 1fr)) auto; gap: 12px; align-items: end; }
        .alert { padding: 14px 16px; border-radius: 10px; margin-bottom: 18px; font-weight: 600; }
        .alert-ok { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
        .alert-err { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }
        .pill { display: inline-block; padding: 4px 10px; border-radius: 999px; background: #e0f2fe; color: #0369a1; font-weight: 700; font-size: 0.85rem; }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>ADMIN PANEL</h2>
        <nav>
            <a href="dashboard.php" class="nav-item">Dashboard</a>
            <a href="hoso.php" class="nav-item">Quản lý hồ sơ</a>
            <a href="nganhhoc.php" class="nav-item">Quản lý ngành</a>
            <a href="dottuyensinh.php" class="nav-item">Đợt tuyển sinh</a>
            <a href="tohop.php" class="nav-item">Tổ hợp môn</a>
            <a href="thisinh.php" class="nav-item">Quản lý thí sinh</a>
            <a href="xettuyen.php" class="nav-item active">Xét tuyển</a>
            <a href="logout.php" class="nav-item" style="margin-top: 50px; color: #ef4444;">Đăng xuất</a>
        </nav>
    </div>

    <div class="main-content">
        <h2 style="margin-bottom: 24px; font-weight: 700;">Cấu hình xét tuyển theo đợt, ngành và tổ hợp</h2>

        <?php if ($success): ?><div class="alert alert-ok"><?php echo h($success); ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-err"><?php echo h($error); ?></div><?php endif; ?>

        <div class="card fade-in" style="margin-bottom: 24px;">
            <h3 style="margin-bottom: 18px; color: var(--primary);">Thêm hoặc cập nhật điểm sàn</h3>
            <form method="POST" class="inline-form">
                <div class="form-group">
                    <label>Đợt tuyển sinh</label>
                    <select name="dot_id" required>
                        <option value="">-- Chọn đợt --</option>
                        <?php foreach ($dots as $dot): ?>
                            <option value="<?php echo (int)$dot['id']; ?>"><?php echo h($dot['ten_dot']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Ngành</label>
                    <select name="nganh_id" required>
                        <option value="">-- Chọn ngành --</option>
                        <?php foreach ($nganhs as $nganh): ?>
                            <option value="<?php echo (int)$nganh['id']; ?>"><?php echo h($nganh['ma_nganh'] . ' - ' . $nganh['tennganh']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Tổ hợp</label>
                    <select name="tohop_id" required>
                        <option value="">-- Chọn tổ hợp --</option>
                        <?php foreach ($tohops as $tohop): ?>
                            <option value="<?php echo (int)$tohop['id']; ?>"><?php echo h($tohop['ma_tohop'] . ' - ' . $tohop['ten_tohop']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Điểm sàn</label>
                    <input type="number" name="diem_san" step="0.01" min="0" max="30" required placeholder="VD: 18.00">
                </div>
                <button type="submit" name="save_config" class="btn btn-primary" style="height: 48px;">Lưu</button>
            </form>
        </div>

        <div class="card fade-in" style="margin-bottom: 24px;">
            <h3 style="margin-bottom: 18px; color: var(--primary);">Danh sách cấu hình</h3>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Đợt</th>
                            <th>Ngành</th>
                            <th>Tổ hợp</th>
                            <th>Điểm sàn</th>
                            <th>Hồ sơ</th>
                            <th>Đạt sàn</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $has_config = false; ?>
                        <?php while ($cfg = mysqli_fetch_assoc($configs)): $has_config = true; ?>
                            <tr>
                                <td><?php echo h($cfg['ten_dot']); ?></td>
                                <td><strong><?php echo h($cfg['ma_nganh']); ?></strong><br><small><?php echo h($cfg['tennganh']); ?></small></td>
                                <td><span class="pill"><?php echo h($cfg['ma_tohop']); ?></span><br><small><?php echo h($cfg['ten_tohop']); ?></small></td>
                                <td><strong style="color: var(--primary);"><?php echo number_format((float)$cfg['diem_san'], 2); ?></strong></td>
                                <td><?php echo (int)$cfg['so_hoso']; ?></td>
                                <td><?php echo (int)$cfg['dat_san']; ?></td>
                                <td>
                                    <a href="xettuyen.php?delete_config=<?php echo (int)$cfg['id']; ?>"
                                       onclick="return confirm('Xóa cấu hình này?')"
                                       style="color: #ef4444;">Xóa</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        <?php if (!$has_config): ?>
                            <tr><td colspan="7" style="text-align:center; padding: 30px; color: var(--text-muted);">Chưa có cấu hình xét tuyển.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <h3 style="margin: 28px 0 16px; color: #1e293b;">Chạy xét tuyển</h3>
        <div class="cards-grid" style="padding: 0; gap: 20px;">
            <?php $has_group = false; ?>
            <?php while ($group = mysqli_fetch_assoc($groups)): $has_group = true; ?>
                <div class="card fade-in">
                    <h3 style="color: var(--primary);"><?php echo h($group['tennganh']); ?></h3>
                    <p style="margin: 10px 0; font-size: 0.9rem;">Đợt: <strong><?php echo h($group['ten_dot']); ?></strong></p>
                    <p style="margin: 10px 0; font-size: 0.9rem;">Mã ngành: <strong><?php echo h($group['ma_nganh']); ?></strong></p>
                    <hr style="margin: 15px 0; opacity: 0.1;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                        <span>Tổ hợp đang mở:</span>
                        <strong><?php echo (int)$group['so_tohop']; ?></strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                        <span>Chỉ tiêu ngành:</span>
                        <strong><?php echo (int)$group['chitieu']; ?></strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                        <span>Hồ sơ đã duyệt:</span>
                        <strong><?php echo (int)$group['da_duyet']; ?></strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
                        <span>Đã trúng tuyển:</span>
                        <strong style="color: #10b981;"><?php echo (int)$group['trung_tuyen']; ?></strong>
                    </div>
                    <form method="POST">
                        <input type="hidden" name="dot_id" value="<?php echo (int)$group['dot_id']; ?>">
                        <input type="hidden" name="nganh_id" value="<?php echo (int)$group['nganh_id']; ?>">
                        <button type="submit" name="execute" class="btn btn-secondary btn-full" style="font-size: 0.85rem;"
                                onclick="return confirm('Chạy xét tuyển cho đợt và ngành này? Hồ sơ dưới điểm sàn sẽ không trúng tuyển.')">
                            Chạy xét tuyển
                        </button>
                    </form>
                </div>
            <?php endwhile; ?>
            <?php if (!$has_group): ?>
                <div class="card" style="color: var(--text-muted);">Cần tạo cấu hình điểm sàn trước khi chạy xét tuyển.</div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
