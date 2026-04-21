<style>
.admin-sidebar {
  width: 280px;
  background: linear-gradient(180deg, #2c3e50 0%, #34495e 100%);
  color: white;
  box-shadow: 4px 0 12px rgba(0,0,0,0.1);
  padding: 25px 15px;
  min-height: calc(100vh - 80px);
  overflow-y: auto;
}
.admin-sidebar::-webkit-scrollbar {
  width: 6px;
}
.admin-sidebar::-webkit-scrollbar-thumb {
  background: rgba(255,255,255,0.3);
  border-radius: 3px;
}
.admin-nav-item {
  margin-bottom: 8px;
}
.admin-nav-link {
  color: rgba(255,255,255,0.85);
  text-decoration: none;
  padding: 12px 20px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  font-weight: 500;
  transition: all 0.3s;
  border-left: 3px solid transparent;
}
.admin-nav-link i {
  width: 25px;
  margin-right: 12px;
  font-size: 1.1rem;
}
.admin-nav-link:hover {
  background: rgba(255,255,255,0.1);
  color: white;
  transform: translateX(5px);
  border-left-color: #667eea;
}
.admin-nav-link.active {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  font-weight: 600;
  box-shadow: 0 4px 12px rgba(102,126,234,0.3);
  border-left-color: transparent;
}
.sidebar-divider {
  height: 1px;
  background: rgba(255,255,255,0.1);
  margin: 20px 0;
}
</style>

<aside class="admin-sidebar">
  <ul class="nav flex-column">
    <li class="admin-nav-item">
      <a href="/admin.php" class="admin-nav-link <?= ($_SERVER['SCRIPT_NAME']=='/admin.php')?' active':'' ?>">
        <i class="fas fa-tachometer-alt"></i>
        <span>Dashboard</span>
      </a>
    </li>
    <li class="admin-nav-item">
      <a href="/backend/pages/users.php" class="admin-nav-link <?= (strpos($_SERVER['SCRIPT_NAME'],'users.php')!==false)?' active':'' ?>">
        <i class="fas fa-users"></i>
        <span>Người dùng</span>
      </a>
    </li>
    <li class="admin-nav-item">
      <a href="/backend/pages/products.php" class="admin-nav-link <?= (strpos($_SERVER['SCRIPT_NAME'],'products.php')!==false)?' active':'' ?>">
        <i class="fas fa-box"></i>
        <span>Sản phẩm</span>
      </a>
    </li>
    <li class="admin-nav-item">
      <a href="/backend/pages/categories.php" class="admin-nav-link <?= (strpos($_SERVER['SCRIPT_NAME'],'categories.php')!==false)?' active':'' ?>">
        <i class="fas fa-list-alt"></i>
        <span>Danh mục</span>
      </a>
    </li>
    <li class="admin-nav-item">
      <a href="/backend/pages/tags.php" class="admin-nav-link <?= (strpos($_SERVER['SCRIPT_NAME'],'tags.php')!==false)?' active':'' ?>">
        <i class="fas fa-tags"></i>
        <span>Thẻ</span>
      </a>
    </li>
    <div class="sidebar-divider"></div>
    <li class="admin-nav-item">
      <a href="/backend/pages/orders.php" class="admin-nav-link <?= (strpos($_SERVER['SCRIPT_NAME'],'orders.php')!==false)?' active':'' ?>">
        <i class="fas fa-shopping-bag"></i>
        <span>Hóa đơn</span>
      </a>
    </li>
    <li class="admin-nav-item">
      <a href="/backend/pages/statistics.php" class="admin-nav-link <?= (strpos($_SERVER['SCRIPT_NAME'],'statistics.php')!==false)?' active':'' ?>">
        <i class="fas fa-chart-line"></i>
        <span>Thống kê</span>
      </a>
    </li>
  </ul>
</aside>