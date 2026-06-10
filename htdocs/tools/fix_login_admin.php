<?php
header('Content-Type: text/html; charset=utf-8');
include '../config/database.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>🔧 Sửa đăng nhập Admin</title>
<style>
body { font-family: Arial, sans-serif; max-width: 800px; margin: 40px auto; padding: 20px; background:#f8fafc; }
.ok  { background:#ecfdf5; border:1px solid #10b981; padding:15px; border-radius:8px; margin:10px 0; color:#065f46; }
.err { background:#fef2f2; border:1px solid #ef4444; padding:15px; border-radius:8px; margin:10px 0; color:#991b1b; }
.info{ background:#eff6ff; border:1px solid #3b82f6; padding:15px; border-radius:8px; margin:10px 0; color:#1e40af; }
table { border-collapse:collapse; width:100%; margin:10px 0; }
td,th { border:1px solid #ddd; padding:8px 12px; text-align:left; }
th { background:#f3f4f6; }
code { background:#f3f4f6; padding:2px 6px; border-radius:4px; font-family:monospace; }
h2 { color:#1e293b; }
h3 { color:#334155; border-bottom:2px solid #e2e8f0; padding-bottom:6px; }
</style>
</head>
<body>
<h2>🔧 Chẩn đoán & Sửa Đăng Nhập Admin</h2>

<?php
// ===== Bước 1: Kiểm tra kết nối =====
echo "<h3>Bước 1: Kiểm tra kết nối Database</h3>";
if (!$conn) {
    echo "<div class='err'>❌ Lỗi kết nối: " . mysqli_connect_error() . "</div>";
    exit;
}
echo "<div class='ok'>✅ Kết nối thành công tới database <strong>tuyensinh</strong></div>";

// ===== Bước 2: Kiểm tra bảng users =====
echo "<h3>Bước 2: Kiểm tra bảng users</h3>";
$check = mysqli_query($conn, "SHOW TABLES LIKE 'users'");
if (mysqli_num_rows($check) == 0) {
    // Tạo bảng mới không có status
    $create = "CREATE TABLE users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        ho_ten VARCHAR(255),
        email VARCHAR(100),
        role ENUM('admin','user') DEFAULT 'user',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    if (mysqli_query($conn, $create)) {
        echo "<div class='ok'>✅ Đã tạo bảng <strong>users</strong> mới</div>";
    } else {
        echo "<div class='err'>❌ Không tạo được bảng users: " . mysqli_error($conn) . "</div>";
        exit;
    }
} else {
    echo "<div class='ok'>✅ Bảng <strong>users</strong> đã tồn tại</div>";
}

// ===== Bước 3: Lấy danh sách cột thực tế =====
echo "<h3>Bước 3: Cấu trúc bảng users</h3>";
$cols_res = mysqli_query($conn, "DESCRIBE users");
$columns = [];
echo "<table><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
while ($col = mysqli_fetch_assoc($cols_res)) {
    $columns[] = $col['Field'];
    echo "<tr><td>{$col['Field']}</td><td>{$col['Type']}</td><td>{$col['Null']}</td><td>{$col['Key']}</td><td>{$col['Default']}</td></tr>";
}
echo "</table>";

$has_status = in_array('status', $columns);
$has_ho_ten = in_array('ho_ten', $columns);
$has_email  = in_array('email',  $columns);

// Thêm cột status nếu thiếu
if (!$has_status) {
    $add_col = "ALTER TABLE users ADD COLUMN status ENUM('Active','Locked') DEFAULT 'Active'";
    if (mysqli_query($conn, $add_col)) {
        echo "<div class='ok'>✅ Đã thêm cột <code>status</code> vào bảng users</div>";
        $has_status = true;
    } else {
        echo "<div class='err'>⚠️ Không thể thêm cột status: " . mysqli_error($conn) . " — Sẽ tiếp tục mà không có cột này</div>";
    }
}

// ===== Bước 4: Hiển thị user hiện có =====
echo "<h3>Bước 4: Tài khoản hiện có</h3>";
$list = mysqli_query($conn, "SELECT id, username, role, LEFT(password,40) as pass_prev FROM users");
if (mysqli_num_rows($list) == 0) {
    echo "<div class='info'>ℹ️ Chưa có tài khoản nào trong bảng</div>";
} else {
    echo "<table><tr><th>ID</th><th>Username</th><th>Role</th><th>Password (40 ký tự đầu)</th></tr>";
    while ($row = mysqli_fetch_assoc($list)) {
        echo "<tr><td>{$row['id']}</td><td>{$row['username']}</td><td>{$row['role']}</td><td><code>{$row['pass_prev']}...</code></td></tr>";
    }
    echo "</table>";
}

// ===== Bước 5: Reset admin =====
echo "<h3>Bước 5: Đặt lại tài khoản Admin</h3>";

$new_pass = 'admin123';
$new_hash = password_hash($new_pass, PASSWORD_DEFAULT);

// Xóa admin cũ
mysqli_query($conn, "DELETE FROM users WHERE username = 'admin'");

// Tạo INSERT động dựa theo cột có thực
$fields = "username, password, role";
$values = "'admin', '$new_hash', 'admin'";

if ($has_ho_ten) { $fields .= ", ho_ten"; $values .= ", 'Quản trị viên'"; }
if ($has_email)  { $fields .= ", email";  $values .= ", 'admin@tuyensinh.edu.vn'"; }
if ($has_status) { $fields .= ", status"; $values .= ", 'Active'"; }

$sql = "INSERT INTO users ($fields) VALUES ($values)";

if (mysqli_query($conn, $sql)) {
    echo "<div class='ok'>✅ Đã tạo lại tài khoản admin thành công!</div>";
    echo "<div class='info'>
        <strong>🔑 Thông tin đăng nhập admin:</strong><br><br>
        Username: <code style='font-size:1.1em'>admin</code><br>
        Password: <code style='font-size:1.1em'>admin123</code>
    </div>";
} else {
    echo "<div class='err'>❌ Lỗi INSERT: " . mysqli_error($conn) . "<br>SQL: " . htmlspecialchars($sql) . "</div>";
    exit;
}

// ===== Bước 6: Xác minh =====
echo "<h3>Bước 6: Xác minh password_verify()</h3>";
$vr = mysqli_query($conn, "SELECT password FROM users WHERE username='admin' AND role='admin'");
if ($vr && mysqli_num_rows($vr) > 0) {
    $ar = mysqli_fetch_assoc($vr);
    if (password_verify($new_pass, $ar['password'])) {
        echo "<div class='ok'>✅ password_verify() xác nhận đúng — Đăng nhập sẽ hoạt động!</div>";
    } else {
        echo "<div class='err'>❌ password_verify() thất bại. Thử dùng mật khẩu plain text.</div>";
        // Fallback: lưu plain text
        mysqli_query($conn, "UPDATE users SET password='$new_pass' WHERE username='admin'");
        echo "<div class='info'>ℹ️ Đã lưu mật khẩu dạng plain text thay thế. Thử đăng nhập lại.</div>";
    }
} else {
    echo "<div class='err'>❌ Không tìm thấy admin sau khi INSERT</div>";
}
?>

<hr style="margin:30px 0">
<div style="background:#fefce8; border:2px solid #eab308; padding:25px; border-radius:12px;">
    <h3 style="color:#92400e; margin-top:0">🎯 Thực hiện bước tiếp theo</h3>
    <ol style="line-height:2">
        <li>Nhấn vào đây để đăng nhập: <a href="admin/login.php" target="_blank" style="font-weight:bold; font-size:1.1em">→ admin/login.php</a></li>
        <li>Username: <code>admin</code> &nbsp; Password: <code>admin123</code></li>
        <li>Sau khi đăng nhập thành công, hãy <strong style="color:red">XÓA</strong> file <code>fix_login_admin.php</code> này</li>
    </ol>
</div>
</body>
</html>
