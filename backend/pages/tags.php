<?php
session_start();
include_once(__DIR__ . '/../../config/database.php');

// Kiểm tra quyền admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../frontend/pages/login.php");
    exit;
}

// Xử lý thêm tag
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'])) {
    $name = trim($_POST['name']);
    if (!empty($name)) {
        $name_esc = mysqli_real_escape_string($conn, $name);
        $sql = "INSERT INTO tags (name) VALUES ('$name_esc')";
        mysqli_query($conn, $sql);
    }
    header("Location: tags.php");
    exit;
}

// Xử lý tìm kiếm
$search = isset($_GET['search']) ? $_GET['search'] : "";

// Phân trang
$limit = 25;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// Đếm tổng số tag
$search_esc = mysqli_real_escape_string($conn, $search);
$count_sql = "SELECT COUNT(*) as total FROM tags WHERE name LIKE '%$search_esc%'";
$count_result = mysqli_query($conn, $count_sql);
$total_tags = ($count_result) ? mysqli_fetch_assoc($count_result)['total'] : 0;
$total_pages = ceil($total_tags / $limit);

// Lấy danh sách tag
$sql = "SELECT * FROM tags WHERE name LIKE '%$search_esc%' 
        ORDER BY id DESC 
        LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $sql);

// URL xóa tag
$deleteUrl = rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . '/delete_tag.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Quản lý Tag</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <link rel="stylesheet" href="../../assets/custom/css/admin-dashboard.css">
  <style>
    .page-header {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      border-radius: 16px;
      padding: 25px 30px;
      color: white;
      box-shadow: 0 8px 20px rgba(102,126,234,0.3);
      margin-bottom: 25px;
    }
    .form-card, .search-card, .data-table {
      background: white;
      border-radius: 16px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.08);
      border: none;
      margin-bottom: 25px;
    }
    .data-table thead {
      background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
      color: white;
    }
    .data-table tbody tr:hover {
      background: #f8f9fa;
      transform: scale(1.005);
    }
  </style>
