<?php
// Trang thông tin người dùng
session_start();
include_once(__DIR__ . '/../../config/database.php');
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$user_id = $_SESSION['user_id'];

// Xử lý upload avatar
$error = '';
if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
    $target_dir = __DIR__ . '/../../uploads/users/';
    if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
    $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
    $filename = 'user_' . $user_id . '_' . time() . '.' . $ext;
    $target_file = $target_dir . $filename;
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    if (in_array(strtolower($ext), $allowed)) {
        if (move_uploaded_file($_FILES['avatar']['tmp_name'], $target_file)) {
            $sql = "UPDATE users SET avatar='$filename' WHERE id=$user_id";
            mysqli_query($conn, $sql);
            header('Location: user_info.php');
            exit;
        } else {
            $error = 'Lỗi khi upload ảnh!';
        }
    } else {
        $error = 'Chỉ chấp nhận file ảnh JPG, PNG, GIF!';
    }
}

// Thống kê đơn hàng
$stat_sql = "
    SELECT 
        COUNT(*) AS total_orders,
        SUM(total) AS total_spent
    FROM orders
    WHERE user_id = $user_id
";
$stats = mysqli_fetch_assoc(mysqli_query($conn, $stat_sql));

// Pin & 2FA trạng thái
$user_sql = "SELECT * FROM users WHERE id = $user_id LIMIT 1";
$user = mysqli_fetch_assoc(mysqli_query($conn, $user_sql));

