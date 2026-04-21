<?php
session_start();
include_once(__DIR__ . '/../../config/database.php');
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo 'Bạn không có quyền.';
    exit;
}
$order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
$status = isset($_POST['status']) ? $_POST['status'] : '';
$valid_status = ['pending','processing','paid','shipped','completed','cancelled'];
if ($order_id > 0 && in_array($status, $valid_status)) {
    $stmt = $conn->prepare("UPDATE orders SET status=? WHERE id=?");
    $stmt->bind_param('si', $status, $order_id);
    if ($stmt->execute()) {
        echo 'success';
    } else {
        echo 'fail';
    }
    $stmt->close();
} else {
    echo 'fail';
}
