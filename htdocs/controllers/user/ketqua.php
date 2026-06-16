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

// Lấy kết quả xét tuyển
$sql = "SELECT h.*, n.tennganh, d.ten_dot, th.ma_tohop, cfg.diem_san 
        FROM hosoxettuyen h
        JOIN thisinh t ON h.thisinh_id = t.id
        JOIN nganhhoc n ON h.nganh_id = n.id
        JOIN dot_tuyensinh d ON h.dot_id = d.id
        JOIN tohop_xettuyen th ON h.tohop_id = th.id
        LEFT JOIN dot_nganh_tohop cfg
          ON cfg.dot_id = h.dot_id AND cfg.nganh_id = h.nganh_id AND cfg.tohop_id = h.tohop_id
        WHERE t.user_id = $uid
        ORDER BY h.created_at DESC";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tra Cứu Kết Quả - Hệ Thống Tuyển Sinh</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <header>
        <h1>🎓 TUYỂN SINH 2026</h1>
        <nav>
            <a href="../../index.php">Trang chủ</a>
            <a href="profile.php">Hồ sơ cá nhân</a>
            <a href="logout.php" style="color: #ef4444;">Đăng xuất</a>
        </nav>
    </header>

    <div class="hero" style="min-height: 30vh; clip-path: none;">
        <h2>Kết Quả Xét Tuyển</h2>
        <p>Tra cứu kết quả và điểm chuẩn chính thức năm 2026.</p>
    </div>

    <div style="max-width: 1000px; margin: -50px auto 100px; padding: 20px;">
        <div class="card fade-in">
            <h3 style="margin-bottom: 25px; color: var(--primary);">📋 Danh sách hồ sơ xét tuyển</h3>
            
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Đợt tuyển</th>
                            <th>Ngành học</th>
                            <th>Tổ hợp</th>
                            <th>Tổng điểm</th>
                            <th>Điểm sàn</th>
                            <th>Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($result) > 0): ?>
                            <?php while($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td><?php echo $row['ten_dot']; ?></td>
                                    <td><strong><?php echo $row['tennganh']; ?></strong></td>
                                    <td><?php echo $row['ma_tohop']; ?></td>
                                    <td><strong style="color: var(--primary); font-size: 1.1rem;"><?php echo number_format($row['diem_tong'], 1); ?></strong></td>
                                    <td><?php echo $row['diem_san'] !== null ? number_format((float)$row['diem_san'], 2) : 'N/A'; ?></td>
                                    <td>
                                        <?php 
                                            $st = $row['trangthai'];
                                            $color = "#64748b"; // Mặc định
                                            $bg = "#f1f5f9";
                                            if ($st == 'Trung tuyen') { $color = "white"; $bg = "#10b981"; }
                                            if ($st == 'Da duyet') { $color = "white"; $bg = "#3b82f6"; }
                                            if ($st == 'Tu choi' || $st == 'Khong trung tuyen') { $color = "white"; $bg = "#ef4444"; }
                                            
                                            echo "<span style='background: $bg; color: $color; padding: 6px 15px; border-radius: 50px; font-weight: 600; font-size: 0.85rem;'>$st</span>";
                                        ?>
                                    </td>
                                </tr>
                                <?php if ($st == 'Trung tuyen'): ?>
                                    <tr>
                                        <td colspan="6" style="background: #ecfdf5; border: none; padding: 20px;">
                                            <div style="color: #065f46; font-weight: 600;">
                                                🎉 Chúc mừng! Bạn đã trúng tuyển vào ngành <strong><?php echo $row['tennganh']; ?></strong>. 
                                                Vui lòng chuẩn bị hồ sơ nhập học và có mặt tại trường trước ngày 15/09/2026.
                                                <br><a href="#" style="color: var(--primary);">Xem hướng dẫn nhập học →</a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="6" style="text-align: center;">Bạn chưa nộp hồ sơ xét tuyển nào.</td></tr>
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
