<?php
session_start();
include_once(__DIR__ . '/../../config/database.php');
if (!isset($_SESSION['user_id'])) exit();

require __DIR__ . '/../../vendor/autoload.php';
use PragmaRX\Google2FA\Google2FA;

$user_id = $_SESSION['user_id'];
$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT auth_secret FROM users WHERE id=$user_id"));
$google2fa = new Google2FA();
$secret = $user['auth_secret'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $otp = trim($_POST['otp']);
    if ($google2fa->verifyKey($secret, $otp)) {
        $_SESSION['otp_verified'] = true;
        header("Location: secure_action.php");
        exit;
    } else {
        $error = "Sai mã OTP!";
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<title>Xác thực OTP</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../../assets/custom/css/style.css">
<link rel="stylesheet" href="../../assets/vendor/font-awesome/css/font-awesome.min.css">
</head>
<body>
<?php include_once(__DIR__ . '/../layouts/partials/header.php'); ?>

<div class="container mt-5 mb-5">
<div class="card shadow p-4" style="max-width:400px;margin:auto;">
<h3><i class="fa fa-mobile"></i> Nhập mã Authenticator</h3>

<?php if (!empty($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

<form method="POST">
<input type="text" name="otp" class="form-control" placeholder="Nhập mã 6 số" required>
<button class="btn btn-primary w-100 mt-3">Xác nhận</button>
</form>
</div>
</div>

<?php include_once(__DIR__ . '/../layouts/partials/footer.php'); ?>
</body>
</html>
