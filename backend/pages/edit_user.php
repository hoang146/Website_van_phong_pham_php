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

$id = intval($_GET['id']);
$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id = $id"));

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $phone    = mysqli_real_escape_string($conn, $_POST['phone']);
    $address  = mysqli_real_escape_string($conn, $_POST['address']);
    $role     = $_POST['role'];

    $sql = "UPDATE users SET 
                username='$username',
                fullname='$fullname',
                email='$email',
                phone='$phone',
                address='$address',
                role='$role'
            WHERE id=$id";

    if (mysqli_query($conn, $sql)) {
        header("Location: users.php");
        exit;
    } else {
        $message = "Lỗi: " . mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Sửa người dùng</title>
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
      <!-- Page Header -->
      <div class="page-header mb-4">
        <h1 class="page-title"><i class="fa fa-user-edit me-2"></i> Sửa thông tin người dùng</h1>
        <p class="page-subtitle">Cập nhật thông tin và quyền hạn của người dùng</p>
      </div>

      <div class="form-card">
        <div class="card-body">
          <?php if ($message) echo "<div class='alert alert-danger'>$message</div>"; ?>
          <form method="post" class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold"><i class="fa fa-user text-primary me-2"></i>Tên tài khoản</label>
              <input type="text" name="username" value="<?= $user['username'] ?>" class="form-control form-control-modern" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold"><i class="fa fa-envelope text-primary me-2"></i>Email</label>
              <input type="email" name="email" value="<?= $user['email'] ?>" class="form-control form-control-modern" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold"><i class="fa fa-id-card text-primary me-2"></i>Họ tên</label>
              <input type="text" name="fullname" value="<?= $user['fullname'] ?>" class="form-control form-control-modern">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold"><i class="fa fa-phone text-primary me-2"></i>Số điện thoại</label>
              <input type="text" name="phone" value="<?= $user['phone'] ?>" class="form-control form-control-modern">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold"><i class="fa fa-map-marker-alt text-primary me-2"></i>Địa chỉ</label>
              <input type="text" name="address" value="<?= $user['address'] ?>" class="form-control form-control-modern">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold"><i class="fa fa-user-shield text-primary me-2"></i>Quyền</label>
              <select name="role" class="form-select form-control-modern">
                <option value="user" <?= $user['role']=="user"?"selected":"" ?>>User</option>
                <option value="admin" <?= $user['role']=="admin"?"selected":"" ?>>Admin</option>
              </select>
            </div>
            <div class="col-12 d-flex justify-content-end gap-2 mt-4">
              <a href="users.php" class="btn btn-secondary px-4 py-2"><i class="fa fa-times me-2"></i>Hủy</a>
              <button type="submit" class="btn btn-success px-4 py-2"><i class="fa fa-save me-2"></i>Lưu</button>
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
.form-control-modern {
  border: 2px solid #e0e6ed;
  border-radius: 10px;
  padding: 0.65rem 1rem;
  transition: all 0.3s ease;
}
.form-control-modern:focus {
  border-color: #667eea;
  box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
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
