<style>
.admin-footer {
  background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
  color: white;
  padding: 25px 0;
  margin-top: auto;
  position: relative;
}
.admin-footer::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 3px;
  background: linear-gradient(90deg, #667eea 0%, #764ba2 50%, #667eea 100%);
}
.footer-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 15px;
}
.footer-copyright {
  font-weight: 600;
  color: rgba(255,255,255,0.9);
}
.footer-links {
  display: flex;
  gap: 25px;
  color: rgba(255,255,255,0.8);
}
.footer-links a {
  color: rgba(255,255,255,0.8);
  text-decoration: none;
  transition: all 0.3s;
}
.footer-links a:hover {
  color: white;
  transform: translateY(-2px);
}
@media (max-width: 768px) {
  .footer-content {
    flex-direction: column;
    text-align: center;
  }
}
</style>

<footer class="admin-footer">
  <div class="container">
    <div class="footer-content">
      <div class="footer-copyright">
        <i class="fas fa-copyright me-2"></i><?= date("Y"); ?> <strong>Admin Panel</strong> - Văn Phòng Phẩm Online
      </div>
      <div class="footer-links">
        <a href="mailto:admin@vanphongpham.vn">
          <i class="fas fa-envelope me-1"></i>admin@vanphongpham.vn
        </a>
        <a href="tel:0123456789">
          <i class="fas fa-phone me-1"></i>0123 456 789
        </a>
      </div>
    </div>
  </div>
</footer>
