<?php
session_start();
include_once(__DIR__ . '/../../config/database.php');
require_once(__DIR__ . '/PayLib.php');
require_once(__DIR__ . '/Util.php');

$vnp_HashSecret = "QQ1V5O6TS0CCRCWFKTQ21H6NUS5DEDFP";

$pay = new PayLib();
foreach ($_GET as $key => $value) {
    if (substr($key, 0, 4) == "vnp_") {
        $pay->AddResponseData($key, $value);
    }
}

$vnp_SecureHash = $_GET['vnp_SecureHash'] ?? '';
$order_id = $pay->GetResponseData("vnp_TxnRef");
$vnp_ResponseCode = $pay->GetResponseData("vnp_ResponseCode");
$vnp_Amount = $pay->GetResponseData("vnp_Amount");
$vnp_TransactionNo = $pay->GetResponseData("vnp_TransactionNo");

$isValid = $pay->ValidateSignature($vnp_SecureHash, $vnp_HashSecret);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta charset="UTF-8">
    <title>Kết quả thanh toán</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/custom/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<?php include_once(__DIR__ . '/../layouts/partials/header.php'); ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card shadow-lg border-0 rounded-4 text-center">
                <div class="card-body p-5">
                    <?php
                    if ($isValid) {
                        if ($vnp_ResponseCode == "00") {
                            // Tạo đơn hàng khi thanh toán thành công
                            $user_id = $_SESSION['user_id'] ?? 0;
                            $name = $_SESSION['checkout_name'] ?? '';
                            $phone = $_SESSION['checkout_phone'] ?? '';
                            $address = $_SESSION['checkout_address'] ?? '';
                            $selected_products = $_SESSION['checkout_products'] ?? [];
                            $cart = $_SESSION['cart'] ?? [];
                            $total = 0;
                            foreach ($selected_products as $pid) {
                                $pid = intval($pid);
                                $quantity = $cart[$pid] ?? 1;
                                $product_sql = "SELECT * FROM products WHERE id = $pid";
                                $product = mysqli_fetch_assoc(mysqli_query($conn, $product_sql));
                                if (!$product) continue;
                                $price = (isset($product['sale_price']) && $product['sale_price'] > 0) ? $product['sale_price'] : $product['price'];
                                $subtotal = $price * $quantity;
                                $total += $subtotal;
                            }
                            $order_sql = "INSERT INTO orders (user_id, shipping_name, payment_method, total, status, shipping_address, shipping_phone, created_at) VALUES ($user_id, '$name', 'vnpay', $total, 'paid', '$address', '$phone', NOW())";
                            mysqli_query($conn, $order_sql);
                            $order_id = mysqli_insert_id($conn);
                            foreach ($selected_products as $pid) {
                                $pid = intval($pid);
                                $quantity = $cart[$pid] ?? 1;
                                $product_sql = "SELECT * FROM products WHERE id = $pid";
                                $product = mysqli_fetch_assoc(mysqli_query($conn, $product_sql));
                                if (!$product) continue;
                                $price = (isset($product['sale_price']) && $product['sale_price'] > 0) ? $product['sale_price'] : $product['price'];
                                $detail_sql = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES ($order_id, $pid, $quantity, $price)";
                                mysqli_query($conn, $detail_sql);
                            }
                            // Gửi email xác nhận đơn hàng cho user
                            include_once(__DIR__ . '/../modules/mailer.php');
                            $user_email = $_SESSION['user_email'] ?? '';
                            if ($user_email) {
                                $orderDetails = [];
                                foreach ($selected_products as $pid) {
                                    $pid = intval($pid);
                                    $quantity = $cart[$pid] ?? 1;
                                    $p = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM products WHERE id = $pid"));
                                    if (!$p) continue;
                                    $price = (isset($p['sale_price']) && $p['sale_price'] > 0) ? $p['sale_price'] : $p['price'];
                                    $orderDetails[] = [
                                        'name' => $p['name'],
                                        'quantity' => $quantity,
                                        'price' => $price
                                    ];
                                }
                                $orderUrl = 'http://localhost:3000/frontend/pages/order_detail.php?order_id=' . $order_id;
                                $res = send_order_confirmation_email($user_email, $order_id, $orderDetails, $total, $orderUrl);
                                if (!$res['success']) {
                                    file_put_contents(__DIR__ . '/../../logs/email_errors.log', date('c') . " - Mail gửi thất bại cho order {$order_id} -> {$user_email}: " . ($res['error'] ?? 'Không rõ') . "\n", FILE_APPEND);
                                }
                            }
                            // Xóa sản phẩm đã đặt khỏi giỏ hàng
                            foreach ($selected_products as $pid) {
                                unset($_SESSION['cart'][$pid]);
                            }
                            // Cập nhật bảng user_cart
                            $cart_json = mysqli_real_escape_string($conn, json_encode($_SESSION['cart']));
                            $sql = "REPLACE INTO user_cart (user_id, cart_data) VALUES ($user_id, '$cart_json')";
                            mysqli_query($conn, $sql);
                            ?>
                            <span class="display-3 text-success"><i class="fa fa-check-circle"></i></span>
                            <h2 class="text-success fw-bold mt-3 mb-2">Thanh toán thành công!</h2>
                            <p class="lead text-muted mb-3">
                                Mã giao dịch: <span class="fw-bold text-primary"><?= $vnp_TransactionNo ?></span>
                            </p>
                            <p class="lead text-muted mb-0">
                                Cảm ơn bạn đã mua sắm tại <span class="fw-bold text-primary">Văn Phòng Phẩm Online</span>.
                            </p>
                            <?php
                        } else {
                            ?>
                            <span class="display-3 text-danger"><i class="fa fa-times-circle"></i></span>
                            <h2 class="text-danger fw-bold mt-3 mb-2">Thanh toán thất bại!</h2>
                            <p class="lead text-muted mb-0">Mã lỗi: <?= htmlspecialchars($vnp_ResponseCode) ?></p>
                            <?php
                        }
                    } else {
                        ?>
                        <span class="display-3 text-danger"><i class="fa fa-exclamation-triangle"></i></span>
                        <h2 class="text-danger fw-bold mt-3 mb-2">Thanh toán thất bại</h2>
                        <p class="lead text-muted mb-0">Vui lòng liên hệ bộ phận hỗ trợ.</p>
                        <?php
                    }
                    ?>

                    <div class="row justify-content-center g-3 mt-4">
                        <div class="col-md-6 col-lg-3">
                            <a href="/frontend/pages/products_list.php" class="btn btn-primary w-100 py-2 fw-bold rounded-3">
                                <i class="fa fa-shopping-bag me-1"></i> Mua sắm
                            </a>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <a href="../../index.php" class="btn btn-secondary w-100 py-2 fw-bold rounded-3">
                                <i class="fa fa-home me-1"></i> Trang chủ
                            </a>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <a href="/frontend/pages/order_detail.php?order_id=<?= $order_id ?>" class="btn btn-outline-success w-100 py-2 fw-bold rounded-3">
                                <i class="fa fa-file-text-o me-1"></i> Xem hóa đơn
                            </a>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <a href="/frontend/pages/order_history.php" class="btn btn-outline-info w-100 py-2 fw-bold rounded-3">
                                <i class="fa fa-history me-1"></i> Lịch sử mua
                            </a>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once(__DIR__ . '/../layouts/partials/footer.php'); ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
