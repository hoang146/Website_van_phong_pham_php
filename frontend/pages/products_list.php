<?php
//trang danh sách sản phẩm
include_once(__DIR__ . '/../../config/database.php');
session_start();
/* --- Lấy filter --- */
$category = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$priceMin = isset($_GET['min']) ? (float)$_GET['min'] : 0;
$priceMax = isset($_GET['max']) ? (float)$_GET['max'] : 0;
$sort     = $_GET['sort'] ?? '';
$search   = isset($_GET['search']) ? trim($_GET['search']) : '';
/* --- Phân trang --- */
$limit = 16;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;
/* --- Điều kiện lọc --- */
$where = "1=1";
if ($category > 0) $where .= " AND p.category_id = $category";
if ($priceMin > 0) $where .= " AND p.price >= $priceMin";
if ($priceMax > 0) $where .= " AND p.price <= $priceMax";
if ($search !== '') {
  $search_escaped = mysqli_real_escape_string($conn, $search);
  $where .= " AND p.name LIKE '%$search_escaped%'";
}
/* --- Sắp xếp --- */
$order = "p.created_at DESC"; 
if ($sort == "name_asc")  $order = "p.name ASC";
if ($sort == "name_desc") $order = "p.name DESC";
if ($sort == "price_asc") $order = "p.price ASC";
if ($sort == "price_desc") $order = "p.price DESC";
/* --- Lấy tổng sản phẩm --- */
$count_sql = "SELECT COUNT(*) as total FROM products p WHERE $where";
$total = mysqli_fetch_assoc(mysqli_query($conn, $count_sql))['total'];
$total_pages = ceil($total / $limit);
/* --- Lấy danh sách sản phẩm --- */
$sql = "
    SELECT p.*, 
           (SELECT pi.image_path FROM product_images pi WHERE pi.product_id=p.id ORDER BY pi.id ASC LIMIT 1) AS image,
           c.name as category_name
    FROM products p
    LEFT JOIN categories c ON p.category_id=c.id
    WHERE $where
    ORDER BY $order
    LIMIT $limit OFFSET $offset
