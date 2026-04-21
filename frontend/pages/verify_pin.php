<?php
session_start();
include_once(__DIR__ . '/../../config/database.php');
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }
$user_id = $_SESSION['user_id'];

$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT pin_code FROM users WHERE id=$user_id"));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pin = $_POST['pin'];
    if (password_verify($pin, $user['pin_code'])) {
        $_SESSION['pin_verified'] = true;
        header("Location: secure_action.php");
        exit;
    } else {
        $error = "Mã PIN không đúng!";
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<title>Xác thực PIN</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../../assets/custom/css/style.css">
<link rel="stylesheet" href="../../assets/vendor/font-awesome/css/font-awesome.min.css">
</head>
<body>
<?php include_once(__DIR__ . '/../layouts/partials/header.php'); ?>

<div class="container mt-5 mb-5">
<div class="card shadow p-4" style="max-width: 400px; margin: auto;">
<h3><i class="fa fa-lock"></i> Xác thực PIN</h3>

<?php if (!empty($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

<form method="POST" class="mt-3">
<input type="password" name="pin" class="form-control" placeholder="Nhập PIN" required>
<button class="btn btn-success w-100 mt-3">Xác nhận</button>
</form>
</div>
</div>

<?php include_once(__DIR__ . '/../layouts/partials/footer.php'); ?>
</body>
</html>
