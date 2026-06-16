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

    $config_sql = "SELECT c.diem_san, th.ma_tohop, th.ten_tohop
                   FROM dot_nganh_tohop c
                   JOIN tohop_xettuyen th ON c.tohop_id = th.id
                   WHERE c.dot_id = $dot_id
                     AND c.nganh_id = $nganh_id
                     AND c.tohop_id = $tohop_id
                   LIMIT 1";
    $config_res = mysqli_query($conn, $config_sql);
    $config = $config_res ? mysqli_fetch_assoc($config_res) : null;

    if (!$config) {
        $error = "Tổ hợp bạn chọn chưa được mở cho ngành này trong đợt tuyển sinh đã chọn.";
    } elseif ($tong_diem < (float)$config['diem_san']) {
        $error = "Tổng điểm của bạn là " . number_format($tong_diem, 1) . ", chưa đạt điểm sàn " . number_format((float)$config['diem_san'], 2) . " của tổ hợp " . $config['ma_tohop'] . ".";
    }

    if (!$error) {
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
}

// Lấy danh sách ngành, đợt và cấu hình tổ hợp theo từng ngành
$nganh_res = mysqli_query($conn, "SELECT * FROM nganhhoc ORDER BY ma_nganh");
$dot_res = mysqli_query($conn, "SELECT * FROM dot_tuyensinh WHERE trang_thai = 'Dang mo'");
$config_res = mysqli_query($conn, "SELECT c.dot_id, c.nganh_id, c.tohop_id, c.diem_san,
                                          th.ma_tohop, th.ten_tohop
                                   FROM dot_nganh_tohop c
                                   JOIN dot_tuyensinh d ON c.dot_id = d.id
                                   JOIN tohop_xettuyen th ON c.tohop_id = th.id
                                   WHERE d.trang_thai = 'Dang mo'
                                   ORDER BY th.ma_tohop");
$tohop_config = [];
while ($cfg = mysqli_fetch_assoc($config_res)) {
    $tohop_config[$cfg['dot_id']][$cfg['nganh_id']][] = [
        'id' => (int)$cfg['tohop_id'],
        'ma' => $cfg['ma_tohop'],
        'ten' => $cfg['ten_tohop'],
        'diem_san' => (float)$cfg['diem_san']
    ];
}
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
                        <select name="dot" id="dot-select" required>
                            <?php while($dot = mysqli_fetch_assoc($dot_res)) echo "<option value='{$dot['id']}'>{$dot['ten_dot']}</option>"; ?>
                        </select>
                    </div>
                    <div class="cards-grid" style="padding: 0; grid-template-columns: repeat(2, 1fr); gap: 20px;">
                        <div class="form-group">
                            <label>Ngành đăng ký xét tuyển</label>
                            <select name="nganh" id="nganh-select" required>
                                <option value="">-- Chọn ngành --</option>
                                <?php while($ng = mysqli_fetch_assoc($nganh_res)) echo "<option value='{$ng['id']}'>{$ng['ma_nganh']} - {$ng['tennganh']}</option>"; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Tổ hợp môn xét tuyển</label>
                            <select name="tohop" id="tohop-select" required>
                                <option value="">-- Chọn đợt và ngành trước --</option>
                            </select>
                        </div>
                    </div>
                    <p id="diem-san-note" style="font-size: 0.9rem; color: var(--text-muted); margin-bottom: 15px;">Nhập điểm trung bình các môn thuộc tổ hợp đã chọn.</p>
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
    <script>
        const tohopConfig = <?php echo json_encode($tohop_config, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
        const dotSelect = document.getElementById('dot-select');
        const nganhSelect = document.getElementById('nganh-select');
        const tohopSelect = document.getElementById('tohop-select');
        const diemSanNote = document.getElementById('diem-san-note');

        function refreshTohopOptions() {
            const dotId = dotSelect.value;
            const nganhId = nganhSelect.value;
            const options = (tohopConfig[dotId] && tohopConfig[dotId][nganhId]) ? tohopConfig[dotId][nganhId] : [];

            tohopSelect.innerHTML = '';
            const placeholder = document.createElement('option');
            placeholder.value = '';
            placeholder.textContent = options.length ? '-- Chọn tổ hợp --' : '-- Ngành này chưa có tổ hợp trong đợt đã chọn --';
            tohopSelect.appendChild(placeholder);

            options.forEach(item => {
                const option = document.createElement('option');
                option.value = item.id;
                option.dataset.diemSan = item.diem_san;
                option.textContent = `${item.ma} (${item.ten}) - điểm sàn ${Number(item.diem_san).toFixed(2)}`;
                tohopSelect.appendChild(option);
            });

            updateDiemSanNote();
        }

        function updateDiemSanNote() {
            const selected = tohopSelect.options[tohopSelect.selectedIndex];
            if (selected && selected.dataset.diemSan) {
                diemSanNote.textContent = `Điểm sàn tổ hợp đã chọn: ${Number(selected.dataset.diemSan).toFixed(2)}. Nhập điểm trung bình các môn thuộc tổ hợp này.`;
            } else {
                diemSanNote.textContent = 'Nhập điểm trung bình các môn thuộc tổ hợp đã chọn.';
            }
        }

        dotSelect.addEventListener('change', refreshTohopOptions);
        nganhSelect.addEventListener('change', refreshTohopOptions);
        tohopSelect.addEventListener('change', updateDiemSanNote);
        refreshTohopOptions();
    </script>
</body>
</html>