// Pin & 2FA trạng thái
$pin_status = !empty($user['pin_code']) ? 'Đã tạo' : 'Chưa thiết lập';
$fa_status = !empty($user['auth_secret']) ? 'Bật' : 'Tắt';

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta charset="UTF-8">
    <title>Thông tin cá nhân - VietOffice</title>
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
        .profile-card {
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
        .profile-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .avatar-wrapper {
            position: relative;
            display: inline-block;
        }
        .avatar-img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid white;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }
        .avatar-upload {
            position: absolute;
            bottom: 5px;
            right: 5px;
            width: 45px;
            height: 45px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            transition: all 0.3s;
        }
        .avatar-upload:hover {
            transform: scale(1.1);
            background: #667eea;
        }
        .avatar-upload:hover i {
            color: white;
        }
        .info-card {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            border-left: 4px solid #667eea;
        }
        .info-card h5 {
            color: #667eea;
            font-weight: 700;
            margin-bottom: 15px;
        }
        .info-row {
            padding: 12px 0;
            border-bottom: 1px solid #e9ecef;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .stat-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            margin-bottom: 20px;
        }
        .stat-box h3 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 5px;
        }
        .security-badge {
            display: inline-flex;
            align-items: center;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .action-btn {
            border-radius: 10px;
            padding: 12px 24px;
            font-weight: 600;
            transition: all 0.3s;
        }
        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
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
                <li class="breadcrumb-item active" aria-current="page">Thông tin cá nhân</li>
            </ol>
        </nav>
    </div>
</section>
<div class="container mb-5">
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-triangle me-2"></i><?= $error ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-lg-4 mb-4">
            <!-- Profile Card -->
            <div class="profile-card">
                <div class="profile-header">
                    <form method="post" enctype="multipart/form-data" id="avatarForm">
                        <div class="avatar-wrapper">
                            <?php if (!empty($user['avatar'])): ?>
                                <img src="/uploads/users/<?= htmlspecialchars($user['avatar']) ?>" class="avatar-img" alt="Avatar">
                            <?php else: ?>
                                <div class="avatar-img d-flex align-items-center justify-content-center" style="background: rgba(255,255,255,0.2);">
                                    <i class="fas fa-user fa-4x"></i>
                                </div>
                            <?php endif; ?>
                            <label for="avatarInput" class="avatar-upload">
                                <i class="fas fa-camera text-primary"></i>
                            </label>
                            <input type="file" id="avatarInput" name="avatar" accept="image/*" class="d-none" onchange="this.form.submit()">
                        </div>
                    </form>
                    <h4 class="mt-3 mb-1"><?= htmlspecialchars($user['fullname'] ?: $user['username']) ?></h4>
                    <p class="mb-0 opacity-75">
                        <i class="fas fa-at me-1"></i><?= htmlspecialchars($user['username']) ?>
                    </p>
                </div>
                <div class="p-4">
                    <div class="text-center mb-3">
                        <span class="badge bg-primary px-3 py-2" style="font-size: 0.9rem;">
                            <i class="fas fa-shield-alt me-1"></i><?= $user['role'] === 'admin' ? 'Quản trị viên' : 'Khách hàng' ?>
                        </span>
                    </div>
                    <p class="text-center text-muted mb-0">
                        <i class="fas fa-calendar-alt me-1"></i>Tham gia: <?= date('d/m/Y', strtotime($user['created_at'])) ?>
                    </p>
                </div>
            </div>

            <!-- Stats -->
            <div class="stat-box">
                <i class="fas fa-shopping-bag fa-2x mb-2"></i>
                <h3><?= $stats['total_orders'] ?? 0 ?></h3>
                <p class="mb-0">Tổng đơn hàng</p>
            </div>
            <div class="stat-box" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
                <i class="fas fa-coins fa-2x mb-2"></i>
                <h3><?= number_format($stats['total_spent'] ?? 0) ?>₫</h3>
                <p class="mb-0">Tổng chi tiêu</p>
            </div>
        </div>

        <div class="col-lg-8">
            <!-- Personal Info -->
            <div class="info-card">
                <h5><i class="fas fa-user-circle me-2"></i>Thông tin cá nhân</h5>
                <div class="info-row d-flex justify-content-between">
                    <span class="text-muted"><i class="fas fa-user me-2"></i>Họ tên:</span>
                    <strong><?= $user['fullname'] ? htmlspecialchars($user['fullname']) : '<span class="text-danger">Chưa cập nhật</span>' ?></strong>
                </div>
                <div class="info-row d-flex justify-content-between">
                    <span class="text-muted"><i class="fas fa-envelope me-2"></i>Email:</span>
                    <strong><?= htmlspecialchars($user['email'] ?? 'Chưa có') ?></strong>
                </div>
                <div class="info-row d-flex justify-content-between">
                    <span class="text-muted"><i class="fas fa-phone me-2"></i>Điện thoại:</span>
                    <strong><?= $user['phone'] ? htmlspecialchars($user['phone']) : '<span class="text-danger">Chưa cập nhật</span>' ?></strong>
                </div>
                <div class="info-row d-flex justify-content-between">
                    <span class="text-muted"><i class="fas fa-map-marker-alt me-2"></i>Địa chỉ:</span>
                    <strong><?= $user['address'] ? htmlspecialchars($user['address']) : '<span class="text-danger">Chưa cập nhật</span>' ?></strong>
                </div>
            </div>
            <!-- Security -->
            <div class="info-card" style="border-left-color: #28a745;">
                <h5 style="color: #28a745;"><i class="fas fa-shield-alt me-2"></i>Bảo mật tài khoản</h5>
                <div class="info-row d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fas fa-key me-2 text-muted"></i>Mã PIN:
                    </div>
                    <span class="security-badge <?= !empty($user['pin_code']) ? 'bg-success text-white' : 'bg-warning text-dark' ?>">
                        <i class="fas fa-<?= !empty($user['pin_code']) ? 'check-circle' : 'exclamation-circle' ?> me-1"></i>
                        <?= !empty($user['pin_code']) ? 'Đã thiết lập' : 'Chưa thiết lập' ?>
                    </span>
                </div>
                <div class="info-row d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fas fa-mobile-alt me-2 text-muted"></i>Xác thực 2FA:
                    </div>
                    <span class="security-badge <?= !empty($user['auth_secret']) ? 'bg-success text-white' : 'bg-secondary text-white' ?>">
                        <i class="fas fa-<?= !empty($user['auth_secret']) ? 'check-circle' : 'times-circle' ?> me-1"></i>
                        <?= !empty($user['auth_secret']) ? 'Đang bật' : 'Tắt' ?>
                    </span>
                </div>
                <div class="d-flex gap-2 mt-3">
                    <a href="setup_pin.php" class="btn btn-outline-primary btn-sm flex-fill">
                        <i class="fas fa-key me-1"></i>Thiết lập PIN
                    </a>
                    <a href="setup_2fa.php" class="btn btn-outline-success btn-sm flex-fill">
                        <i class="fas fa-mobile-alt me-1"></i>Quản lý 2FA
                    </a>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="row g-3">
                <div class="col-md-6">
                    <a href="edit_user.php" class="btn btn-primary action-btn w-100">
                        <i class="fas fa-edit me-2"></i>Chỉnh sửa thông tin
                    </a>
                </div>
                <div class="col-md-6">
                    <a href="change_password.php" class="btn btn-warning action-btn w-100">
                        <i class="fas fa-lock me-2"></i>Đổi mật khẩu
                    </a>
                </div>
                <div class="col-md-6">
                    <a href="order_history.php" class="btn btn-info action-btn w-100">
                        <i class="fas fa-history me-2"></i>Lịch sử đơn hàng
                    </a>
                </div>
                <div class="col-md-6">
                    <a href="logout.php" class="btn btn-danger action-btn w-100">
                        <i class="fas fa-sign-out-alt me-2"></i>Đăng xuất
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include_once(__DIR__ . '/../layouts/partials/footer.php'); ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
