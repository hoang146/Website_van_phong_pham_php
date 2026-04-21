<?php
session_start();
include_once(__DIR__ . '/../../config/database.php');
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$user_id = $_SESSION['user_id'];
$error = '';
$success = '';
$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id = $user_id"));
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = $_POST['fullname'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email không hợp lệ!';
    } elseif (strlen($fullname) < 2) {
        $error = 'Họ tên phải từ 2 ký tự.';
    } else {
        mysqli_query($conn, "UPDATE users SET fullname='" . mysqli_real_escape_string($conn, $fullname) . "', email='" . mysqli_real_escape_string($conn, $email) . "', phone='" . mysqli_real_escape_string($conn, $phone) . "', address='" . mysqli_real_escape_string($conn, $address) . "' WHERE id=$user_id");
        $success = 'Cập nhật thông tin thành công!';
        $user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id = $user_id"));
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta charset="UTF-8">
    <title>Chỉnh sửa thông tin - VietOffice</title>
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
        .edit-card {
            background: white;
            border: none;
            border-radius: 20px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .edit-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px 30px;
            font-size: 1.5rem;
            font-weight: 700;
        }
        .form-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
        }
        .input-with-icon {
            position: relative;
        }
        .input-with-icon input,
        .input-with-icon textarea {
            padding-left: 45px;
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
                <li class="breadcrumb-item"><a href="user_info.php">Tài khoản</a></li>
                <li class="breadcrumb-item active" aria-current="page">Chỉnh sửa thông tin</li>
            </ol>
        </nav>
    </div>
</section>
<div class="container mb-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="edit-card">
                <div class="edit-header">
                    <i class="fas fa-user-edit me-2"></i>Chỉnh sửa thông tin cá nhân
                </div>
                <div class="card-body p-4">
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-triangle me-2"></i><?= $error ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php elseif ($success): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fas fa-check-circle me-2"></i><?= $success ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <form method="post">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-user me-2 text-primary"></i>Tên đăng nhập
                            </label>
                            <div class="input-with-icon">
                                <i class="fas fa-lock form-icon"></i>
                                <input type="text" class="form-control form-control-lg rounded-3" 
                                       value="<?= htmlspecialchars($user['username']) ?>" 
                                       disabled style="background: #f8f9fa;">
                            </div>
                            <small class="text-muted">Tên đăng nhập không thể thay đổi</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-id-card me-2 text-primary"></i>Họ tên <span class="text-danger">*</span>
                            </label>
                            <div class="input-with-icon">
                                <i class="fas fa-id-card form-icon"></i>
                                <input type="text" name="fullname" class="form-control form-control-lg rounded-3" 
                                       value="<?= htmlspecialchars($user['fullname']) ?>" 
                                       required placeholder="Nhập họ tên của bạn">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-envelope me-2 text-primary"></i>Email <span class="text-danger">*</span>
                            </label>
                            <div class="input-with-icon">
                                <i class="fas fa-envelope form-icon"></i>
                                <input type="email" name="email" class="form-control form-control-lg rounded-3" 
                                       value="<?= htmlspecialchars($user['email']) ?>" 
                                       required placeholder="example@gmail.com">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-phone me-2 text-primary"></i>Số điện thoại
                            </label>
                            <div class="input-with-icon">
                                <i class="fas fa-phone form-icon"></i>
                                <input type="text" name="phone" class="form-control form-control-lg rounded-3" 
                                       value="<?= htmlspecialchars($user['phone']) ?>" 
                                       placeholder="Nhập số điện thoại">
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-map-marker-alt me-2 text-primary"></i>Địa chỉ
                            </label>
                            <div class="input-with-icon">
                                <i class="fas fa-map-marker-alt form-icon"></i>
                                <textarea name="address" class="form-control form-control-lg rounded-3" 
                                          rows="3" placeholder="Nhập địa chỉ của bạn"><?= htmlspecialchars($user['address']) ?></textarea>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg py-3 rounded-3" 
                                    style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; font-weight: 600;">
                                <i class="fas fa-save me-2"></i>Cập nhật thông tin
                            </button>
                            <a href="user_info.php" class="btn btn-outline-secondary btn-lg py-3 rounded-3">
                                <i class="fas fa-arrow-left me-2"></i>Quay lại
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include_once(__DIR__ . '/../layouts/partials/footer.php'); ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
