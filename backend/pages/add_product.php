<?php
header("X-Frame-Options: SAMEORIGIN");
header("X-Content-Type-Options: nosniff");
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://cdn.ckeditor.com; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://fonts.googleapis.com https://cdn.ckeditor.com; font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com;");
session_start();
include_once(__DIR__ . '/../../config/database.php');

// kiểm tra role = admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../frontend/pages/login.php");
    exit;
}

// Lấy danh mục + tags
$categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY name ASC");
$tags = mysqli_query($conn, "SELECT * FROM tags ORDER BY name ASC");

// Xử lý thêm
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = mysqli_real_escape_string($conn, $_POST['name']);
    $brand       = mysqli_real_escape_string($conn, $_POST['brand']);
    $origin      = mysqli_real_escape_string($conn, $_POST['origin']);
    $category_id = (int)$_POST['category_id'];
    $price       = (float)$_POST['price'];
    $price_sale  = !empty($_POST['price_sale']) ? (float)$_POST['price_sale'] : NULL;
    $stock       = (int)$_POST['stock'];
    $warranty    = (int)$_POST['warranty_days'];
    $returnDays  = (int)$_POST['return_days'];
    $description = mysqli_real_escape_string($conn, $_POST['description']);

    $sql = "INSERT INTO products (category_id, name, brand, origin, price, price_sale, stock, warranty_days, return_days, description) 
            VALUES ('$category_id','$name','$brand','$origin','$price',".($price_sale!==NULL?"'$price_sale'":"NULL").",
                    '$stock','$warranty','$returnDays','$description')";
    if (mysqli_query($conn, $sql)) {
        $product_id = mysqli_insert_id($conn);

        // Upload ảnh
        if (!empty($_FILES['images']['name'][0])) {
            $upload_dir = __DIR__ . '/../../uploads/products/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

            foreach ($_FILES['images']['tmp_name'] as $k => $tmp_name) {
                $file_name = time() . "_" . basename($_FILES['images']['name'][$k]);
                $target = $upload_dir . $file_name;
                if (move_uploaded_file($tmp_name, $target)) {
                    mysqli_query($conn, "INSERT INTO product_images (product_id, image_path) VALUES ($product_id, '$file_name')");
                }
            }
        }

        // Tag
        if (!empty($_POST['tags'])) {
            foreach ($_POST['tags'] as $tag_id) {
                $tag_id = (int)$tag_id;
                mysqli_query($conn, "INSERT INTO product_tags (product_id, tag_id) VALUES ($product_id, $tag_id)");
            }
        }

        header("Location: products.php?msg=added");
        exit;
    } else {
        $error = "Lỗi: " . mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Thêm sản phẩm</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.ckeditor.com/4.21.0/standard/ckeditor.js"></script>
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
        <h1 class="page-title"><i class="fa fa-plus-circle me-2"></i> Thêm sản phẩm</h1>
        <p class="page-subtitle">Tạo sản phẩm mới và thêm vào cửa hàng</p>
      </div>

      <div class="form-card">
        <div class="card-body">
          <?php if(isset($error)) echo "<div class='alert alert-danger mb-3'>$error</div>"; ?>
          <form method="post" enctype="multipart/form-data" class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Tên sản phẩm</label>
              <input type="text" name="name" class="form-control" required>
            </div>
            <div class="col-md-3">
              <label class="form-label">Thương hiệu</label>
              <input type="text" name="brand" class="form-control">
            </div>
            <div class="col-md-3">
              <label class="form-label">Xuất xứ</label>
              <input type="text" name="origin" class="form-control">
            </div>
            <div class="col-md-4">
              <label class="form-label">Danh mục</label>
              <select name="category_id" class="form-select" required>
                <?php while($cat = mysqli_fetch_assoc($categories)): ?>
                  <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                <?php endwhile; ?>
              </select>
            </div>
            <div class="col-md-2">
              <label class="form-label">Số lượng</label>
              <input type="number" name="stock" class="form-control" min="0" required>
            </div>
            <div class="col-md-3">
              <label class="form-label">Giá gốc</label>
              <input type="number" step="0.01" name="price" class="form-control" required>
            </div>
            <div class="col-md-3">
              <label class="form-label">Giá khuyến mãi</label>
              <input type="number" step="0.01" name="price_sale" class="form-control">
            </div>
            <div class="col-md-3">
              <label class="form-label">Bảo hành (ngày)</label>
              <input type="number" name="warranty_days" class="form-control" min="0" value="0">
            </div>
            <div class="col-md-3">
              <label class="form-label">Đổi trả (ngày)</label>
              <input type="number" name="return_days" class="form-control" min="0" value="0">
            </div>
            <div class="col-md-6">
              <label class="form-label">Thẻ sản phẩm</label><br>
              <?php while($tag = mysqli_fetch_assoc($tags)): ?>
                <label class="me-2">
                  <input type="checkbox" name="tags[]" value="<?= $tag['id'] ?>"> <?= htmlspecialchars($tag['name']) ?>
                </label>
              <?php endwhile; ?>
            </div>
            <div class="col-md-12">
              <label class="form-label">Hình ảnh (có thể chọn nhiều)</label>
              <input type="file" name="images[]" class="form-control" multiple>
            </div>
            <div class="col-md-12">
              <label class="form-label">Mô tả sản phẩm</label>
              <textarea name="description" id="description"></textarea>
              <script>CKEDITOR.replace('description', {height: 300});</script>
            </div>
            <div class="col-12 d-flex justify-content-end gap-2 mt-4">
              <a href="products.php" class="btn btn-secondary px-4 py-2"><i class="fa fa-times me-2"></i>Hủy</a>
              <button type="submit" class="btn btn-success px-4 py-2"><i class="fa fa-plus me-2"></i>Thêm sản phẩm</button>
            </div>
          </form>
        </div>
      </div>
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
.form-card {
  background: white;
  border-radius: 16px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
  padding: 2rem;
}
.form-control, .form-select {
  border: 2px solid #e0e6ed;
  border-radius: 10px;
  padding: 0.65rem 1rem;
  transition: all 0.3s ease;
}
.form-control:focus, .form-select:focus {
  border-color: #667eea;
  box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
}
.form-label {
  font-weight: 600;
  color: #2c3e50;
  margin-bottom: 0.5rem;
}
.btn {
  border-radius: 10px;
  font-weight: 600;
  transition: all 0.3s ease;
}
.btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}
</style>

</body>
</html>
