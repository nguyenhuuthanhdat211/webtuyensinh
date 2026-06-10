<?php
mysqli_report(MYSQLI_REPORT_OFF);
include '../config/database.php';

echo "<style>body{font-family:Arial;padding:20px;} .ok{color:green;} .err{color:red;} .skip{color:#666;} table{border-collapse:collapse;width:100%;margin:10px 0;} td,th{border:1px solid #ddd;padding:8px;} th{background:#f3f4f6;}</style>";

echo "<h2>🔧 Kiểm tra & Sửa cấu trúc bảng thisinh</h2>";

// Lấy danh sách cột hiện có trong bảng thisinh
$cols_res = mysqli_query($conn, "DESCRIBE thisinh");
$existing_cols = [];
echo "<h3>Cột hiện có trong bảng thisinh:</h3>";
echo "<table><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
while ($col = mysqli_fetch_assoc($cols_res)) {
    $existing_cols[] = $col['Field'];
    echo "<tr><td>{$col['Field']}</td><td>{$col['Type']}</td><td>{$col['Null']}</td><td>{$col['Key']}</td><td>{$col['Default']}</td></tr>";
}
echo "</table>";

// Danh sách cột cần có
$required_cols = [
    'cccd'        => "ALTER TABLE thisinh ADD COLUMN cccd VARCHAR(20) UNIQUE AFTER email",
    'que_quan'    => "ALTER TABLE thisinh ADD COLUMN que_quan VARCHAR(255) AFTER cccd",
    'truong_thpt' => "ALTER TABLE thisinh ADD COLUMN truong_thpt VARCHAR(255) AFTER que_quan",
    'ngaysinh'    => "ALTER TABLE thisinh ADD COLUMN ngaysinh DATE AFTER hoten",
    'gioitinh'    => "ALTER TABLE thisinh ADD COLUMN gioitinh VARCHAR(10) AFTER ngaysinh",
    'diachi'      => "ALTER TABLE thisinh ADD COLUMN diachi TEXT AFTER gioitinh",
    'sdt'         => "ALTER TABLE thisinh ADD COLUMN sdt VARCHAR(20) AFTER diachi",
    'email'       => "ALTER TABLE thisinh ADD COLUMN email VARCHAR(100) AFTER sdt",
    'user_id'     => "ALTER TABLE thisinh ADD COLUMN user_id INT AFTER id",
];

echo "<h3>Thêm cột còn thiếu:</h3>";
foreach ($required_cols as $col_name => $alter_sql) {
    if (!in_array($col_name, $existing_cols)) {
        if (mysqli_query($conn, $alter_sql)) {
            echo "<p class='ok'>✅ Đã thêm cột: <strong>$col_name</strong></p>";
        } else {
            echo "<p class='err'>❌ Lỗi thêm cột $col_name: " . mysqli_error($conn) . "</p>";
        }
    } else {
        echo "<p class='skip'>⏭️ Cột <strong>$col_name</strong> đã tồn tại</p>";
    }
}

// Kiểm tra bảng hosoxettuyen
echo "<h3>Kiểm tra bảng hosoxettuyen:</h3>";
$cols2 = mysqli_query($conn, "DESCRIBE hosoxettuyen");
echo "<table><tr><th>Field</th><th>Type</th></tr>";
$hoso_cols = [];
while ($c = mysqli_fetch_assoc($cols2)) {
    $hoso_cols[] = $c['Field'];
    echo "<tr><td>{$c['Field']}</td><td>{$c['Type']}</td></tr>";
}
echo "</table>";

// Thêm cột thiếu vào hosoxettuyen nếu cần
$hoso_required = [
    'dot_id'   => "ALTER TABLE hosoxettuyen ADD COLUMN dot_id INT AFTER nganh_id",
    'tohop_id' => "ALTER TABLE hosoxettuyen ADD COLUMN tohop_id INT AFTER dot_id",
    'diem_mon1'=> "ALTER TABLE hosoxettuyen ADD COLUMN diem_mon1 FLOAT DEFAULT 0",
    'diem_mon2'=> "ALTER TABLE hosoxettuyen ADD COLUMN diem_mon2 FLOAT DEFAULT 0",
    'diem_mon3'=> "ALTER TABLE hosoxettuyen ADD COLUMN diem_mon3 FLOAT DEFAULT 0",
    'diem_tong'=> "ALTER TABLE hosoxettuyen ADD COLUMN diem_tong FLOAT DEFAULT 0",
    'file_hocba'=> "ALTER TABLE hosoxettuyen ADD COLUMN file_hocba VARCHAR(255)",
    'ghi_chu'  => "ALTER TABLE hosoxettuyen ADD COLUMN ghi_chu TEXT",
];
foreach ($hoso_required as $col_name => $alter_sql) {
    if (!in_array($col_name, $hoso_cols)) {
        if (mysqli_query($conn, $alter_sql)) {
            echo "<p class='ok'>✅ Thêm cột hosoxettuyen.$col_name</p>";
        } else {
            echo "<p class='err'>❌ Lỗi: " . mysqli_error($conn) . "</p>";
        }
    }
}

// Test query cuối
echo "<h3>Test query hoso.php:</h3>";
$test = mysqli_query($conn, "SELECT h.*, t.hoten, COALESCE(t.cccd,'') as cccd, n.tennganh,
    COALESCE(d.ten_dot,'N/A') as ten_dot, COALESCE(th.ma_tohop,'N/A') as ma_tohop
    FROM hosoxettuyen h
    LEFT JOIN thisinh t ON h.thisinh_id = t.id
    LEFT JOIN nganhhoc n ON h.nganh_id = n.id
    LEFT JOIN dot_tuyensinh d ON h.dot_id = d.id
    LEFT JOIN tohop_xettuyen th ON h.tohop_id = th.id
    ORDER BY h.created_at DESC LIMIT 1");
if ($test) {
    echo "<p class='ok'>✅ Query chạy thành công! Hoso.php sẽ hoạt động.</p>";
} else {
    echo "<p class='err'>❌ Vẫn còn lỗi: " . mysqli_error($conn) . "</p>";
}

echo "<hr><p>→ <a href='admin/hoso.php' style='font-weight:bold;color:#3b82f6'>Vào Quản lý hồ sơ ngay</a></p>";
?>
