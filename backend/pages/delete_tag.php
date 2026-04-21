<?php
session_start();
include_once(__DIR__ . '/../../config/database.php');

header('Content-Type: text/plain; charset=utf-8');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo "Bạn không có quyền thực hiện.";
    exit;
}

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo "Thiếu tham số id.";
    exit;
}

$id = intval($_GET['id']);

// Kiểm tra tag có tồn tại không (tùy chọn, để tránh lỗi nếu ID sai)
$check_sql = "SELECT id FROM tags WHERE id = $id";
$check_result = mysqli_query($conn, $check_sql);
if (mysqli_num_rows($check_result) == 0) {
    echo "Tag không tồn tại!";
    exit;
}

$sql = "DELETE FROM tags WHERE id = $id";
if (mysqli_query($conn, $sql)) {
    echo "Xóa tag thành công";
} else {
    echo "Có lỗi khi xóa: " . mysqli_error($conn);
}
?>