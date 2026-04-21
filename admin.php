
<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: frontend/pages/login.php");
    exit;
}
include_once(__DIR__ . '/config/database.php');
$total_users = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users"))['total'] ?? 0;
$total_products = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM products"))['total'] ?? 0;
$total_orders = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM orders"))['total'] ?? 0;
$total_sales = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(price * quantity) as total FROM order_items"))['total'] ?? 0;

// Doanh thu 6 tháng gần nhất
$revenue_months = [];
$revenue_data = mysqli_query($conn, "
    SELECT DATE_FORMAT(created_at, '%Y-%m') as month, SUM(total) as revenue
    FROM orders 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY month ASC
");
while($row = mysqli_fetch_assoc($revenue_data)) {
    $revenue_months[] = $row;
}

// Top 5 sản phẩm bán chạy
$top_products = [];
$top_products_data = mysqli_query($conn, "
    SELECT p.name, SUM(oi.quantity) as sold
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    GROUP BY oi.product_id
    ORDER BY sold DESC
    LIMIT 5
");
while($row = mysqli_fetch_assoc($top_products_data)) {
    $top_products[] = $row;
}

// Đơn hàng theo trạng thái
$order_status = [];
$order_status_data = mysqli_query($conn, "
    SELECT status, COUNT(*) as count
    FROM orders
    GROUP BY status
");
while($row = mysqli_fetch_assoc($order_status_data)) {
    $order_status[] = $row;
}

// 10 đơn hàng mới nhất
$recent_orders = [];
$recent_orders_data = mysqli_query($conn, "
    SELECT o.id, o.shipping_name, o.total, o.status, o.created_at
    FROM orders o
    ORDER BY o.created_at DESC
    LIMIT 10
");
while($row = mysqli_fetch_assoc($recent_orders_data)) {
    $recent_orders[] = $row;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <link rel="stylesheet" href="assets/custom/css/style.css">
  <link rel="stylesheet" href="assets/custom/css/admin-dashboard.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body class="bg-light">




<div class="d-flex flex-column min-vh-100" style="background:#f8f9fa;">
  <?php include_once(__DIR__ . '/backend/layouts/partials/header.php'); ?>
  <div class="d-flex flex-grow-1">
    <?php include_once(__DIR__ . '/backend/layouts/partials/sidebar.php'); ?>
    <main class="flex-grow-1 px-4" style="min-width:0;">
      <div class="container py-4">
        <!-- Nội dung dashboard ở đây -->
        <h1 class="mb-4 text-center fw-bold text-primary"><i class="fa fa-tachometer-alt me-2"></i> Dashboard Admin</h1>
        <div class="row g-4 mb-5">
          <div class="col-md-3">
            <div class="card shadow-lg border-0 text-center dashboard-stat-card h-100">
              <div class="card-body">
                <div class="mb-2"><i class="fa fa-users fa-2x text-info"></i></div>
                <h5 class="fw-bold">Người dùng</h5>
                <span class="fs-3 fw-bold text-dark"><?= $total_users ?></span>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="card shadow-lg border-0 text-center dashboard-stat-card h-100">
              <div class="card-body">
                <div class="mb-2"><i class="fa fa-box fa-2x text-success"></i></div>
                <h5 class="fw-bold">Sản phẩm</h5>
                <span class="fs-3 fw-bold text-dark"><?= $total_products ?></span>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="card shadow-lg border-0 text-center dashboard-stat-card h-100">
              <div class="card-body">
                <div class="mb-2"><i class="fa fa-shopping-cart fa-2x text-warning"></i></div>
                <h5 class="fw-bold">Đơn hàng</h5>
                <span class="fs-3 fw-bold text-dark"><?= $total_orders ?></span>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="card shadow-lg border-0 text-center dashboard-stat-card h-100">
              <div class="card-body">
                <div class="mb-2"><i class="fa fa-money-bill-wave fa-2x text-danger"></i></div>
                <h5 class="fw-bold">Doanh thu</h5>
                <span class="fs-3 fw-bold text-success"><?= number_format($total_sales) ?>₫</span>
              </div>
            </div>
          </div>
        </div>
        <!-- Biểu đồ doanh thu theo tháng -->
        <div class="row g-4 mb-5">
          <div class="col-lg-8">
            <div class="card shadow-lg border-0 h-100">
              <div class="card-body p-4">
                <h4 class="fw-bold mb-3 text-secondary"><i class="fa fa-chart-line me-2"></i> Doanh thu 6 tháng gần nhất</h4>
                <canvas id="revenueChart" style="max-height: 300px;"></canvas>
              </div>
            </div>
          </div>
          <div class="col-lg-4">
            <div class="card shadow-lg border-0 h-100">
              <div class="card-body p-4">
                <h4 class="fw-bold mb-3 text-secondary"><i class="fa fa-chart-pie me-2"></i> Trạng thái đơn hàng</h4>
                <canvas id="orderStatusChart" style="max-height: 300px;"></canvas>
              </div>
            </div>
          </div>
        </div>

        <!-- Top sản phẩm bán chạy -->
        <div class="row g-4 mb-5">
          <div class="col-lg-6">
            <div class="card shadow-lg border-0">
              <div class="card-body p-4">
                <h4 class="fw-bold mb-3 text-secondary"><i class="fa fa-fire me-2"></i> Top 5 sản phẩm bán chạy</h4>
                <canvas id="topProductsChart" style="max-height: 300px;"></canvas>
              </div>
            </div>
          </div>
          <div class="col-lg-6">
            <div class="card shadow-lg border-0">
              <div class="card-body p-4">
                <h4 class="fw-bold mb-3 text-secondary"><i class="fa fa-shopping-bag me-2"></i> Đơn hàng gần đây</h4>
                <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                  <table class="table table-hover">
                    <thead class="table-light sticky-top">
                      <tr>
                        <th>Mã ĐH</th>
                        <th>Khách hàng</th>
                        <th>Tổng tiền</th>
                        <th>Trạng thái</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach($recent_orders as $order): ?>
                      <tr>
                        <td>#<?= $order['id'] ?></td>
                        <td><?= htmlspecialchars($order['shipping_name']) ?></td>
                        <td><?= number_format($order['total']) ?>₫</td>
                        <td>
                          <?php 
                          $badge_class = 'secondary';
                          switch($order['status']) {
                            case 'completed': $badge_class = 'success'; break;
                            case 'processing': $badge_class = 'info'; break;
                            case 'paid': $badge_class = 'primary'; break;
                            case 'shipped': $badge_class = 'warning'; break;
                            case 'cancelled': $badge_class = 'danger'; break;
                          }
                          ?>
                          <span class="badge bg-<?= $badge_class ?>"><?= $order['status'] ?></span>
                        </td>
                      </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
  <?php include_once(__DIR__ . '/backend/layouts/partials/footer.php'); ?>
</div>

<script>
// Biểu đồ doanh thu theo tháng
const revenueCtx = document.getElementById('revenueChart').getContext('2d');
const revenueChart = new Chart(revenueCtx, {
    type: 'line',
    data: {
        labels: <?= json_encode(array_column($revenue_months, 'month')) ?>,
        datasets: [{
            label: 'Doanh thu (VNĐ)',
            data: <?= json_encode(array_column($revenue_months, 'revenue')) ?>,
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: true,
                position: 'top'
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return 'Doanh thu: ' + new Intl.NumberFormat('vi-VN').format(context.parsed.y) + '₫';
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return new Intl.NumberFormat('vi-VN', { notation: 'compact' }).format(value) + '₫';
                    }
                }
            }
        }
    }
});

// Biểu đồ trạng thái đơn hàng
const orderStatusCtx = document.getElementById('orderStatusChart').getContext('2d');
const orderStatusChart = new Chart(orderStatusCtx, {
    type: 'doughnut',
    data: {
        labels: <?= json_encode(array_column($order_status, 'status')) ?>,
        datasets: [{
            data: <?= json_encode(array_column($order_status, 'count')) ?>,
            backgroundColor: [
                'rgba(255, 99, 132, 0.8)',
                'rgba(54, 162, 235, 0.8)',
                'rgba(255, 206, 86, 0.8)',
                'rgba(75, 192, 192, 0.8)',
                'rgba(153, 102, 255, 0.8)',
                'rgba(255, 159, 64, 0.8)'
            ],
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// Biểu đồ top sản phẩm bán chạy
const topProductsCtx = document.getElementById('topProductsChart').getContext('2d');
const topProductsChart = new Chart(topProductsCtx, {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column($top_products, 'name')) ?>,
        datasets: [{
            label: 'Số lượng bán',
            data: <?= json_encode(array_column($top_products, 'sold')) ?>,
            backgroundColor: [
                'rgba(255, 99, 132, 0.7)',
                'rgba(54, 162, 235, 0.7)',
                'rgba(255, 206, 86, 0.7)',
                'rgba(75, 192, 192, 0.7)',
                'rgba(153, 102, 255, 0.7)'
            ],
            borderColor: [
                'rgba(255, 99, 132, 1)',
                'rgba(54, 162, 235, 1)',
                'rgba(255, 206, 86, 1)',
                'rgba(75, 192, 192, 1)',
                'rgba(153, 102, 255, 1)'
            ],
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        }
    }
});
</script>

      </div>
    </main>
  </div>
  <?php include_once(__DIR__ . '/backend/layouts/partials/footer.php'); ?>
</div>

</body>
</html>
