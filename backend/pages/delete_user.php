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

// Không cho xóa chính chủ tài khoản
if ($id == intval($_SESSION['user_id'])) {
    echo "Không thể tự xóa chính mình!";
    exit;
}

$sql = "DELETE FROM users WHERE id = $id";
if (mysqli_query($conn, $sql)) {
    echo "Xóa người dùng thành công";
} else {
    echo "Có lỗi khi xóa: " . mysqli_error($conn);
}
