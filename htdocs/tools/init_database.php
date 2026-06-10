<?php
header('Content-Type: text/html; charset=utf-8');
include '../config/database.php';
?><!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>🗄️ Khởi tạo Database</title>
<style>
body{font-family:Arial,sans-serif;max-width:900px;margin:40px auto;padding:20px;background:#f8fafc;}
.ok {background:#ecfdf5;border:1px solid #10b981;padding:12px 16px;border-radius:8px;margin:6px 0;color:#065f46;}
.err{background:#fef2f2;border:1px solid #ef4444;padding:12px 16px;border-radius:8px;margin:6px 0;color:#991b1b;}
.skip{background:#f0fdf4;border:1px solid #86efac;padding:12px 16px;border-radius:8px;margin:6px 0;color:#14532d;}
h2{color:#1e293b;border-bottom:2px solid #e2e8f0;padding-bottom:8px;}
h3{color:#334155;margin-top:30px;}
code{background:#f1f5f9;padding:2px 8px;border-radius:4px;font-size:0.9em;}
.btn{display:inline-block;margin-top:20px;padding:12px 24px;background:#3b82f6;color:white;border-radius:8px;text-decoration:none;font-weight:bold;}
</style>
</head>
<body>
<h2>🗄️ Khởi tạo / Sửa chữa Database Tuyển Sinh</h2>

<?php
if (!$conn) {
    die("<div class='err'>❌ Không kết nối được database: " . mysqli_connect_error() . "</div>");
}
echo "<div class='ok'>✅ Kết nối database <strong>tuyensinh</strong> thành công</div>";

// Hàm tạo bảng nếu chưa tồn tại
function create_table($conn, $name, $sql) {
    $check = mysqli_query($conn, "SHOW TABLES LIKE '$name'");
    if (mysqli_num_rows($check) > 0) {
        echo "<div class='skip'>⏭️ Bảng <code>$name</code> đã tồn tại — bỏ qua</div>";
        return true;
    }
    if (mysqli_query($conn, $sql)) {
        echo "<div class='ok'>✅ Đã tạo bảng <code>$name</code></div>";
        return true;
    } else {
        echo "<div class='err'>❌ Lỗi tạo bảng <code>$name</code>: " . mysqli_error($conn) . "</div>";
        return false;
    }
}

echo "<h3>📋 Tạo các bảng cần thiết</h3>";

// 1. Bảng users
create_table($conn, 'users', "CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    ho_ten VARCHAR(255),
    email VARCHAR(100),
    role ENUM('admin','user') DEFAULT 'user',
    status ENUM('Active','Locked') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB CHARSET=utf8mb4");

// 2. Đợt tuyển sinh
create_table($conn, 'dot_tuyensinh', "CREATE TABLE dot_tuyensinh (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ten_dot VARCHAR(255) NOT NULL,
    ngay_bat_dau DATE,
    ngay_ket_thuc DATE,
    trang_thai ENUM('Dang mo','Da dong') DEFAULT 'Dang mo',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB CHARSET=utf8mb4");

// 3. Tổ hợp xét tuyển
create_table($conn, 'tohop_xettuyen', "CREATE TABLE tohop_xettuyen (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ma_tohop VARCHAR(10) NOT NULL UNIQUE,
    ten_tohop VARCHAR(255) NOT NULL,
    mo_ta VARCHAR(255)
) ENGINE=InnoDB CHARSET=utf8mb4");

// 4. Ngành học
create_table($conn, 'nganhhoc', "CREATE TABLE nganhhoc (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tennganh VARCHAR(255) NOT NULL,
    ma_nganh VARCHAR(20) UNIQUE,
    chitieu INT DEFAULT 0,
    mo_ta TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB CHARSET=utf8mb4");

// 5. Ngành - Tổ hợp
create_table($conn, 'nganh_tohop', "CREATE TABLE nganh_tohop (
    nganh_id INT,
    tohop_id INT,
    PRIMARY KEY (nganh_id, tohop_id),
    FOREIGN KEY (nganh_id) REFERENCES nganhhoc(id) ON DELETE CASCADE,
    FOREIGN KEY (tohop_id) REFERENCES tohop_xettuyen(id) ON DELETE CASCADE
) ENGINE=InnoDB CHARSET=utf8mb4");

// 6. Thí sinh
create_table($conn, 'thisinh', "CREATE TABLE thisinh (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    hoten VARCHAR(255) NOT NULL,
    ngaysinh DATE,
    gioitinh VARCHAR(10),
    diachi TEXT,
    sdt VARCHAR(20),
    email VARCHAR(100),
    cccd VARCHAR(20) UNIQUE,
    que_quan VARCHAR(255),
    truong_thpt VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB CHARSET=utf8mb4");

// 7. Hồ sơ xét tuyển
create_table($conn, 'hosoxettuyen', "CREATE TABLE hosoxettuyen (
    id INT AUTO_INCREMENT PRIMARY KEY,
    thisinh_id INT NOT NULL,
    nganh_id INT NOT NULL,
    dot_id INT,
    tohop_id INT,
    diem_mon1 FLOAT DEFAULT 0,
    diem_mon2 FLOAT DEFAULT 0,
    diem_mon3 FLOAT DEFAULT 0,
    diem_tong FLOAT DEFAULT 0,
    trangthai ENUM('Cho duyet','Da duyet','Tu choi','Trung tuyen','Khong trung tuyen') DEFAULT 'Cho duyet',
    file_hocba VARCHAR(255),
    ghi_chu TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (thisinh_id) REFERENCES thisinh(id) ON DELETE CASCADE,
    FOREIGN KEY (nganh_id) REFERENCES nganhhoc(id) ON DELETE CASCADE,
    FOREIGN KEY (dot_id) REFERENCES dot_tuyensinh(id) ON DELETE SET NULL,
    FOREIGN KEY (tohop_id) REFERENCES tohop_xettuyen(id) ON DELETE SET NULL
) ENGINE=InnoDB CHARSET=utf8mb4");

echo "<h3>📦 Chèn dữ liệu mẫu</h3>";

// Admin account
$check_admin = mysqli_query($conn, "SELECT id FROM users WHERE username='admin'");
if (mysqli_num_rows($check_admin) == 0) {
    $hash = password_hash('admin123', PASSWORD_DEFAULT);
    $cols = [];
    $col_res = mysqli_query($conn, "DESCRIBE users");
    while($c = mysqli_fetch_assoc($col_res)) $cols[] = $c['Field'];
    
    $f = "username,password,role";
    $v = "'admin','$hash','admin'";
    if (in_array('ho_ten', $cols)) { $f .= ",ho_ten"; $v .= ",'Quản trị viên'"; }
    if (in_array('email',  $cols)) { $f .= ",email";  $v .= ",'admin@ts.edu.vn'"; }
    if (in_array('status', $cols)) { $f .= ",status"; $v .= ",'Active'"; }
    
    if (mysqli_query($conn, "INSERT INTO users ($f) VALUES ($v)")) {
        echo "<div class='ok'>✅ Đã tạo tài khoản admin (<b>username:</b> admin | <b>password:</b> admin123)</div>";
    }
} else {
    echo "<div class='skip'>⏭️ Tài khoản admin đã tồn tại</div>";
}

// Đợt tuyển sinh
$check_dot = mysqli_query($conn, "SELECT id FROM dot_tuyensinh LIMIT 1");
if (mysqli_num_rows($check_dot) == 0) {
    mysqli_query($conn, "INSERT INTO dot_tuyensinh (ten_dot, ngay_bat_dau, ngay_ket_thuc, trang_thai) VALUES
        ('Tuyển sinh Đại học Đợt 1 - 2026', '2026-05-01', '2026-08-30', 'Dang mo')");
    echo "<div class='ok'>✅ Đã thêm đợt tuyển sinh mẫu</div>";
} else {
    echo "<div class='skip'>⏭️ Đã có đợt tuyển sinh</div>";
}

// Tổ hợp
$check_th = mysqli_query($conn, "SELECT id FROM tohop_xettuyen LIMIT 1");
if (mysqli_num_rows($check_th) == 0) {
    mysqli_query($conn, "INSERT INTO tohop_xettuyen (ma_tohop, ten_tohop, mo_ta) VALUES
        ('A00','Toán, Lý, Hóa','Khối tự nhiên'),
        ('A01','Toán, Lý, Anh','Khối tự nhiên + Anh'),
        ('D01','Toán, Văn, Anh','Khối xã hội'),
        ('B00','Toán, Hóa, Sinh','Khối y dược')");
    echo "<div class='ok'>✅ Đã thêm 4 tổ hợp xét tuyển (A00, A01, D01, B00)</div>";
} else {
    echo "<div class='skip'>⏭️ Đã có tổ hợp xét tuyển</div>";
}

// Ngành học
$check_ng = mysqli_query($conn, "SELECT id FROM nganhhoc LIMIT 1");
if (mysqli_num_rows($check_ng) == 0) {
    mysqli_query($conn, "INSERT INTO nganhhoc (tennganh, ma_nganh, chitieu, mo_ta) VALUES
        ('Công nghệ thông tin','CNTT',200,'Đào tạo kỹ sư CNTT'),
        ('Quản trị kinh doanh','QTKD',150,'Đào tạo cử nhân kinh tế'),
        ('Kế toán','KT',120,'Đào tạo kế toán - kiểm toán'),
        ('Điện - Điện tử','DDT',100,'Đào tạo kỹ sư điện'),
        ('Kiến trúc','KTR',80,'Đào tạo kiến trúc sư')");
    echo "<div class='ok'>✅ Đã thêm 5 ngành học mẫu</div>";
} else {
    echo "<div class='skip'>⏭️ Đã có ngành học</div>";
}

echo "<h3>✅ Kiểm tra tổng kết</h3>";
$tables_needed = ['users','dot_tuyensinh','tohop_xettuyen','nganhhoc','nganh_tohop','thisinh','hosoxettuyen'];
$all_ok = true;
foreach ($tables_needed as $t) {
    $r = mysqli_query($conn, "SHOW TABLES LIKE '$t'");
    $cnt = mysqli_query($conn, "SELECT COUNT(*) as c FROM `$t`");
    $row = mysqli_fetch_assoc($cnt);
    if (mysqli_num_rows($r) > 0) {
        echo "<div class='ok'>✅ <code>$t</code> — {$row['c']} bản ghi</div>";
    } else {
        echo "<div class='err'>❌ <code>$t</code> — CHƯA TỒN TẠI!</div>";
        $all_ok = false;
    }
}

if ($all_ok) {
    echo "
    <div style='background:#ecfdf5;border:2px solid #10b981;padding:25px;border-radius:12px;margin-top:25px;'>
        <h3 style='color:#065f46;margin:0 0 15px'>🎉 Database đã sẵn sàng!</h3>
        <p><strong>Đăng nhập Admin:</strong></p>
        <ul>
            <li>URL: <a href='admin/login.php'><strong>admin/login.php</strong></a></li>
            <li>Username: <code>admin</code></li>
            <li>Password: <code>admin123</code></li>
        </ul>
        <p><a href='admin/hoso.php' class='btn'>→ Vào Quản lý hồ sơ</a>
           <a href='admin/dashboard.php' class='btn' style='background:#8b5cf6;margin-left:10px;'>→ Vào Dashboard</a></p>
        <p style='color:#ef4444;margin-bottom:0'><strong>⚠️ Sau khi xong, hãy XÓA file này: <code>init_database.php</code></strong></p>
    </div>";
}
?>
</body>
</html>
