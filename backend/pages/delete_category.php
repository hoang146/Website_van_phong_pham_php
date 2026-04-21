<?php
session_start();
include_once(__DIR__ . '/../../config/database.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    exit("Bạn không có quyền!");
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $sql = "DELETE FROM categories WHERE id=$id";
    if (mysqli_query($conn, $sql)) {
        echo "Xóa danh mục thành công!";
    } else {
        echo "Có lỗi khi xóa: " . mysqli_error($conn);
    }
} else {
    echo "Thiếu ID!";
}
