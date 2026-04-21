<?php
if (session_status() === PHP_SESSION_NONE) session_start();

/*
  Cách hoạt động:
  - Tìm đường dẫn vật lý của thư mục project (ví dụ .../Website_Van_Phong_Pham)
  - So sánh với DOCUMENT_ROOT để tạo $base_url (ví dụ '/Website_Van_Phong_Pham' hoặc '' nếu site ở gốc)
  - Khi tạo link (login/register/...) sẽ ưu tiên file trong frontend/pages nếu tồn tại,
    nếu không sẽ fallback sang frontend/modules.
*/

$projectRoot = realpath(__DIR__ . '/../../..'); // thư mục gốc project
$docRoot     = realpath($_SERVER['DOCUMENT_ROOT']);

// đảm bảo chuỗi dùng dấu / (cross-platform)
$projectRoot = $projectRoot !== false ? str_replace('\\','/',$projectRoot) : '';
$docRoot     = $docRoot !== false ? str_replace('\\','/',$docRoot) : '';

// tạo base URL (chuỗi bắt đầu bằng '/' nếu project nằm trong subfolder)
$base_url = '';
if ($projectRoot !== '' && $docRoot !== '' && strpos($projectRoot, $docRoot) === 0) {
    $base_url = substr($projectRoot, strlen($docRoot));
    if ($base_url === false) $base_url = '';
    $base_url = '/' . trim($base_url, '/');
    if ($base_url === '/') $base_url = '';
}

// helper: chọn file (pages ưu tiên, nếu không có thì modules)
function choose_path($pages_path, $modules_path, $projectRoot, $base_url) {
    $p_full = rtrim($projectRoot, '/') . '/' . ltrim($pages_path, '/');
    $m_full = rtrim($projectRoot, '/') . '/' . ltrim($modules_path, '/');

    if ($p_full && file_exists($p_full)) {
        return $base_url . '/' . ltrim($pages_path, '/');
    }
    if ($m_full && file_exists($m_full)) {
        return $base_url . '/' . ltrim($modules_path, '/');
    }
    // fallback: return pages path (even nếu không tồn tại), để dev dễ thấy link
    return $base_url . '/' . ltrim($pages_path, '/');
}

// các link dùng chung
$index_link     = $base_url . '/index.php';
$products_link  = $base_url . '/frontend/pages/products_list.php';
$contact_link   = $base_url . '/frontend/pages/contact.php';
$about_link     = $base_url . '/frontend/pages/about_us.php';
$cart_link      = $base_url . '/frontend/pages/cart.php';

// login/register/logout/user_info (tự chọn pages hoặc modules)
$login_link     = choose_path('frontend/pages/login.php', 'frontend/modules/login.php', $projectRoot, $base_url);
$register_link  = choose_path('frontend/pages/register.php', 'frontend/modules/register.php', $projectRoot, $base_url);
$logout_link    = choose_path('frontend/pages/logout.php', 'frontend/modules/logout.php', $projectRoot, $base_url);
$user_info_link = choose_path('frontend/pages/user_info.php', 'frontend/modules/user_info.php', $projectRoot, $base_url);

// đường dẫn tới assets
$css_fa = $base_url . '/assets/vendor/font-awesome/css/font-awesome.min.css';
$css_main = $base_url . '/assets/custom/css/style.css';
$css_suggestions = $base_url . '/assets/custom/css/search-suggestions.css?v=2';

// chuẩn hóa (loại bỏ // thừa)
$css_fa = preg_replace('#/+#','/',$css_fa);
$css_main = preg_replace('#/+#','/',$css_main);
$index_link = preg_replace('#/+#','/',$index_link);
$products_link = preg_replace('#/+#','/',$products_link);
$contact_link = preg_replace('#/+#','/',$contact_link);
$about_link = preg_replace('#/+#','/',$about_link);
$cart_link = preg_replace('#/+#','/',$cart_link);
$login_link = preg_replace('#/+#','/',$login_link);
$register_link = preg_replace('#/+#','/',$register_link);
$logout_link = preg_replace('#/+#','/',$logout_link);
$user_info_link = preg_replace('#/+#','/',$user_info_link);
?>

