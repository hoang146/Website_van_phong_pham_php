<?php
session_start();
include_once(__DIR__ . '/../../config/database.php');

// Lấy thông tin từ session
$user_id = $_SESSION['user_id'] ?? 0;
$name = $_SESSION['checkout_name'] ?? '';
$phone = $_SESSION['checkout_phone'] ?? '';
$address = $_SESSION['checkout_address'] ?? '';
$selected_products = $_SESSION['checkout_products'] ?? [];
$cart = $_SESSION['cart'] ?? [];

// Tính tổng tiền
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

$bankCode = 'MB'; // MB Bank
$accountNo = '76620102004999';
$accountName = 'PHAM DUY HOANG';
$amount = intval($total);
$content = "Thanh toan don hang cho " . $name;
$qr_url = "https://img.vietqr.io/image/{$bankCode}-{$accountNo}-compact2.png?amount={$amount}&addInfo=" . urlencode($content);

// Xử lý xác nhận chuyển khoản
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_transfer'])) {
  // Tạo đơn hàng
  $order_sql = "INSERT INTO orders (user_id, shipping_name, payment_method, total, status, shipping_address, shipping_phone, created_at) VALUES ($user_id, '$name', 'bank', $total, 'paid', '$address', '$phone', NOW())";
  mysqli_query($conn, $order_sql);
  $order_id = mysqli_insert_id($conn);
  foreach ($selected_products as $pid) {
  $pid = intval($pid);
  $quantity = $cart[$pid] ?? 1;
  $product_sql = "SELECT * FROM products WHERE id = $pid";
  $product = mysqli_fetch_assoc(mysqli_query($conn, $product_sql));
  if (!$product) continue;
  $price = (isset($product['price_sale']) && $product['price_sale'] > 0) ? $product['price_sale'] : $product['price'];
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
  // Xóa sản phẩm đã đặt khỏi giỏ hàng
  foreach ($selected_products as $pid) {
    unset($_SESSION['cart'][$pid]);
  }
  // Cập nhật bảng user_cart
  $cart_json = mysqli_real_escape_string($conn, json_encode($_SESSION['cart']));
  $sql = "REPLACE INTO user_cart (user_id, cart_data) VALUES ($user_id, '$cart_json')";
  mysqli_query($conn, $sql);
  // Chuyển về trang thành công
  header('Location: order_success.php?order_id=' . $order_id);
  exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Chuyển khoản ngân hàng</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <?php include_once(__DIR__ . '/../layouts/partials/header.php'); ?>
<div class="container py-5">
  <div class="card shadow-lg border-0 rounded-4 mx-auto" style="max-width: 600px;">
    <div class="card-body text-center p-4">
  <h3 class="fw-bold text-success mb-3"><i class="fa fa-qrcode me-2"></i> Thanh toán bằng VietQR</h3>
  <p class="mb-4 text-muted">Quét mã QR bên dưới để chuyển khoản cho đơn hàng của bạn.</p>

      <img src="<?= $qr_url ?>" alt="VietQR" class="img-fluid rounded mb-3" style="max-width:300px;">

      <div class="alert alert-info text-start">
        <strong>Ngân hàng:</strong> <?= $bankCode ?><br>
        <strong>Số tài khoản:</strong> <?= $accountNo ?><br>
        <strong>Chủ tài khoản:</strong> <?= $accountName ?><br>
        <strong>Số tiền:</strong> <?= number_format($amount) ?>₫<br>
        <strong>Nội dung:</strong> <?= htmlspecialchars($content) ?>
      </div>

      <form method="post">
        <button type="submit" name="confirm_transfer" class="btn btn-success fw-bold px-4 py-2">
          Tôi đã chuyển khoản
        </button>
      </form>
    </div>
  </div>
</div>
<?php include_once(__DIR__ . '/../layouts/partials/footer.php'); ?>
</body>
</html>
