<?php
session_start();
include_once(__DIR__ . '/../../config/database.php');
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }

$user_id = $_SESSION['user_id'];
$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT pin_code FROM users WHERE id=$user_id"));

$hasPin = !empty($user['pin_code']);

// Xử lý xóa PIN
if (isset($_POST['remove_pin'])) {
    mysqli_query($conn, "UPDATE users SET pin_code=NULL WHERE id=$user_id");
    $success = "Đã tắt mã PIN";
    $hasPin = false;
}

// Xử lý tạo / đổi PIN
if (isset($_POST['save_pin'])) {
    $pin = $_POST['pin'];
    $confirm_pin = $_POST['confirm_pin'];

    if ($pin !== $confirm_pin) {
        $error = "Mã PIN không trùng khớp!";
    } elseif (!preg_match("/^[0-9]{4,6}$/", $pin)) {
        $error = "PIN phải là 4-6 số!";
    } else {
        $pin_hash = password_hash($pin, PASSWORD_DEFAULT);
        mysqli_query($conn, "UPDATE users SET pin_code='$pin_hash' WHERE id=$user_id");
        $success = $hasPin ? "Đổi mã PIN thành công!" : "Tạo mã PIN thành công!";
        $hasPin = true;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<title>Quản lý mã PIN</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../../assets/custom/css/style.css">
<link rel="stylesheet" href="../../assets/vendor/font-awesome/css/font-awesome.min.css">
</head>
<body>
<?php include_once(__DIR__ . '/../layouts/partials/header.php'); ?>

<div class="container mt-5 mb-5">
<div class="card shadow p-4" style="max-width:400px;margin:auto;">
<h3><i class="fa fa-key"></i> Mã PIN bảo mật</h3>

<?php if (!empty($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
<?php if (!empty($success)) echo "<div class='alert alert-success'>$success</div>"; ?>

<?php if (!$hasPin): ?>
    <!-- Tạo PIN -->
    <form method="POST">
        <label>Nhập PIN (4-6 số):</label>
        <input type="password" name="pin" class="form-control" required>

        <label class="mt-2">Nhập lại PIN:</label>
        <input type="password" name="confirm_pin" class="form-control" required>

        <button name="save_pin" class="btn btn-primary w-100 mt-3">Tạo mã PIN</button>
    </form>
<?php else: ?>
    <!-- Đổi PIN -->
    <p class="text-success">✅ Bạn đã bật mã PIN</p>

    <form method="POST">
        <label>PIN mới:</label>
        <input type="password" name="pin" class="form-control" required>

        <label class="mt-2">Nhập lại:</label>
        <input type="password" name="confirm_pin" class="form-control" required>

        <button name="save_pin" class="btn btn-warning w-100 mt-3">Đổi mã PIN</button>
    </form>

    <form method="POST" class="mt-3">
        <button name="remove_pin" class="btn btn-danger w-100"
            onclick="return confirm('Bạn chắc chắn muốn tắt mã PIN?');">
            Tắt mã PIN
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
