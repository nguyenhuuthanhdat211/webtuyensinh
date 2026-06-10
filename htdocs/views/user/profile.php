<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}
include '../../config/database.php';

$user = $_SESSION['user'];
$uid = $user['id'];

// Lấy thông tin thí sinh
$ts_res = mysqli_query($conn, "SELECT * FROM thisinh WHERE user_id = $uid");
$ts = mysqli_fetch_assoc($ts_res);

// Lấy danh sách hồ sơ đã nộp
$hoso_sql = "SELECT h.*, n.tennganh, d.ten_dot, t.ma_tohop 
             FROM hosoxettuyen h
             JOIN nganhhoc n ON h.nganh_id = n.id
             JOIN dot_tuyensinh d ON h.dot_id = d.id
             JOIN tohop_xettuyen t ON h.tohop_id = t.id
             WHERE h.thisinh_id = (SELECT id FROM thisinh WHERE user_id = $uid)
             ORDER BY h.created_at DESC";
$hoso_res = mysqli_query($conn, $hoso_sql);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hồ Sơ Cá Nhân - Hệ Thống Tuyển Sinh</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <header>
        <h1>🎓 TUYỂN SINH 2026</h1>
        <nav>
            <a href="../../index.php">Trang chủ</a>
            <a href="../../controllers/user/dangky_xettuyen.php">Nộp hồ sơ mới</a>
            <a href="logout.php" style="color: #ef4444;">Đăng xuất</a>
        </nav>
    </header>

    <div class="hero" style="min-height: 30vh; clip-path: none;">
        <h2>Hồ Sơ Cá Nhân</h2>
        <p>Chào mừng <strong><?php echo htmlspecialchars($user['ho_ten']); ?></strong>, xem lại thông tin và trạng thái hồ sơ của bạn.</p>
    </div>

    <div style="max-width: 1200px; margin: -50px auto 100px; padding: 20px; display: grid; grid-template-columns: 1fr 2fr; gap: 30px;">
        <!-- Thông tin cá nhân -->
        <div class="card fade-in">
            <h3 style="margin-bottom: 20px; color: var(--primary);">📋 Thông tin của bạn</h3>
            <?php if ($ts): ?>
                <div style="font-size: 0.95rem;">
                    <p><strong>Họ tên:</strong> <?php echo $ts['hoten']; ?></p>
                    <p><strong>CCCD:</strong> <?php echo $ts['cccd']; ?></p>
                    <p><strong>Ngày sinh:</strong> <?php echo date('d/m/Y', strtotime($ts['ngaysinh'])); ?></p>
                    <p><strong>Giới tính:</strong> <?php echo $ts['gioitinh']; ?></p>
                    <p><strong>SĐT:</strong> <?php echo $ts['sdt']; ?></p>
                    <p><strong>Email:</strong> <?php echo $ts['email']; ?></p>
                    <p><strong>Địa chỉ:</strong> <?php echo $ts['diachi']; ?></p>
                </div>
                <br>
                <a href="#" class="btn btn-secondary btn-full" style="text-align: center; font-size: 0.9rem;">Chỉnh sửa thông tin</a>
            <?php else: ?>
                <p>Bạn chưa hoàn thiện thông tin thí sinh. Vui lòng nộp hồ sơ xét tuyển.</p>
                <a href="../../controllers/user/dangky_xettuyen.php" class="btn btn-primary btn-full" style="text-align: center;">Nộp hồ sơ ngay</a>
            <?php endif; ?>
        </div>

        <!-- Trạng thái hồ sơ -->
        <div class="card fade-in" style="animation-delay: 0.2s;">
            <h3 style="margin-bottom: 20px; color: var(--primary);">🚀 Hồ sơ đã nộp</h3>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Đợt xét tuyển</th>
                            <th>Ngành đăng ký</th>
                            <th>Tổ hợp</th>
                            <th>Tổng điểm</th>
                            <th>Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($hoso_res) > 0): ?>
                            <?php while($h = mysqli_fetch_assoc($hoso_res)): ?>
                                <tr>
                                    <td><?php echo $h['ten_dot']; ?></td>
                                    <td><strong><?php echo $h['tennganh']; ?></strong></td>
                                    <td><?php echo $h['ma_tohop']; ?></td>
                                    <td><span style="font-weight: 700; color: var(--primary);"><?php echo number_format($h['diem_tong'], 1); ?></span></td>
                                    <td>
                                        <?php 
                                            $st = $h['trangthai'];
                                            $color = "#64748b"; // Chờ duyệt
                                            if ($st == 'Da duyet' || $st == 'Trung tuyen') $color = "#10b981";
                                            if ($st == 'Tu choi' || $st == 'Khong trung tuyen') $color = "#ef4444";
                                            echo "<span style='background: $color; color: white; padding: 4px 10px; border-radius: 50px; font-size: 0.9rem; display: inline-block; white-space: nowrap;'>$st</span>";
                                        ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5" style="text-align: center;">Bạn chưa nộp hồ sơ nào.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <footer>
        <p>© 2026 Hệ Thống Tuyển Sinh. All rights reserved.</p>
    </footer>
</body>
</html>
