<?php
session_start();
include_once(__DIR__ . '/../../config/database.php');
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../frontend/pages/login.php");
    exit;
}
$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($order_id <= 0) {
    echo 'Hóa đơn không tồn tại.';
    exit;
}
$sql = "SELECT o.*, u.username, u.fullname FROM orders o LEFT JOIN users u ON o.user_id = u.id WHERE o.id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $order_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();
if (!$order) {
    echo 'Không tìm thấy hóa đơn.';
    exit;
}
$sql_items = "SELECT oi.*, p.name FROM order_items oi LEFT JOIN products p ON oi.product_id = p.id WHERE oi.order_id=?";
$stmt_items = $conn->prepare($sql_items);
$stmt_items->bind_param('i', $order_id);
$stmt_items->execute();
$result_items = $stmt_items->get_result();
function formatCurrency($number) {
    return number_format($number, 0, ",", ".") . " VNĐ";
}
$status_labels = [
    'pending' => 'Chờ xử lý',
    'processing' => 'Đang xử lý',
    'paid' => 'Đã thanh toán',
    'shipped' => 'Đã giao',
    'completed' => 'Hoàn tất',
    'cancelled' => 'Đã hủy'
];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Hóa đơn #<?= $order['id'] ?> - Văn Phòng Phẩm Online</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <style>
    @media print {
      body { background: #fff !important; }
      .no-print { display: none !important; }
      .print-container { box-shadow: none !important; }
    }
    @media screen {
      body { background: #e9ecef; padding: 20px; }
    }
    .print-container {
      background: white;
      max-width: 210mm;
      margin: 0 auto;
      padding: 20mm;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    .invoice-header {
      border-bottom: 3px solid #0d6efd;
      padding-bottom: 20px;
      margin-bottom: 30px;
    }
    .company-logo {
      font-size: 32px;
      color: #0d6efd;
      font-weight: bold;
    }
    .invoice-title {
      font-size: 28px;
      color: #0d6efd;
      font-weight: bold;
      margin-top: 10px;
    }
    .info-box {
      background: #f8f9fa;
      padding: 15px;
      border-radius: 8px;
      margin-bottom: 20px;
    }
    .info-label {
      font-weight: 600;
      color: #495057;
      min-width: 150px;
    }
    .table-invoice {
      margin-top: 20px;
    }
    .table-invoice thead {
      background: #0d6efd;
      color: white;
    }
    .table-invoice tbody tr:hover {
      background: #f8f9fa;
    }
    .total-row {
      background: #e7f3ff !important;
      font-weight: bold;
      font-size: 16px;
    }
    .invoice-footer {
      margin-top: 40px;
      padding-top: 20px;
      border-top: 2px solid #dee2e6;
      text-align: center;
      color: #6c757d;
    }
    .signature-section {
      margin-top: 60px;
      display: flex;
      justify-content: space-between;
    }
    .signature-box {
      text-align: center;
      width: 45%;
    }
    .status-badge {
      padding: 8px 16px;
      border-radius: 20px;
      font-weight: 600;
      display: inline-block;
    }
  </style>
</head>
<body>
  <div class="no-print mb-3 text-center">
    <button onclick="window.print()" class="btn btn-primary btn-lg me-2">
      <i class="fa fa-print me-2"></i> In hóa đơn
    </button>
    <button onclick="window.close()" class="btn btn-secondary btn-lg">
      <i class="fa fa-times me-2"></i> Đóng
    </button>
  </div>

  <div class="print-container">
    <!-- Header -->
    <div class="invoice-header">
      <div class="row align-items-center">
        <div class="col-8">
          <div class="company-logo">
            <i class="fa fa-store me-2"></i> VĂN PHÒNG PHẨM ONLINE
          </div>
          <div class="mt-2">
            <p class="mb-1"><i class="fa fa-map-marker-alt me-2"></i> Số 123, Đường ABC, Quận XYZ, TP. Hồ Chí Minh</p>
            <p class="mb-1"><i class="fa fa-phone me-2"></i> Hotline: 0123 456 789</p>
            <p class="mb-1"><i class="fa fa-globe me-2"></i> Website: vanphongpham.vn</p>
            <p class="mb-0"><i class="fa fa-envelope me-2"></i> Email: info@vanphongpham.vn</p>
          </div>
        </div>
        <div class="col-4 text-end">
          <div class="invoice-title">HÓA ĐƠN</div>
          <h4 class="text-muted">#<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?></h4>
          <p class="mb-0"><small>Ngày: <?= date('d/m/Y', strtotime($order['created_at'])) ?></small></p>
        </div>
      </div>
    </div>

    <!-- Thông tin khách hàng -->
    <div class="row mb-4">
      <div class="col-6">
        <h5 class="mb-3"><i class="fa fa-user me-2 text-primary"></i> Thông tin khách hàng</h5>
        <div class="info-box">
          <div class="d-flex mb-2">
            <span class="info-label">Họ tên:</span>
            <span class="flex-grow-1"><?= htmlspecialchars($order['shipping_name']) ?></span>
          </div>
          <div class="d-flex mb-2">
            <span class="info-label">Địa chỉ:</span>
            <span class="flex-grow-1"><?= htmlspecialchars($order['shipping_address']) ?></span>
          </div>
          <div class="d-flex">
            <span class="info-label">Số điện thoại:</span>
            <span class="flex-grow-1"><?= htmlspecialchars($order['shipping_phone']) ?></span>
          </div>
        </div>
      </div>
      <div class="col-6">
        <h5 class="mb-3"><i class="fa fa-info-circle me-2 text-primary"></i> Thông tin đơn hàng</h5>
        <div class="info-box">
          <div class="d-flex mb-2">
            <span class="info-label">Mã đơn hàng:</span>
            <span class="flex-grow-1 fw-bold">#<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?></span>
          </div>
          <div class="d-flex mb-2">
            <span class="info-label">Ngày đặt:</span>
            <span class="flex-grow-1"><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></span>
          </div>
          <div class="d-flex mb-2">
            <span class="info-label">Thanh toán:</span>
            <span class="flex-grow-1"><?= htmlspecialchars($order['payment_method'] ?? 'Tiền mặt') ?></span>
          </div>
          <div class="d-flex">
            <span class="info-label">Trạng thái:</span>
            <span class="flex-grow-1">
              <span class="status-badge bg-<?= 
                $order['status'] === 'completed' ? 'success' : 
                ($order['status'] === 'cancelled' ? 'danger' : 
                ($order['status'] === 'shipped' ? 'info' : 'warning')) 
              ?>">
                <?= $status_labels[$order['status']] ?? $order['status'] ?>
              </span>
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Chi tiết đơn hàng -->
    <h5 class="mb-3"><i class="fa fa-shopping-cart me-2 text-primary"></i> Chi tiết đơn hàng</h5>
    <table class="table table-bordered table-invoice">
      <thead>
        <tr>
          <th width="50" class="text-center">STT</th>
          <th>Tên sản phẩm</th>
          <th width="100" class="text-center">Số lượng</th>
          <th width="150" class="text-end">Đơn giá</th>
          <th width="150" class="text-end">Thành tiền</th>
        </tr>
      </thead>
      <tbody>
        <?php 
        $stt = 1;
        $subtotal_sum = 0;
        while($item = $result_items->fetch_assoc()): 
          $subtotal = $item['quantity'] * $item['price'];
          $subtotal_sum += $subtotal;
        ?>
        <tr>
          <td class="text-center"><?= $stt++ ?></td>
          <td><?= htmlspecialchars($item['name']) ?></td>
          <td class="text-center"><?= $item['quantity'] ?></td>
          <td class="text-end"><?= formatCurrency($item['price']) ?></td>
          <td class="text-end"><?= formatCurrency($subtotal) ?></td>
        </tr>
        <?php endwhile; ?>
      </tbody>
      <tfoot>
        <tr class="total-row">
          <td colspan="4" class="text-end fw-bold">TỔNG CỘNG:</td>
          <td class="text-end fw-bold fs-5"><?= formatCurrency($order['total']) ?></td>
        </tr>
      </tfoot>
    </table>

    <!-- Chữ ký -->
    <div class="signature-section">
      <div class="signature-box">
        <p class="fw-bold mb-5">Người mua hàng</p>
        <p class="text-muted"><i>(Ký và ghi rõ họ tên)</i></p>
      </div>
      <div class="signature-box">
        <p class="fw-bold mb-5">Người bán hàng</p>
        <p class="text-muted"><i>(Ký và ghi rõ họ tên)</i></p>
      </div>
    </div>

    <!-- Footer -->
    <div class="invoice-footer">
      <p class="mb-2 fw-bold text-primary">Cảm ơn quý khách đã mua hàng!</p>
      <p class="mb-0"><i>Đây là hóa đơn bán hàng được tạo tự động từ hệ thống</i></p>
      <p class="mb-0 mt-2"><small>In lúc: <?= date('d/m/Y H:i:s') ?></small></p>
    </div>
  </div>

  <script>
    // Tự động in khi load trang (có thể bỏ comment nếu muốn)
    // window.onload = function() { window.print(); }
  </script>
</body>
</html>
