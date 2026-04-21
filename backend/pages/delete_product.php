<?php
session_start();
include_once(__DIR__ . '/../../config/database.php');

// Chỉ admin mới được xóa
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../frontend/pages/login.php");
    exit;
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Xóa ảnh vật lý trước
    $img_res = mysqli_query($conn, "SELECT image_path FROM product_images WHERE product_id = $id");
    while ($img = mysqli_fetch_assoc($img_res)) {
        $file = __DIR__ . '/../../' . $img['image_path'];
        if (file_exists($file)) {
            unlink($file);
        }
    }

    // Xóa dữ liệu trong bảng phụ (nếu có)
    mysqli_query($conn, "DELETE FROM product_images WHERE product_id = $id");

    // Xóa sản phẩm
    mysqli_query($conn, "DELETE FROM products WHERE id = $id");
}

// Quay về danh sách sản phẩm
header("Location: products.php");
exit;
