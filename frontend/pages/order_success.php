<?php
session_start();
// Trang thông báo thanh toán thành công
$order_id = $_GET['order_id'] ?? 0;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta charset="UTF-8">
    <title>Đặt hàng thành công - VPP Online</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../../assets/custom/css/style.css">
</head>
<body>
<?php include_once(__DIR__ . '/../layouts/partials/header.php'); ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="success-card">
                <div class="success-animation">
                    <div class="checkmark-circle">
                        <div class="checkmark-background"></div>
                        <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                            <circle class="checkmark-circle-check" cx="26" cy="26" r="25" fill="none"/>
                            <path class="checkmark-check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
                        </svg>
                    </div>
                </div>
                
                <div class="success-content text-center">
                    <h2 class="success-title">Đặt hàng thành công!</h2>
                    <p class="success-subtitle">Cảm ơn bạn đã mua sắm tại <strong>Văn Phòng Phẩm Online</strong></p>
                    
                    <?php if($order_id): ?>
                    <div class="order-info-box">
                        <div class="order-number">
                            <i class="fa fa-receipt me-2"></i>Mã đơn hàng: <strong>#<?= $order_id ?></strong>
                        </div>
                        <p class="order-note">
                            <i class="fa fa-envelope me-2"></i>Email xác nhận đã được gửi đến hòm thư của bạn.<br>
                            <i class="fa fa-box me-2"></i>Chúng tôi sẽ xử lý đơn hàng trong vòng 24h.
                        </p>
                    </div>
                    <?php endif; ?>
                    
                    <div class="success-actions">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <a href="/frontend/pages/order_detail.php?order_id=<?= $order_id ?>" class="btn btn-primary btn-action w-100">
                                    <i class="fa fa-file-alt me-2"></i>Xem hóa đơn
                                </a>
                            </div>
                            <div class="col-md-6">
                                <a href="/frontend/pages/order_history.php" class="btn btn-info btn-action w-100">
                                    <i class="fa fa-history me-2"></i>Lịch sử mua hàng
                                </a>
                            </div>
                            <div class="col-md-6">
                                <a href="/frontend/pages/products_list.php" class="btn btn-success btn-action w-100">
                                    <i class="fa fa-shopping-bag me-2"></i>Tiếp tục mua sắm
                                </a>
                            </div>
                            <div class="col-md-6">
                                <a href="../../index.php" class="btn btn-secondary btn-action w-100">
                                    <i class="fa fa-home me-2"></i>Về trang chủ
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="support-box">
                        <h5><i class="fa fa-headset me-2"></i>Cần hỗ trợ?</h5>
                        <p>Liên hệ với chúng tôi qua:</p>
                        <div class="support-links">
                            <a href="tel:0123456789"><i class="fa fa-phone"></i> 0123 456 789</a>
                            <a href="mailto:support@vpponline.vn"><i class="fa fa-envelope"></i> support@vpponline.vn</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include_once(__DIR__ . '/../layouts/partials/footer.php'); ?>

<style>
.success-card {
  background: white;
  border-radius: 20px;
  box-shadow: 0 10px 40px rgba(0,0,0,0.1);
  padding: 3rem 2rem;
}
.success-animation {
  display: flex;
  justify-content: center;
  margin-bottom: 2rem;
}
.checkmark-circle {
  width: 120px;
  height: 120px;
  position: relative;
  display: inline-block;
  vertical-align: top;
}
.checkmark-background {
  width: 120px;
  height: 120px;
  background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
  border-radius: 50%;
  position: absolute;
  animation: scaleIn 0.5s ease-in-out;
}
.checkmark {
  width: 120px;
  height: 120px;
  border-radius: 50%;
  display: block;
  stroke-width: 3;
  stroke: #fff;
  stroke-miterlimit: 10;
  box-shadow: inset 0px 0px 0px #28a745;
  animation: fillGreen 0.4s ease-in-out 0.3s forwards, scaleIn 0.3s ease-in-out 0.8s both;
}
.checkmark-circle-check {
  stroke-dasharray: 166;
  stroke-dashoffset: 166;
  stroke-width: 3;
  stroke-miterlimit: 10;
  stroke: #28a745;
  fill: none;
  animation: stroke 0.6s cubic-bezier(0.65, 0, 0.45, 1) 0.3s forwards;
}
.checkmark-check {
  transform-origin: 50% 50%;
  stroke-dasharray: 48;
  stroke-dashoffset: 48;
  stroke-width: 4;
  animation: stroke 0.3s cubic-bezier(0.65, 0, 0.45, 1) 0.9s forwards;
}
@keyframes stroke {
  100% { stroke-dashoffset: 0; }
}
@keyframes scaleIn {
  0% { transform: scale(0); opacity: 0; }
  100% { transform: scale(1); opacity: 1; }
}
@keyframes fillGreen {
  100% { box-shadow: inset 0px 0px 0px 60px #28a745; }
}
.success-content {
  animation: fadeInUp 0.6s ease-out 0.5s both;
}
.success-title {
  font-size: 2rem;
  font-weight: 700;
  color: #28a745;
  margin-bottom: 0.5rem;
}
.success-subtitle {
  font-size: 1.1rem;
  color: #6c757d;
  margin-bottom: 2rem;
}
.order-info-box {
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
  border-radius: 12px;
  padding: 1.5rem;
  margin: 2rem 0;
  border-left: 4px solid #28a745;
}
.order-number {
  font-size: 1.25rem;
  color: #2c3e50;
  margin-bottom: 1rem;
}
.order-note {
  font-size: 0.95rem;
  color: #6c757d;
  margin: 0;
  line-height: 1.8;
}
.success-actions {
  margin: 2rem 0;
}
.btn-action {
  padding: 0.875rem 1.5rem;
  font-weight: 600;
  font-size: 1rem;
  border-radius: 12px;
  transition: all 0.3s ease;
}
.btn-action:hover {
  transform: translateY(-3px);
  box-shadow: 0 6px 20px rgba(0,0,0,0.15);
}
.support-box {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border-radius: 12px;
  padding: 1.5rem;
  margin-top: 2rem;
}
.support-box h5 {
  font-size: 1.1rem;
  font-weight: 700;
  margin-bottom: 0.5rem;
}
.support-box p {
  font-size: 0.95rem;
  margin-bottom: 1rem;
  opacity: 0.95;
}
.support-links {
  display: flex;
  justify-content: center;
  gap: 1.5rem;
  flex-wrap: wrap;
}
.support-links a {
  color: white;
  text-decoration: none;
  font-weight: 600;
  padding: 0.5rem 1rem;
  background: rgba(255,255,255,0.2);
  border-radius: 8px;
  transition: all 0.3s ease;
}
.support-links a:hover {
  background: rgba(255,255,255,0.3);
  transform: translateY(-2px);
}
@keyframes fadeInUp {
  0% {
    opacity: 0;
    transform: translateY(20px);
  }
  100% {
    opacity: 1;
    transform: translateY(0);
  }
}
@media (max-width: 768px) {
  .success-card { padding: 2rem 1rem; }
  .success-title { font-size: 1.5rem; }
  .checkmark-circle, .checkmark-background, .checkmark { width: 90px; height: 90px; }
}
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
