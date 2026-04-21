<?php
//trang chi tiết sản phẩm
session_start();
include_once(__DIR__ . '/../../config/database.php');

/* --- Lấy id sản phẩm --- */
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header("Location: ../../index.php");
    exit;
}

/* --- Lấy thông tin sản phẩm --- */
$sql = "
    SELECT p.*, c.name as category_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.id = $id
    LIMIT 1
";
$res = mysqli_query($conn, $sql);
$product = $res ? mysqli_fetch_assoc($res) : null;
if (!$product) {
    echo "<h2 class='text-center text-danger mt-5'>❌ Sản phẩm không tồn tại!</h2>";
    exit;
}

/* --- Lấy tất cả ảnh sản phẩm --- */
$sql_img = "SELECT * FROM product_images WHERE product_id = $id ORDER BY id ASC";
$img_res = mysqli_query($conn, $sql_img);
$images = [];
if ($img_res) {
    while ($r = mysqli_fetch_assoc($img_res)) $images[] = $r;
}

/* --- Hàm lấy đường dẫn ảnh --- */
function imageUrl($fileName) {
    if (!$fileName) return '../../assets/img/no-image.png';
    return '../../uploads/products/' . htmlspecialchars($fileName);
}

/* --- Chuẩn bị mô tả --- */
$description_html = '';
if (!empty($product['description'])) {
    $description_html = html_entity_decode($product['description'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/* --- Logic tình trạng hàng --- */
$status = '<span class="text-danger fw-bold">Hết hàng</span>';
if ($product['stock'] > 30) {
    $status = '<span class="text-success fw-bold">Còn hàng</span>';
} elseif ($product['stock'] > 0) {
    $status = '<span class="text-warning fw-bold">Sắp cháy hàng</span>';
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($product['name']) ?> - VietOffice</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
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
    .product-box {
      background: white;
      border: none;
      border-radius: 20px;
      box-shadow: 0 8px 24px rgba(0,0,0,0.1);
      padding: 30px;
      margin-bottom: 30px;
      animation: fadeIn 0.5s ease;
    }
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }
    .product-gallery { display: flex; gap: 40px; flex-wrap: wrap; align-items: flex-start; }
    .gallery-left { flex: 0 0 500px; max-width: 500px; width: 100%; }
    .gallery-right { flex: 1 1 350px; min-width: 280px; }

    .main-img-wrapper { 
      width: 100%; 
      max-width: 500px; 
      margin: 0 auto;
      border-radius: 16px;
      overflow: hidden;
      box-shadow: 0 8px 20px rgba(0,0,0,0.12);
      background: #f8f9fa;
    }
    .main-img {
      display: block; 
      width: 100%; 
      height: 450px;
      object-fit: contain;
      transition: transform 0.3s ease;
    }
    .main-img:hover {
      transform: scale(1.05);
    }
    .thumb-list { display: flex; flex-wrap: wrap; gap: 10px; justify-content: center; margin-top: 15px; }
    .thumb-img { 
      width: 80px; 
      height: 80px; 
      object-fit: cover; 
      border-radius: 10px; 
      cursor: pointer;
      border: 3px solid transparent; 
      transition: all 0.2s ease;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .thumb-img:hover { 
      transform: scale(1.08); 
      box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    }
    .thumb-img.active { 
      border-color: #667eea; 
      box-shadow: 0 4px 12px rgba(102,126,234,0.4);
    }

    .info-box {
      background: white;
      border: none;
      border-radius: 16px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.08);
      padding: 25px;
      margin-top: 30px;
    }
    .info-box h5 { 
      margin-bottom: 20px; 
      font-weight: 700;
      color: #333;
      font-size: 1.3rem;
    }
    .info-box ul { 
      list-style: none;
      padding-left: 0;
      margin-bottom: 15px; 
    }
    .info-box ul li {
      padding: 8px 0;
      padding-left: 30px;
      position: relative;
    }
    .info-box ul li:before {
      content: "✓";
      position: absolute;
      left: 0;
      color: #28a745;
      font-weight: bold;
      font-size: 1.2rem;
    }
    .info-box p { margin: 8px 0; }

    .product-description { 
      margin-top: 20px; 
      line-height: 1.8; 
      color: #444;
      font-size: 1rem;
    }
    .product-description img { max-width: 100%; height: auto; border-radius: 8px; }

    .quantity-group {
      display: inline-flex;
      border: 2px solid #ddd;
      border-radius: 8px;
      overflow: hidden;
      align-items: center;
    }
    .quantity-group button {
      background: #f8f9fa;
      border: none;
      padding: 10px 20px;
      font-size: 1.2rem;
      font-weight: bold;
      cursor: pointer;
      transition: background 0.2s;
      width: 45px;
      height: 45px;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .quantity-group button:hover {
      background: #667eea;
      color: white;
    }
    .quantity-group input {
      border: none;
      width: 60px;
      text-align: center;
      font-size: 1rem;
      font-weight: 600;
      height: 45px;
    }

    @media (max-width:767px) {
      .product-gallery { flex-direction: column; gap: 20px; }
      .gallery-left, .gallery-right { max-width: 100%; }
      .main-img { height: 350px; }
      .product-box { padding: 20px; }
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
        <li class="breadcrumb-item"><a href="products_list.php">Sản phẩm</a></li>
        <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($product['name']) ?></li>
      </ol>
    </nav>
  </div>
</section>

<main class="container mb-5">
  <!-- Khung chính -->
  <div class="product-box">
    <div class="product-gallery">
      <!-- Left -->
      <div class="gallery-left text-center">
        <?php $mainImage = isset($images[0]) ? imageUrl($images[0]['image_path']) : '../../assets/img/no-image.png'; ?>
        <div class="main-img-wrapper">
          <img id="mainImage" src="<?= $mainImage ?>" class="main-img mb-3" alt="<?= htmlspecialchars($product['name']) ?>">
        </div>
        <div class="thumb-list">
          <?php foreach($images as $k=>$img): 
              $url = imageUrl($img['image_path']);
              $active = $k===0 ? 'active' : '';
          ?>
            <img src="<?= $url ?>" data-src="<?= $url ?>" class="thumb-img <?= $active ?>" onclick="changeImage(this)">
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Right -->
      <div class="gallery-right">
        <h2 class="mb-3 fw-bold" style="font-size: 2rem; color: #333; line-height: 1.3;">
          <?= htmlspecialchars($product['name']) ?>
        </h2>
        <div class="mb-3">
          <span class="badge bg-primary me-2" style="padding: 8px 14px; font-size: 0.9rem;">
            <i class="fas fa-tag me-1"></i><?= htmlspecialchars($product['category_name'] ?? '—') ?>
          </span>
          <span class="badge bg-info text-white me-2" style="padding: 8px 14px; font-size: 0.9rem;">
            <i class="fas fa-building me-1"></i><?= htmlspecialchars($product['brand'] ?? 'Không rõ') ?>
          </span>
          <span class="badge bg-secondary text-white" style="padding: 8px 14px; font-size: 0.9rem;">
            <i class="fas fa-globe me-1"></i><?= htmlspecialchars($product['origin'] ?? 'Không rõ') ?>
          </span>
        </div>
        <div class="mb-4">
          <div class="price d-flex align-items-baseline gap-3">
            <?php if (!empty($product['price_sale'])): ?>
              <div class="h2 text-danger mb-0 fw-bold">
                <?= number_format($product['price_sale'],0,',','.') ?>₫
              </div>
              <div class="h5 text-muted"><del><?= number_format($product['price'],0,',','.') ?>₫</del></div>
              <span class="badge bg-danger" style="padding: 8px 12px;">-<?= round((($product['price']-$product['price_sale'])/$product['price'])*100) ?>%</span>
            <?php else: ?>
              <div class="h2 text-danger mb-0 fw-bold">
                <?= number_format($product['price'],0,',','.') ?>₫
              </div>
            <?php endif; ?>
          </div>
        </div>
        <div class="mb-3 p-3 bg-light rounded-3">
          <p class="mb-0"><strong><i class="fas fa-box me-2 text-primary"></i>Tình trạng:</strong> <?= $status ?></p>
        </div>
        <form id="addToCartForm" action="cart_add.php" method="post" class="mt-4">
          <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
          <div class="mb-3">
            <label class="form-label fw-semibold"><i class="fas fa-sort-numeric-up me-2"></i>Số lượng:</label>
            <div class="quantity-group">
              <button type="button" onclick="changeQty(-1)">−</button>
              <input type="number" name="quantity" id="quantityInput" value="1" min="1">
              <button type="button" onclick="changeQty(1)">+</button>
            </div>
          </div>
          <button type="submit" class="btn btn-success btn-lg w-100 fw-bold py-3 rounded-3 mb-3" style="font-size: 1.1rem;">
            <i class="fas fa-shopping-cart me-2"></i>Thêm vào giỏ hàng
          </button>
          <a href="products_list.php" class="btn btn-outline-primary btn-lg w-100 fw-bold py-3 rounded-3">
            <i class="fas fa-arrow-left me-2"></i>Tiếp tục mua sắm
          </a>
        </form>
        <div id="cartAlert" class="alert alert-success mt-3 d-none rounded-3">
          <i class="fas fa-check-circle me-2"></i>Đã thêm vào giỏ hàng thành công!
        </div>
        <div class="mt-4 pt-3 border-top">
          <p class="text-muted mb-0" style="font-size: 0.9rem;">
            <i class="fas fa-calendar-alt me-2"></i>Cập nhật: <?= date('d/m/Y H:i', strtotime($product['created_at'])) ?>
          </p>
        </div>
      </div>
    </div>
  </div>

  <!-- Box lợi ích & liên hệ -->
  <div class="info-box">
    <h5><i class="fas fa-gift text-danger me-2"></i>Lợi ích khi mua hàng</h5>
    <ul>
      <li>Miễn phí giao hàng cho đơn từ 500.000₫</li>
      <li>Cam kết sản phẩm chính hãng 100%</li>
      <li>Đổi trả trong vòng 7 ngày nếu lỗi sản xuất</li>
      <li>Hỗ trợ tư vấn 24/7</li>
    </ul>
    <div class="mt-3 p-3 bg-light rounded-3">
      <p class="mb-2"><strong><i class="fas fa-phone-alt text-success me-2"></i>Hotline:</strong> <a href="tel:0123456789" class="text-decoration-none">0123 456 789</a></p>
      <p class="mb-0"><strong><i class="fas fa-envelope text-primary me-2"></i>Email:</strong> <a href="mailto:hotro@vpp.com" class="text-decoration-none">hotro@vpp.com</a></p>
    </div>
  </div>

  <!-- Mô tả -->
  <div class="info-box">
    <h5><i class="fas fa-info-circle text-primary me-2"></i>Mô tả sản phẩm</h5>
    <div class="product-description">
      <?= $description_html ?: '<p>Chưa có mô tả cho sản phẩm này.</p>' ?>
    </div>
  </div>
</main>

<?php include_once(__DIR__ . '/../layouts/partials/footer.php'); ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
  function changeImage(el) {
    const src = el.dataset.src || el.src;
    const main = document.getElementById('mainImage');
    if (!main) return;
    main.src = src;
    document.querySelectorAll('.thumb-img').forEach(i => i.classList.remove('active'));
    el.classList.add('active');
  }

  // AJAX thêm giỏ hàng
  document.getElementById('addToCartForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const form = this;
    const data = new FormData(form);
    fetch('cart_add.php', {
      method: 'POST',
      body: data
    })
    .then(res => res.ok ? res.text() : Promise.reject(res))
    .then(() => {
      const alert = document.getElementById('cartAlert');
      alert.classList.remove('d-none');
      setTimeout(() => alert.classList.add('d-none'), 2000);
    })
    .catch(() => alert('Lỗi khi thêm vào giỏ hàng!'));
  });

  function changeQty(delta) {
  const input = document.getElementById('quantityInput');
  let val = parseInt(input.value) || 1;
  val += delta;
  if (val < 1) val = 1;
  input.value = val;
  }
</script>
</body>
</html>