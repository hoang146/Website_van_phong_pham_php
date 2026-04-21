<?php
session_start();
include_once(__DIR__ . '/../../config/database.php');

// Chỉ admin mới được vào
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../frontend/pages/login.php");
    exit;
}

// Tìm kiếm
$search = $_GET['search'] ?? "";
$limit = 25;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

$search_esc = mysqli_real_escape_string($conn, $search);

// Đếm tổng số sản phẩm
$count_sql = "SELECT COUNT(*) as total FROM products WHERE name LIKE '%$search_esc%'";
$total = mysqli_fetch_assoc(mysqli_query($conn, $count_sql))['total'];
$total_pages = ceil($total / $limit);

// Lấy danh sách sản phẩm + ảnh đại diện
$sql = "SELECT p.*, c.name as category_name,
        (SELECT image_path FROM product_images WHERE product_id=p.id LIMIT 1) as image_path
        FROM products p
        LEFT JOIN categories c ON p.category_id=c.id
        WHERE p.name LIKE '%$search_esc%'
        ORDER BY p.id DESC
        LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Quản lý sản phẩm</title>
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
    .data-table tbody tr:hover {
      background: #f8f9fa;
      transform: scale(1.005);
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .product-img {
      width: 60px;
      height: 60px;
      object-fit: cover;
      border-radius: 10px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
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
            <h2 class="fw-bold mb-1"><i class="fas fa-box me-2"></i>Quản lý sản phẩm</h2>
            <p class="mb-0 opacity-75">Quản lý danh sách sản phẩm, giá cả và tồn kho</p>
          </div>
          <div class="d-flex gap-2 align-items-center">
            <span class="badge badge-modern bg-light text-dark"><i class="fas fa-database me-1"></i>Tổng: <?= $total ?></span>
            <a href="add_product.php" class="btn btn-light fw-bold">
              <i class="fas fa-plus me-2"></i>Thêm sản phẩm
            </a>
          </div>
        </div>
      </div>
      
      <div class="card search-card">
        <div class="card-body">
          <form method="get" class="row g-3 align-items-end">
            <div class="col-md-7">
              <label class="form-label fw-bold"><i class="fa fa-search me-1"></i> Tìm kiếm sản phẩm</label>
              <input type="text" name="search" class="form-control" placeholder="Tìm theo tên sản phẩm..." value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="col-md-5 text-end">
              <button type="submit" class="btn btn-primary px-4 fw-bold"><i class="fa fa-search me-1"></i> Tìm kiếm</button>
              <a href="products.php" class="btn btn-secondary px-4 fw-bold"><i class="fa fa-redo me-1"></i> Làm mới</a>
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
                <th width="100">Hình ảnh</th>
                  <th>Tên sản phẩm</th>
                  <th>Danh mục</th>
                  <th>Thương hiệu</th>
                  <th>Xuất xứ</th>
                  <th>Tồn kho</th>
                  <th>Giá</th>
                  <th>Giá KM</th>
                  <th width="160">Hành động</th>
                </tr>
              </thead>
              <tbody>
              <?php 
              if (mysqli_num_rows($result) > 0):
                $stt = 1 + ($page - 1) * $limit;
                while($row = mysqli_fetch_assoc($result)): ?>
                <tr class="align-middle">
                  <td><?= $stt++ ?></td>
                  <td>
                    <?php if ($row['image_path']): ?>
                      <img src="../../uploads/products/<?= htmlspecialchars($row['image_path']) ?>" class="product-img" alt="Product">
                    <?php else: ?>
                      <span class="text-muted">Chưa có</span>
                    <?php endif; ?>
                  </td>
                  <td class="fw-bold text-dark"><?= htmlspecialchars($row['name']) ?></td>
                  <td><?= htmlspecialchars($row['category_name']) ?></td>
                  <td><?= htmlspecialchars($row['brand']) ?></td>
                  <td><?= htmlspecialchars($row['origin']) ?></td>
                  <td><span class="badge bg-secondary px-3 py-2 fs-6"><?= $row['stock'] ?></span></td>
                  <td><span class="fw-bold text-success"><?= number_format($row['price'],0,',','.') ?> đ</span></td>
                  <td><?= $row['price_sale'] ? '<span class="fw-bold text-danger">'.number_format($row['price_sale'],0,',','.').' đ</span>' : '-' ?></td>
                  <td>
                    <a href="edit_product.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning fw-bold px-3 me-1"><i class="fa fa-edit"></i> Sửa</a>
                    <button class="btn btn-sm btn-danger fw-bold px-3" data-bs-toggle="modal" data-bs-target="#deleteProductModal" data-product-id="<?= $row['id'] ?>"><i class="fa fa-trash"></i> Xóa</button>
                  </td>
                </tr>
                <?php endwhile;
              else: ?>
                <tr><td colspan="10" class="text-center text-muted">Không có sản phẩm nào</td></tr>
              <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <nav class="mt-4">
        <ul class="pagination justify-content-center">
          <?php if ($page > 1): ?>
            <li class="page-item">
              <a class="page-link" href="?search=<?= urlencode($search) ?>&page=<?= $page-1 ?>">« Trước</a>
            </li>
          <?php endif; ?>
          <?php for($i=1;$i<=$total_pages;$i++): ?>
            <li class="page-item <?= ($i==$page)?'active':'' ?>">
              <a class="page-link" href="?search=<?= urlencode($search) ?>&page=<?= $i ?>"><?= $i ?></a>
            </li>
          <?php endfor; ?>
          <?php if ($page < $total_pages): ?>
            <li class="page-item">
              <a class="page-link" href="?search=<?= urlencode($search) ?>&page=<?= $page+1 ?>">Sau »</a>
            </li>
          <?php endif; ?>
        </ul>
      </nav>
    <!-- Modal xác nhận xóa sản phẩm -->
    <div class="modal fade" id="deleteProductModal" tabindex="-1" aria-labelledby="deleteProductModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header bg-danger text-white">
            <h5 class="modal-title" id="deleteProductModalLabel"><i class="fa fa-trash me-2"></i> Xác nhận xóa sản phẩm</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
          </div>
          <div class="modal-body">
            Bạn có chắc chắn muốn xóa sản phẩm này không?
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
            <button type="button" class="btn btn-danger" id="confirmDeleteProduct">Xóa</button>
          </div>
        </div>
      </div>
    </div>
    <script>
    const deleteProductUrl = 'delete_product.php';
    let productIdToDelete = null;
    document.addEventListener('DOMContentLoaded', function() {
      var deleteProductModal = document.getElementById('deleteProductModal');
      deleteProductModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        productIdToDelete = button.getAttribute('data-product-id');
      });
      document.getElementById('confirmDeleteProduct').onclick = function() {
        if (!productIdToDelete) return;
        fetch(deleteProductUrl + '?id=' + encodeURIComponent(productIdToDelete), {
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
        var modal = bootstrap.Modal.getInstance(deleteProductModal);
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