</head>
<body class="bg-light">
<div class="d-flex flex-column min-vh-100" style="background:#f8f9fa;">
  <?php include_once(__DIR__ . '/../layouts/partials/header.php'); ?>
  <div class="d-flex flex-grow-1">
    <?php include_once(__DIR__ . '/../layouts/partials/sidebar.php'); ?>
    <main class="flex-grow-1 px-4" style="min-width:0;">
      <div class="container py-4">
      <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h2 class="fw-bold mb-1"><i class="fas fa-tags me-2"></i>Quản lý thẻ (Tag)</h2>
            <p class="mb-0 opacity-75">Quản lý thẻ gắn cho sản phẩm</p>
          </div>
          <span class="badge badge-modern bg-light text-dark"><i class="fas fa-database me-1"></i>Tổng: <?= $total_tags ?></span>
        </div>
      </div>
      
      <div class="card form-card">
        <div class="card-body">
          <h5 class="fw-bold mb-3"><i class="fas fa-plus-circle me-2 text-success"></i>Thêm thẻ mới</h5>
          <form method="post" class="row g-3 align-items-end">
            <div class="col-md-7">
              <label class="form-label fw-bold"><i class="fa fa-tag me-1"></i> Tên thẻ</label>
              <input type="text" name="name" class="form-control" placeholder="Nhập tên thẻ mới..." required>
            </div>
            <div class="col-md-5 text-end">
              <button type="submit" class="btn btn-success px-4 fw-bold"><i class="fa fa-plus me-1"></i> Thêm thẻ</button>
            </div>
          </form>
        </div>
      </div>
      
      <div class="card search-card">
        <div class="card-body">
          <form method="get" class="row g-3 align-items-end">
            <div class="col-md-7">
              <label class="form-label fw-bold"><i class="fa fa-search me-1"></i> Tìm kiếm thẻ</label>
              <input type="text" name="search" class="form-control" placeholder="Tìm thẻ..." value="<?= htmlspecialchars($search ?? '') ?>">
            </div>
            <div class="col-md-5 text-end">
              <button type="submit" class="btn btn-primary px-4 fw-bold"><i class="fa fa-search me-1"></i> Tìm kiếm</button>
              <a href="tags.php" class="btn btn-secondary px-4 fw-bold"><i class="fa fa-redo me-1"></i> Làm mới</a>
            </div>
          </form>
        </div>
      </div>
      
      <div class="card shadow-sm border-0">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="border-radius:0">
              <thead class="table-dark">
                <tr>
                  <th width="60">STT</th>
                  <th>Tên thẻ</th>
                  <th>Ngày tạo</th>
                  <th width="120">Hành động</th>
                </tr>
              </thead>
              <tbody>
              <?php 
              $stt = 1 + ($page - 1) * $limit;
              while($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                  <td><?= $stt++ ?></td>
                  <td class="fw-bold text-dark"><?= htmlspecialchars($row['name'] ?? '') ?></td>
                  <td><?= $row['created_at'] ?></td>
                  <td>
                    <button class="btn btn-sm btn-danger fw-bold px-3" data-bs-toggle="modal" data-bs-target="#deleteTagModal" data-tag-id="<?= $row['id'] ?>"><i class="fa fa-trash"></i> Xóa</button>
                  </td>
                </tr>
              <?php endwhile; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <nav class="mt-4">
        <ul class="pagination justify-content-center">
          <?php if ($page > 1): ?>
            <li class="page-item"><a class="page-link" href="?search=<?= urlencode($search) ?>&page=<?= $page-1 ?>">« Trước</a></li>
          <?php endif; ?>
          <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <li class="page-item <?= ($i==$page)?"active":"" ?>">
              <a class="page-link" href="?search=<?= urlencode($search) ?>&page=<?= $i ?>"><?= $i ?></a>
            </li>
          <?php endfor; ?>
          <?php if ($page < $total_pages): ?>
            <li class="page-item"><a class="page-link" href="?search=<?= urlencode($search) ?>&page=<?= $page+1 ?>">Sau »</a></li>
          <?php endif; ?>
        </ul>
      </nav>
    </main>
  </div>
</div>

<!-- Modal xác nhận xóa thẻ -->
<div class="modal fade" id="deleteTagModal" tabindex="-1" aria-labelledby="deleteTagModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="deleteTagModalLabel"><i class="fa fa-trash me-2"></i> Xác nhận xóa thẻ</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
      </div>
      <div class="modal-body">
        Bạn có chắc chắn muốn xóa thẻ này không?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
        <button type="button" class="btn btn-danger" id="confirmDeleteTag">Xóa</button>
      </div>
    </div>
  </div>
</div>
<script>
const deleteTagUrl = '<?= $deleteUrl ?>';  // Đảm bảo $deleteUrl đúng: '/delete_tag.php'
let tagIdToDelete = null;

document.addEventListener('DOMContentLoaded', function() {
    var deleteTagModal = document.getElementById('deleteTagModal');
    if (!deleteTagModal) {
        console.error('Modal deleteTagModal không tồn tại!');
        return;
    }

    deleteTagModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        if (!button) {
            console.error('Button trigger modal không tồn tại!');
            return;
        }
        tagIdToDelete = button.getAttribute('data-tag-id');
        console.log('Tag ID to delete:', tagIdToDelete);  // Debug: Kiểm tra ID
    });

    var confirmBtn = document.getElementById('confirmDeleteTag');
    if (!confirmBtn) {
        console.error('Button confirmDeleteTag không tồn tại!');
        return;
    }

    confirmBtn.onclick = function() {
        if (!tagIdToDelete) {
            alert('Lỗi: Không có ID tag để xóa!');
            return;
        }

        // Log URL để debug
        const fullUrl = window.location.origin + deleteTagUrl + '?id=' + encodeURIComponent(tagIdToDelete);
        console.log('Calling URL:', fullUrl);  // Debug: Xem URL gọi

        fetch(fullUrl, {
            method: 'GET',
            credentials: 'same-origin'
        })
        .then(resp => {
            if (!resp.ok) {
                throw new Error(`HTTP ${resp.status}: ${resp.statusText}`);
            }
            return resp.text();
        })
        .then(txt => {
            console.log('Response:', txt);  // Debug: Xem response
            if (txt.includes('thành công')) {
                alert('Xóa tag thành công!');  // Thêm alert để confirm (tùy chọn)
            }
            location.reload();  // Reload trang
        })
        .catch(err => {
            console.error('Fetch error:', err);  // Log lỗi chi tiết
            alert('Lỗi khi xóa tag: ' + err.message);  // Alert rõ ràng hơn
        });

        // Đóng modal ngay lập tức
        var modal = bootstrap.Modal.getInstance(deleteTagModal);
        if (modal) modal.hide();
    };
});
</script>
      </div>
    </main>
  </div>
  <?php include_once(__DIR__ . '/../layouts/partials/footer.php'); ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
