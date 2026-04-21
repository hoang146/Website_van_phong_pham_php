<?php
// Trang dashboard admin hiện đại với thống kê đơn giản
session_start();
include_once(__DIR__ . '/../../config/database.php');
include_once(__DIR__ . '/../layouts/config.php');
$PAGE_TITLE = 'Dashboard Admin';
// Thống kê đơn giản
$total_users = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users"))['total'] ?? 0;
$total_products = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM products"))['total'] ?? 0;
$total_orders = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM orders"))['total'] ?? 0;
$total_sales = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(price * quantity) as total FROM order_items"))['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <?php include_once(__DIR__ . '/../layouts/head.php'); ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../../assets/custom/css/style.css">
</head>
<body class="bg-light min-vh-100 d-flex flex-column">
<?php include_once(__DIR__ . '/../layouts/partials/header.php'); ?>
<div class="container py-5">
  <h2 class="mb-4 text-center fw-bold text-primary"><i class="fa fa-tachometer-alt me-2"></i> Dashboard Admin</h2>
  <div class="row g-4 mb-5">
    <div class="col-md-3">
      <div class="card shadow-sm border-0 rounded-4 text-center">
        <div class="card-body">
          <div class="mb-2"><i class="fa fa-users fa-2x text-info"></i></div>
          <h5 class="fw-bold">Người dùng</h5>
          <span class="fs-3 fw-bold text-dark"><?= $total_users ?></span>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card shadow-sm border-0 rounded-4 text-center">
        <div class="card-body">
          <div class="mb-2"><i class="fa fa-box fa-2x text-success"></i></div>
          <h5 class="fw-bold">Sản phẩm</h5>
          <span class="fs-3 fw-bold text-dark"><?= $total_products ?></span>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card shadow-sm border-0 rounded-4 text-center">
        <div class="card-body">
          <div class="mb-2"><i class="fa fa-shopping-cart fa-2x text-warning"></i></div>
          <h5 class="fw-bold">Đơn hàng</h5>
          <span class="fs-3 fw-bold text-dark"><?= $total_orders ?></span>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card shadow-sm border-0 rounded-4 text-center">
        <div class="card-body">
          <div class="mb-2"><i class="fa fa-money-bill-wave fa-2x text-danger"></i></div>
          <h5 class="fw-bold">Doanh thu</h5>
          <span class="fs-3 fw-bold text-success"><?= number_format($total_sales) ?>₫</span>
        </div>
      </div>
    </div>
  </div>
  <div class="card shadow-lg border-0 rounded-4">
    <div class="card-body p-4">
      <h4 class="fw-bold mb-3 text-secondary"><i class="fa fa-chart-bar me-2"></i> Thống kê tổng quan</h4>
      <ul class="list-group list-group-flush">
        <li class="list-group-item d-flex justify-content-between align-items-center">
          <span><i class="fa fa-user me-2 text-info"></i> Tổng số người dùng</span>
          <span class="badge bg-info rounded-pill fs-6"><?= $total_users ?></span>
        </li>
        <li class="list-group-item d-flex justify-content-between align-items-center">
          <span><i class="fa fa-box me-2 text-success"></i> Tổng số sản phẩm</span>
          <span class="badge bg-success rounded-pill fs-6"><?= $total_products ?></span>
        </li>
        <li class="list-group-item d-flex justify-content-between align-items-center">
          <span><i class="fa fa-shopping-cart me-2 text-warning"></i> Tổng số đơn hàng</span>
          <span class="badge bg-warning rounded-pill fs-6 text-dark"><?= $total_orders ?></span>
        </li>
        <li class="list-group-item d-flex justify-content-between align-items-center">
          <span><i class="fa fa-money-bill-wave me-2 text-danger"></i> Tổng doanh thu</span>
          <span class="badge bg-danger rounded-pill fs-6"><?= number_format($total_sales) ?>₫</span>
        </li>
      </ul>
    </div>
  </div>
</div>
<?php include_once(__DIR__ . '/../layouts/partials/footer.php'); ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
