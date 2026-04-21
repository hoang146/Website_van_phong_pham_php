<?php
session_start();
include_once(__DIR__ . '/../../config/database.php');
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }

require __DIR__ . '/../../vendor/autoload.php';
use PragmaRX\Google2FA\Google2FA;

$user_id = $_SESSION['user_id'];
$google2fa = new Google2FA();

$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT email, auth_secret, is_2fa_enabled FROM users WHERE id=$user_id"));
$secret = $user['auth_secret'];
$enabled = $user['is_2fa_enabled'];

// Tắt 2FA
if (isset($_POST['disable_2fa'])) {
    mysqli_query($conn, "UPDATE users SET auth_secret=NULL, is_2fa_enabled=0 WHERE id=$user_id");
    $success = "Đã tắt xác thực 2 bước";
    $enabled = false;
    $secret = null;
}

// Nếu chưa có secret → tạo
if (!$secret) {
    $secret = $google2fa->generateSecretKey();
    mysqli_query($conn, "UPDATE users SET auth_secret='$secret' WHERE id=$user_id");
}

$qrData = $google2fa->getQRCodeUrl("VanPhongPham-Web", $user['email'], $secret);
$qr = "https://api.qrserver.com/v1/create-qr-code/?data=" . urlencode($qrData) . "&size=200x200";

// Xác nhận OTP
if (isset($_POST['verify_otp'])) {
    $otp = trim($_POST['otp']);

    if ($google2fa->verifyKey($secret, $otp)) {
        mysqli_query($conn, "UPDATE users SET is_2fa_enabled=1 WHERE id=$user_id");
        $success = "✅ Đã bật xác thực 2 bước!";
        $enabled = true;
    } else {
        $error = "❌ OTP không đúng!";
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<title>Quản lý 2FA</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../../assets/custom/css/style.css">
<link rel="stylesheet" href="../../assets/vendor/font-awesome/css/font-awesome.min.css">
</head>
<body>
<?php include_once(__DIR__ . '/../layouts/partials/header.php'); ?>

<div class="container mt-5 mb-5">
<div class="card shadow p-4" style="max-width:450px;margin:auto;">

<h3><i class="fa fa-shield"></i> Xác thực 2 bước</h3>

<?php if (!empty($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
<?php if (!empty($success)) echo "<div class='alert alert-success'>$success</div>"; ?>

<?php if (!$enabled): ?>
    <p>Quét mã QR bằng ứng dụng Google Authenticator:</p>
    <img src="<?= $qr ?>" class="img-fluid mb-2">

    <p><strong>Backup key:</strong> <?= $secret ?></p>

    <form method="POST">
        <input type="text" name="otp" class="form-control" placeholder="Nhập mã OTP 6 số" required>
        <button name="verify_otp" class="btn btn-success w-100 mt-3">Bật 2FA</button>
    </form>

<?php else: ?>
    <p class="text-success">✅ Bạn đã bật 2FA</p>

    <form method="POST">
        <button name="disable_2fa" class="btn btn-danger w-100"
            onclick="return confirm('Tắt 2FA có thể giảm bảo mật. Bạn chắc chứ?');">
            Tắt 2FA
        </button>
    </form>
<?php endif; ?>

<a href="user_info.php" class="btn btn-outline-secondary w-100 mt-3">
    <i class="fa fa-arrow-left"></i> Quay lại
</a>

</div>
</div>

<?php include_once(__DIR__ . '/../layouts/partials/footer.php'); ?>
</body>
</html>
