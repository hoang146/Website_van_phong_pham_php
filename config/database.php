<?php
// File: config/database.php

$host = "localhost";   // máy chủ
$user = "root";        // user mặc định của XAMPP
$pass = "";            // mật khẩu mặc định trống
$db   = "vanphongpham"; // tên database bạn đã tạo

// Tạo kết nối
$conn = mysqli_connect($host, $user, $pass, $db);

// Kiểm tra kết nối
if (!$conn) {
    die("Kết nối thất bại: " . mysqli_connect_error());
}

// Thiết lập UTF-8 để hỗ trợ tiếng Việt
mysqli_set_charset($conn, "utf8");
?>
