<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
if (!isset($_SESSION['admin'])) { header("Location: login.php"); exit(); }
include '../../config/database.php';

$id = (int)$_GET['id'];

// Xử lý Duyệt/Từ chối
if (isset($_POST['update_status'])) {
    $st = $_POST['status'];
    $note = mysqli_real_escape_string($conn, $_POST['ghi_chu']);
    mysqli_query($conn, "UPDATE hosoxettuyen SET trangthai='$st', ghi_chu='$note' WHERE id=$id");
    header("Location: view_hoso.php?id=$id&msg=updated"); exit();
}

$sql = "SELECT h.*, t.hoten, t.cccd, t.sdt, t.email, t.diachi, n.tennganh, d.ten_dot, th.ma_tohop, th.ten_tohop, cfg.diem_san 
        FROM hosoxettuyen h 
        LEFT JOIN thisinh t ON h.thisinh_id = t.id 
        LEFT JOIN nganhhoc n ON h.nganh_id = n.id 
        LEFT JOIN dot_tuyensinh d ON h.dot_id = d.id 
        LEFT JOIN tohop_xettuyen th ON h.tohop_id = th.id
        LEFT JOIN dot_nganh_tohop cfg
          ON cfg.dot_id = h.dot_id AND cfg.nganh_id = h.nganh_id AND cfg.tohop_id = h.tohop_id
        WHERE h.id = $id";
$h = mysqli_fetch_assoc(mysqli_query($conn, $sql));
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Chi Tiết Hồ Sơ - <?php echo $h['hoten']; ?></title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        body { background: #f1f5f9; padding: 40px; font-family: sans-serif; }
        .container { max-width: 900px; margin: 0 auto; background: white; padding: 40px; border-radius: 20px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); }
        .header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #f1f5f9; padding-bottom: 20px; margin-bottom: 30px; }
        .section-title { font-size: 1.1rem; font-weight: 700; color: #1e293b; margin: 30px 0 15px 0; display: flex; align-items: center; gap: 10px; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .info-item { background: #f8fafc; padding: 15px; border-radius: 12px; }
        .info-label { font-size: 0.75rem; color: #64748b; text-transform: uppercase; margin-bottom: 5px; font-weight: 600; }
        .info-val { font-size: 1rem; color: #1e293b; font-weight: 600; }
        .status-badge { padding: 6px 15px; border-radius: 50px; color: white; font-weight: 700; font-size: 0.85rem; }
        .st-waiting { background: #f59e0b; }
        .st-done { background: #10b981; }
        .st-fail { background: #ef4444; }
        .action-box { margin-top: 40px; padding: 30px; background: #f8fafc; border-radius: 15px; border: 1px solid #e2e8f0; }
        select, textarea { width: 100%; padding: 12px; border-radius: 10px; border: 1px solid #cbd5e1; margin-bottom: 15px; font-size: 1rem; }
        .btn-save { background: #3b82f6; color: white; padding: 12px 30px; border: none; border-radius: 10px; font-weight: 700; cursor: pointer; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <a href="hoso.php" style="text-decoration: none; color: #3b82f6; font-weight: 600;">← Quay lại danh sách</a>
                <h1 style="margin-top: 15px;"><?php echo $h['hoten']; ?></h1>
                <p style="color: #64748b;">Mã hồ sơ: #HS<?php echo $h['id']; ?> | Ngày nộp: <?php echo date('d/m/Y', strtotime($h['created_at'])); ?></p>
            </div>
            <span class="status-badge <?php 
                if($h['trangthai']=='Cho duyet') echo 'st-waiting';
                elseif($h['trangthai']=='Da duyet') echo 'st-done';
                else echo 'st-fail';
            ?>"><?php echo $h['trangthai']; ?></span>
        </div>

        <div class="section-title">👤 Thông tin cá nhân</div>
        <div class="grid">
            <div class="info-item"><div class="info-label">Số CCCD</div><div class="info-val"><?php echo $h['cccd']; ?></div></div>
            <div class="info-item"><div class="info-label">Số điện thoại</div><div class="info-val"><?php echo $h['sdt']; ?></div></div>
            <div class="info-item"><div class="info-label">Email</div><div class="info-val"><?php echo $h['email']; ?></div></div>
            <div class="info-item"><div class="info-label">Địa chỉ</div><div class="info-val"><?php echo $h['diachi']; ?></div></div>
        </div>

        <div class="section-title">🎓 Ngành xét tuyển</div>
        <div class="grid">
            <div class="info-item"><div class="info-label">Ngành học</div><div class="info-val"><?php echo $h['tennganh']; ?></div></div>
            <div class="info-item"><div class="info-label">Đợt tuyển sinh</div><div class="info-val"><?php echo $h['ten_dot']; ?></div></div>
            <div class="info-item"><div class="info-label">Tổ hợp</div><div class="info-val"><?php echo $h['ma_tohop']; ?> - <?php echo $h['ten_tohop']; ?></div></div>
            <div class="info-item"><div class="info-label">Điểm sàn</div><div class="info-val"><?php echo $h['diem_san'] !== null ? number_format((float)$h['diem_san'], 2) . ' điểm' : 'Chưa cấu hình'; ?></div></div>
            <div class="info-item"><div class="info-label">Điểm xét tuyển</div><div class="info-val" style="color:#2563eb; font-size:1.4rem;"><?php echo $h['diem_tong']; ?> điểm</div></div>
        </div>

        <div class="action-box">
            <h3 style="margin-bottom: 15px;">Duyệt hồ sơ</h3>
            <form method="POST">
                <label class="info-label">Trạng thái mới</label>
                <select name="status">
                    <option value="Cho duyet" <?php if($h['trangthai']=='Cho duyet') echo 'selected'; ?>>Chờ duyệt</option>
                    <option value="Da duyet" <?php if($h['trangthai']=='Da duyet') echo 'selected'; ?>>Đã duyệt (Trúng tuyển)</option>
                    <option value="Tu choi" <?php if($h['trangthai']=='Tu choi') echo 'selected'; ?>>Từ chối</option>
                </select>
                <label class="info-label">Ghi chú / Lý do (Nếu từ chối)</label>
                <textarea name="ghi_chu" rows="3" placeholder="Nhập ghi chú cho thí sinh..."><?php echo $h['ghi_chu']; ?></textarea>
                <button type="submit" name="update_status" class="btn-save">Cập nhật hồ sơ</button>
            </form>
        </div>
    </div>
</body>
</html>
