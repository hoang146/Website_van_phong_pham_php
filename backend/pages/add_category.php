<?php
header("X-Frame-Options: SAMEORIGIN");
header("X-Content-Type-Options: nosniff");
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://cdn.ckeditor.com; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://fonts.googleapis.com https://cdn.ckeditor.com; font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com;");
session_start();
include_once(__DIR__ . '/../../config/database.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $sql = "INSERT INTO categories (name, description) VALUES ('$name','$description')";
    if (mysqli_query($conn, $sql)) {
        header("Location: categories.php");
        exit;
    } else {
        $error = " Lỗi: " . mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Thêm danh mục</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
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
      <div class="card shadow-lg border-0 mx-auto" style="max-width:700px;">
        <div class="card-header bg-success text-white">
          <h3 class="mb-0 fw-bold"><i class="fa fa-plus-circle me-2"></i> Thêm danh mục mới</h3>
        </div>
        <div class="card-body p-4">
          <?php if (!empty($error)) echo "<div class='alert alert-danger mb-3'><i class='fa fa-exclamation-triangle me-2'></i>$error</div>"; ?>
          <form method="post">
            <div class="mb-4">
              <label class="form-label fw-bold"><i class="fa fa-tag me-1"></i> Tên danh mục <span class="text-danger">*</span></label>
              <input type="text" name="name" class="form-control form-control-lg" placeholder="Nhập tên danh mục..." required>
            </div>
            <div class="mb-4">
              <label class="form-label fw-bold"><i class="fa fa-align-left me-1"></i> Mô tả</label>
              <textarea name="description" class="form-control" rows="4" placeholder="Nhập mô tả cho danh mục..."></textarea>
            </div>
            <div class="d-flex justify-content-end gap-2">
              <a href="categories.php" class="btn btn-secondary px-4 py-2 fw-bold"><i class="fa fa-times me-1"></i> Hủy</a>
              <button type="submit" class="btn btn-success px-4 py-2 fw-bold"><i class="fa fa-check me-1"></i> Lưu danh mục</button>
            </div>
          </form>
        </div>
      </div>
      </div>
    </main>
  </div>
  <?php include_once(__DIR__ . '/../layouts/partials/footer.php'); ?>
</div>
</body>
</html>
