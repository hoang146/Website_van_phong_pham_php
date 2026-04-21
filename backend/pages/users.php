<?php
session_start();
include_once(__DIR__ . '/../../config/database.php');

// Kiểm tra quyền admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../frontend/pages/login.php");
    exit;
}

// Xử lý tìm kiếm & sắp xếp
$search = isset($_GET['search']) ? $_GET['search'] : "";
$order  = isset($_GET['order']) ? $_GET['order'] : "id";

// whitelist ORDER BY
$allowed_orders = ['id','username','fullname','email','phone','role','created_at'];
if (!in_array($order, $allowed_orders)) $order = 'id';

$search_esc = mysqli_real_escape_string($conn, $search);

// Phân trang
$limit = 25;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// Đếm tổng số user
$count_sql = "SELECT COUNT(*) as total FROM users 
              WHERE username LIKE '%$search_esc%' 
                 OR fullname LIKE '%$search_esc%' 
                 OR email LIKE '%$search_esc%' 
                 OR phone LIKE '%$search_esc%'";
$count_result = mysqli_query($conn, $count_sql);
$total_users = ($count_result) ? mysqli_fetch_assoc($count_result)['total'] : 0;
$total_pages = ceil($total_users / $limit);

// Lấy danh sách user theo phân trang
$sql = "SELECT * FROM users 
        WHERE username LIKE '%$search_esc%' 
           OR fullname LIKE '%$search_esc%'
           OR email LIKE '%$search_esc%' 
           OR phone LIKE '%$search_esc%' 
        ORDER BY $order ASC 
        LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $sql);

// Đường dẫn xóa user
$deleteUrl = rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . '/delete_user.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Quản lý người dùng</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <link rel="stylesheet" href="../../assets/custom/css/admin-dashboard.css">
  <style>
    .page-header {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      border-radius: 16px;
      padding: 25px 30px;
      color: white;
      box-shadow: 0 8px 20px rgba(102,126,234,0.3);
      margin-bottom: 25px;
    }
    .search-card {
      background: white;
      border-radius: 16px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.08);
      border: none;
      margin-bottom: 25px;
    }
    .data-table {
      background: white;
      border-radius: 16px;
      overflow: hidden;
      box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }
    .data-table thead {
      background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
      color: white;
    }
    .data-table tbody tr {
      transition: all 0.3s;
    }
    .data-table tbody tr:hover {
      background: #f8f9fa;
      transform: scale(1.01);
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .action-btn {
      padding: 6px 14px;
      border-radius: 8px;
      font-weight: 600;
      transition: all 0.3s;
    }
    .action-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }
    .badge-modern {
      padding: 8px 16px;
      border-radius: 20px;
      font-weight: 600;
    }
  </style>