<!-- header.php -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
<link rel="stylesheet" href="<?= htmlspecialchars($css_main . '?v=1') ?>">
<link rel="stylesheet" href="<?= htmlspecialchars($css_suggestions) ?>">
<style>
.modern-navbar {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
  padding: 12px 0;
}
.navbar-brand {
  font-size: 1.5rem;
  font-weight: 700;
  display: flex;
  align-items: center;
  gap: 10px;
}
.navbar-brand i {
  font-size: 1.8rem;
}
.nav-link {
  font-weight: 500;
  padding: 8px 16px !important;
  border-radius: 8px;
  transition: all 0.3s;
}
.nav-link:hover {
  background: rgba(255,255,255,0.2);
  transform: translateY(-2px);
}
.search-wrapper {
  position: relative;
  max-width: 300px;
}
.search-input-modern {
  border-radius: 25px;
  padding: 10px 45px 10px 20px;
  border: 2px solid rgba(255,255,255,0.3);
  background: rgba(255,255,255,0.15);
  color: white;
  transition: all 0.3s;
}
.search-input-modern::placeholder {
  color: rgba(255,255,255,0.8);
}
.search-input-modern:focus {
  background: white;
  color: #333;
  border-color: white;
}
.search-input-modern:focus::placeholder {
  color: #999;
}
.search-btn-modern {
  position: absolute;
  right: 5px;
  top: 50%;
  transform: translateY(-50%);
  border: none;
  background: transparent;
  color: white;
  padding: 8px 12px;
  border-radius: 20px;
  transition: all 0.3s;
}
.search-input-modern:focus + .search-btn-modern {
  color: #667eea;
}
.cart-btn-modern {
  position: relative;
  background: rgba(255,255,255,0.2);
  border: 2px solid rgba(255,255,255,0.3);
  color: white;
  padding: 8px 16px;
  border-radius: 25px;
  transition: all 0.3s;
}
.cart-btn-modern:hover {
  background: white;
  color: #667eea;
  transform: scale(1.05);
}
.cart-badge-modern {
  position: absolute;
  top: -8px;
  right: -8px;
  background: #ff4757;
  color: white;
  border-radius: 50%;
  width: 22px;
  height: 22px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.75rem;
  font-weight: 700;
  border: 2px solid white;
}
.auth-btn-modern {
  border-radius: 20px;
  padding: 8px 20px;
  font-weight: 600;
  border: 2px solid rgba(255,255,255,0.5);
  transition: all 0.3s;
}
.auth-btn-modern:hover {
  background: white;
  color: #667eea;
  border-color: white;
}
@media (max-width: 991px) {
  .search-wrapper {
    max-width: 100%;
    margin: 15px 0;
  }
  .navbar-collapse {
    background: rgba(0,0,0,0.1);
    padding: 20px;
    border-radius: 12px;
    margin-top: 15px;
  }
}
</style>

