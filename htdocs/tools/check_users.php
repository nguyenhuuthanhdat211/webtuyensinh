<?php
include '../config/database.php';

$res = mysqli_query($conn, "DESCRIBE users");
echo "<h2>Cấu trúc bảng users</h2>";
echo "<table border='1'>";
echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
while($row = mysqli_fetch_assoc($res)) {
    echo "<tr>";
    foreach($row as $val) echo "<td>$val</td>";
    echo "</tr>";
}
echo "</table>";

$res2 = mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role='admin'");
$row2 = mysqli_fetch_assoc($res2);
echo "<p>Số lượng admin hiện có: " . $row2['total'] . "</p>";

$res3 = mysqli_query($conn, "SELECT username, role FROM users");
echo "<h3>Danh sách tài khoản:</h3><ul>";
while($row3 = mysqli_fetch_assoc($res3)) {
    echo "<li>User: " . $row3['username'] . " - Role: " . $row3['role'] . "</li>";
}
echo "</ul>";
?>
