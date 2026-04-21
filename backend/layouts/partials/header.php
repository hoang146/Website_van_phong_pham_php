<style>
.admin-header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
  padding: 15px 0;
  position: sticky;
  top: 0;
  z-index: 1000;
}
.admin-brand {
  color: white !important;
  font-size: 1.5rem;
  font-weight: 700;
  letter-spacing: 1px;
  display: flex;
  align-items: center;
  gap: 10px;
}
.admin-brand i {
  font-size: 1.8rem;
}
.admin-user-info {
  display: flex;
  align-items: center;
  gap: 15px;
}
.admin-user-name {
  color: white;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 15px;
  background: rgba(255,255,255,0.15);
  border-radius: 25px;
}
.admin-logout-btn {
  background: white;
  color: #667eea;
  border: none;
  padding: 10px 24px;
  border-radius: 25px;
  font-weight: 600;
  transition: all 0.3s;
}
.admin-logout-btn:hover {
  background: rgba(255,255,255,0.9);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.2);
}
</style>

<nav class="navbar navbar-expand-lg navbar-dark admin-header">
  <div class="container-fluid px-4">
    <a class="navbar-brand admin-brand" href="/admin.php">
      <i class="fas fa-shield-alt"></i>
      <span>ADMIN PANEL</span>
    </a>
    <div class="admin-user-info">
      <span class="admin-user-name">
        <i class="fas fa-user-circle"></i>
        <?= $_SESSION['username']; ?>
      </span>
      <a href="/frontend/pages/logout.php" class="btn admin-logout-btn">
        <i class="fas fa-sign-out-alt me-2"></i>Đăng xuất
      </a>
    </div>
  </div>
</nav>
