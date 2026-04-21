<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Lấy thông tin user
include_once(__DIR__ . '/../../config/database.php');
$user_id = $_SESSION['user_id'];
$user_sql = "SELECT * FROM users WHERE id = $user_id LIMIT 1";
$user = mysqli_fetch_assoc(mysqli_query($conn, $user_sql));

$selected_products = [];
if (isset($_POST['selected_products']) && is_array($_POST['selected_products'])) {

    // Xử lý đặt hàng khi submit
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['payment_method'])) {
        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $payment_method = $_POST['payment_method'] ?? '';
        $selected_products = $_POST['selected_products'] ?? [];

        if ($name && $phone && $address && $payment_method && !empty($selected_products)) {

            // Lưu thông tin người dùng (nếu thay đổi)
            $update_sql = "UPDATE users SET fullname='$name', phone='$phone', address='$address' WHERE id=$user_id";
            mysqli_query($conn, $update_sql);

            // Nếu chọn VNPay hoặc Bank thì lưu thông tin vào session và chuyển hướng sang trang thanh toán
            if ($payment_method === 'vnpay' || $payment_method === 'bank') {
                $_SESSION['checkout_name'] = $name;
                $_SESSION['checkout_phone'] = $phone;
                $_SESSION['checkout_address'] = $address;
                $_SESSION['checkout_products'] = $selected_products;
                header('Location: ' . ($payment_method === 'vnpay' ? 'vnpay_create_payment.php' : 'bank_transfer.php'));
                exit;
            }

            // Nếu chọn COD thì tạo đơn hàng như cũ
            $cart = $_SESSION['cart'] ?? [];
            $total = 0;
            foreach ($selected_products as $pid) {
                $pid = intval($pid);
                $quantity = $cart[$pid] ?? 1;
                $product_sql = "SELECT * FROM products WHERE id = $pid";
                $product = mysqli_fetch_assoc(mysqli_query($conn, $product_sql));
                if (!$product) continue;
                $price = (isset($product['price_sale']) && $product['price_sale'] > 0) ? $product['price_sale'] : $product['price'];
                $subtotal = $price * $quantity;
                $total += $subtotal;
            }

            // Tạo đơn hàng
            $order_sql = "INSERT INTO orders (user_id, shipping_name, payment_method, total, status, shipping_address, shipping_phone, created_at)
                          VALUES ($user_id, '$name', '$payment_method', $total, 'pending', '$address', '$phone', NOW())";
            mysqli_query($conn, $order_sql);
            $order_id = mysqli_insert_id($conn);

            // Gửi email xác nhận đơn hàng cho user
            include_once(__DIR__ . '/../modules/mailer.php');
            $user_email = $user['email'] ?? '';
            if ($user_email) {
                // Chuẩn bị dữ liệu sản phẩm cho email
                $orderDetails = [];
                foreach ($selected_products as $pid) {
                    $pid = intval($pid);
                    $quantity = $cart[$pid] ?? 1;
                    $p = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM products WHERE id = $pid"));
                    if (!$p) continue;
                    $price = (isset($p['price_sale']) && $p['price_sale'] > 0) ? $p['price_sale'] : $p['price'];
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

            // Lưu chi tiết đơn hàng
            foreach ($selected_products as $pid) {
                $pid = intval($pid);
                $quantity = $cart[$pid] ?? 1;
                $product_sql = "SELECT * FROM products WHERE id = $pid";
                $product = mysqli_fetch_assoc(mysqli_query($conn, $product_sql));
                if (!$product) continue;
                $price = (isset($product['price_sale']) && $product['price_sale'] > 0) ? $product['price_sale'] : $product['price'];
                $detail_sql = "INSERT INTO order_items (order_id, product_id, quantity, price)
                               VALUES ($order_id, $pid, $quantity, $price)";
                mysqli_query($conn, $detail_sql);
            }

            // Xóa sản phẩm đã đặt khỏi giỏ hàng
            foreach ($selected_products as $pid) {
                unset($_SESSION['cart'][$pid]);
            }

            // Cập nhật bảng user_cart
            if (isset($_SESSION['user_id'])) {
                $cart_json = mysqli_real_escape_string($conn, json_encode($_SESSION['cart']));
                $sql = "REPLACE INTO user_cart (user_id, cart_data) VALUES ($user_id, '$cart_json')";
                mysqli_query($conn, $sql);
            }

            // Chuyển về trang thành công
            header('Location: order_success.php?order_id=' . $order_id);
            exit;

        } else {
            $error = 'Vui lòng nhập đầy đủ thông tin và chọn sản phẩm.';
        }
    }

    // Hiển thị danh sách sản phẩm được chọn
    $cart = $_SESSION['cart'] ?? [];
    $ids = array_map('intval', $_POST['selected_products']);
    $ids_str = implode(',', $ids);
    $sql = "SELECT * FROM products WHERE id IN ($ids_str)";
    $result = mysqli_query($conn, $sql);
    while ($row = mysqli_fetch_assoc($result)) {
    $row['quantity'] = $cart[$row['id']];
    $price = (isset($row['price_sale']) && $row['price_sale'] > 0) ? $row['price_sale'] : $row['price'];
    $row['subtotal'] = $row['quantity'] * $price;
    $row['display_price'] = $price;
    $selected_products[] = $row;
    }
} else {
    header('Location: cart.php');
    exit;
}

// Các phương thức thanh toán
$payment_methods = [
    'cod' => 'Thanh toán khi nhận hàng',
    'bank' => 'Chuyển khoản ngân hàng',
    'vnpay' => 'Thanh toán qua VNPay',
];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Thanh toán - VietOffice</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../../assets/custom/css/style.css">
    <style>
        .breadcrumb-section {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            padding: 20px 0;
            margin-bottom: 30px;
        }
        .breadcrumb-section .breadcrumb {
            background: transparent;
            margin: 0;
        }
        .breadcrumb-section .breadcrumb-item,
        .breadcrumb-section .breadcrumb-item a {
            color: white;
            font-weight: 500;
        }
        .breadcrumb-section .breadcrumb-item.active {
            color: rgba(255,255,255,0.8);
        }
        .checkout-card {
            background: white;
            border: none;
            border-radius: 20px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.1);
            overflow: hidden;
            animation: fadeIn 0.5s ease;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .checkout-header {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 25px 30px;
            font-size: 1.5rem;
            font-weight: 700;
        }
        .info-section {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .payment-option {
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 15px;
            transition: all 0.3s;
            cursor: pointer;
        }
        .payment-option:hover {
            border-color: #28a745;
            background: #f8fff9;
        }
        .payment-option input[type="radio"]:checked + label {
            color: #28a745;
            font-weight: 700;
        }
        .step-progress {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            position: relative;
        }
        .step-progress::before {
            content: '';
            position: absolute;
            top: 20px;
            left: 0;
            right: 0;
            height: 2px;
            background: #e9ecef;
            z-index: 0;
        }
        .step-item {
            text-align: center;
            position: relative;
            z-index: 1;
            flex: 1;
        }
        .step-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e9ecef;
            color: #999;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-bottom: 8px;
        }
        .step-item.active .step-circle {
            background: #28a745;
            color: white;
        }
        .step-item.completed .step-circle {
            background: #20c997;
            color: white;
        }
    </style>
</head>
<body>
<?php include_once(__DIR__ . '/../layouts/partials/header.php'); ?>

<!-- Breadcrumb -->
<section class="breadcrumb-section">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="../../index.php"><i class="fas fa-home me-1"></i>Trang chủ</a></li>
                <li class="breadcrumb-item"><a href="cart.php">Giỏ hàng</a></li>
                <li class="breadcrumb-item active" aria-current="page">Thanh toán</li>
            </ol>
        </nav>
    </div>
</section>
<div class="container mb-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Step Progress -->
            <div class="step-progress">
                <div class="step-item completed">
                    <div class="step-circle"><i class="fas fa-shopping-cart"></i></div>
                    <div class="small">Giỏ hàng</div>
                </div>
                <div class="step-item active">
                    <div class="step-circle"><i class="fas fa-credit-card"></i></div>
                    <div class="small">Thanh toán</div>
                </div>
                <div class="step-item">
                    <div class="step-circle"><i class="fas fa-check"></i></div>
                    <div class="small">Hoàn tất</div>
                </div>
            </div>

            <div class="checkout-card">
                <div class="checkout-header">
                    <i class="fas fa-credit-card me-2"></i>Thanh toán đơn hàng
                </div>
                <div class="card-body p-4">

                    <form method="post" action="">
                        <?php foreach ($selected_products as $p): ?>
                            <input type="hidden" name="selected_products[]" value="<?= $p['id'] ?>">
                        <?php endforeach; ?>

                        <div class="row">
                            <div class="col-lg-7 mb-4">
                                <div class="info-section">
                                    <h5 class="fw-bold mb-3">
                                        <i class="fas fa-user-circle me-2 text-primary"></i>Thông tin người nhận
                                    </h5>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">
                                            <i class="fas fa-user me-2 text-muted"></i>Họ tên
                                        </label>
                                        <input type="text" class="form-control form-control-lg rounded-3"
                                               name="name"
                                               value="<?= htmlspecialchars($user['fullname'] ?? '') ?>"
                                               required placeholder="Nhập họ tên">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">
                                            <i class="fas fa-phone me-2 text-muted"></i>Số điện thoại
                                        </label>
                                        <input type="text" class="form-control form-control-lg rounded-3"
                                               name="phone"
                                               value="<?= htmlspecialchars($user['phone'] ?? '') ?>"
                                               required placeholder="Nhập số điện thoại">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">
                                            <i class="fas fa-map-marker-alt me-2 text-muted"></i>Địa chỉ nhận hàng
                                        </label>
                                        <textarea class="form-control form-control-lg rounded-3" rows="3"
                                               name="address"
                                               required placeholder="Nhập địa chệ nhận hàng"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                                    </div>
                                </div>

                                <!-- Phương thức thanh toán -->
                                <div class="info-section">
                                    <h5 class="fw-bold mb-3">
                                        <i class="fas fa-wallet me-2 text-success"></i>Phương thức thanh toán
                                    </h5>
                                    <?php foreach ($payment_methods as $key => $label): 
                                        $icons = ['cod' => 'truck', 'bank' => 'university', 'vnpay' => 'credit-card'];
                                    ?>
                                        <div class="payment-option">
                                            <input class="form-check-input me-2" type="radio" name="payment_method"
                                                   value="<?= $key ?>" id="pm_<?= $key ?>"
                                                   <?= $key == 'cod' ? 'checked' : '' ?> required>
                                            <label class="form-check-label fw-semibold" for="pm_<?= $key ?>" style="cursor: pointer;">
                                                <i class="fas fa-<?= $icons[$key] ?> me-2"></i><?= $label ?>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <div class="col-lg-5 mb-4">
                                <div class="info-section sticky-top" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); top: 20px;">
                                    <h5 class="fw-bold mb-3">
                                        <i class="fas fa-shopping-bag me-2 text-warning"></i>Đơn hàng của bạn
                                    </h5>
                                    <div style="max-height: 350px; overflow-y: auto;">
                                        <?php $total = 0; foreach ($selected_products as $p): $total += $p['subtotal']; ?>
                                        <div class="mb-3 pb-3" style="border-bottom: 1px solid #dee2e6;">
                                            <div class="d-flex justify-content-between align-items-start gap-2">
                                                <div style="flex: 1; min-width: 0;">
                                                    <div class="fw-semibold mb-1" style="font-size: 0.95rem; word-wrap: break-word;"><?= htmlspecialchars($p['name']) ?></div>
                                                    <small class="text-muted">Số lượng: <?= $p['quantity'] ?></small>
                                                </div>
                                                <div class="fw-bold text-success text-nowrap ms-2"><?= number_format($p['subtotal']) ?>₫</div>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <div class="mt-3 pt-3 border-top">
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Tạm tính:</span>
                                            <span class="fw-bold"><?= number_format($total) ?>₫</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Phí vận chuyển:</span>
                                            <span class="text-success fw-bold">Miễn phí</span>
                                        </div>
                                        <div class="d-flex justify-content-between mt-3 pt-3 border-top">
                                            <h5 class="mb-0">Tổng cộng:</h5>
                                            <h5 class="mb-0 text-danger"><?= number_format($total) ?>₫</h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-success btn-lg px-5 py-3 fw-bold rounded-3" 
                                    style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); border: none; font-size: 1.1rem;">
                                <i class="fas fa-check-circle me-2"></i>Xác nhận đặt hàng
                            </button>
                            <br>
                            <a href="cart.php" class="btn btn-outline-secondary mt-3 px-4 py-2 rounded-3">
                                <i class="fas fa-arrow-left me-2"></i>Quay lại giỏ hàng
                            </a>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>
<?php include_once(__DIR__ . '/../layouts/partials/footer.php'); ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
