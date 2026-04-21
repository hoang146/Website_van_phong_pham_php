<?php
include_once(__DIR__ . '/../../config/database.php');
session_start();

// Kiểm tra đăng nhập
$logged_in = isset($_SESSION['user_id']);

// Nếu chưa đăng nhập, hiển thị thông báo và không cho thao tác
if (!$logged_in) {
    echo '<!DOCTYPE html>
<html lang="vi">
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Giỏ hàng</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../../assets/custom/css/style.css">
</head>
<body>';
include_once(__DIR__ . '/../layouts/partials/header.php');
echo '<div class="container py-4">'; 
    echo '<div class="container mt-5">
      <div class="alert alert-warning text-center">
        Bạn cần <a href="login.php">đăng nhập</a> để sử dụng chức năng giỏ hàng!
      </div>
    </div>';
  echo '</div>';
  include_once(__DIR__ . '/../layouts/partials/footer.php');
  echo '<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>';
  echo '</body></html>';
    exit;
}

// Khôi phục giỏ hàng từ database nếu user đã đăng nhập
if ($logged_in) {
    $user_id = $_SESSION['user_id'];
    $cart = [];
    $sql = "SELECT cart_data FROM user_cart WHERE user_id = $user_id LIMIT 1";
    $result = mysqli_query($conn, $sql);
    if ($result && $row = mysqli_fetch_assoc($result)) {
        $cart = json_decode($row['cart_data'], true) ?: [];
    }
    $_SESSION['cart'] = $cart;
}

// Khởi tạo giỏ hàng nếu chưa có
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}
$cart = $_SESSION['cart'];

// Xử lý thêm/xóa/cập nhật sản phẩm
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        $product_id = intval($_POST['product_id']);
        $quantity = max(1, intval($_POST['quantity']));
        if ($action === 'add') {
            if (isset($cart[$product_id])) {
                $cart[$product_id] += $quantity;
            } else {
                $cart[$product_id] = $quantity;
            }
        } elseif ($action === 'remove') {
            unset($cart[$product_id]);
        } elseif ($action === 'update') {
            $cart[$product_id] = $quantity;
        }
        $_SESSION['cart'] = $cart;
        // Lưu giỏ hàng vào database nếu đã đăng nhập
        if ($logged_in) {
            $user_id = $_SESSION['user_id'];
            $cart_json = mysqli_real_escape_string($conn, json_encode($cart));
            $sql = "REPLACE INTO user_cart (user_id, cart_data) VALUES ($user_id, '$cart_json')";
            mysqli_query($conn, $sql);
        }
        header('Location: cart.php');
        exit;
    }
}

