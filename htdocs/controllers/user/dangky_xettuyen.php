<?php
header('Content-Type: text/html; charset=utf-8');
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: ../../views/user/login.php');
    exit();
}

include '../../config/database.php';

$user = $_SESSION['user'];
$success = "";
$error = "";

// Xử lý nộp hồ sơ
if (isset($_POST['submit'])) {
    $hoten = mysqli_real_escape_string($conn, trim($_POST['hoten']));
    $ngaysinh = mysqli_real_escape_string($conn, $_POST['ngaysinh']);
    $gioitinh = mysqli_real_escape_string($conn, $_POST['gioitinh']);
    $diachi = mysqli_real_escape_string($conn, trim($_POST['diachi']));
    $sdt = mysqli_real_escape_string($conn, trim($_POST['sdt']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $cccd = mysqli_real_escape_string($conn, trim($_POST['cccd']));
    
    $nganh_id = (int)$_POST['nganh'];
    $dot_id = (int)$_POST['dot'];
    $tohop_id = (int)$_POST['tohop'];
    
    $diem1 = (float)$_POST['diem1'];
    $diem2 = (float)$_POST['diem2'];
    $diem3 = (float)$_POST['diem3'];
    $tong_diem = $diem1 + $diem2 + $diem3;

    // Upload file
    $file_path = "";
    if (isset($_FILES['hocba']) && $_FILES['hocba']['error'] == 0) {
        $target_dir = __DIR__ . "/../../uploads/hocba/";
        if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
        
        $file_ext = pathinfo($_FILES['hocba']['name'], PATHINFO_EXTENSION);
        $file_name = "hocba_" . $user['id'] . "_" . time() . "." . $file_ext;
        $target_file = $target_dir . $file_name;
        
        if (move_uploaded_file($_FILES['hocba']['tmp_name'], $target_file)) {
            $file_path = $file_name;
        }
    }

    // 1. Cập nhật/Chèn thông tin thí sinh
    $check_ts = mysqli_query($conn, "SELECT id FROM thisinh WHERE user_id = " . $user['id']);
    if (mysqli_num_rows($check_ts) > 0) {
        $ts_data = mysqli_fetch_assoc($check_ts);
        $ts_id = $ts_data['id'];
        $sql_ts = "UPDATE thisinh SET hoten='$hoten', ngaysinh='$ngaysinh', gioitinh='$gioitinh', diachi='$diachi', sdt='$sdt', email='$email', cccd='$cccd' WHERE id=$ts_id";
    } else {
        $sql_ts = "INSERT INTO thisinh (user_id, hoten, ngaysinh, gioitinh, diachi, sdt, email, cccd) VALUES ('".$user['id']."', '$hoten', '$ngaysinh', '$gioitinh', '$diachi', '$sdt', '$email', '$cccd')";
    }

    if (mysqli_query($conn, $sql_ts)) {
        if (!isset($ts_id)) $ts_id = mysqli_insert_id($conn);
        
        // 2. Chèn hồ sơ xét tuyển
        $sql_hoso = "INSERT INTO hosoxettuyen (thisinh_id, nganh_id, dot_id, tohop_id, diem_mon1, diem_mon2, diem_mon3, diem_tong, file_hocba, trangthai) 
                     VALUES ('$ts_id', '$nganh_id', '$dot_id', '$tohop_id', '$diem1', '$diem2', '$diem3', '$tong_diem', '$file_path', 'Cho duyet')";
        
        if (mysqli_query($conn, $sql_hoso)) {
            $success = "Chúc mừng! Hồ sơ xét tuyển của bạn đã được gửi thành công.";
        } else {
            $error = "Lỗi nộp hồ sơ: " . mysqli_error($conn);
        }
    } else {
        $error = "Lỗi lưu thông tin thí sinh: " . mysqli_error($conn);
    }
}

// Lấy danh sách ngành, đợt, tổ hợp
$nganh_res = mysqli_query($conn, "SELECT * FROM nganhhoc");
$dot_res = mysqli_query($conn, "SELECT * FROM dot_tuyensinh WHERE trang_thai = 'Dang mo'");
$tohop_res = mysqli_query($conn, "SELECT * FROM tohop_xettuyen");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nộp Hồ Sơ Xét Tuyển - Hệ Thống Tuyển Sinh</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .step-container { background: white; padding: 40px; border-radius: 24px; box-shadow: var(--shadow); margin-top: 40px; }
        .step-title { color: var(--primary); font-weight: 700; margin-bottom: 25px; display: flex; align-items: center; gap: 10px; }
        .step-title span { background: var(--primary); color: white; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; border-radius: 50%; font-size: 0.9rem; }
    </style>
</head>
<body>
    <header>
        <h1>🎓 TUYỂN SINH 2026</h1>
        <nav>
            <span>Chào, <strong><?php echo htmlspecialchars($user['ho_ten']); ?></strong></span>
            <a href="../../views/user/profile.php">Hồ sơ của tôi</a>
            <a href="ketqua.php">Kết quả</a>
            <a href="../../views/user/logout.php" style="color: #ef4444;">Đăng xuất</a>
        </nav>
    </header>

    <div class="hero" style="min-height: 40vh; clip-path: none;">
        <h2>Nộp Hồ Sơ Xét Tuyển</h2>
        <p>Vui lòng điền chính xác thông tin để quá trình xét tuyển diễn ra thuận lợi.</p>
    </div>

    <div style="max-width: 1000px; margin: -100px auto 100px; padding: 20px;">
        <?php if ($success): ?>
            <div class="card fade-in" style="border-left: 5px solid #10b981; text-align: center;">
                <h3 style="color: #10b981; margin-bottom: 10px;">🎉 Thành công!</h3>
                <p><?php echo $success; ?></p>
                <br>
                <a href="../../views/user/profile.php" class="btn btn-primary">Xem trạng thái hồ sơ</a>
            </div>
        <?php else: ?>
            <?php if ($error): ?>
                <div class="card" style="border-left: 5px solid #ef4444; margin-bottom: 20px;">
                    <p style="color: #ef4444;">⚠️ <?php echo $error; ?></p>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="step-container fade-in">
                    <h3 class="step-title"><span>1</span> Thông tin cá nhân</h3>
                    <div class="cards-grid" style="padding: 0; grid-template-columns: repeat(2, 1fr); gap: 20px;">
                        <div class="form-group">
                            <label>Họ và tên thí sinh</label>
                            <input type="text" name="hoten" required value="<?php echo htmlspecialchars($user['ho_ten']); ?>">
                        </div>
                        <div class="form-group">
                            <label>Số CCCD/Mã định danh</label>
                            <input type="text" name="cccd" required placeholder="Nhập số CCCD">
                        </div>
                        <div class="form-group">
                            <label>Ngày sinh</label>
                            <input type="date" name="ngaysinh" required>
                        </div>
                        <div class="form-group">
                            <label>Giới tính</label>
                            <select name="gioitinh">
                                <option value="Nam">Nam</option>
                                <option value="Nữ">Nữ</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Số điện thoại</label>
                            <input type="text" name="sdt" required>
                        </div>
                        <div class="form-group">
                            <label>Email liên hệ</label>
                            <input type="email" name="email" required value="<?php echo htmlspecialchars($user['email']); ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Địa chỉ thường trú</label>
                        <input type="text" name="diachi" required placeholder="Số nhà, đường, phường/xã, quận/huyện, tỉnh/thành phố">
                    </div>
                </div>

                <div class="step-container fade-in" style="animation-delay: 0.2s;">
                    <h3 class="step-title"><span>2</span> Nguyện vọng & Tổ hợp môn</h3>
                    <div class="form-group">
                        <label>Đợt tuyển sinh</label>
                        <select name="dot" required>
                            <?php while($dot = mysqli_fetch_assoc($dot_res)) echo "<option value='{$dot['id']}'>{$dot['ten_dot']}</option>"; ?>
                        </select>
                    </div>
                    <div class="cards-grid" style="padding: 0; grid-template-columns: repeat(2, 1fr); gap: 20px;">
                        <div class="form-group">
                            <label>Ngành đăng ký xét tuyển</label>
                            <select name="nganh" required>
                                <option value="">-- Chọn ngành --</option>
                                <?php while($ng = mysqli_fetch_assoc($nganh_res)) echo "<option value='{$ng['id']}'>{$ng['ma_nganh']} - {$ng['tennganh']}</option>"; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Tổ hợp môn xét tuyển</label>
                            <select name="tohop" required>
                                <option value="">-- Chọn tổ hợp --</option>
                                <?php while($th = mysqli_fetch_assoc($tohop_res)) echo "<option value='{$th['id']}'>{$th['ma_tohop']} ({$th['ten_tohop']})</option>"; ?>
                            </select>
                        </div>
                    </div>
                    <p style="font-size: 0.9rem; color: var(--text-muted); margin-bottom: 15px;">Nhập điểm trung bình các môn thuộc tổ hợp đã chọn:</p>
                    <div class="cards-grid" style="padding: 0; grid-template-columns: repeat(3, 1fr); gap: 20px;">
                        <div class="form-group">
                            <label>Điểm Môn 1</label>
                            <input type="number" name="diem1" step="0.1" min="0" max="10" required>
                        </div>
                        <div class="form-group">
                            <label>Điểm Môn 2</label>
                            <input type="number" name="diem2" step="0.1" min="0" max="10" required>
                        </div>
                        <div class="form-group">
                            <label>Điểm Môn 3</label>
                            <input type="number" name="diem3" step="0.1" min="0" max="10" required>
                        </div>
                    </div>
                </div>

                <div class="step-container fade-in" style="animation-delay: 0.4s;">
                    <h3 class="step-title"><span>3</span> Hồ sơ đính kèm</h3>
                    <div class="form-group">
                        <label>Ảnh chụp học bạ / Chứng nhận tốt nghiệp (PDF/JPG/PNG)</label>
                        <input type="file" name="hocba" required accept=".pdf,.jpg,.jpeg,.png">
                        <p style="font-size: 0.8rem; color: var(--text-muted); margin-top: 5px;">* Dung lượng tối đa 5MB</p>
                    </div>
                </div>

                <div style="margin-top: 40px; text-align: right;">
                    <button type="submit" name="submit" class="btn btn-primary" style="font-size: 1.2rem; padding: 15px 60px;">
                        🚀 Xác nhận nộp hồ sơ
                    </button>
                </div>
            </form>
        <?php endif; ?>
    </div>

    <footer>
        <p>© 2026 Hệ Thống Tuyển Sinh. All rights reserved.</p>
    </footer>
</body>
</html>