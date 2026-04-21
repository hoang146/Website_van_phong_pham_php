<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Liên hệ - VPP Online</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <link rel="stylesheet" href="/assets/custom/css/style.css">
</head>
<body>

<?php include_once(__DIR__ . '/../layouts/partials/header.php'); ?>

<main class="container my-5">
  <!-- Page Header -->
  <div class="page-header-contact mb-5">
    <h1 class="page-title"><i class="fa fa-envelope me-2"></i>Liên hệ với chúng tôi</h1>
    <p class="page-subtitle">Chúng tôi luôn sẵn sàng lắng nghe và hỗ trợ bạn 24/7</p>
  </div>

  <div class="row g-4">
    <!-- Contact Info Cards -->
    <div class="col-lg-4">
      <div class="contact-info-card">
        <div class="contact-icon contact-icon-primary">
          <i class="fa fa-map-marker-alt"></i>
        </div>
        <h5 class="contact-title">Địa chỉ</h5>
        <p class="contact-text">123 Đường ABC, Quận 1<br>Thành phố Hồ Chí Minh</p>
      </div>
      
      <div class="contact-info-card mt-3">
        <div class="contact-icon contact-icon-success">
          <i class="fa fa-phone"></i>
        </div>
        <h5 class="contact-title">Điện thoại</h5>
        <p class="contact-text">Hotline: <strong>0123 456 789</strong><br>Hỗ trợ: <strong>0987 654 321</strong></p>
      </div>
      
      <div class="contact-info-card mt-3">
        <div class="contact-icon contact-icon-info">
          <i class="fa fa-envelope"></i>
        </div>
        <h5 class="contact-title">Email</h5>
        <p class="contact-text">contact@vpponline.vn<br>support@vpponline.vn</p>
      </div>
      
      <div class="contact-info-card mt-3">
        <div class="contact-icon contact-icon-warning">
          <i class="fa fa-clock"></i>
        </div>
        <h5 class="contact-title">Giờ làm việc</h5>
        <p class="contact-text">Thứ 2 - Thứ 6: 8:00 - 18:00<br>Thứ 7 - CN: 9:00 - 17:00</p>
      </div>
    </div>

    <!-- Contact Form -->
    <div class="col-lg-8">
      <div class="contact-form-card">
        <h4 class="form-card-title"><i class="fa fa-paper-plane text-primary me-2"></i>Gửi tin nhắn cho chúng tôi</h4>
        
        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            echo '<div class="alert alert-success alert-modern"><i class="fa fa-check-circle me-2"></i>Cảm ơn bạn đã liên hệ! Chúng tôi sẽ phản hồi trong vòng 24h.</div>';
        }
        ?>
        
        <form method="post" class="contact-form">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold"><i class="fa fa-user text-primary me-2"></i>Họ tên</label>
              <input type="text" name="name" class="form-control form-control-modern" required placeholder="Nguyễn Văn A">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold"><i class="fa fa-envelope text-primary me-2"></i>Email</label>
              <input type="email" name="email" class="form-control form-control-modern" required placeholder="email@example.com">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold"><i class="fa fa-phone text-primary me-2"></i>Số điện thoại</label>
              <input type="tel" name="phone" class="form-control form-control-modern" placeholder="0123456789">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold"><i class="fa fa-tag text-primary me-2"></i>Chủ đề</label>
              <input type="text" name="subject" class="form-control form-control-modern" placeholder="Tình trạng đơn hàng">
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold"><i class="fa fa-comment text-primary me-2"></i>Nội dung</label>
              <textarea name="message" class="form-control form-control-modern" rows="6" required placeholder="Viết tin nhắn của bạn tại đây..."></textarea>
            </div>
            <div class="col-12">
              <button type="submit" class="btn btn-primary btn-modern px-5 py-3">
                <i class="fa fa-paper-plane me-2"></i>Gửi tin nhắn
              </button>
            </div>
          </div>
        </form>
      </div>
      
      <!-- Map -->
      <div class="map-card mt-4">
        <h5 class="map-title"><i class="fa fa-map text-primary me-2"></i>Bản đồ vị trí</h5>
        <div class="map-container">
          <iframe src="https://www.google.com/maps?q=123+Đường+ABC,+Quận+1,+TP.HCM&output=embed"
                  width="100%" height="350" style="border:0;border-radius:12px;" allowfullscreen loading="lazy"></iframe>
        </div>
      </div>
    </div>
  </div>
</main>

<?php include_once(__DIR__ . '/../layouts/partials/footer.php'); ?>

<style>
.page-header-contact {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 3rem 2rem;
  border-radius: 16px;
  text-align: center;
  box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
}
.page-title {
  font-size: 2.25rem;
  font-weight: 700;
  margin: 0;
}
.page-subtitle {
  font-size: 1.1rem;
  margin: 0.75rem 0 0 0;
  opacity: 0.95;
}
.contact-info-card {
  background: white;
  border-radius: 16px;
  padding: 1.5rem;
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
  transition: all 0.3s ease;
  border-left: 4px solid transparent;
}
.contact-info-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 20px rgba(0,0,0,0.12);
}
.contact-icon {
  width: 50px;
  height: 50px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  color: white;
  margin-bottom: 1rem;
}
.contact-icon-primary {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}
.contact-icon-success {
  background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
}
.contact-icon-info {
  background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
}
.contact-icon-warning {
  background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
}
.contact-title {
  font-size: 1.1rem;
  font-weight: 700;
  color: #2c3e50;
  margin-bottom: 0.5rem;
}
.contact-text {
  color: #6c757d;
  margin: 0;
  line-height: 1.6;
}
.contact-form-card, .map-card {
  background: white;
  border-radius: 16px;
  padding: 2rem;
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}
.form-card-title, .map-title {
  font-size: 1.25rem;
  font-weight: 700;
  color: #2c3e50;
  margin-bottom: 1.5rem;
  padding-bottom: 1rem;
  border-bottom: 2px solid #f0f0f0;
}
.form-control-modern {
  border: 2px solid #e0e6ed;
  border-radius: 10px;
  padding: 0.75rem 1rem;
  transition: all 0.3s ease;
}
.form-control-modern:focus {
  border-color: #667eea;
  box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
}
.btn-modern {
  border-radius: 12px;
  font-weight: 600;
  font-size: 1.1rem;
  transition: all 0.3s ease;
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}
.btn-modern:hover {
  transform: translateY(-3px);
  box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
}
.alert-modern {
  border-radius: 12px;
  border: none;
  font-weight: 500;
}
.map-container {
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
