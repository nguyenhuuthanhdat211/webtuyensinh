<?php
header('Content-Type: text/html; charset=utf-8');
include 'config/database.php';
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hệ Thống Tuyển Sinh - Trang Chủ</title>
    <meta name="description" content="Hệ thống tuyển sinh trực tuyến - Đăng ký xét tuyển đại học nhanh chóng, thuận tiện">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

    <header>
        <h1>🎓 TUYỂN SINH 2026</h1>
        <nav>
            <a href="index.php">Trang chủ</a>
            <a href="#nganh">Ngành học</a>
            <a href="#tintuc">Tin tức</a>
            <a href="login.php">Đăng nhập</a>
            <a href="controllers/user/register.php" class="btn-primary">Đăng ký ngay</a>
        </nav>
    </header>

    <!-- Hero Section -->
    <section class="hero fade-in">
        <h2>Cổng Tuyển Sinh <br>Trực Tuyến</h2>
        <p>Hệ thống tiếp nhận hồ sơ, xét tuyển và công bố kết quả hiện đại, minh bạch và nhanh chóng cho năm học 2026.</p>
        <div class="hero-btns">
            <a href="controllers/user/register.php" class="btn btn-primary">🚀 Bắt đầu nộp hồ sơ</a>
            <a href="login.php" class="btn btn-secondary">Tra cứu kết quả</a>
        </div>
    </section>

    <!-- Features -->
    <div class="cards-grid">
        <div class="card fade-in">
            <div class="card-icon">📢</div>
            <h3>Thông báo mới</h3>
            <p>Luôn cập nhật các thông tin mới nhất về quy chế và thời gian tuyển sinh.</p>
        </div>
        <div class="card fade-in">
            <div class="card-icon">📅</div>
            <h3>Lịch tuyển sinh</h3>
            <p>Theo dõi các mốc thời gian quan trọng để không bỏ lỡ cơ hội nhập học.</p>
        </div>
        <div class="card fade-in">
            <div class="card-icon">📑</div>
            <h3>Hướng dẫn hồ sơ</h3>
            <p>Quy trình đăng ký đơn giản, hướng dẫn chi tiết từng bước cho thí sinh.</p>
        </div>
    </div>

    <!-- Stats & Targets -->
    <section id="nganh" class="section-title">
        <h2>Chỉ Tiêu Ngành Học</h2>
        <p>Thông tin các ngành đào tạo và chỉ tiêu tuyển sinh năm 2026</p>
    </section>

    <div class="news-section" style="padding-top: 0;">
        <div class="news-box" style="flex: 2;">
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Mã Ngành</th>
                            <th>Tên Ngành</th>
                            <th>Chỉ Tiêu</th>
                            <th>Tổ Hợp Xét Tuyển</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT n.*, GROUP_CONCAT(DISTINCT CONCAT(t.ma_tohop, ' (san ', FORMAT(c.diem_san, 2), ')') ORDER BY t.ma_tohop SEPARATOR ', ') as tohop 
                                FROM nganhhoc n 
                                LEFT JOIN dot_nganh_tohop c ON n.id = c.nganh_id
                                  AND c.dot_id IN (SELECT id FROM dot_tuyensinh WHERE trang_thai = 'Dang mo')
                                LEFT JOIN tohop_xettuyen t ON c.tohop_id = t.id 
                                GROUP BY n.id, n.tennganh, n.ma_nganh, n.chitieu, n.mo_ta, n.created_at";
                        $result = mysqli_query($conn, $sql);
                        while($row = mysqli_fetch_assoc($result)) {
                            echo "<tr>";
                            echo "<td><strong>" . $row['ma_nganh'] . "</strong></td>";
                            echo "<td>" . $row['tennganh'] . "</td>";
                            echo "<td>" . $row['chitieu'] . "</td>";
                            echo "<td><span style='color: var(--primary); font-weight:600;'>" . ($row['tohop'] ?? 'Chưa cập nhật') . "</span></td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="notice-box">
            <h3>🔔 Thông báo quan trọng</h3>
            <a href="#" class="item-link">Hướng dẫn nộp hồ sơ xét tuyển học bạ 2026</a>
            <a href="#" class="item-link">Quy chế tuyển sinh đại học chính quy mới nhất</a>
            <a href="#" class="item-link">Danh sách các tổ hợp môn xét tuyển theo ngành</a>
            <a href="#" class="item-link">Thời hạn nộp hồ sơ đợt 1: 30/08/2026</a>
        </div>
    </div>

    <!-- News Section -->
    <section id="tintuc" class="section-title">
        <h2>Tin Tức Tuyển Sinh</h2>
    </section>
    
    <div class="news-section" style="padding-top: 0; margin-bottom: 80px;">

    <div class="card" style="padding: 20px;">
        <div style="height: 150px; border-radius: 12px; margin-bottom: 15px; overflow: hidden;">
            <img src="assets/images/ngay-hoi-tvts-hai-duong-nguyen-khanh-3-1740881457558808668878.jpg"
                 style="width: 100%; height: 100%; object-fit: cover;">
        </div>

        <h4>Ngày hội tư vấn tuyển sinh 2026</h4>
        <p style="font-size: 0.9rem; color: var(--text-muted);">
            Cơ hội tìm hiểu trực tiếp các ngành nghề...
        </p>
    </div>

    <div class="card" style="padding: 20px;">
        <div style="height: 150px; border-radius: 12px; margin-bottom: 15px; overflow: hidden;">
            <img src="assets/images/684084-74t05053.jpg"
                 style="width: 100%; height: 100%; object-fit: cover;">
        </div>

        <h4>Học bổng tài năng dành cho tân sinh viên</h4>
        <p style="font-size: 0.9rem; color: var(--text-muted);">
            Tổng giá trị học bổng lên đến 10 tỷ đồng...
        </p>
    </div>

    <div class="card" style="padding: 20px;">
        <div style="height: 150px; border-radius: 12px; margin-bottom: 15px; overflow: hidden;">
            <img src="assets/images/truong.jpg"
                 style="width: 100%; height: 100%; object-fit: cover;">
        </div>

        <h4>Khám phá môi trường học tập hiện đại</h4>
        <p style="font-size: 0.9rem; color: var(--text-muted);">
            Cơ sở vật chất đạt chuẩn quốc tế...
        </p>
    </div>

</div>

    <footer>
        <div style="margin-bottom: 20px;">
            <h2 style="color: white;">🎓 TUYỂN SINH 2026</h2>
            <p>Hệ thống quản lý tuyển sinh trực tuyến chuyên nghiệp</p>
        </div>
        <p>© 2026 Hệ Thống Tuyển Sinh. All rights reserved.</p>
    </footer>

</body>

</html>
