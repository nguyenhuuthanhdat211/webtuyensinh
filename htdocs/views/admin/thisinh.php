<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}
mysqli_report(MYSQLI_REPORT_OFF);
include '../../config/database.php';

// Xử lý xóa thí sinh
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM hosoxettuyen WHERE thisinh_id = $id");
    mysqli_query($conn, "DELETE FROM thisinh WHERE id = $id");
    header("Location: thisinh.php?msg=deleted");
    exit();
}

// Tìm kiếm
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$where  = '';
if ($search) {
    $s = mysqli_real_escape_string($conn, $search);
    $where = "WHERE ts.hoten LIKE '%$s%' OR COALESCE(ts.email,'') LIKE '%$s%' OR COALESCE(ts.sdt,'') LIKE '%$s%' OR COALESCE(ts.cccd,'') LIKE '%$s%'";
}

$sql    = "SELECT ts.*, u.username,
           (SELECT COUNT(*) FROM hosoxettuyen h WHERE h.thisinh_id = ts.id) AS so_hoso
           FROM thisinh ts
           LEFT JOIN users u ON ts.user_id = u.id
           $where
           ORDER BY ts.id DESC";
$result = mysqli_query($conn, $sql);
if (!$result) {
    die("<p style='color:red;padding:20px'>Lỗi: " . mysqli_error($conn) . "</p>");
}
$total = mysqli_num_rows($result);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Thí Sinh - Hệ Thống Tuyển Sinh</title>
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
        <a href="dashboard.php"   class="nav-item">📊 Dashboard</a>
        <a href="hoso.php"        class="nav-item">📄 Quản lý hồ sơ</a>
        <a href="nganhhoc.php"    class="nav-item">🎓 Quản lý ngành</a>
        <a href="dottuyensinh.php" class="nav-item">📅 Đợt tuyển sinh</a>
        <a href="tohop.php"       class="nav-item">🧩 Tổ hợp môn</a>
        <a href="thisinh.php"     class="nav-item active">👥 Quản lý thí sinh</a>
        <a href="xettuyen.php"    class="nav-item">⚖️ Xét tuyển</a>
        <a href="logout.php"      class="nav-item" style="margin-top:50px;color:#f87171;">🚪 Đăng xuất</a>
    </nav>
</div>

<div class="main-content">
    <div class="page-header">
        <div>
            <div class="page-title">👥 Quản lý Thí sinh</div>
            <div class="page-sub">Danh sách thí sinh đã đăng ký trong hệ thống</div>
        </div>
    </div>

    <?php if (isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
        <div class="alert-success">✅ Đã xóa thí sinh thành công!</div>
    <?php endif; ?>

    <div class="stats-row">
        <div class="stat-card">
            <div class="num"><?= $total ?></div>
            <div class="lbl">Tổng số thí sinh</div>
        </div>
        <?php
        $r_hoso = mysqli_query($conn, "SELECT COUNT(*) as c FROM hosoxettuyen");
        $c_hoso = mysqli_fetch_assoc($r_hoso)['c'] ?? 0;
        ?>
        <div class="stat-card" style="border-left-color:#10b981">
            <div class="num" style="color:#10b981"><?= $c_hoso ?></div>
            <div class="lbl">Tổng hồ sơ đã nộp</div>
        </div>
        <?php
        $r_cho = mysqli_query($conn, "SELECT COUNT(*) as c FROM hosoxettuyen WHERE trangthai='Cho duyet'");
        $c_cho = mysqli_fetch_assoc($r_cho)['c'] ?? 0;
        ?>
        <div class="stat-card" style="border-left-color:#f59e0b">
            <div class="num" style="color:#f59e0b"><?= $c_cho ?></div>
            <div class="lbl">Hồ sơ chờ duyệt</div>
        </div>
    </div>

    <div class="toolbar">
        <form method="GET" style="display:flex;gap:10px;flex:1">
            <div class="search-box">
                <span class="icon">🔍</span>
                <input type="text" name="search" placeholder="Tìm theo tên, email, SĐT, CCCD..."
                       value="<?= htmlspecialchars($search) ?>">
            </div>
            <button type="submit" class="btn-primary-sm" style="border:none;cursor:pointer">Tìm kiếm</button>
            <?php if ($search): ?>
                <a href="thisinh.php" class="btn-primary-sm" style="background:#64748b">✕ Xóa lọc</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="card">
        <table>
            <thead>
                <tr>
                    <th style="width:45px">STT</th>
                    <th>Thí sinh</th>
                    <th>Tài khoản</th>
                    <th>Liên hệ</th>
                    <th>CCCD</th>
                    <th>Số hồ sơ</th>
                    <th style="text-align:center">Thao tác</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $stt = 1;
            $has = false;
            // Reset result pointer
            mysqli_data_seek($result, 0);
            while ($row = mysqli_fetch_assoc($result)):
                $has = true;
                $initial = mb_strtoupper(mb_substr($row['hoten'], 0, 1, 'UTF-8'), 'UTF-8');
                $ngaysinh = (!empty($row['ngaysinh']) && $row['ngaysinh'] != '0000-00-00')
                    ? date('d/m/Y', strtotime($row['ngaysinh'])) : 'N/A';
            ?>
                <tr>
                    <td style="color:#94a3b8;font-weight:600"><?= $stt++ ?></td>
                    <td>
                        <div style="display:flex;align-items:center;gap:12px">
                            <div class="avatar"><?= $initial ?></div>
                            <div>
                                <div style="font-weight:600;color:#1e293b"><?= htmlspecialchars($row['hoten']) ?></div>
                                <div style="font-size:0.78rem;color:#94a3b8">NS: <?= $ngaysinh ?></div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <?php if ($row['username']): ?>
                            <span class="badge-user">@<?= htmlspecialchars($row['username']) ?></span>
                        <?php else: ?>
                            <span class="badge-noaccount">Chưa có TK</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div style="font-size:0.85rem">📞 <?= htmlspecialchars($row['sdt'] ?? 'N/A') ?></div>
                        <div style="font-size:0.85rem;color:#64748b">✉️ <?= htmlspecialchars($row['email'] ?? 'N/A') ?></div>
                    </td>
                    <td style="font-size:0.85rem;color:#475569"><?= htmlspecialchars($row['cccd'] ?? 'N/A') ?></td>
                    <td>
                        <span class="badge-count"><?= $row['so_hoso'] ?> hồ sơ</span>
                    </td>
                    <td style="text-align:center">
                        <a href="hoso.php?thisinh_id=<?= $row['id'] ?>" class="btn-act btn-view" title="Xem hồ sơ">👁️ Hồ sơ</a>
                        <a href="thisinh.php?delete=<?= $row['id'] ?>" class="btn-act btn-del"
                           onclick="return confirm('Xóa thí sinh này sẽ xóa toàn bộ hồ sơ liên quan. Tiếp tục?')"
                           title="Xóa">🗑️</a>
                    </td>
                </tr>
            <?php endwhile; ?>
            <?php if (!$has): ?>
                <tr>
                    <td colspan="7">
                        <div class="empty-state">
                            <div class="icon">👤</div>
                            <p><?= $search ? "Không tìm thấy thí sinh phù hợp với \"$search\"" : "Chưa có thí sinh nào đăng ký" ?></p>
                        </div>
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
