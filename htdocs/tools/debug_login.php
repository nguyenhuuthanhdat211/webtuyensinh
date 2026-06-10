<?php
include '../config/database.php';

if (!$conn) {
    die("<p style='color:red'>❌ Lỗi kết nối DB: " . mysqli_connect_error() . "</p>");
}

echo "<style>body{font-family:monospace;padding:20px;} .ok{color:green;} .err{color:red;} table{border-collapse:collapse;margin:10px 0;} td,th{border:1px solid #ddd;padding:8px;}</style>";

echo "<h2>1. Kiểm tra DB tuyensinh</h2>";
$db_check = mysqli_query($conn, "SELECT DATABASE() as db");
$db_row = mysqli_fetch_assoc($db_check);
echo "<p class='ok'>Database đang dùng: <b>" . $db_row['db'] . "</b></p>";

echo "<h2>2. Bảng trong DB</h2>";
$tables = mysqli_query($conn, "SHOW TABLES");
while($t = mysqli_fetch_row($tables)) echo "<p>- {$t[0]}</p>";

echo "<h2>3. Tất cả user trong bảng users</h2>";
$users = mysqli_query($conn, "SELECT id, username, password, role FROM users");
if (!$users) {
    echo "<p class='err'>Lỗi query: " . mysqli_error($conn) . "</p>";
} elseif (mysqli_num_rows($users) == 0) {
    echo "<p class='err'>Bảng users RỖNG - chưa có tài khoản nào!</p>";
} else {
    echo "<table><tr><th>ID</th><th>Username</th><th>Role</th><th>Password stored</th><th>password_verify('admin123')</th><th>password_verify('admin')</th></tr>";
    while ($u = mysqli_fetch_assoc($users)) {
        $v1 = password_verify('admin123', $u['password']) ? "✅ ĐÚNG" : "❌ Sai";
        $v2 = password_verify('admin', $u['password']) ? "✅ ĐÚNG" : "❌ Sai";
        $v3 = (md5('admin123') === $u['password']) ? "✅ ĐÚNG (md5)" : "";
        echo "<tr>
            <td>{$u['id']}</td>
            <td>{$u['username']}</td>
            <td>{$u['role']}</td>
            <td>" . htmlspecialchars(substr($u['password'],0,60)) . "...</td>
            <td>$v1 $v3</td>
            <td>$v2</td>
        </tr>";
    }
    echo "</table>";
}

echo "<h2>4. Reset admin ngay bây giờ</h2>";

// Xóa admin cũ
mysqli_query($conn, "DELETE FROM users WHERE username='admin'");
$deleted = mysqli_affected_rows($conn);
echo "<p>Đã xóa $deleted bản ghi admin cũ</p>";

// Tạo hash mới
$new_hash = password_hash('admin123', PASSWORD_BCRYPT);
echo "<p>Hash mới tạo: " . htmlspecialchars(substr($new_hash, 0, 60)) . "...</p>";
echo "<p>Kiểm tra hash mới: " . (password_verify('admin123', $new_hash) ? "<span class='ok'>✅ ĐÚNG</span>" : "<span class='err'>❌ SAI</span>") . "</p>";

// Lấy cột thực tế
$cols = mysqli_query($conn, "DESCRIBE users");
$col_names = [];
while ($c = mysqli_fetch_assoc($cols)) $col_names[] = $c['Field'];
echo "<p>Các cột trong bảng users: " . implode(', ', $col_names) . "</p>";

// Build INSERT phù hợp
$fields = ['username', 'password', 'role'];
$vals   = ["'admin'", "'$new_hash'", "'admin'"];

if (in_array('ho_ten', $col_names)) { $fields[] = 'ho_ten'; $vals[] = "'Quản trị viên'"; }
if (in_array('email',  $col_names)) { $fields[] = 'email';  $vals[] = "'admin@ts.edu.vn'"; }
if (in_array('status', $col_names)) { $fields[] = 'status'; $vals[] = "'Active'"; }

$insert_sql = "INSERT INTO users (" . implode(',', $fields) . ") VALUES (" . implode(',', $vals) . ")";
$r = mysqli_query($conn, $insert_sql);

if ($r) {
    $new_id = mysqli_insert_id($conn);
    echo "<p class='ok'>✅ Đã tạo admin mới (ID=$new_id)</p>";
} else {
    echo "<p class='err'>❌ INSERT lỗi: " . mysqli_error($conn) . "</p>";
    echo "<p>SQL: " . htmlspecialchars($insert_sql) . "</p>";
}

echo "<h2>5. Xác minh đăng nhập cuối cùng</h2>";
$verify_q = mysqli_query($conn, "SELECT * FROM users WHERE username='admin' AND role='admin'");
if ($verify_q && mysqli_num_rows($verify_q) > 0) {
    $admin = mysqli_fetch_assoc($verify_q);
    if (password_verify('admin123', $admin['password'])) {
        echo "<div style='background:#ecfdf5;border:2px solid #10b981;padding:20px;border-radius:8px;'>
            <h3 style='color:#065f46;margin:0'>🎉 THÀNH CÔNG! Đăng nhập sẽ hoạt động!</h3>
            <p>→ Truy cập: <a href='admin/login.php'><b>admin/login.php</b></a></p>
            <p>Username: <b>admin</b> | Password: <b>admin123</b></p>
        </div>";
    } else {
        echo "<p class='err'>❌ password_verify vẫn thất bại sau khi reset — PHP có thể bị lỗi cấu hình</p>";
        // Emergency: lưu password plain text để test
        mysqli_query($conn, "UPDATE users SET password='admin123' WHERE username='admin'");
        echo "<div style='background:#fef3c7;border:1px solid #f59e0b;padding:15px;border-radius:8px;'>
            <b>⚠️ Đã lưu mật khẩu dạng plain text tạm thời!</b><br>
            Thử đăng nhập với: admin / admin123
        </div>";
    }
} else {
    echo "<p class='err'>❌ Không tìm thấy admin sau INSERT!</p>";
}
?>
