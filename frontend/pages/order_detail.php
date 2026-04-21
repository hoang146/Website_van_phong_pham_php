<?php
session_start();
include_once(__DIR__ . '/../../config/database.php');

// Trang chi tiết hóa đơn
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$order_id = intval($_GET['order_id'] ?? 0);
$order_sql = "SELECT * FROM orders WHERE id = $order_id AND user_id = {$_SESSION['user_id']}";
$order = mysqli_fetch_assoc(mysqli_query($conn, $order_sql));
if (!$order) {
    echo '<div class="container mt-5"><div class="alert alert-danger">Không tìm thấy hóa đơn!</div></div>';
    exit;
}
$user = $order;
// Lấy thêm thông tin user
$user_sql = "SELECT * FROM users WHERE id = {$order['user_id']}";
$user_info = mysqli_fetch_assoc(mysqli_query($conn, $user_sql));
// Sửa lại truy vấn cho order_items
$details_sql = "SELECT oi.*, p.name FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = $order_id";
$details = mysqli_query($conn, $details_sql);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta charset="UTF-8">
    <title>Chi tiết đơn hàng #<?= $order_id ?> - VietOffice</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../../assets/custom/css/style.css">
    <style>
        .breadcrumb-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px 0;
            margin-bottom: 30px;
        }
        .breadcrumb-section .breadcrumb {
            background: transparent;
            margin: 0;
        }
        .breadcrumb-section .breadcrumb-item,
        .breadcrumb-section .breadcrumb-item a {
            color: white;
            font-weight: 500;
        }
        .breadcrumb-section .breadcrumb-item.active {
            color: rgba(255,255,255,0.8);
        }
        .invoice-card {
            background: white;
            border: none;
            border-radius: 20px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.1);
            overflow: hidden;
            animation: fadeIn 0.5s ease;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .invoice-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .invoice-header h2 {
            color: white;
            margin: 0;
            font-size: 1.8rem;
        }
        .info-box {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            height: 100%;
            border-left: 4px solid #667eea;
        }
        .info-box h5 {
            color: #667eea;
            font-weight: 700;
            margin-bottom: 15px;
        }
        .info-box p {
            margin-bottom: 8px;
            line-height: 1.6;
        }
        .product-row {
            transition: background 0.2s;
        }
        .product-row:hover {
            background: #f8f9fa;
        }
        .total-box {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 12px;
            padding: 20px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
<?php include_once(__DIR__ . '/../layouts/partials/header.php'); ?>

<!-- Breadcrumb -->
<section class="breadcrumb-section">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="../../index.php"><i class="fas fa-home me-1"></i>Trang chủ</a></li>
                <li class="breadcrumb-item"><a href="order_history.php">Lịch sử đơn hàng</a></li>
                <li class="breadcrumb-item active" aria-current="page">Chi tiết #<?= $order_id ?></li>
            </ol>
        </nav>
    </div>
</section>
<div class="container mb-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="invoice-card">
                <div class="invoice-header">
                    <i class="fas fa-file-invoice fa-3x mb-3"></i>
                    <h2>CHI TIẾT ĐỠN HÀNG</h2>
                    <p class="mb-0" style="font-size: 1.2rem;">#<?= $order_id ?></p>
                </div>
                <div class="card-body p-4">
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <div class="info-box">
                                <h5><i class="fas fa-user-circle me-2"></i>Thông tin người nhận</h5>
                                <p><i class="fas fa-user me-2 text-muted"></i><strong>Họ tên:</strong> <?= isset($order['shipping_name']) ? htmlspecialchars($order['shipping_name']) : htmlspecialchars($user_info['fullname']) ?></p>
                                <p><i class="fas fa-phone me-2 text-muted"></i><strong>Điện thoại:</strong> <?= isset($order['shipping_phone']) ? htmlspecialchars($order['shipping_phone']) : htmlspecialchars($user_info['phone']) ?></p>
                                <p><i class="fas fa-envelope me-2 text-muted"></i><strong>Email:</strong> <?= htmlspecialchars($user_info['email']) ?></p>
                                <p><i class="fas fa-map-marker-alt me-2 text-muted"></i><strong>Địa chỉ:</strong> <?= isset($order['shipping_address']) ? htmlspecialchars($order['shipping_address']) : htmlspecialchars($user_info['address']) ?></p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="info-box" style="border-left-color: #28a745;">
                                <h5 style="color: #28a745;"><i class="fas fa-info-circle me-2"></i>Thông tin đơn hàng</h5>
                                <p><i class="fas fa-hashtag me-2 text-muted"></i><strong>Mã đơn:</strong> #<?= $order_id ?></p>
                                <p><i class="fas fa-calendar me-2 text-muted"></i><strong>Ngày đặt:</strong> <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></p>
                                <p>
                                    <i class="fas fa-credit-card me-2 text-muted"></i><strong>Thanh toán:</strong> 
                                    <?php 
                                    $pm_labels = ['cod' => 'COD', 'bank' => 'Chuyển khoản', 'vnpay' => 'VNPay'];
                                    echo $pm_labels[$order['payment_method']] ?? htmlspecialchars($order['payment_method']);
                                    ?>
                                </p>
                                <p>
                                    <i class="fas fa-info-circle me-2 text-muted"></i><strong>Trạng thái:</strong><br>
                                    <?php
                                        $status = htmlspecialchars($order['status']);
                                        $status_config = [
                                            'pending' => ['class' => 'warning', 'icon' => 'clock', 'text' => 'Chờ xử lý'],
                                            'processing' => ['class' => 'info', 'icon' => 'spinner', 'text' => 'Đang xử lý'],
                                            'completed' => ['class' => 'success', 'icon' => 'check-circle', 'text' => 'Hoàn thành'],
                                            'cancelled' => ['class' => 'danger', 'icon' => 'times-circle', 'text' => 'Đã hủy']
                                        ];
                                        $config = $status_config[$status] ?? ['class' => 'secondary', 'icon' => 'info-circle', 'text' => ucfirst($status)];
                                    ?>
                                    <span class="badge bg-<?= $config['class'] ?> px-3 py-2 mt-1" style="font-size: 0.9rem;">
                                        <i class="fas fa-<?= $config['icon'] ?> me-1"></i><?= $config['text'] ?>
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                    <h5 class="fw-bold mb-3 mt-4"><i class="fas fa-box-open me-2 text-primary"></i>Danh sách sản phẩm</h5>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead style="background: #f8f9fa; border-bottom: 2px solid #667eea;">
                                <tr>
                                    <th><i class="fas fa-shopping-bag me-2"></i>Sản phẩm</th>
                                    <th width="120" class="text-center"><i class="fas fa-sort-numeric-up me-2"></i>Số lượng</th>
                                    <th width="150" class="text-center"><i class="fas fa-tag me-2"></i>Đơn giá</th>
                                    <th width="150" class="text-center"><i class="fas fa-calculator me-2"></i>Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $total = 0; while($d = mysqli_fetch_assoc($details)): $subtotal = $d['price'] * $d['quantity']; $total += $subtotal; ?>
                                <tr class="product-row">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-cube me-2 text-primary"></i>
                                            <span class="fw-semibold"><?= htmlspecialchars($d['name']) ?></span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark px-3 py-2"><?= $d['quantity'] ?></span>
                                    </td>
                                    <td class="text-center fw-semibold"><?= number_format($d['price']) ?>₫</td>
                                    <td class="text-center fw-bold text-success" style="font-size: 1.05rem;"><?= number_format($subtotal) ?>₫</td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="total-box">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <p class="mb-0"><i class="fas fa-info-circle text-primary me-2"></i>Phí vận chuyển: <strong class="text-success">Miễn phí</strong></p>
                            </div>
                            <div class="col-md-6 text-md-end mt-2 mt-md-0">
                                <h4 class="mb-0">Tổng cộng: <span class="text-danger" style="font-size: 1.8rem;"><?= number_format($total) ?>₫</span></h4>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 d-flex flex-wrap gap-3 justify-content-center">
                        <a href="order_history.php" class="btn btn-outline-secondary btn-lg px-4 py-2 rounded-3">
                            <i class="fas fa-arrow-left me-2"></i>Quay lại
                        </a>
                        <button onclick="window.print()" class="btn btn-outline-primary btn-lg px-4 py-2 rounded-3">
                            <i class="fas fa-print me-2"></i>In hóa đơn
                        </button>
                        <a href="products_list.php" class="btn btn-success btn-lg px-4 py-2 rounded-3" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); border: none;">
                            <i class="fas fa-shopping-cart me-2"></i>Tiếp tục mua sắm
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include_once(__DIR__ . '/../layouts/partials/footer.php'); ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
