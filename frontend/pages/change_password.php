<?php
session_start();
include_once(__DIR__ . '/../../config/database.php');

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }

require __DIR__ . '/../../vendor/autoload.php';
use PragmaRX\Google2FA\Google2FA;

$user_id = $_SESSION['user_id'];

// Lấy thông tin user
$user = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT password, pin_code, auth_secret, is_2fa_enabled
    FROM users
    WHERE id=$user_id
"));

$google2fa = new Google2FA();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $old_pass = $_POST['old_password'];
    $new_pass = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];
    $pin = $_POST['pin'] ?? null;  // tránh lỗi khi không có PIN
    $otp = $_POST['otp'] ?? null;  // tránh lỗi khi không có OTP

    // Kiểm tra mật khẩu cũ
    if (!password_verify($old_pass, $user['password'])) {
        $error = "❌ Mật khẩu hiện tại không đúng!";
    }
    // Kiểm tra PIN
    // Nếu user đã tạo PIN thì phải kiểm tra PIN đúng
    elseif (!empty($user['pin_code']) && !password_verify($pin, $user['pin_code'])) {
        $error = "❌ Mã PIN không đúng!";
    }
    // Kiểm tra OTP nếu 2FA bật
    // Nếu user đã bật 2FA thì mới kiểm tra OTP
    elseif ($user['is_2fa_enabled'] == 1 && !$google2fa->verifyKey($user['auth_secret'], $otp)) {
        $error = "❌ Mã OTP không chính xác!";
    }

    // Chính sách mật khẩu mới
    elseif ($new_pass !== $confirm_pass) {
        $error = "❌ Mật khẩu xác nhận không khớp!";
    }
    elseif (strlen($new_pass) < 6) {
        $error = "❌ Mật khẩu phải >= 6 ký tự!";
    } else {
        // Cập nhật mật khẩu mới
        $newHash = password_hash($new_pass, PASSWORD_DEFAULT);
        mysqli_query($conn, "UPDATE users SET password='$newHash' WHERE id=$user_id");

        $success = "✅ Đổi mật khẩu thành công!";
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Đổi mật khẩu - VietOffice</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../../assets/custom/css/style.css">
    <style>
        .breadcrumb-section {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
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
        .password-card {
            background: white;
            border: none;
            border-radius: 20px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .password-header {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            padding: 25px 30px;
            font-size: 1.5rem;
            font-weight: 700;
            text-align: center;
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
        .input-with-icon input {
            padding-left: 45px;
            padding-right: 45px;
        }
        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #999;
        }
        .password-strength {
            height: 5px;
            border-radius: 3px;
            margin-top: 8px;
            transition: all 0.3s;
        }
        .security-notice {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
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
                <li class="breadcrumb-item active" aria-current="page">Đổi mật khẩu</li>
            </ol>
        </nav>
    </div>
</section>

<div class="container mb-5">
    <div class="row justify-content-center">
        <div class="col-lg-5">
            <div class="password-card">
                <div class="password-header">
                    <i class="fas fa-lock fa-2x mb-2"></i>
                    <div>Đổi mật khẩu</div>
                </div>
                <div class="card-body p-4">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <?= $error ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <?= $success ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <div class="security-notice">
                        <div class="d-flex align-items-start">
                            <i class="fas fa-shield-alt fa-2x me-3 text-warning"></i>
                            <div>
                                <h6 class="fw-bold mb-1">Bảo mật tài khoản</h6>
                                <small class="text-muted">
                                    <?php if (!empty($user['pin_code'])): ?>
                                        Yêu cầu nhập mã PIN để xác thực
                                    <?php endif; ?>
                                    <?php if ($user['is_2fa_enabled'] == 1): ?>
                                        <?= !empty($user['pin_code']) ? 'và mã OTP' : 'Yêu cầu nhập mã OTP để xác thực' ?>
                                    <?php endif; ?>
                                </small>
                            </div>
                        </div>
                    </div>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-key me-2 text-primary"></i>Mật khẩu hiện tại <span class="text-danger">*</span>
                            </label>
                            <div class="input-with-icon">
                                <i class="fas fa-lock form-icon"></i>
                                <input type="password" name="old_password" id="oldPass" 
                                       class="form-control form-control-lg rounded-3" 
                                       required placeholder="Nhập mật khẩu hiện tại">
                                <i class="fas fa-eye password-toggle" onclick="togglePassword('oldPass', this)"></i>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-key me-2 text-success"></i>Mật khẩu mới <span class="text-danger">*</span>
                            </label>
                            <div class="input-with-icon">
                                <i class="fas fa-lock form-icon"></i>
                                <input type="password" name="new_password" id="newPass" 
                                       class="form-control form-control-lg rounded-3" 
                                       required placeholder="Nhập mật khẩu mới (≥ 6 ký tự)" 
                                       oninput="checkPasswordStrength(this.value)">
                                <i class="fas fa-eye password-toggle" onclick="togglePassword('newPass', this)"></i>
                            </div>
                            <div id="passwordStrength" class="password-strength"></div>
                            <small class="text-muted">Mật khẩu phải có ít nhất 6 ký tự</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-check-circle me-2 text-success"></i>Xác nhận mật khẩu mới <span class="text-danger">*</span>
                            </label>
                            <div class="input-with-icon">
                                <i class="fas fa-lock form-icon"></i>
                                <input type="password" name="confirm_password" id="confirmPass" 
                                       class="form-control form-control-lg rounded-3" 
                                       required placeholder="Nhập lại mật khẩu mới">
                                <i class="fas fa-eye password-toggle" onclick="togglePassword('confirmPass', this)"></i>
                            </div>
                        </div>

                        <?php if (!empty($user['pin_code'])): ?>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-hashtag me-2 text-warning"></i>Mã PIN <span class="text-danger">*</span>
                            </label>
                            <div class="input-with-icon">
                                <i class="fas fa-hashtag form-icon"></i>
                                <input type="password" name="pin" class="form-control form-control-lg rounded-3" 
                                       placeholder="Nhập mã PIN của bạn" required>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if ($user['is_2fa_enabled'] == 1): ?>
                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-mobile-alt me-2 text-info"></i>Mã OTP (Google Authenticator) <span class="text-danger">*</span>
                            </label>
                            <div class="input-with-icon">
                                <i class="fas fa-mobile-alt form-icon"></i>
                                <input type="text" name="otp" class="form-control form-control-lg rounded-3" 
                                       placeholder="Nhập mã 6 số" required maxlength="6">
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg py-3 rounded-3" 
                                    style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); border: none; font-weight: 600;">
                                <i class="fas fa-save me-2"></i>Cập nhật mật khẩu
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

<script>
function togglePassword(inputId, icon) {
    const input = document.getElementById(inputId);
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

function checkPasswordStrength(password) {
    const strengthBar = document.getElementById('passwordStrength');
    let strength = 0;
    
    if (password.length >= 6) strength++;
    if (password.length >= 10) strength++;
    if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
    if (/\d/.test(password)) strength++;
    if (/[^a-zA-Z\d]/.test(password)) strength++;
    
    const colors = ['#dc3545', '#ffc107', '#28a745'];
    const widths = ['20%', '50%', '100%'];
    
    if (strength <= 2) {
        strengthBar.style.background = colors[0];
        strengthBar.style.width = widths[0];
    } else if (strength <= 3) {
        strengthBar.style.background = colors[1];
        strengthBar.style.width = widths[1];
    } else {
        strengthBar.style.background = colors[2];
        strengthBar.style.width = widths[2];
    }
}
</script>

<?php include_once(__DIR__ . '/../layouts/partials/footer.php'); ?>
</body>
</html>
