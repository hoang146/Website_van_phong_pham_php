<?php
session_start();
include_once(__DIR__ . '/../../config/database.php');

// Khởi tạo giỏ hàng nếu chưa có
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}
$cart = $_SESSION['cart'];
$logged_in = isset($_SESSION['user_id']);

// Xử lý thêm sản phẩm vào giỏ
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = intval($_POST['product_id'] ?? 0);
    $quantity = max(1, intval($_POST['quantity'] ?? 1));
    if ($product_id > 0) {
        if (isset($cart[$product_id])) {
            $cart[$product_id] += $quantity;
        } else {
            $cart[$product_id] = $quantity;
        }
        $_SESSION['cart'] = $cart;
        // Nếu đã đăng nhập, lưu giỏ hàng vào database
        if ($logged_in) {
            $user_id = $_SESSION['user_id'];
            $cart_json = mysqli_real_escape_string($conn, json_encode($cart));
            $sql = "REPLACE INTO user_cart (user_id, cart_data) VALUES ($user_id, '$cart_json')";
            mysqli_query($conn, $sql);
        }
        // Trả về thông báo thành công nếu là AJAX
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            echo 'OK';
            exit;
        }
        // Chuyển hướng về giỏ hàng nếu là submit thường
        header('Location: cart.php?added=1');
        exit;
    }
}
// Nếu không phải POST, chuyển về trang chủ
header('Location: ../../index.php');
exit;
