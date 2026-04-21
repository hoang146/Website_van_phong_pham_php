<?php
session_start();
include_once(__DIR__ . '/../../config/database.php');
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../frontend/pages/login.php");
    exit;
}
// Thống kê tổng số đơn hàng, doanh thu, sản phẩm, người dùng
$total_orders = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM orders"))['total'];
$total_revenue = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(total) as revenue FROM orders WHERE status!='cancelled'"))['revenue'] ?? 0;
$total_products = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM products"))['total'];
$total_users = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users"))['total'];
// Thống kê doanh thu theo tháng
$monthly_revenue = [];
$monthly_sql = "SELECT DATE_FORMAT(created_at,'%Y-%m') as month, SUM(total) as revenue FROM orders WHERE status!='cancelled' GROUP BY month ORDER BY month DESC LIMIT 12";
$monthly_result = mysqli_query($conn, $monthly_sql);
while($row = mysqli_fetch_assoc($monthly_result)) {
    $monthly_revenue[] = $row;
}
// Thống kê sản phẩm bán chạy
$best_sql = "SELECT p.name, SUM(oi.quantity) as sold FROM order_items oi JOIN products p ON oi.product_id=p.id GROUP BY oi.product_id ORDER BY sold DESC LIMIT 10";
$best_result = mysqli_query($conn, $best_sql);
$best_products = [];
while($row = mysqli_fetch_assoc($best_result)) {
    $best_products[] = $row;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Thống kê hệ thống</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../../assets/custom/css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <link rel="stylesheet" href="../../assets/custom/css/admin-dashboard.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        <h1 class="page-title"><i class="fa fa-chart-line me-2"></i> Thống kê hệ thống</h1>
        <p class="page-subtitle">Theo dõi doanh thu, đơn hàng và hiệu suất kinh doanh</p>
      </div>

      <!-- Stats Cards -->
      <div class="row mb-4 g-4">
      <div class="row mb-4 g-4">
    <div class="col-md-3">
      <div class="stat-card stat-card-primary">
        <div class="stat-icon">
          <i class="fa fa-shopping-cart"></i>
        </div>
        <div class="stat-content">
          <div class="stat-label">Tổng đơn hàng</div>
          <div class="stat-value"><?= $total_orders ?></div>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="stat-card stat-card-success">
        <div class="stat-icon">
          <i class="fa fa-money-bill-wave"></i>
        </div>
        <div class="stat-content">
          <div class="stat-label">Doanh thu</div>
          <div class="stat-value"><?= number_format($total_revenue,0,',','.') ?>₫</div>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="stat-card stat-card-info">
        <div class="stat-icon">
          <i class="fa fa-box"></i>
        </div>
        <div class="stat-content">
          <div class="stat-label">Sản phẩm</div>
          <div class="stat-value"><?= $total_products ?></div>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="stat-card stat-card-warning">
        <div class="stat-icon">
          <i class="fa fa-users"></i>
        </div>
        <div class="stat-content">
          <div class="stat-label">Người dùng</div>
          <div class="stat-value"><?= $total_users ?></div>
        </div>
      </div>
    </div>
  </div>
    <div class="row mb-4 g-4">
    <div class="col-lg-8">
      <div class="chart-card">
        <div class="card-body">
          <h5 class="card-title"><i class="fa fa-chart-area text-primary me-2"></i>Doanh thu 12 tháng gần nhất</h5>
          <canvas id="revenueChart" height="100"></canvas>
        </div>
      </div>
    </div>
    <div class="col-lg-4">
      <div class="data-card">
        <div class="card-body">
          <h5 class="card-title"><i class="fa fa-fire text-danger me-2"></i>Top 10 sản phẩm bán chạy</h5>
          <div class="table-responsive">
            <table class="table mini-table">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Tên sản phẩm</th>
                  <th class="text-end">Đã bán</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach($best_products as $i => $p): ?>
                <tr>
                  <td><span class="badge bg-primary"><?= $i+1 ?></span></td>
                  <td><?= htmlspecialchars($p['name']) ?></td>
                  <td class="text-end"><strong class="text-success"><?= $p['sold'] ?></strong></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
      <div class="row mb-4 g-4">
        <div class="col-lg-6">
          <div class="data-card">
            <div class="card-body">
              <h5 class="card-title"><i class="fa fa-tasks text-primary me-2"></i>Đơn hàng theo trạng thái</h5>
              <div class="table-responsive">
                <table class="table mini-table">
                  <thead>
                    <tr>
                      <th>Trạng thái</th>
                      <th class="text-end">Số lượng</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php $status_sql = "SELECT status, COUNT(*) as total FROM orders GROUP BY status";
                    $status_result = mysqli_query($conn, $status_sql);
                    while($row = mysqli_fetch_assoc($status_result)): ?>
                    <tr>
                      <td><span class="badge bg-light text-dark"><?= htmlspecialchars($row['status']) ?></span></td>
                      <td class="text-end"><strong class="text-primary"><?= $row['total'] ?></strong></td>
                    </tr>
                    <?php endwhile; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-6">
          <div class="data-card">
            <div class="card-body">
              <h5 class="card-title"><i class="fa fa-dollar-sign text-success me-2"></i>Doanh thu theo sản phẩm</h5>
              <div class="table-responsive">
                <table class="table mini-table">
                <table class="table mini-table">
                  <thead>
                    <tr>
                      <th>Tên sản phẩm</th>
                      <th class="text-end">Doanh thu</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php $revenue_sql = "SELECT p.name, SUM(oi.price * oi.quantity) as revenue FROM order_items oi JOIN products p ON oi.product_id=p.id GROUP BY oi.product_id ORDER BY revenue DESC LIMIT 10";
                    $revenue_result = mysqli_query($conn, $revenue_sql);
                    while($row = mysqli_fetch_assoc($revenue_result)): ?>
                    <tr>
                      <td><?= htmlspecialchars($row['name']) ?></td>
                      <td class="text-end"><strong class="text-success"><?= number_format($row['revenue'],0,',','.') ?>₫</strong></td>
                    </tr>
                    <?php endwhile; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="row mb-4 g-4">
        <div class="col-lg-6">
          <div class="data-card">
            <div class="card-body">
              <h5 class="card-title"><i class="fa fa-credit-card text-info me-2"></i>Đơn hàng theo phương thức thanh toán</h5>
              <div class="table-responsive">
                <table class="table mini-table">
                  <thead>
                    <tr>
                      <th>Phương thức</th>
                      <th class="text-end">Số lượng</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php $pm_sql = "SELECT payment_method, COUNT(*) as total FROM orders GROUP BY payment_method";
                    $pm_result = mysqli_query($conn, $pm_sql);
                    while($row = mysqli_fetch_assoc($pm_result)): ?>
                    <tr>
                      <td><span class="badge bg-light text-dark"><?= htmlspecialchars($row['payment_method'] ?? 'Chưa rõ') ?></span></td>
                      <td class="text-end"><strong class="text-info"><?= $row['total'] ?></strong></td>
                    </tr>
                    <?php endwhile; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-6">
          <div class="data-card">
            <div class="card-body">
              <h5 class="card-title"><i class="fa fa-calendar-alt text-warning me-2"></i>Đơn hàng theo tháng</h5>
              <div class="table-responsive">
                <table class="table mini-table">
                  <thead>
                    <tr>
                      <th>Tháng</th>
                      <th class="text-end">Số đơn hàng</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php $month_sql = "SELECT DATE_FORMAT(created_at,'%Y-%m') as month, COUNT(*) as total FROM orders GROUP BY month ORDER BY month DESC LIMIT 12";
                    $month_result = mysqli_query($conn, $month_sql);
                    while($row = mysqli_fetch_assoc($month_result)): ?>
                    <tr>
                      <td><span class="badge bg-light text-dark"><?= $row['month'] ?></span></td>
                      <td class="text-end"><strong class="text-warning"><?= $row['total'] ?></strong></td>
                    </tr>
                    <?php endwhile; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
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
.stat-card {
  background: white;
  border-radius: 16px;
  padding: 1.5rem;
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
  display: flex;
  align-items: center;
  transition: all 0.3s ease;
  border-left: 4px solid;
}
.stat-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 20px rgba(0,0,0,0.12);
}
.stat-card-primary { border-color: #667eea; }
.stat-card-success { border-color: #28a745; }
.stat-card-info { border-color: #17a2b8; }
.stat-card-warning { border-color: #ffc107; }
.stat-icon {
  width: 60px;
  height: 60px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.75rem;
  margin-right: 1rem;
}
.stat-card-primary .stat-icon {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}
.stat-card-success .stat-icon {
  background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
  color: white;
}
.stat-card-info .stat-icon {
  background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
  color: white;
}
.stat-card-warning .stat-icon {
  background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
  color: white;
}
.stat-label {
  font-size: 0.875rem;
  color: #6c757d;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}
.stat-value {
  font-size: 1.75rem;
  font-weight: 700;
  color: #2c3e50;
  margin-top: 0.25rem;
}
.chart-card, .data-card {
  background: white;
  border-radius: 16px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
  overflow: hidden;
}
.card-title {
  font-size: 1.1rem;
  font-weight: 700;
  color: #2c3e50;
  margin-bottom: 1.5rem;
  padding-bottom: 1rem;
  border-bottom: 2px solid #f0f0f0;
}
.mini-table {
  margin-bottom: 0;
}
.mini-table thead {
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
}
.mini-table thead th {
  font-weight: 600;
  font-size: 0.875rem;
  color: #2c3e50;
  border: none;
  padding: 0.75rem;
}
.mini-table tbody td {
  padding: 0.75rem;
  border-bottom: 1px solid #f0f0f0;
  font-size: 0.875rem;
}
.mini-table tbody tr:hover {
  background-color: #f8f9fa;
}
.mini-table tbody tr:last-child td {
  border-bottom: none;
}
</style>

<script>
const ctx = document.getElementById('revenueChart').getContext('2d');
const gradient = ctx.createLinearGradient(0, 0, 0, 400);
gradient.addColorStop(0, 'rgba(102, 126, 234, 0.3)');
gradient.addColorStop(1, 'rgba(118, 75, 162, 0.05)');

const revenueChart = new Chart(ctx, {
  type: 'line',
  data: {
    labels: <?= json_encode(array_reverse(array_column($monthly_revenue,'month'))) ?>,
    datasets: [{
      label: 'Doanh thu (VNĐ)',
      data: <?= json_encode(array_reverse(array_column($monthly_revenue,'revenue'))) ?>,
      borderColor: '#667eea',
      backgroundColor: gradient,
      fill: true,
      tension: 0.4,
      pointRadius: 5,
      pointBackgroundColor: '#667eea',
      pointBorderColor: '#fff',
      pointBorderWidth: 2,
      pointHoverRadius: 7,
      borderWidth: 3
    }]
  },
  options: {
    responsive: true,
    maintainAspectRatio: true,
    plugins: {
      legend: { 
        display: true,
        position: 'top',
        labels: {
          font: { size: 12, weight: '600' },
          padding: 15,
          usePointStyle: true
        }
      },
      tooltip: {
        backgroundColor: 'rgba(0,0,0,0.8)',
        padding: 12,
        borderRadius: 8,
        titleFont: { size: 14, weight: 'bold' },
        bodyFont: { size: 13 },
        callbacks: {
          label: function(context) {
            return 'Doanh thu: ' + context.parsed.y.toLocaleString('vi-VN') + ' VNĐ';
          }
        }
      }
    },
    scales: {
      y: { 
        beginAtZero: true,
        grid: { color: 'rgba(0,0,0,0.05)' },
        ticks: { 
          callback: function(value){ 
            return value.toLocaleString('vi-VN'); 
          },
          font: { size: 11 }
        }
      },
      x: { 
        grid: { display: false },
        ticks: { font: { size: 11 } }
      }
    }
  }
});
</script>
      </div>
    </main>
  </div>
  <?php include_once(__DIR__ . '/../layouts/partials/footer.php'); ?>
</div>
</body>
</html>
