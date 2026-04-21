<?php
session_start();
include_once(__DIR__ . '/../../config/database.php');

// Kiểm tra role = admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../frontend/pages/login.php");
    exit;
}

// Xử lý tìm kiếm
$search = isset($_GET['search']) ? $_GET['search'] : "";

// Phân trang
$limit = 25;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

$search_esc = mysqli_real_escape_string($conn, $search);

// Đếm tổng số danh mục
$count_sql = "SELECT COUNT(*) as total FROM categories 
              WHERE name LIKE '%$search_esc%' 
                 OR description LIKE '%$search_esc%'";
$count_result = mysqli_query($conn, $count_sql);
$total_categories = ($count_result) ? mysqli_fetch_assoc($count_result)['total'] : 0;
$total_pages = ceil($total_categories / $limit);

// Lấy danh sách danh mục
$sql = "SELECT * FROM categories 
        WHERE name LIKE '%$search_esc%' 
           OR description LIKE '%$search_esc%'
        ORDER BY id ASC 
        LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $sql);

// Tạo URL xóa danh mục
$deleteUrl = rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . '/delete_category.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Quản lý danh mục sản phẩm</title>
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
    .search-card, .data-table {
      background: white;
      border-radius: 16px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.08);
      border: none;
      margin-bottom: 25px;
    }
    .data-table thead {
      background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
      color: white;
    }
    .data-table tbody tr:hover {
      background: #f8f9fa;
      transform: scale(1.005);
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
            <h2 class="fw-bold mb-1"><i class="fas fa-list-alt me-2"></i>Quản lý danh mục</h2>
            <p class="mb-0 opacity-75">Quản lý danh mục sản phẩm</p>
          </div>
          <div class="d-flex gap-2 align-items-center">
            <span class="badge badge-modern bg-light text-dark"><i class="fas fa-database me-1"></i>Tổng: <?= $total_categories ?></span>
            <a href="add_category.php" class="btn btn-light fw-bold">
              <i class="fas fa-plus me-2"></i>Thêm danh mục
            </a>
          </div>
        </div>
      </div>
      
      <div class="card search-card">
        <div class="card-body">
          <form method="get" class="row g-3 align-items-end">
            <div class="col-md-7">
              <label class="form-label fw-bold"><i class="fa fa-search me-1"></i> Tìm kiếm danh mục</label>
              <input type="text" name="search" class="form-control" placeholder="Tìm theo tên hoặc mô tả..." value="<?= htmlspecialchars($search ?? '') ?>">
            </div>
            <div class="col-md-5 text-end">
              <button type="submit" class="btn btn-primary px-4 fw-bold"><i class="fa fa-search me-1"></i> Tìm kiếm</button>
              <a href="categories.php" class="btn btn-secondary px-4 fw-bold"><i class="fa fa-redo me-1"></i> Làm mới</a>
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
                  <th>Tên danh mục</th>
                  <th>Mô tả</th>
                  <th width="160">Hành động</th>
                </tr>
              </thead>
              <tbody>
              <?php 
              $stt = 1 + ($page - 1) * $limit;
              while($row = mysqli_fetch_assoc($result)): ?>
          <tr>
            <td><?= $stt++ ?></td>
            <td><?= htmlspecialchars($row['name'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['description'] ?? '') ?></td>
            <td>
              <a href="edit_category.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">Sửa</a>
              <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteCategoryModal" data-category-id="<?= $row['id'] ?>">Xóa</button>
            </td>
          </tr>
        <?php endwhile; ?>
        </tbody>
      </table>

      <!-- Phân trang -->
      <nav>
        <ul class="pagination justify-content-center">
          <?php if ($page > 1): ?>
            <li class="page-item"><a class="page-link" href="?search=<?= urlencode($search) ?>&page=<?= $page-1 ?>">« Trước</a></li>
          <?php endif; ?>

          <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <li class="page-item <?= ($i==$page)?"active":"" ?>">
              <a class="page-link" href="?search=<?= urlencode($search) ?>&page=<?= $i ?>"><?= $i ?></a>
            </li>
          <?php endfor; ?>

          <?php if ($page < $total_pages): ?>
            <li class="page-item"><a class="page-link" href="?search=<?= urlencode($search) ?>&page=<?= $page+1 ?>">Sau »</a></li>
          <?php endif; ?>
        </ul>
      </nav>
    </main>
  </div>
</div>

<!-- Modal xác nhận xóa danh mục -->
<div class="modal fade" id="deleteCategoryModal" tabindex="-1" aria-labelledby="deleteCategoryModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="deleteCategoryModalLabel"><i class="fa fa-trash me-2"></i> Xác nhận xóa danh mục</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
      </div>
      <div class="modal-body">
        Bạn có chắc chắn muốn xóa danh mục này không?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
        <button type="button" class="btn btn-danger" id="confirmDeleteCategory">Xóa</button>
      </div>
    </div>
  </div>
</div>
<script>
const deleteCategoryUrl = '<?= $deleteUrl ?>';
let categoryIdToDelete = null;
document.addEventListener('DOMContentLoaded', function() {
  var deleteCategoryModal = document.getElementById('deleteCategoryModal');
  deleteCategoryModal.addEventListener('show.bs.modal', function (event) {
    var button = event.relatedTarget;
    categoryIdToDelete = button.getAttribute('data-category-id');
  });
  document.getElementById('confirmDeleteCategory').onclick = function() {
    if (!categoryIdToDelete) return;
    fetch(window.location.origin + deleteCategoryUrl + '?id=' + encodeURIComponent(categoryIdToDelete), {
      method: 'GET',
      credentials: 'same-origin'
    })
    .then(resp => resp.text())
    .then(txt => {
      location.reload();
    })
    .catch(err => {
      alert('Có Lỗi khi xóa: ' + err);
    });
    var modal = bootstrap.Modal.getInstance(deleteCategoryModal);
    modal.hide();
  };
});
</script>
      </div>
    </main>
  </div>
  <?php include_once(__DIR__ . '/../layouts/partials/footer.php'); ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