<nav class="navbar navbar-expand-lg navbar-dark sticky-top modern-navbar">
  <div class="container">
    <a class="navbar-brand" href="<?= htmlspecialchars($index_link) ?>">
      <i class="fas fa-book-open"></i>
      <span>VietOffice</span>
    </a>

    <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#menu">
      <i class="fas fa-bars fa-lg"></i>
    </button>

    <div class="collapse navbar-collapse" id="menu">
      <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-lg-center">
        <li class="nav-item">
          <a class="nav-link" href="<?= htmlspecialchars($index_link) ?>">
            <i class="fas fa-home me-1"></i>Trang chủ
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="<?= htmlspecialchars($products_link) ?>">
            <i class="fas fa-box me-1"></i>Sản phẩm
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="<?= htmlspecialchars($contact_link) ?>">
            <i class="fas fa-envelope me-1"></i>Liên hệ
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="<?= htmlspecialchars($about_link) ?>">
            <i class="fas fa-info-circle me-1"></i>Giới thiệu
          </a>
        </li>
      </ul>

      <form class="search-wrapper ms-lg-3 my-2 my-lg-0" method="get" action="<?= htmlspecialchars($products_link) ?>">
        <input id="search-input" class="form-control search-input-modern" autocomplete="off" 
               type="search" name="search" placeholder="Tìm kiếm sản phẩm...">
        <button class="search-btn-modern" type="submit">
          <i class="fas fa-search"></i>
        </button>
        <div id="search-suggestions" class="list-group position-absolute w-100 shadow" 
             style="z-index:1000; top:calc(100% + 5px); display:none; border-radius:12px; overflow:hidden;"></div>
      </form>

      <div class="d-flex ms-lg-3 align-items-center gap-2 my-2 my-lg-0">
        <?php
          $cart_count = 0;
          if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
            foreach ($_SESSION['cart'] as $qty) $cart_count += $qty;
          }
        ?>
        <a class="btn cart-btn-modern" href="<?= htmlspecialchars($cart_link) ?>">
          <i class="fas fa-shopping-cart"></i>
          <?php if ($cart_count > 0): ?>
            <span class="cart-badge-modern"><?= $cart_count ?></span>
          <?php endif; ?>
        </a>
      </div>

      <div class="d-flex ms-lg-3 gap-2 my-2 my-lg-0 flex-wrap">
        <?php if (isset($_SESSION['user_id'])): ?>
          <a href="<?= htmlspecialchars($user_info_link) ?>" class="btn btn-outline-light auth-btn-modern">
            <i class="fas fa-user-circle me-1"></i>Tài khoản
          </a>
          <a href="<?= htmlspecialchars($logout_link) ?>" class="btn btn-outline-light auth-btn-modern">
            <i class="fas fa-sign-out-alt me-1"></i>Đăng xuất
          </a>
        <?php else: ?>
          <a href="<?= htmlspecialchars($login_link) ?>" class="btn btn-outline-light auth-btn-modern">
            <i class="fas fa-sign-in-alt me-1"></i>Đăng nhập
          </a>
          <a href="<?= htmlspecialchars($register_link) ?>" class="btn btn-outline-light auth-btn-modern">
            <i class="fas fa-user-plus me-1"></i>Đăng ký
          </a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</nav>


<script>
document.addEventListener('DOMContentLoaded', function() {
  const input = document.getElementById('search-input');
  const suggestions = document.getElementById('search-suggestions');
  let timer;

  input.addEventListener('input', function() {
    clearTimeout(timer);
    const query = input.value.trim();
    if (query.length < 2) {
      suggestions.style.display = 'none';
      suggestions.innerHTML = '';
      return;
    }
    timer = setTimeout(function() {
      fetch('<?= $base_url ?>/frontend/modules/search_suggestions.php?q=' + encodeURIComponent(query))
        .then(res => res.json())
        .then(data => {
          if (Array.isArray(data) && data.length > 0) {
            suggestions.innerHTML = data.map(item =>
              `<a href='<?= $products_link ?>?search=${encodeURIComponent(item.name)}' class='list-group-item list-group-item-action d-flex align-items-center' style='padding:12px;'>
                <img src='${item.image}' alt='' style='width:45px;height:45px;object-fit:cover;border-radius:8px;margin-right:12px;'>
                <div>
                  <div style='font-weight:600;'>${item.name}</div>
                  <small class='text-muted'>${item.price ? new Intl.NumberFormat('vi-VN').format(item.price) + '₫' : ''}</small>
                </div>
              </a>`
            ).join('');
            suggestions.style.display = 'block';
          } else {
            suggestions.innerHTML = `<div class='list-group-item text-center text-muted py-3'><i class='fas fa-search me-2'></i>Không có kết quả</div>`;
            suggestions.style.display = 'block';
          }
        })
        .catch(() => { suggestions.style.display = 'none'; });
    }, 200);
  });

  document.addEventListener('click', function(e) {
    if (!input.contains(e.target) && !suggestions.contains(e.target)) {
      suggestions.style.display = 'none';
    }
  });
});
</script>