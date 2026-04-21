<style>
.modern-footer {
  background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
  color: white;
  margin-top: auto;
  padding: 60px 0 30px;
  position: relative;
}
.modern-footer::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
  background: linear-gradient(90deg, #667eea 0%, #764ba2 50%, #667eea 100%);
}
.footer-heading {
  font-size: 1.25rem;
  font-weight: 700;
  margin-bottom: 20px;
  position: relative;
  padding-bottom: 10px;
}
.footer-heading::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 0;
  width: 50px;
  height: 3px;
  background: linear-gradient(90deg, #667eea, #764ba2);
  border-radius: 2px;
}
.footer-text {
  color: rgba(255,255,255,0.8);
  line-height: 1.8;
}
.footer-link {
  color: rgba(255,255,255,0.8);
  text-decoration: none;
  display: flex;
  align-items: center;
  padding: 8px 0;
  transition: all 0.3s;
}
.footer-link:hover {
  color: white;
  transform: translateX(5px);
}
.footer-link i {
  margin-right: 10px;
  width: 20px;
}
.contact-item {
  display: flex;
  align-items: start;
  margin-bottom: 15px;
  color: rgba(255,255,255,0.8);
}
.contact-item i {
  width: 30px;
  margin-right: 12px;
  color: #667eea;
  font-size: 1.1rem;
}
.footer-bottom {
  margin-top: 40px;
  padding-top: 20px;
  border-top: 1px solid rgba(255,255,255,0.1);
  text-align: center;
}
.social-footer {
  display: flex;
  gap: 15px;
  margin-top: 20px;
}
.social-footer a {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: rgba(255,255,255,0.1);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 1.2rem;
  transition: all 0.3s;
}
.social-footer a:hover {
  background: linear-gradient(135deg, #667eea, #764ba2);
  transform: translateY(-3px);
}
#scrollBtn {
  position: fixed;
  bottom: 30px;
  right: 30px;
  width: 50px;
  height: 50px;
  background: linear-gradient(135deg, #667eea, #764ba2);
  color: white;
  border: none;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  cursor: pointer;
  opacity: 0;
  visibility: hidden;
  transition: all 0.3s;
  box-shadow: 0 4px 12px rgba(0,0,0,0.3);
  z-index: 999;
}
#scrollBtn.show {
  opacity: 1;
  visibility: visible;
}
#scrollBtn:hover {
  transform: translateY(-5px);
  box-shadow: 0 6px 20px rgba(102,126,234,0.5);
}
.social-links {
  position: fixed;
  right: 30px;
  bottom: 100px;
  display: flex;
  flex-direction: column;
  gap: 10px;
  z-index: 998;
}
.social-links a {
  width: 45px;
  height: 45px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 1.2rem;
  transition: all 0.3s;
  box-shadow: 0 3px 10px rgba(0,0,0,0.2);
}
.social-links a:nth-child(1) {
  background: #1877f2;
}
.social-links a:nth-child(2) {
  background: #ff0000;
}
.social-links a:nth-child(3) {
  background: #0068ff;
}
.social-links a:hover {
  transform: scale(1.1);
}
</style>

