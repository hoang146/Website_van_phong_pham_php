<?php
session_start();
include_once(__DIR__ . '/../../config/database.php');
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../frontend/pages/login.php");
    exit;
}
$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($order_id <= 0) {
    echo 'Hóa đơn không tồn tại.';
    exit;
}
$sql = "SELECT o.*, u.username, u.fullname FROM orders o LEFT JOIN users u ON o.user_id = u.id WHERE o.id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $order_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();
if (!$order) {
    echo 'Không tìm thấy hóa đơn.';
    exit;
}
// Lấy danh sách sản phẩm trong hóa đơn
$sql_items = "SELECT oi.*, p.name FROM order_items oi LEFT JOIN products p ON oi.product_id = p.id WHERE oi.order_id=?";
$stmt_items = $conn->prepare($sql_items);
$stmt_items->bind_param('i', $order_id);
$stmt_items->execute();
$result_items = $stmt_items->get_result();
$status_labels = [
  'pending' => 'Chờ xử lý',
  'processing' => 'Đang xử lý',
  'paid' => 'Đã thanh toán',
  'shipped' => 'Đã giao',
  'completed' => 'Hoàn tất',
  'cancelled' => 'Đã hủy'
];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Chi tiết hóa đơn #<?= $order['id'] ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <link rel="stylesheet" href="../../assets/custom/css/admin-dashboard.css">
</head>
<body class="bg-light">
<div class="d-flex flex-column min-vh-100" style="background:#f8f9fa;">
  <?php include_once(__DIR__ . '/../layouts/partials/header.php'); ?>
  <div class="d-flex flex-grow-1">
    <?php include_once(__DIR__ . '/../layouts/partials/sidebar.php'); ?>
    <main class="flex-grow-1 px-4" style="min-width:0;">
      <div class="container py-4">
      <!-- Page Header -->
      <div class="page-header mb-4">
        <h1 class="page-title"><i class="fa fa-file-invoice me-2"></i> Chi tiết hóa đơn #<?= $order['id'] ?></h1>
        <p class="page-subtitle">Thông tin chi tiết và sản phẩm trong đơn hàng</p>
      </div>

  <div class="order-info-card mb-4">
    <div class="card-body">
      <h5 class="fw-bold mb-3"><i class="fa fa-info-circle text-primary me-2"></i>Thông tin đơn hàng</h5>
      <div class="row g-4">
        <div class="col-md-6">
          <div class="info-item">
            <i class="fa fa-user text-primary me-2"></i>
            <span class="info-label">Người dùng:</span>
            <span class="info-value"><?= htmlspecialchars($order['username']) ?> (<?= htmlspecialchars($order['fullname']) ?>)</span>
          </div>
          <div class="info-item">
            <i class="fa fa-calendar text-primary me-2"></i>
            <span class="info-label">Ngày tạo:</span>
            <span class="info-value"><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></span>
          </div>
          <div class="info-item">
            <i class="fa fa-credit-card text-primary me-2"></i>
            <span class="info-label">Thanh toán:</span>
            <span class="info-value"><span class="badge bg-light text-dark"><?= htmlspecialchars($order['payment_method'] ?? 'Chưa rõ') ?></span></span>
          </div>
        </div>
        <div class="col-md-6">
          <div class="info-item">
            <i class="fa fa-map-marker-alt text-primary me-2"></i>
            <span class="info-label">Địa chỉ:</span>
            <span class="info-value"><?= htmlspecialchars($order['shipping_address']) ?></span>
          </div>
          <div class="info-item">
            <i class="fa fa-phone text-primary me-2"></i>
            <span class="info-label">Số điện thoại:</span>
            <span class="info-value"><?= htmlspecialchars($order['shipping_phone']) ?></span>
          </div>
          <div class="info-item">
            <i class="fa fa-info-circle text-primary me-2"></i>
            <span class="info-label">Tình trạng:</span>
            <span class="info-value"><span class="badge bg-success px-3 py-2"><?= $status_labels[$order['status']] ?? $order['status'] ?></span></span>
          </div>
        </div>
      </div>
      <div class="total-amount mt-4">
        <i class="fa fa-money-bill-wave text-success me-2"></i>
        <span class="total-label">Tổng tiền:</span>
        <span class="total-value"><?= number_format($order['total'],0,',','.') ?> đ</span>
      </div>
    </div>
  </div>
  <div class="products-card">
    <div class="card-body">
      <h5 class="fw-bold mb-3"><i class="fa fa-shopping-cart text-primary me-2"></i>Danh sách sản phẩm</h5>
      <div class="table-responsive">
        <table class="table align-middle products-table">
          <thead>
            <tr>
              <th><i class="fa fa-box me-1"></i>Tên sản phẩm</th>
              <th class="text-center"><i class="fa fa-sort-numeric-up me-1"></i>Số lượng</th>
              <th class="text-end"><i class="fa fa-tag me-1"></i>Giá</th>
              <th class="text-end"><i class="fa fa-calculator me-1"></i>Thành tiền</th>
            </tr>
          </thead>
          <tbody>
          <?php while($item = $result_items->fetch_assoc()): ?>
            <tr>
              <td><strong><?= htmlspecialchars($item['name']) ?></strong></td>
              <td class="text-center"><span class="badge bg-light text-dark"><?= $item['quantity'] ?></span></td>
              <td class="text-end"><?= number_format($item['price'],0,',','.') ?> đ</td>
              <td class="text-end"><strong class="text-success"><?= number_format($item['price'] * $item['quantity'],0,',','.') ?> đ</strong></td>
            </tr>
          <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <div class="mt-4">
    <a href="orders.php" class="btn btn-secondary px-4 py-2"><i class="fa fa-arrow-left me-2"></i>Quay lại danh sách</a>
    <a href="print_order.php?id=<?= $order['id'] ?>" class="btn btn-primary px-4 py-2 ms-2" target="_blank"><i class="fa fa-print me-2"></i>In hóa đơn</a>
  </div>
      </div>
    </main>
  </div>
  <?php include_once(__DIR__ . '/../layouts/partials/footer.php'); ?>
</div>

<style>
.page-header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 2rem;
  border-radius: 16px;
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.25);
}
.page-title {
  font-size: 1.75rem;
  font-weight: 700;
  margin: 0;
}
.page-subtitle {
  margin: 0.5rem 0 0 0;
  opacity: 0.95;
  font-size: 0.95rem;
}
.order-info-card, .products-card {
  background: white;
  border-radius: 16px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
  padding: 2rem;
}
.info-item {
  padding: 0.75rem 0;
  border-bottom: 1px solid #f0f0f0;
  display: flex;
  align-items: center;
}
.info-item:last-child {
  border-bottom: none;
}
.info-label {
  font-weight: 600;
  color: #2c3e50;
  margin-right: 0.5rem;
  min-width: 120px;
}
.info-value {
  color: #555;
}
.total-amount {
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
  padding: 1.5rem;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 2px solid #e0e6ed;
}
.total-label {
  font-size: 1.25rem;
  font-weight: 600;
  color: #2c3e50;
  margin-right: 1rem;
}
.total-value {
  font-size: 2rem;
  font-weight: 700;
  color: #28a745;
}
.products-table thead {
  background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
  color: white;
}
.products-table thead th {
  padding: 1rem;
  font-weight: 600;
  border: none;
}
.products-table tbody td {
  padding: 1rem;
  border-bottom: 1px solid #f0f0f0;
}
.products-table tbody tr:hover {
  background-color: #f8f9fa;
}
.btn {
  border-radius: 10px;
  font-weight: 600;
  transition: all 0.3s ease;
}
.btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
