<?php
session_start();
include_once(__DIR__ . '/config/database.php');
include_once(__DIR__ . '/frontend/modules/session_guard.php');

/* Lấy sản phẩm mới nhất (có ảnh đầu tiên) */
$sql_new = "
    SELECT p.*, 
           (SELECT pi.image_path 
            FROM product_images pi 
            WHERE pi.product_id = p.id 
            ORDER BY pi.id ASC LIMIT 1) AS image
    FROM products p
    ORDER BY p.created_at DESC
    LIMIT 4
";
$new_products = mysqli_query($conn, $sql_new);

/* Lấy sản phẩm bán chạy theo tag */
$sql_hot = "
    SELECT p.*, 
           (SELECT pi.image_path 
            FROM product_images pi 
            WHERE pi.product_id = p.id 
            ORDER BY pi.id ASC LIMIT 1) AS image
    FROM products p
    INNER JOIN product_tags pt ON p.id = pt.product_id
    INNER JOIN tags t ON pt.tag_id = t.id
    WHERE t.name = 'Bán chạy'
    GROUP BY p.id
    ORDER BY p.id DESC
    LIMIT 4
";
$hot_products = mysqli_query($conn, $sql_hot);

/* Hàm xử lý ảnh */
function productImage($row) {
    if (!empty($row['image'])) {
        return 'uploads/products/' . htmlspecialchars($row['image']);
    }
    return 'assets/img/no-image.png'; // fallback nếu chưa có ảnh
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Văn Phòng Phẩm Online</title>

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- CSS Custom -->
  <link rel="stylesheet" href="assets/custom/css/style.css">
</head>
<body>

<!-- Navbar -->
<?php include_once(__DIR__ . '/frontend/layouts/partials/header.php'); ?>

<!-- Main Content -->
<main>
  <!-- Banner Carousel -->
  <div class="hero-section">
    <div id="bannerCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="4000">
      <div class="carousel-indicators">
        <button type="button" data-bs-target="#bannerCarousel" data-bs-slide-to="0" class="active"></button>
        <button type="button" data-bs-target="#bannerCarousel" data-bs-slide-to="1"></button>
        <button type="button" data-bs-target="#bannerCarousel" data-bs-slide-to="2"></button>
        <button type="button" data-bs-target="#bannerCarousel" data-bs-slide-to="3"></button>
      </div>
      <div class="carousel-inner">
        <div class="carousel-item active"><img src="assets/img/banner1.jpg" class="d-block w-100" alt="Banner 1"></div>
        <div class="carousel-item"><img src="assets/img/banner2.jpg" class="d-block w-100" alt="Banner 2"></div>
        <div class="carousel-item"><img src="assets/img/banner3.jpg" class="d-block w-100" alt="Banner 3"></div>
        <div class="carousel-item"><img src="assets/img/banner4.jpg" class="d-block w-100" alt="Banner 4"></div>
      </div>
      <button class="carousel-control-prev" type="button" data-bs-target="#bannerCarousel" data-bs-slide="prev">
        <span class="carousel-control-prev-icon"></span>
      </button>
      <button class="carousel-control-next" type="button" data-bs-target="#bannerCarousel" data-bs-slide="next">
        <span class="carousel-control-next-icon"></span>
      </button>
    </div>
  </div>

  <!-- Ưu điểm nổi bật -->
  <section class="features-section py-5 bg-light">
    <div class="container">
      <div class="row g-4">
        <div class="col-md-3 col-6">
          <div class="feature-box text-center">
            <div class="feature-icon mb-3">
              <i class="fa fa-truck fa-3x text-primary"></i>
            </div>
            <h5 class="fw-bold">Giao hàng nhanh</h5>
            <p class="text-muted mb-0">Miễn phí vận chuyển</p>
          </div>
        </div>
        <div class="col-md-3 col-6">
          <div class="feature-box text-center">
            <div class="feature-icon mb-3">
              <i class="fa fa-shield fa-3x text-success"></i>
            </div>
            <h5 class="fw-bold">Chính hãng 100%</h5>
            <p class="text-muted mb-0">Bảo hành 1 đổi 1</p>
          </div>
        </div>
        <div class="col-md-3 col-6">
          <div class="feature-box text-center">
            <div class="feature-icon mb-3">
              <i class="fa fa-credit-card fa-3x text-warning"></i>
            </div>
            <h5 class="fw-bold">Thanh toán an toàn</h5>
            <p class="text-muted mb-0">Nhiều phương thức</p>
          </div>
        </div>
        <div class="col-md-3 col-6">
          <div class="feature-box text-center">
            <div class="feature-icon mb-3">
              <i class="fa fa-headphones fa-3x text-info"></i>
            </div>
            <h5 class="fw-bold">Hỗ trợ 24/7</h5>
            <p class="text-muted mb-0">Tư vấn miễn phí</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Sản phẩm mới -->
  <section class="products-section py-5">
    <div class="container">
      <div class="section-header text-center mb-5">
        <span class="badge bg-primary mb-2">Sản phẩm mới</span>
        <h2 class="section-title fw-bold">✨ Sản phẩm mới nhất</h2>
        <p class="text-muted">Khám phá những sản phẩm văn phòng phẩm mới nhất</p>
      </div>
      <div class="row g-4">
        <?php while($p = mysqli_fetch_assoc($new_products)): ?>
        <div class="col-lg-3 col-md-4 col-sm-6">
          <div class="product-card-modern h-100">
            <div class="product-badge">
              <span class="badge bg-success">Mới</span>
            </div>
            <div class="product-image">
              <img src="<?= productImage($p) ?>" alt="<?= htmlspecialchars($p['name']) ?>">
              <div class="product-overlay">
                <a href="frontend/pages/product_detail.php?id=<?= $p['id'] ?>" class="btn btn-light btn-sm">
                  <i class="fa fa-eye me-1"></i> Xem chi tiết
                </a>
              </div>
            </div>
            <div class="product-info">
              <h6 class="product-name"><?= htmlspecialchars($p['name']) ?></h6>
              <div class="product-price">
                <span class="current-price"><?= number_format($p['price'], 0, ',', '.') ?>₫</span>
              </div>
              <a href="frontend/pages/product_detail.php?id=<?= $p['id'] ?>" class="btn btn-primary btn-sm w-100 mt-2">
                <i class="fa fa-shopping-cart me-1"></i> Thêm vào giỏ
              </a>
            </div>
          </div>
        </div>
        <?php endwhile; ?>
      </div>
    </div>
  </section>

  <!-- Sản phẩm bán chạy -->
  <section class="products-section py-5 bg-light">
    <div class="container">
      <div class="section-header text-center mb-5">
        <span class="badge bg-danger mb-2">Bán chạy</span>
        <h2 class="section-title fw-bold">🔥 Sản phẩm bán chạy</h2>
        <p class="text-muted">Được ưa chuộng bởi hàng nghìn khách hàng</p>
      </div>
      <div class="row g-4">
        <?php while($p = mysqli_fetch_assoc($hot_products)): ?>
        <div class="col-lg-3 col-md-4 col-sm-6">
          <div class="product-card-modern h-100">
            <div class="product-badge">
              <span class="badge bg-danger">🔥 Hot</span>
            </div>
            <div class="product-image">
              <img src="<?= productImage($p) ?>" alt="<?= htmlspecialchars($p['name']) ?>">
              <div class="product-overlay">
                <a href="frontend/pages/product_detail.php?id=<?= $p['id'] ?>" class="btn btn-light btn-sm">
                  <i class="fa fa-eye me-1"></i> Xem chi tiết
                </a>
              </div>
            </div>
            <div class="product-info">
              <h6 class="product-name"><?= htmlspecialchars($p['name']) ?></h6>
              <div class="product-price">
                <?php if ($p['price_sale']): ?>
                  <span class="old-price"><?= number_format($p['price'], 0, ',', '.') ?>₫</span>
                  <span class="current-price"><?= number_format($p['price_sale'], 0, ',', '.') ?>₫</span>
                <?php else: ?>
                  <span class="current-price"><?= number_format($p['price'], 0, ',', '.') ?>₫</span>
                <?php endif; ?>
              </div>
              <a href="frontend/pages/product_detail.php?id=<?= $p['id'] ?>" class="btn btn-danger btn-sm w-100 mt-2">
                <i class="fa fa-shopping-cart me-1"></i> Mua ngay
              </a>
            </div>
          </div>
        </div>
        <?php endwhile; ?>
      </div>
    </div>
  </section>
</main>

<!-- Footer -->
<?php include_once(__DIR__ . '/frontend/layouts/partials/footer.php'); ?>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/custom/js/main.js"></script>
<script>window.phpIsLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;</script>
<script src="assets/custom/js/session-timeout.js"></script>
</body>
</html>