";
$products = mysqli_query($conn, $sql);
/* --- Lấy danh mục để filter --- */
$categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY name ASC");
/* Hàm xử lý ảnh */
function productImage($row) {
    if (!empty($row['image'])) {
        return '../../uploads/products/' . htmlspecialchars($row['image']);
    }
    return '../../assets/img/no-image.png';
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Tất cả sản phẩm - VietOffice</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <link rel="stylesheet" href="../../assets/custom/css/style.css">
  <style>
    .breadcrumb-section {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
    .filter-card {
      background: white;
      border-radius: 16px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.08);
      border: none;
      overflow: hidden;
      animation: slideIn 0.5s ease;
    }
    @keyframes slideIn {
      from { opacity: 0; transform: translateX(-20px); }
      to { opacity: 1; transform: translateX(0); }
    }
    .filter-header {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: 15px 20px;
      font-weight: 600;
      font-size: 1.1rem;
    }
    .filter-body {
      padding: 20px;
    }
    .product-header {
      text-align: center;
      margin-bottom: 30px;
    }
    .product-header h2 {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      font-size: 2rem;
      font-weight: 700;
    }
    .product-count {
      color: #666;
      font-size: 0.95rem;
      margin-top: 5px;
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
        <li class="breadcrumb-item active" aria-current="page">Sản phẩm</li>
      </ol>
    </nav>
  </div>
</section>

<div class="container mb-5">
  <div class="row">
    <!-- Sidebar -->
    <aside class="col-lg-3 col-md-4 mb-4">
      <div class="filter-card sticky-top" style="top: 80px;">
        <div class="filter-header">
          <i class="fas fa-filter me-2"></i>Bộ lọc tìm kiếm
        </div>
        <div class="filter-body">
          <form method="get">
            <!-- Danh mục -->
            <div class="mb-3">
              <label class="form-label fw-semibold"><i class="fas fa-list me-2 text-primary"></i>Danh mục</label>
              <select name="category" class="form-select form-select-lg rounded-3">
                <option value="0">🏷️ Tất cả danh mục</option>
                <?php while($cat = mysqli_fetch_assoc($categories)): ?>
                  <option value="<?= $cat['id'] ?>" <?= $category==$cat['id']?"selected":"" ?>>
                    <?= htmlspecialchars($cat['name']) ?>
                  </option>
                <?php endwhile; ?>
              </select>
            </div>

            <!-- Giá -->
            <div class="mb-3">
              <label class="form-label fw-semibold"><i class="fas fa-dollar-sign me-2 text-success"></i>Khoảng giá</label>
              <div class="row g-2">
                <div class="col-6">
                  <input type="number" name="min" value="<?= $priceMin ?>" class="form-control rounded-3" placeholder="Từ">
                </div>
                <div class="col-6">
                  <input type="number" name="max" value="<?= $priceMax ?>" class="form-control rounded-3" placeholder="Đến">
                </div>
              </div>
            </div>

            <!-- Sắp xếp -->
            <div class="mb-3">
              <label class="form-label fw-semibold"><i class="fas fa-sort me-2 text-warning"></i>Sắp xếp theo</label>
              <select name="sort" class="form-select form-select-lg rounded-3">
                <option value="">🆕 Mới nhất</option>
                <option value="name_asc" <?= $sort=="name_asc"?"selected":"" ?>>📝 Tên A → Z</option>
                <option value="name_desc" <?= $sort=="name_desc"?"selected":"" ?>>📝 Tên Z → A</option>
                <option value="price_asc" <?= $sort=="price_asc"?"selected":"" ?>>💰 Giá thấp → cao</option>
                <option value="price_desc" <?= $sort=="price_desc"?"selected":"" ?>>💰 Giá cao → thấp</option>
              </select>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2 fw-bold rounded-3" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">
              <i class="fas fa-search me-2"></i>Áp dụng lọc
            </button>
            <a href="products_list.php" class="btn btn-outline-secondary w-100 mt-2 py-2 rounded-3">
              <i class="fas fa-redo me-2"></i>Làm mới
            </a>
          </form>
        </div>
      </div>
    </aside>

    <!-- Danh sách sản phẩm -->
    <main class="col-lg-9 col-md-8">
      <div class="product-header">
        <h2 class="mb-2">✨ Danh sách sản phẩm</h2>
        <p class="product-count">Tìm thấy <strong><?= $total ?></strong> sản phẩm</p>
      </div>
      <div class="row g-4">
        <?php while($p = mysqli_fetch_assoc($products)): ?>
          <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6">
            <div class="product-card-modern h-100">
              <div class="product-badge">
                <?php if ($p['price_sale']): ?>
                  <span class="badge bg-danger">🔥 Sale</span>
                <?php else: ?>
                  <span class="badge bg-success">Mới</span>
                <?php endif; ?>
              </div>
              <div class="product-image">
                <img src="<?= productImage($p) ?>" alt="<?= htmlspecialchars($p['name']) ?>">
                <div class="product-overlay">
                  <a href="product_detail.php?id=<?= $p['id'] ?>" class="btn btn-light btn-sm">
                    <i class="fas fa-eye me-1"></i> Xem chi tiết
                  </a>
                </div>
              </div>
              <div class="product-info">
                <div class="mb-2">
                  <span class="badge bg-light text-dark" style="font-size: 0.75rem;">
                    <i class="fas fa-tag me-1"></i><?= htmlspecialchars($p['category_name']) ?>
                  </span>
                </div>
                <h6 class="product-name"><?= htmlspecialchars($p['name']) ?></h6>
                <div class="product-price">
                  <?php if ($p['price_sale']): ?>
                    <span class="old-price"><?= number_format($p['price'],0,',','.') ?>₫</span>
                    <span class="current-price"><?= number_format($p['price_sale'],0,',','.') ?>₫</span>
                  <?php else: ?>
                    <span class="current-price"><?= number_format($p['price'],0,',','.') ?>₫</span>
                  <?php endif; ?>
                </div>
                <a href="product_detail.php?id=<?= $p['id'] ?>" class="btn btn-primary btn-sm w-100 mt-2">
                  <i class="fas fa-shopping-cart me-1"></i> Thêm vào giỏ
                </a>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      </div>

      <!-- Phân trang -->
      <?php if ($total_pages > 1): ?>
      <nav class="mt-5">
        <ul class="pagination justify-content-center">
          <?php if ($page > 1): ?>
            <li class="page-item">
              <a class="page-link rounded-3 me-1" href="?<?= http_build_query(array_merge($_GET,['page'=>$page-1])) ?>">
                <i class="fas fa-chevron-left"></i> Trước
              </a>
            </li>
          <?php endif; ?>

          <?php 
          $start = max(1, $page - 2);
          $end = min($total_pages, $page + 2);
          for($i=$start; $i<=$end; $i++): 
          ?>
            <li class="page-item <?= ($i==$page)?"active":"" ?>">
              <a class="page-link rounded-3 mx-1" href="?<?= http_build_query(array_merge($_GET,['page'=>$i])) ?>"><?= $i ?></a>
            </li>
          <?php endfor; ?>

          <?php if ($page < $total_pages): ?>
            <li class="page-item">
              <a class="page-link rounded-3 ms-1" href="?<?= http_build_query(array_merge($_GET,['page'=>$page+1])) ?>">
                Sau <i class="fas fa-chevron-right"></i>
              </a>
            </li>
          <?php endif; ?>
        </ul>
      </nav>
      <?php endif; ?>
    </main>
  </div>
</div>

<?php include_once(__DIR__ . '/../layouts/partials/footer.php'); ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>window.phpIsLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;</script>
</body>
