<?php
include_once(__DIR__ . '/../../config/database.php');
session_start();
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $recaptcha = $_POST['g-recaptcha-response'];
    $secret = '6Le5RTcrAAAAAOYUzqdkv4NqE9wFqk_2wwp3HLPt';
    // Sử dụng cURL để xác thực reCAPTCHA
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://www.google.com/recaptcha/api/siteverify");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['secret' => $secret, 'response' => $recaptcha]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    $result = json_decode($response, true);
    if (!$result['success']) {
        $message = "❌ Vui lòng xác nhận bạn không phải robot!";
    } else {
        $username = trim($_POST['username']);
        $password = $_POST['password'];
        // Sử dụng prepared statement để truy vấn
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            // Kiểm tra mật khẩu với password_verify
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id']   = $user['id'];
                $_SESSION['username']  = $user['username'];
                $_SESSION['role']      = $user['role'];
                $stmt->close();
                // Chuyển hướng
                if ($user['role'] === 'admin') {
                    header("Location: ../../admin.php");
                    exit;
                } else {
                    header("Location: ../../index.php");
                    exit;
                }
            } else {
                $message = "❌ Sai tên đăng nhập hoặc mật khẩu!";
            }
        } else {
            $message = "❌ Sai tên đăng nhập hoặc mật khẩu!";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Đăng nhập - VietOffice</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <link rel="stylesheet" href="/assets/custom/css/style.css">
  <style>
    .auth-page {
      min-height: 100vh;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      display: flex;
      align-items: center;
      padding: 40px 0;
    }
    .auth-card {
      backdrop-filter: blur(10px);
      background: rgba(255, 255, 255, 0.95);
      border-radius: 20px;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
      overflow: hidden;
      animation: slideUp 0.6s ease;
    }
    @keyframes slideUp {
      from { opacity: 0; transform: translateY(30px); }
      to { opacity: 1; transform: translateY(0); }
    }
    .auth-header {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: 30px;
      text-align: center;
    }
    .auth-header i {
      font-size: 3rem;
      margin-bottom: 15px;
      animation: bounce 1s infinite;
    }
    @keyframes bounce {
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(-10px); }
    }
    .auth-header h2 {
      color: white;
      margin: 0;
    }
    .form-control:focus {
      border-color: #667eea;
      box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }
    .btn-auth-primary {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      border: none;
      transition: all 0.3s ease;
    }
    .btn-auth-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
    }
    .divider {
      display: flex;
      align-items: center;
      text-align: center;
      margin: 20px 0;
    }
    .divider::before,
    .divider::after {
      content: '';
      flex: 1;
      border-bottom: 1px solid #ddd;
    }
    .divider span {
      padding: 0 10px;
      color: #999;
      font-size: 0.9rem;
    }
    .input-icon {
      position: relative;
    }
    .input-icon i {
      position: absolute;
      left: 15px;
      top: 50%;
      transform: translateY(-50%);
      color: #999;
    }
    .input-icon input {
      padding-left: 45px;
    }
  </style>
</head>
<body>
<?php include_once(__DIR__ . '/../layouts/partials/header.php'); ?>

<main class="auth-page">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-6 col-lg-5">
        <div class="auth-card">
          <div class="auth-header">
            <i class="fas fa-user-circle"></i>
            <h2 class="fw-bold mb-2">Đăng nhập</h2>
            <p class="mb-0 opacity-75">Chào mừng bạn quay trở lại!</p>
          </div>
          <div class="card-body p-4">
            <?php if ($message): ?>
              <div class="alert alert-danger text-center"><?= $message ?></div>
            <?php endif; ?>
            <form method="post" autocomplete="off">
              <div class="mb-3">
                <label class="form-label fw-semibold"><i class="fas fa-user me-2 text-primary"></i>Tên đăng nhập</label>
                <div class="input-icon">
                  <i class="fas fa-user"></i>
                  <input type="text" name="username" class="form-control form-control-lg rounded-3" required placeholder="Nhập tên đăng nhập">
                </div>
              </div>
              <div class="mb-3">
                <label class="form-label fw-semibold"><i class="fas fa-lock me-2 text-primary"></i>Mật khẩu</label>
                <div class="input-icon">
                  <i class="fas fa-lock"></i>
                  <input type="password" name="password" class="form-control form-control-lg rounded-3" required placeholder="Nhập mật khẩu">
                </div>
              </div>
              <div class="g-recaptcha mb-3 d-flex justify-content-center" data-sitekey="6Le5RTcrAAAAACfjMM5fjFOOYKCfXVLzPIVqL8NL"></div>
              <button type="submit" class="btn btn-auth-primary w-100 py-3 fw-bold rounded-3 mb-3 text-white" style="font-size:1.1rem;letter-spacing:1px;">
                <i class="fas fa-sign-in-alt me-2"></i>Đăng nhập
              </button>
            </form>
            <div class="divider">
              <span>hoặc đăng nhập bằng</span>
            </div>
            <div class="d-grid gap-2">
              <a href="google-login.php" class="btn btn-outline-danger w-100 py-2 fw-bold rounded-3" style="font-size:1rem;">
                <i class="fab fa-google me-2"></i> Google
              </a>
            </div>
            <p class="mt-4 text-center mb-0">
              Bạn chưa có tài khoản? <a href="register.php" class="fw-bold" style="color: #667eea; text-decoration: none;">Đăng ký ngay</a>
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>

<?php include_once(__DIR__ . '/../layouts/partials/footer.php'); ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
</body>
</html>
