<?php
session_start();
include_once(__DIR__ . '/../../config/database.php');
// Chỉ admin mới được vào
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../frontend/pages/login.php");
    exit;
}

// Tìm kiếm và lọc
$search = isset($_GET['search']) ? $_GET['search'] : "";
$status_filter = isset($_GET['status']) ? $_GET['status'] : "";

// Phân trang
$limit = 20;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

$search_esc = mysqli_real_escape_string($conn, $search);

// Xây dựng điều kiện WHERE
$where_conditions = [];
if ($search !== "") {
    $where_conditions[] = "(o.id LIKE '%$search_esc%' 
                           OR u.username LIKE '%$search_esc%' 
                           OR u.fullname LIKE '%$search_esc%'
                           OR o.shipping_name LIKE '%$search_esc%'
                           OR o.shipping_phone LIKE '%$search_esc%')";
}
if ($status_filter !== "") {
    $where_conditions[] = "o.status = '" . mysqli_real_escape_string($conn, $status_filter) . "'";
}

$where_sql = count($where_conditions) > 0 ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Đếm tổng số hóa đơn
$count_sql = "SELECT COUNT(*) as total FROM orders o 
              LEFT JOIN users u ON o.user_id = u.id 
              $where_sql";
$count_result = mysqli_query($conn, $count_sql);
$total_orders = ($count_result) ? mysqli_fetch_assoc($count_result)['total'] : 0;
$total_pages = ceil($total_orders / $limit);

// Lấy danh sách hóa đơn
$sql = "SELECT o.id, o.user_id, o.status, o.created_at, o.payment_method, u.username, u.fullname, o.total, o.shipping_name 
        FROM orders o 
        LEFT JOIN users u ON o.user_id = u.id 
        $where_sql
        ORDER BY o.id DESC 
        LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $sql);

$status_options = [
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
  <title>Quản lý hóa đơn</title>
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
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h1 class="page-title"><i class="fa fa-file-invoice-dollar me-2"></i> Quản lý hóa đơn</h1>
            <p class="page-subtitle">Theo dõi và xử lý tất cả đơn hàng</p>
          </div>
          <div class="order-stats">
            <span class="badge bg-white text-primary px-3 py-2 fs-6">Tổng: <strong><?= $total_orders ?></strong> đơn hàng</span>
          </div>
        </div>
      </div>
      
      <!-- Form tìm kiếm và lọc -->
      <div class="search-card mb-4">
        <div class="card-body">
          <form method="get" class="row g-3 align-items-end">
            <div class="col-md-5">
              <label class="form-label fw-bold"><i class="fa fa-search me-1"></i> Tìm kiếm</label>
              <input type="text" name="search" class="form-control" placeholder="Tìm theo mã ĐH, tên KH, SĐT..." value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="col-md-3">
              <label class="form-label fw-bold"><i class="fa fa-filter me-1"></i> Lọc trạng thái</label>
              <select name="status" class="form-select">
                <option value="">-- Tất cả --</option>
                <?php foreach($status_options as $key => $label): ?>
                  <option value="<?= $key ?>" <?= $status_filter === $key ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-4 text-end">
              <button type="submit" class="btn btn-primary px-4 fw-bold"><i class="fa fa-search me-1"></i> Tìm kiếm</button>
              <a href="orders.php" class="btn btn-secondary px-4 fw-bold"><i class="fa fa-redo me-1"></i> Làm mới</a>
            </div>
          </form>
        </div>
      </div>

      <div class="data-table">
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-hover align-middle">
              <thead>
                <tr>
                  <th><i class="fa fa-hashtag me-1"></i>ID</th>
                  <th><i class="fa fa-user me-1"></i>Người dùng</th>
                  <th><i class="fa fa-id-card me-1"></i>Họ tên</th>
                  <th><i class="fa fa-calendar me-1"></i>Ngày tạo</th>
                  <th><i class="fa fa-credit-card me-1"></i>Thanh toán</th>
                  <th><i class="fa fa-money-bill-wave me-1"></i>Tổng tiền</th>
                  <th><i class="fa fa-info-circle me-1"></i>Tình trạng</th>
                  <th class="text-center"><i class="fa fa-cog me-1"></i>Thao tác</th>
                </tr>
              </thead>
              <tbody>
              <?php while($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                  <td><strong>#<?= $row['id'] ?></strong></td>
                  <td><?= htmlspecialchars($row['username']) ?></td>
                  <td><?= htmlspecialchars($row['fullname']) ?></td>
                  <td><small><?= date('d/m/Y H:i', strtotime($row['created_at'])) ?></small></td>
                  <td><span class="badge bg-light text-dark"><?= htmlspecialchars($row['payment_method'] ?? 'Chưa rõ') ?></span></td>
                  <td><span class="text-success fw-bold"><?= number_format($row['total'],0,',','.') ?> đ</span></td>
                  <td>
                    <select name="status" class="form-select form-select-sm order-status-select status-select-modern" data-order-id="<?= $row['id'] ?>">
                      <?php foreach($status_options as $key => $label): ?>
                        <option value="<?= $key ?>" <?= $row['status']==$key?'selected':'' ?>><?= $label ?></option>
                      <?php endforeach; ?>
                    </select>
                    <span class="order-status-msg ms-2" style="display:none"></span>
                  </td>
                  <td class="text-center">
                    <div class="btn-group" role="group">
                      <a href="view_order.php?id=<?= $row['id'] ?>" class="btn btn-info btn-sm" title="Xem chi tiết"><i class="fa fa-eye"></i></a>
                      <a href="print_order.php?id=<?= $row['id'] ?>" class="btn btn-secondary btn-sm" target="_blank" title="In hóa đơn"><i class="fa fa-print"></i></a>
                    </div>
                  </td>
                </tr>
              <?php endwhile; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Phân trang -->
      <?php if ($total_pages > 1): ?>
      <nav aria-label="Phân trang">
        <ul class="pagination justify-content-center">
          <!-- Nút Previous -->
          <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
            <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status_filter) ?>">
              <i class="fa fa-chevron-left"></i> Trước
            </a>
          </li>
          
          <?php
          // Hiển thị các trang
          $start_page = max(1, $page - 2);
          $end_page = min($total_pages, $page + 2);
          
          if ($start_page > 1): ?>
            <li class="page-item">
              <a class="page-link" href="?page=1&search=<?= urlencode($search) ?>&status=<?= urlencode($status_filter) ?>">1</a>
            </li>
            <?php if ($start_page > 2): ?>
              <li class="page-item disabled"><span class="page-link">...</span></li>
            <?php endif; ?>
          <?php endif; ?>
          
          <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
              <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status_filter) ?>"><?= $i ?></a>
            </li>
          <?php endfor; ?>
          
          <?php if ($end_page < $total_pages): ?>
            <?php if ($end_page < $total_pages - 1): ?>
              <li class="page-item disabled"><span class="page-link">...</span></li>
            <?php endif; ?>
            <li class="page-item">
              <a class="page-link" href="?page=<?= $total_pages ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status_filter) ?>"><?= $total_pages ?></a>
            </li>
          <?php endif; ?>
          
          <!-- Nút Next -->
          <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
            <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status_filter) ?>">
              Sau <i class="fa fa-chevron-right"></i>
            </a>
          </li>
        </ul>
      </nav>
      <?php endif; ?>

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
.order-stats .badge {
  box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}