</head>
<body class="bg-light">
<div class="d-flex flex-column min-vh-100" style="background:#f8f9fa;">
  <?php include_once(__DIR__ . '/../layouts/partials/header.php'); ?>
  <div class="d-flex flex-grow-1">
    <?php include_once(__DIR__ . '/../layouts/partials/sidebar.php'); ?>
    <main class="flex-grow-1 px-4" style="min-width:0;">
      <div class="container py-4">
      <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h2 class="fw-bold mb-1"><i class="fas fa-users me-2"></i>Quản lý người dùng</h2>
            <p class="mb-0 opacity-75">Quản lý tài khoản người dùng và phân quyền</p>
          </div>
          <div class="d-flex gap-2 align-items-center">
            <span class="badge badge-modern bg-light text-dark"><i class="fas fa-database me-1"></i>Tổng: <?= $total_users ?></span>
            <a href="add_user.php" class="btn btn-light fw-bold">
              <i class="fas fa-user-plus me-2"></i>Thêm người dùng
            </a>
          </div>
        </div>
      </div>
      
      <div class="card search-card">
        <div class="card-body">
          <form method="get" class="row g-3 mb-3 align-items-end">
            <div class="col-md-4">
              <label class="form-label fw-bold"><i class="fa fa-search me-1"></i> Tìm kiếm</label>
              <input type="text" name="search" class="form-control" placeholder="Tìm theo tên, email hoặc SĐT..." value="<?= htmlspecialchars($search ?? '') ?>">
            </div>
            <div class="col-md-3">
              <label class="form-label fw-bold"><i class="fa fa-sort me-1"></i> Sắp xếp theo</label>
              <select name="order" class="form-select">
                <option value="id" <?= $order=="id"?"selected":"" ?>>Mặc định</option>
                <option value="username" <?= $order=="username"?"selected":"" ?>>Tên tài khoản</option>
                <option value="fullname" <?= $order=="fullname"?"selected":"" ?>>Họ tên</option>
                <option value="email" <?= $order=="email"?"selected":"" ?>>Email</option>
                <option value="phone" <?= $order=="phone"?"selected":"" ?>>SĐT</option>
                <option value="role" <?= $order=="role"?"selected":"" ?>>Quyền</option>
                <option value="created_at" <?= $order=="created_at"?"selected":"" ?>>Ngày tạo</option>
              </select>
            </div>
            <div class="col-md-5 text-end">
              <button type="submit" class="btn btn-primary px-4 fw-bold"><i class="fa fa-search me-1"></i> Tìm kiếm</button>
              <a href="users.php" class="btn btn-secondary px-4 fw-bold"><i class="fa fa-redo me-1"></i> Làm mới</a>
            </div>
          </form>
        </div>
      </div>
      
      <div class="data-table">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead>
              <tr>
                <th width="60">STT</th>
                  <th><i class="fa fa-user"></i> Tài khoản</th>
                  <th><i class="fa fa-id-card"></i> Họ tên</th>
                  <th><i class="fa fa-envelope"></i> Email</th>
                  <th><i class="fa fa-phone"></i> SĐT</th>
                  <th><i class="fa fa-map-marker"></i> Địa chỉ</th>
                  <th><i class="fa fa-user-shield"></i> Quyền</th>
                  <th width="160">Hành động</th>
                </tr>
              </thead>
              <tbody>
              <?php 
              $stt = 1 + ($page - 1) * $limit;
              while($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                  <td><?= $stt++ ?></td>
                  <td class="fw-bold text-dark"><?= htmlspecialchars($row['username'] ?? '') ?></td>
                  <td><?= htmlspecialchars($row['fullname'] ?? '') ?></td>
                  <td><?= htmlspecialchars($row['email'] ?? '') ?></td>
                  <td><?= htmlspecialchars($row['phone'] ?? '') ?></td>
                  <td><?= htmlspecialchars($row['address'] ?? '') ?></td>
                  <td>
                    <span class="badge <?= ($row['role']=="admin") ? "bg-danger" : "bg-secondary" ?> px-3 py-2" style="font-size:1rem;">
                      <i class="fa fa-user-<?= ($row['role']=="admin") ? "shield" : "tie" ?> me-1"></i> <?= htmlspecialchars($row['role'] ?? '') ?>
                    </span>
                  </td>
                  <td>
                    <a href="edit_user.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning rounded-3 fw-bold"><i class="fa fa-edit me-1"></i> Sửa</a>
                    <button class="btn btn-sm btn-danger rounded-3 fw-bold" data-bs-toggle="modal" data-bs-target="#deleteUserModal" data-user-id="<?= $row['id'] ?>"><i class="fa fa-trash me-1"></i> Xóa</button>
                  </td>
                </tr>
              <?php endwhile; ?>
              </tbody>
            </table>
          </div>
          <nav>
            <ul class="pagination justify-content-center">
              <?php if ($page > 1): ?>
                <li class="page-item"><a class="page-link" href="?search=<?= urlencode($search) ?>&order=<?= $order ?>&page=<?= $page-1 ?>">« Trước</a></li>
              <?php endif; ?>
              <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?= ($i==$page)?"active":"" ?>">
                  <a class="page-link" href="?search=<?= urlencode($search) ?>&order=<?= $order ?>&page=<?= $i ?>"><?= $i ?></a>
                </li>
              <?php endfor; ?>
              <?php if ($page < $total_pages): ?>
                <li class="page-item"><a class="page-link" href="?search=<?= urlencode($search) ?>&order=<?= $order ?>&page=<?= $page+1 ?>">Sau »</a></li>
              <?php endif; ?>
            </ul>
          </nav>
        </div>
      </div>
    </main>
  </div>
</div>

<!-- Modal xác nhận xóa -->
<div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="deleteUserModalLabel"><i class="fa fa-trash me-2"></i> Xác nhận xóa người dùng</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
      </div>
      <div class="modal-body">
        Bạn có chắc chắn muốn xóa người dùng này không?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
        <button type="button" class="btn btn-danger" id="confirmDeleteUser">Xóa</button>
      </div>
    </div>
  </div>
</div>
<script>
const deleteUrl = '<?= $deleteUrl ?>';
let userIdToDelete = null;
document.addEventListener('DOMContentLoaded', function() {
  var deleteUserModal = document.getElementById('deleteUserModal');
  deleteUserModal.addEventListener('show.bs.modal', function (event) {
    var button = event.relatedTarget;
    userIdToDelete = button.getAttribute('data-user-id');
  });
  document.getElementById('confirmDeleteUser').onclick = function() {
    if (!userIdToDelete) return;
    fetch(window.location.origin + deleteUrl + '?id=' + encodeURIComponent(userIdToDelete), {
      method: 'GET',
      credentials: 'same-origin'
    })
    .then(resp => resp.text())
    .then(txt => {
      location.reload();
    })
    .catch(err => {
      alert('Lỗi khi xóa: ' + err);
    });
    var modal = bootstrap.Modal.getInstance(deleteUserModal);
    modal.hide();
  };
});
</script>

      </div>
    </main>
  </div>
  <?php include_once(__DIR__ . '/../layouts/partials/footer.php'); ?>
</div>
<!-- Toast thông báo -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
