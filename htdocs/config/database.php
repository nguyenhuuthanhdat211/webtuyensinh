<?php
mysqli_report(MYSQLI_REPORT_OFF);

$host = "sql107.infinityfree.com";
$user = "if0_41979554";
$pass = "vannhat123456";
$db   = "if0_41979554_db_tuyensinh";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Ket noi that bai: " . mysqli_connect_error());
}

// Bắt buộc dùng UTF-8 để hiển thị tiếng Việt đúng
mysqli_set_charset($conn, "utf8mb4");
mysqli_query($conn, "SET NAMES 'utf8mb4'");
mysqli_query($conn, "SET CHARACTER SET utf8mb4");
mysqli_query($conn, "SET collation_connection = 'utf8mb4_unicode_ci'");
?>