// Lấy thông tin sản phẩm từ database
$product_ids = array_keys($cart);
$products = [];
$total = 0;
if ($product_ids) {
    $ids = implode(',', array_map('intval', $product_ids));
    $sql = "SELECT * FROM products WHERE id IN ($ids)";
    $result = mysqli_query($conn, $sql);
    while ($row = mysqli_fetch_assoc($result)) {
  $row['quantity'] = $cart[$row['id']];
  $price = (isset($row['price_sale']) && $row['price_sale'] > 0) ? $row['price_sale'] : $row['price'];
  $row['subtotal'] = $row['quantity'] * $price;
  $row['display_price'] = $price;
  $products[] = $row;
  $total += $row['subtotal'];
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta charset="UTF-8">
  <title>Giỏ hàng - VietOffice</title>
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
    .cart-card {
      background: white;
      border: none;
      border-radius: 20px;
      box-shadow: 0 8px 24px rgba(0,0,0,0.1);
      overflow: hidden;
      animation: fadeIn 0.5s ease;
    }
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }
    .cart-header {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: 25px 30px;
      font-size: 1.5rem;
      font-weight: 700;
    }
    .product-img {
      width: 80px;
      height: 80px;
      object-fit: cover;
      border-radius: 10px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .quantity-control {
      display: inline-flex;
      border: 2px solid #ddd;
      border-radius: 8px;
      overflow: hidden;
    }
    .quantity-control button {
      background: #f8f9fa;
      border: none;
      padding: 8px 16px;
      font-size: 1.1rem;
      font-weight: bold;
      cursor: pointer;
      transition: all 0.2s;
    }
    .quantity-control button:hover {
      background: #667eea;
      color: white;
    }
    .quantity-control input {
      border: none;
      width: 60px;
      text-align: center;
      font-size: 1rem;
      font-weight: 600;
    }
    .total-section {
      background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
      padding: 20px;
      border-radius: 12px;
      margin-top: 20px;
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
        <li class="breadcrumb-item active" aria-current="page">Giỏ hàng</li>
      </ol>
    </nav>
  </div>
</section>

<div class="container mb-5">
  <div class="row justify-content-center">
    <div class="col-lg-11">
      <div class="cart-card">
        <div class="cart-header">
          <i class="fas fa-shopping-cart me-2"></i>Giỏ hàng của bạn
        </div>
        <div class="card-body p-4">
          <?php if (isset($_GET['removed']) && $_GET['removed'] == 1): ?>
            <div class="alert alert-success text-center">Sản phẩm đã được xóa khỏi giỏ hàng thành công!</div>
          <?php endif; ?>

          <?php if (empty($products)): ?>
            <div class="alert alert-info text-center py-5">
              <i class="fas fa-shopping-cart fa-3x mb-3 text-muted"></i>
              <h5>Giỏ hàng trống</h5>
              <p class="mb-3">Hãy thêm sản phẩm vào giỏ hàng để tiếp tục mua sắm!</p>
              <a href="products_list.php" class="btn btn-primary">
                <i class="fas fa-shopping-bag me-2"></i>Xem sản phẩm
              </a>
            </div>
          <?php else: ?>
            <form method="post" action="checkout.php">
              <div class="table-responsive">
                <table class="table align-middle">
                  <thead style="background: #f8f9fa; border-bottom: 2px solid #667eea;">
                    <tr>
                      <th width="50"><input type="checkbox" id="checkAll" class="form-check-input" style="cursor: pointer;"></th>
                      <th>Sản phẩm</th>
                      <th width="150" class="text-center">Giá</th>
                      <th width="150" class="text-center">Số lượng</th>
                      <th width="150" class="text-center">Tạm tính</th>
                      <th width="100" class="text-center">Thao tác</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($products as $p): 
                      $img_sql = "SELECT image_path FROM product_images WHERE product_id = {$p['id']} LIMIT 1";
                      $img_result = mysqli_query($conn, $img_sql);
                      $img = mysqli_fetch_assoc($img_result);
                      $img_url = $img ? '../../uploads/products/' . $img['image_path'] : '../../assets/img/no-image.png';
                    ?>
                    <tr style="border-bottom: 1px solid #e9ecef;">
                      <td>
                        <input type="checkbox" name="selected_products[]" value="<?= $p['id'] ?>" class="product-checkbox form-check-input" style="cursor: pointer;">
                      </td>
                      <td>
                        <div class="d-flex align-items-center gap-3">
                          <img src="<?= $img_url ?>" alt="<?= htmlspecialchars($p['name']) ?>" class="product-img">
                          <div class="text-start">
                            <h6 class="mb-1 fw-bold"><?= htmlspecialchars($p['name']) ?></h6>
                            <?php if (!empty($p['price_sale'])): ?>
                              <span class="badge bg-danger"><i class="fas fa-tag me-1"></i>Giảm giá</span>
                            <?php endif; ?>
                          </div>
                        </div>
                      </td>
                      <td class="text-center">
                        <?php if (isset($p['price_sale']) && $p['price_sale'] > 0): ?>
                          <div><del class="text-muted small"><?= number_format($p['price']) ?>₫</del></div>
                          <div class="text-danger fw-bold"><?= number_format($p['price_sale']) ?>₫</div>
                        <?php else: ?>
                          <div class="text-danger fw-bold"><?= number_format($p['price']) ?>₫</div>
                        <?php endif; ?>
                      </td>
                      <td class="text-center">
                        <div class="quantity-control">
                          <button type="button" class="btn-qty" data-action="decrease" data-id="<?= $p['id'] ?>">−</button>
                          <input type="number" min="1" value="<?= $p['quantity'] ?>" class="qty-input" data-id="<?= $p['id'] ?>">
                          <button type="button" class="btn-qty" data-action="increase" data-id="<?= $p['id'] ?>">+</button>
                        </div>
                      </td>
                      <td class="text-center">
                        <div class="fw-bold text-success" style="font-size: 1.1rem;"><?= number_format($p['subtotal']) ?>₫</div>
                      </td>
                      <td class="text-center">
                        <form class="remove-form d-inline" method="post">
                          <input type="hidden" name="action" value="remove">
                          <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                          <button type="button" class="btn btn-sm btn-outline-danger remove-btn rounded-3">
                            <i class="fas fa-trash-alt"></i>
                          </button>
                        </form>
                      </td>
                    </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
              
              <div class="total-section">
                <div class="row align-items-center">
                  <div class="col-md-6">
                    <p class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Sản phẩm đã chọn: <strong id="selectedCount">0</strong></p>
                    <p class="mb-0"><i class="fas fa-box text-primary me-2"></i>Tổng số lượng: <strong><?= count($products) ?></strong> sản phẩm</p>
                  </div>
                  <div class="col-md-6 text-md-end">
                    <h4 class="mb-0">Tổng tiền: <span class="text-danger" style="font-size: 1.8rem;"><?= number_format($total) ?>₫</span></h4>
                  </div>
                </div>
              </div>
              
              <div class="d-flex gap-3 mt-4 flex-wrap">
                <a href="products_list.php" class="btn btn-outline-secondary btn-lg px-4 py-3 rounded-3">
                  <i class="fas fa-arrow-left me-2"></i>Tiếp tục mua hàng
                </a>
                <button type="submit" class="btn btn-success btn-lg px-5 py-3 fw-bold rounded-3 flex-grow-1" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); border: none;">
                  <i class="fas fa-credit-card me-2"></i>Thanh toán
                </button>
              </div>
            </form>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include_once(__DIR__ . '/../layouts/partials/footer.php'); ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // Cập nhật số lượng sản phẩm đã chọn
  function updateSelectedCount() {
    const count = document.querySelectorAll('.product-checkbox:checked').length;
    const el = document.getElementById('selectedCount');
    if (el) el.textContent = count;
  }
  
  // Chọn/bỏ chọn tất cả sản phẩm
  document.getElementById('checkAll')?.addEventListener('change', function() {
    const checked = this.checked;
    document.querySelectorAll('.product-checkbox').forEach(function(cb) {
      cb.checked = checked;
    });
    updateSelectedCount();
  });
  
  // Cập nhật count khi chọn từng checkbox
  document.querySelectorAll('.product-checkbox').forEach(function(cb) {
    cb.addEventListener('change', updateSelectedCount);
  });
  updateSelectedCount();
  document.querySelectorAll('.remove-btn').forEach(function(btn) {
    btn.addEventListener('click', function(e) {
      e.preventDefault();
      if (document.getElementById('confirmRemoveModal')) {
        document.getElementById('confirmRemoveModal').remove();
      }
      const modalHtml = `<div class='modal fade' id='confirmRemoveModal' tabindex='-1'>
        <div class='modal-dialog modal-dialog-centered'>
          <div class='modal-content'>
            <div class='modal-header bg-danger text-white'>
              <h5 class='modal-title'>Xác nhận xóa</h5>
              <button type='button' class='btn-close' data-bs-dismiss='modal'></button>
            </div>
            <div class='modal-body'>
              <p>Bạn có muốn xóa sản phẩm này khỏi giỏ hàng?</p>
            </div>
            <div class='modal-footer'>
              <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Hủy</button>
              <button type='button' class='btn btn-danger' id='confirmRemoveBtn'>Xóa</button>
            </div>
          </div>
        </div>
      </div>`;
      document.body.insertAdjacentHTML('beforeend', modalHtml);
      const confirmModal = new bootstrap.Modal(document.getElementById('confirmRemoveModal'));
      confirmModal.show();
      document.getElementById('confirmRemoveBtn').onclick = function() {
        btn.closest('form').submit();
      };
      document.getElementById('confirmRemoveModal').addEventListener('hidden.bs.modal', function() {
        if (document.getElementById('confirmRemoveModal')) {
          document.getElementById('confirmRemoveModal').remove();
        }
      });
    });
  });

  // Xử lý tăng/giảm số lượng và cập nhật giá bằng AJAX
  document.querySelectorAll('.btn-qty').forEach(function(btn) {
    btn.addEventListener('click', function() {
      const id = this.dataset.id;
      const input = document.querySelector('.qty-input[data-id="' + id + '"]');
      let qty = parseInt(input.value) || 1;
      if (this.dataset.action === 'increase') qty++;
      if (this.dataset.action === 'decrease' && qty > 1) qty--;
      input.value = qty;
      updateQuantity(id, qty);
    });
  });
  document.querySelectorAll('.qty-input').forEach(function(input) {
    input.addEventListener('change', function() {
      let qty = parseInt(this.value) || 1;
      if (qty < 1) qty = 1;
      this.value = qty;
      updateQuantity(this.dataset.id, qty);
    });
  });

  function updateQuantity(id, qty) {
    fetch('cart.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: 'action=update&product_id=' + id + '&quantity=' + qty
    })
    .then(res => res.ok ? res.text() : Promise.reject(res))
    .then(() => {
      // Reload lại trang để cập nhật giá, số lượng
      location.reload();
    })
    .catch(() => alert('Lỗi khi cập nhật số lượng!'));
  }
</script>
</body>
</html>
