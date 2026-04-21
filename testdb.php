<?php
$servername = "localhost";
$username = "root";   // mặc định của XAMPP
$password = "";       // để trống
$dbname = "vanphongpham"; // thay bằng tên database của bạn

$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
    die("Kết nối thất bại: " . mysqli_connect_error());
}
echo "✅ Kết nối thành công!";
?>
