<?php
header("X-Frame-Options: SAMEORIGIN");
header("X-Content-Type-Options: nosniff");
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://cdn.ckeditor.com; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://fonts.googleapis.com https://cdn.ckeditor.com; font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com;");
session_start();
include_once(__DIR__ . '/../../config/database.php');

// Kiểm tra role = admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../frontend/pages/login.php");
    exit;
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $password = trim($_POST['password']);
    $email    = mysqli_real_escape_string($conn, trim($_POST['email']));
    $fullname = mysqli_real_escape_string($conn, trim($_POST['fullname']));
    $phone    = mysqli_real_escape_string($conn, trim($_POST['phone']));
    $address  = mysqli_real_escape_string($conn, trim($_POST['address']));
    $role     = isset($_POST['role']) ? $_POST['role'] : 'user';

    // validate cơ bản
    if ($username == "" || $password == "" || $email == "") {
        $message = "Vui lòng nhập đầy đủ: Tên tài khoản, mật khẩu và email.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Email không hợp lệ.";
    } else {
        // kiểm tra trùng username hoặc email
        $check = mysqli_query($conn, "SELECT id FROM users WHERE username='$username' OR email='$email' LIMIT 1");
        if (mysqli_num_rows($check) > 0) {
            $message = "Tên đăng nhập hoặc email đã tồn tại.";
        } else {
            $password_hash = password_hash($password, PASSWORD_DEFAULT); // dùng password_hash để hash mật khẩu
            // Sử dụng prepared statement để insert
            $stmt = $conn->prepare("INSERT INTO users (username, password, email, fullname, phone, address, role) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssss", $username, $password_hash, $email, $fullname, $phone, $address, $role);
            if ($stmt->execute()) {
                header("Location: users.php");
                exit;
            } else {
                $message = "Có lỗi khi thêm: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Thêm người dùng</title>
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
        <h1 class="page-title"><i class="fa fa-user-plus me-2"></i> Thêm người dùng</h1>
        <p class="page-subtitle">Tạo tài khoản người dùng mới cho hệ thống</p>
      </div>

      <div class="form-card">
        <div class="card-body">
          <?php if ($message) echo "<div class='alert alert-danger'>$message</div>"; ?>
          <form method="post" class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold"><i class="fa fa-user text-primary me-2"></i>Tên tài khoản</label>
              <input type="text" name="username" class="form-control form-control-modern" required value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>" placeholder="Nhập tên đăng nhập">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold"><i class="fa fa-lock text-primary me-2"></i>Mật khẩu</label>
              <input type="password" name="password" class="form-control form-control-modern" required placeholder="Nhập mật khẩu">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold"><i class="fa fa-envelope text-primary me-2"></i>Email</label>
              <input type="email" name="email" class="form-control form-control-modern" required value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>" placeholder="email@example.com">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold"><i class="fa fa-id-card text-primary me-2"></i>Họ tên</label>
              <input type="text" name="fullname" class="form-control form-control-modern" value="<?= isset($_POST['fullname']) ? htmlspecialchars($_POST['fullname']) : '' ?>" placeholder="Họ và tên đầy đủ">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold"><i class="fa fa-phone text-primary me-2"></i>Số điện thoại</label>
              <input type="text" name="phone" class="form-control form-control-modern" value="<?= isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : '' ?>" placeholder="0123456789">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold"><i class="fa fa-user-shield text-primary me-2"></i>Quyền</label>
              <select name="role" class="form-select form-control-modern">
                <option value="user" <?= (isset($_POST['role']) && $_POST['role']=='user')?'selected':'' ?>>User</option>
                <option value="admin" <?= (isset($_POST['role']) && $_POST['role']=='admin')?'selected':'' ?>>Admin</option>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold"><i class="fa fa-map-marker-alt text-primary me-2"></i>Địa chỉ</label>
              <input type="text" name="address" class="form-control form-control-modern" value="<?= isset($_POST['address']) ? htmlspecialchars($_POST['address']) : '' ?>" placeholder="Địa chỉ đầy đủ">
            </div>
            <div class="col-12 d-flex justify-content-end gap-2 mt-4">
              <a href="users.php" class="btn btn-secondary px-4 py-2"><i class="fa fa-times me-2"></i>Hủy</a>
              <button type="submit" class="btn btn-success px-4 py-2"><i class="fa fa-check me-2"></i>Xác nhận</button>
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
