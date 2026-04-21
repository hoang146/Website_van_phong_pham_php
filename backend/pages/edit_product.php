<?php
header("X-Frame-Options: SAMEORIGIN");
header("X-Content-Type-Options: nosniff");
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://cdn.ckeditor.com; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://fonts.googleapis.com https://cdn.ckeditor.com; font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com;");
session_start();
include_once(__DIR__ . '/../../config/database.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../frontend/pages/login.php");
    exit;
}

$id = (int)$_GET['id'];
$product = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM products WHERE id=$id"));
if (!$product) die("Lỗi Không tìm thấy sản phẩm");

$categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY name ASC");
$tags = mysqli_query($conn, "SELECT * FROM tags ORDER BY name ASC");
$currentTags = [];
$res = mysqli_query($conn, "SELECT tag_id FROM product_tags WHERE product_id=$id");
while($row = mysqli_fetch_assoc($res)) $currentTags[] = $row['tag_id'];

// Xử lý xóa ảnh riêng lẻ
if (isset($_GET['del_img'])) {
    $img_id = (int)$_GET['del_img'];
    $img = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM product_images WHERE id=$img_id AND product_id=$id"));
    if ($img) {
        $file = __DIR__ . '/../../uploads/products/' . $img['image_path'];
        if (file_exists($file)) unlink($file);
        mysqli_query($conn, "DELETE FROM product_images WHERE id=$img_id");
    }
    header("Location: edit_product.php?id=$id");
    exit;
}

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

    $sql = "UPDATE products SET 
                category_id='$category_id', name='$name', brand='$brand', origin='$origin',
                price='$price', price_sale=".($price_sale!==NULL?"'$price_sale'":"NULL").",
                stock='$stock', warranty_days='$warranty', return_days='$returnDays',
                description='$description'
            WHERE id=$id";

    if (mysqli_query($conn, $sql)) {
        // Cập nhật tags
        mysqli_query($conn, "DELETE FROM product_tags WHERE product_id=$id");
        if (!empty($_POST['tags'])) {
            foreach ($_POST['tags'] as $tag_id) {
                $tag_id = (int)$tag_id;
                mysqli_query($conn, "INSERT INTO product_tags (product_id, tag_id) VALUES ($id, $tag_id)");
            }
        }

        // Upload ảnh mới
        if (!empty($_FILES['images']['name'][0])) {
            $upload_dir = __DIR__ . '/../../uploads/products/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

            foreach ($_FILES['images']['tmp_name'] as $k => $tmp_name) {
                $file_name = time() . "_" . basename($_FILES['images']['name'][$k]);
                $target = $upload_dir . $file_name;
                if (move_uploaded_file($tmp_name, $target)) {
                    mysqli_query($conn, "INSERT INTO product_images (product_id, image_path) VALUES ($id, '$file_name')");
                }
            }
        }

        header("Location: products.php?msg=updated");
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
  <title>Sửa sản phẩm</title>
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
        <h1 class="page-title"><i class="fa fa-edit me-2"></i> Sửa sản phẩm</h1>
        <p class="page-subtitle">Cập nhật thông tin và hình ảnh sản phẩm</p>
      </div>

      <div class="form-card">
        <div class="card-body">
          <?php if(isset($error)) echo "<div class='alert alert-danger mb-3'>$error</div>"; ?>
          <form method="post" enctype="multipart/form-data" class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Tên sản phẩm</label>
              <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($product['name']) ?>" required>
            </div>
            <div class="col-md-3">
              <label class="form-label">Thương hiệu</label>
              <input type="text" name="brand" class="form-control" value="<?= htmlspecialchars($product['brand']) ?>">
            </div>
            <div class="col-md-3">
              <label class="form-label">Xuất xứ</label>
              <input type="text" name="origin" class="form-control" value="<?= htmlspecialchars($product['origin']) ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label">Danh mục</label>
              <select name="category_id" class="form-select" required>
                <?php while($cat = mysqli_fetch_assoc($categories)): ?>
                  <option value="<?= $cat['id'] ?>" <?= $product['category_id']==$cat['id']?"selected":"" ?>>
                    <?= htmlspecialchars($cat['name']) ?>
                  </option>
                <?php endwhile; ?>
              </select>
            </div>
            <div class="col-md-2">
              <label class="form-label">Số lượng</label>
              <input type="number" name="stock" class="form-control" value="<?= $product['stock'] ?>" min="0">
            </div>
            <div class="col-md-3">
              <label class="form-label">Giá gốc</label>
              <input type="number" step="0.01" name="price" class="form-control" value="<?= $product['price'] ?>" required>
            </div>
            <div class="col-md-3">
              <label class="form-label">Giá khuyến mãi</label>
              <input type="number" step="0.01" name="price_sale" class="form-control" value="<?= $product['price_sale'] ?>">
            </div>
            <div class="col-md-3">
              <label class="form-label">Bảo hành (ngày)</label>
              <input type="number" name="warranty_days" class="form-control" value="<?= $product['warranty_days'] ?>">
            </div>
            <div class="col-md-3">
              <label class="form-label">Đổi trả (ngày)</label>
              <input type="number" name="return_days" class="form-control" value="<?= $product['return_days'] ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Thẻ sản phẩm</label><br>
              <?php mysqli_data_seek($tags, 0); while($tag = mysqli_fetch_assoc($tags)): ?>
                <label class="me-2">
                  <input type="checkbox" name="tags[]" value="<?= $tag['id'] ?>" <?= in_array($tag['id'], $currentTags)?"checked":"" ?>>
                  <?= htmlspecialchars($tag['name']) ?>
                </label>
              <?php endwhile; ?>
            </div>
            <div class="col-md-12">
              <label class="form-label">Ảnh hiện tại</label><br>
              <?php
              $imgs = mysqli_query($conn, "SELECT * FROM product_images WHERE product_id=$id");
              while($img = mysqli_fetch_assoc($imgs)): ?>
                <div class="d-inline-block position-relative me-2 mb-2">
                  <img src="../../uploads/products/<?= htmlspecialchars($img['image_path']) ?>" class="product-img-preview">
                  <a href="edit_product.php?id=<?= $id ?>&del_img=<?= $img['id'] ?>" 
                     onclick="return confirm('Xóa ảnh này?')" 
                     class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1"><i class="fa fa-times"></i></a>
                </div>
              <?php endwhile; ?>
            </div>
            <div class="col-md-12">
              <label class="form-label">Thêm hình ảnh mới</label>
              <input type="file" name="images[]" class="form-control" multiple>
            </div>
            <div class="col-md-12">
              <label class="form-label">Mô tả sản phẩm</label>
              <textarea name="description" id="description"><?= htmlspecialchars($product['description']) ?></textarea>
              <script>CKEDITOR.replace('description', {height: 300});</script>
            </div>
            <div class="col-12 d-flex justify-content-end gap-2 mt-4">
              <a href="products.php" class="btn btn-secondary px-4 py-2"><i class="fa fa-times me-2"></i>Hủy</a>
              <button type="submit" class="btn btn-primary px-4 py-2"><i class="fa fa-save me-2"></i>Cập nhật</button>
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
.product-img-preview {
  max-height: 120px;
  border-radius: 12px;
  border: 3px solid #e0e6ed;
  object-fit: cover;
  transition: all 0.3s ease;
}
.product-img-preview:hover {
  border-color: #667eea;
  transform: scale(1.05);
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
