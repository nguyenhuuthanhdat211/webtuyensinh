<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}
include '../../config/database.php';

// Lấy thống kê
$total_hoso = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM hosoxettuyen"))['total'];
$total_thisinh = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM thisinh"))['total'];
$total_nganh = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM nganhhoc"))['total'];

// Thống kê trạng thái (Đảm bảo đếm đúng không phân biệt chữ hoa thường)
$cho_duyet = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM hosoxettuyen WHERE LOWER(trangthai) LIKE 'chờ%'"))['total'] ?? 0;
$da_duyet = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM hosoxettuyen WHERE LOWER(trangthai) LIKE 'đã%'"))['total'] ?? 0;
$tu_choi = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM hosoxettuyen WHERE LOWER(trangthai) LIKE 'từ%' OR LOWER(trangthai) LIKE 'từ chối%'"))['total'] ?? 0;

// Lấy hoạt động gần đây
$recent_hoso = mysqli_query($conn, "SELECT h.*, t.hoten, n.tennganh 
                                     FROM hosoxettuyen h 
                                     LEFT JOIN thisinh t ON h.thisinh_id = t.id 
                                     LEFT JOIN nganhhoc n ON h.nganh_id = n.id 
                                     ORDER BY h.id DESC LIMIT 3");

// Lấy 2 người dùng mới nhất dựa trên ID (đảm bảo không lỗi nếu thiếu cột created_at)
$recent_users = mysqli_query($conn, "SELECT ho_ten FROM users WHERE role='user' ORDER BY id DESC LIMIT 2");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Hệ Thống Tuyển Sinh</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { display: flex; background: #f1f5f9; }
        .sidebar { width: 260px; height: 100vh; background: #0f172a; color: white; position: fixed; padding: 30px 20px; }
        .main-content { margin-left: 260px; flex: 1; padding: 40px; }
        .sidebar h2 { font-size: 1.2rem; margin-bottom: 40px;  }
        .nav-item { display: block; padding: 12px 15px; color: rgba(255,255,255,0.7); text-decoration: none; border-radius: 10px; margin-bottom: 5px; transition: 0.3s; }
        .nav-item:hover, .nav-item.active { background: rgba(255,255,255,0.1); color: white; }
        .stat-card { background: white; padding: 25px; border-radius: 20px; box-shadow: var(--shadow); }
        .stat-card h4 { color: var(--text-muted); font-size: 0.9rem; margin-bottom: 10px; }
        .stat-card .value { font-size: 2rem; font-weight: 700; color: var(--text-main); }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>🛡️ ADMIN PANEL</h2>
        <nav>
            <a href="dashboard.php" class="nav-item active">📊 Dashboard</a>
            <a href="hoso.php" class="nav-item">📄 Quản lý hồ sơ</a>
            <a href="nganhhoc.php" class="nav-item">🎓 Quản lý ngành</a>
            <a href="dottuyensinh.php" class="nav-item">📅 Đợt tuyển sinh</a>
            <a href="tohop.php" class="nav-item">🧩 Tổ hợp môn</a>
            <a href="thisinh.php" class="nav-item">👥 Quản lý thí sinh</a>
            <a href="xettuyen.php" class="nav-item">⚖️ Xét tuyển</a>
            <a href="logout.php" class="nav-item" style="margin-top: 50px; color: #ef4444;">🚪 Đăng xuất</a>
        </nav>
    </div>

    <div class="main-content">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px;">
            <h2 style="font-size: 1.8rem; font-weight: 700;">📊 Tổng quan hệ thống</h2>
            <div style="background: white; padding: 10px 20px; border-radius: 50px; box-shadow: var(--shadow);">
                Chào, <strong>Admin</strong>
            </div>
        </div>

        <div class="cards-grid" style="padding: 0; margin-bottom: 40px;">
            <div class="stat-card fade-in">
                <h4>Tổng số hồ sơ</h4>
                <div class="value"><?php echo $total_hoso; ?></div>
                <p style="color: var(--primary); font-size: 0.8rem; margin-top: 5px;">↑ 12% so với hôm qua</p>
            </div>
            <div class="stat-card fade-in" style="animation-delay: 0.1s;">
                <h4>Tổng số thí sinh</h4>
                <div class="value"><?php echo $total_thisinh; ?></div>
            </div>
            <div class="stat-card fade-in" style="animation-delay: 0.2s;">
                <h4>Tổng số ngành</h4>
                <div class="value"><?php echo $total_nganh; ?></div>
            </div>
            <div class="stat-card fade-in" style="animation-delay: 0.3s;">
                <h4>Hồ sơ chờ duyệt</h4>
                <div class="value" style="color: var(--secondary);"><?php echo $cho_duyet; ?></div>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px;">
            <div class="card fade-in" style="animation-delay: 0.4s;">
                <h3 style="margin-bottom: 20px;">📈 Biểu đồ trạng thái hồ sơ</h3>
                <canvas id="statusChart" style="max-height: 300px;"></canvas>
            </div>
            <div class="card fade-in" style="animation-delay: 0.5s;">
                <h3 style="margin-bottom: 20px;">🔔 Hoạt động gần đây</h3>
                <div style="font-size: 0.9rem;">
                    <?php 
                    $has_activity = false;
                    if($recent_hoso) {
                        while($h = mysqli_fetch_assoc($recent_hoso)) {
                            $has_activity = true;
                            $ten = $h['hoten'] ?? 'Thí sinh';
                            $nganh = $h['tennganh'] ?? 'ngành chưa rõ';
                            $st = $h['trangthai'];
                            $color = '#f59e0b'; // vàng cho chờ duyệt
                            if(strpos(mb_strtolower($st), 'đã') !== false) $color = '#10b981';
                            if(strpos(mb_strtolower($st), 'từ') !== false) $color = '#ef4444';
                            
                            echo "<p style='padding: 10px 0; border-bottom: 1px solid #eee; display:flex; justify-content:space-between; align-items:center;'>
                                    <span><strong>{$ten}</strong> nộp hồ sơ <strong>{$nganh}</strong></span>
                                    <span style='font-size:0.9rem; padding:2px 8px; border-radius:50px; display: inline-block; white-space: nowrap; background:{$color}; color:white;'>{$st}</span>
                                  </p>";
                        }
                    }
                    if($recent_users) {
                        while($u = mysqli_fetch_assoc($recent_users)) {
                            $has_activity = true;
                            $ten_u = $u['ho_ten'] ?? 'Thành viên mới';
                            echo "<p style='padding: 10px 0; border-bottom: 1px solid #eee;'><strong>{$ten_u}</strong> vừa đăng ký tài khoản</p>";
                        }
                    }
                    if(!$has_activity) {
                        echo "<p style='padding: 20px 0; color: #94a3b8; text-align:center;'>Chưa có hoạt động mới</p>";
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        const ctx = document.getElementById('statusChart').getContext('2d');
        const dataStatus = [<?php echo (int)$cho_duyet; ?>, <?php echo (int)$da_duyet; ?>, <?php echo (int)$tu_choi; ?>];
        const total = dataStatus.reduce((a, b) => a + b, 0);

        if (total === 0) {
            ctx.font = "16px Arial";
            ctx.fillStyle = "#94a3b8";
            ctx.textAlign = "center";
            ctx.fillText("Chưa có dữ liệu thống kê", 150, 150);
        } else {
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Cho duyet', 'Da duyet', 'Tu choi'],
                    datasets: [{
                        data: dataStatus,
                        backgroundColor: ['#f59e0b', '#10b981', '#ef4444'],
                        hoverOffset: 4,
                        borderWidth: 2,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: {
                        legend: { 
                            position: 'bottom',
                            labels: { padding: 20, usePointStyle: true }
                        }
                    }
                }
            });
        }
    </script>
</body>
</html>