.search-card {
  background: white;
  border-radius: 16px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
  padding: 1.5rem;
}
.search-input {
  border: 2px solid #e0e6ed;
  border-radius: 10px;
  padding: 0.65rem 1rem;
  transition: all 0.3s ease;
}
.search-input:focus {
  border-color: #667eea;
  box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
}
.data-table {
  background: white;
  border-radius: 16px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
  overflow: hidden;
}
.data-table thead {
  background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
  color: white;
}
.data-table thead th {
  padding: 1rem;
  font-weight: 600;
  border: none;
}
.data-table tbody td {
  padding: 1rem;
  vertical-align: middle;
}
.data-table tbody tr {
  border-bottom: 1px solid #f0f0f0;
  transition: all 0.2s ease;
}
.data-table tbody tr:hover {
  background-color: #f8f9fa;
  transform: scale(1.01);
  box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}
.status-select-modern {
  border-radius: 8px;
  border: 2px solid #e0e6ed;
  font-size: 0.875rem;
}
.btn {
  border-radius: 8px;
  font-weight: 600;
  transition: all 0.3s ease;
}
.btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}
.btn-group .btn {
  border-radius: 0;
}
.btn-group .btn:first-child {
  border-top-left-radius: 8px;
  border-bottom-left-radius: 8px;
}
.btn-group .btn:last-child {
  border-top-right-radius: 8px;
  border-bottom-right-radius: 8px;
}
.pagination .page-link {
  border-radius: 8px;
  margin: 0 3px;
  border: 2px solid #e0e6ed;
  color: #667eea;
}
.pagination .page-item.active .page-link {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-color: #667eea;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.querySelectorAll('.order-status-select').forEach(function(select) {
  select.addEventListener('change', function() {
    var orderId = this.getAttribute('data-order-id');
    var status = this.value;
    var msgSpan = this.parentElement.querySelector('.order-status-msg');
    fetch('update_order_status.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: 'order_id=' + encodeURIComponent(orderId) + '&status=' + encodeURIComponent(status)
    })
    .then(resp => resp.text())
    .then(txt => {
      if (txt.trim() === 'success') {
        msgSpan.textContent = '✓ Đã cập nhật';
        msgSpan.style.display = 'inline';
        msgSpan.classList.add('text-success');
        msgSpan.classList.remove('text-danger');
        setTimeout(function(){ msgSpan.style.display = 'none'; }, 1500);
      } else {
        msgSpan.textContent = '✗ Lỗi!';
        msgSpan.style.display = 'inline';
        msgSpan.classList.add('text-danger');
        msgSpan.classList.remove('text-success');
        setTimeout(function(){ msgSpan.style.display = 'none'; }, 2000);
      }
    });
  });
});
</script>
</body>
</html>