<footer class="modern-footer">
  <div class="container">
    <div class="row">
      <div class="col-lg-4 col-md-6 mb-4">
        <h5 class="footer-heading">
          <i class="fas fa-book-open me-2"></i>VietOffice
        </h5>
        <p class="footer-text">
          Văn Phòng Phẩm Online - Đối tác đáng tin cậy cho mọi nhu cầu văn phòng và học tập của bạn. 
          Chất lượng cao, giá cả hợp lý, giao hàng nhanh chóng.
        </p>
        <div class="social-footer">
          <a href="https://facebook.com" target="_blank" title="Facebook">
            <i class="fab fa-facebook-f"></i>
          </a>
          <a href="https://youtube.com" target="_blank" title="YouTube">
            <i class="fab fa-youtube"></i>
          </a>
          <a href="https://zalo.me" target="_blank" title="Zalo">
            <i class="fas fa-comment-dots"></i>
          </a>
          <a href="https://instagram.com" target="_blank" title="Instagram">
            <i class="fab fa-instagram"></i>
          </a>
        </div>
      </div>
      
      <div class="col-lg-3 col-md-6 mb-4">
        <h5 class="footer-heading">Thông tin liên hệ</h5>
        <div class="contact-item">
          <i class="fas fa-map-marker-alt"></i>
          <span>123 Đường ABC, Quận 1, TP. Hồ Chí Minh</span>
        </div>
        <div class="contact-item">
          <i class="fas fa-phone-alt"></i>
          <span>0123 456 789</span>
        </div>
        <div class="contact-item">
          <i class="fas fa-envelope"></i>
          <span>contact@vietoffice.vn</span>
        </div>
        <div class="contact-item">
          <i class="fas fa-clock"></i>
          <span>T2 - T7: 8:00 - 20:00<br>CN: 9:00 - 18:00</span>
        </div>
      </div>
      
      <div class="col-lg-2 col-md-6 mb-4">
        <h5 class="footer-heading">Liên kết</h5>
        <ul class="list-unstyled">
          <li><a href="/index.php" class="footer-link"><i class="fas fa-home"></i>Trang chủ</a></li>
          <li><a href="/frontend/pages/products_list.php" class="footer-link"><i class="fas fa-box"></i>Sản phẩm</a></li>
          <li><a href="/frontend/pages/about_us.php" class="footer-link"><i class="fas fa-info-circle"></i>Giới thiệu</a></li>
          <li><a href="/frontend/pages/contact.php" class="footer-link"><i class="fas fa-envelope"></i>Liên hệ</a></li>
        </ul>
      </div>
      
      <div class="col-lg-3 col-md-6 mb-4">
        <h5 class="footer-heading">Hỗ trợ khách hàng</h5>
        <ul class="list-unstyled">
          <li><a href="/frontend/pages/order_history.php" class="footer-link"><i class="fas fa-history"></i>Tra cứu đơn hàng</a></li>
          <li><a href="/frontend/pages/cart.php" class="footer-link"><i class="fas fa-shopping-cart"></i>Giỏ hàng</a></li>
          <li><a href="/frontend/pages/user_info.php" class="footer-link"><i class="fas fa-user-circle"></i>Tài khoản</a></li>
          <li><a href="#" class="footer-link"><i class="fas fa-question-circle"></i>Câu hỏi thường gặp</a></li>
        </ul>
      </div>
    </div>
    
    <div class="footer-bottom">
      <p class="mb-2" style="color: rgba(255,255,255,0.6);">
        © <?= date("Y"); ?> VietOffice - Văn Phòng Phẩm Online. All rights reserved.
      </p>
      <p class="mb-0" style="color: rgba(255,255,255,0.4); font-size: 0.85rem;">
        <i class="fas fa-shield-alt me-1"></i>Giao dịch an toàn & bảo mật
      </p>
    </div>
  </div>
  
  <!-- Scroll to top button -->
  <div id="scrollBtn">
    <i class="fas fa-chevron-up"></i>
  </div>

  <!-- Social Media Float buttons -->
  <div class="social-links">
    <a href="https://facebook.com" target="_blank" title="Facebook">
      <i class="fab fa-facebook-f"></i>
    </a>
    <a href="https://youtube.com" target="_blank" title="YouTube">
      <i class="fab fa-youtube"></i>
    </a>
    <a href="https://zalo.me" target="_blank" title="Zalo">
      <i class="fas fa-comment-dots"></i>
    </a>
  </div>
  
  <script>
  const scrollBtn = document.getElementById("scrollBtn");

  window.addEventListener("scroll", () => {
    if (window.scrollY > 300) {
      scrollBtn.classList.add("show");
    } else {
      scrollBtn.classList.remove("show");
    }
  });

  scrollBtn.onclick = () => {
    window.scrollTo({ top: 0, behavior: "smooth" });
  };
  </script>
</footer>
