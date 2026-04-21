<?php
session_start();
include_once(__DIR__ . '/../../config/database.php');

// Trang lịch sử mua hàng
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM orders WHERE user_id = $user_id ORDER BY created_at DESC";
$orders = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta charset="UTF-8">
    <title>Lịch sử đơn hàng - VietOffice</title>
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
        .order-card {
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
        .order-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px 30px;
            font-size: 1.5rem;
            font-weight: 700;
        }
        .order-row {
            transition: all 0.3s;
            border-left: 4px solid transparent;
        }
        .order-row:hover {
            background: #f8f9fa;
            border-left-color: #667eea;
            transform: translateX(5px);
        }
        .status-badge {
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.85rem;
        }
        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }
        .empty-state i {
            font-size: 4rem;
            color: #ddd;
            margin-bottom: 20px;
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
                <li class="breadcrumb-item"><a href="user_info.php">Tài khoản</a></li>
                <li class="breadcrumb-item active" aria-current="page">Lịch sử đơn hàng</li>
            </ol>
        </nav>
    </div>
</section>
<div class="container mb-5">
    <div class="row justify-content-center">
        <div class="col-lg-11">
            <div class="order-card">
                <div class="order-header">
                    <i class="fas fa-clipboard-list me-2"></i>Lịch sử đơn hàng
                </div>
                <div class="card-body p-4">
                    <?php if (mysqli_num_rows($orders) == 0): ?>
                        <div class="empty-state">
                            <i class="fas fa-shopping-bag"></i>
                            <h5>Chưa có đơn hàng nào</h5>
                            <p class="text-muted mb-4">Bạn chưa có đơn hàng nào. Hãy bắt đầu mua sắm ngay!</p>
                            <a href="products_list.php" class="btn btn-primary btn-lg">
                                <i class="fas fa-shopping-cart me-2"></i>Mua sắm ngay
                            </a>
                        </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead style="background: #f8f9fa; border-bottom: 2px solid #667eea;">
                                <tr>
                                    <th width="120"><i class="fas fa-hashtag me-2"></i>Mã đơn</th>
                                    <th width="150"><i class="fas fa-calendar me-2"></i>Ngày đặt</th>
                                    <th><i class="fas fa-credit-card me-2"></i>Thanh toán</th>
                                    <th width="150" class="text-center"><i class="fas fa-info-circle me-2"></i>Trạng thái</th>
                                    <th width="150" class="text-center"><i class="fas fa-money-bill-wave me-2"></i>Tổng tiền</th>
                                    <th width="120" class="text-center">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($order = mysqli_fetch_assoc($orders)): ?>
                                <tr class="order-row">
                                    <td>
                                        <span class="badge bg-light text-dark px-3 py-2" style="font-size: 1rem;">
                                            #<?= $order['id'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-semibold"><?= date('d/m/Y', strtotime($order['created_at'])) ?></div>
                                        <small class="text-muted"><?= date('H:i', strtotime($order['created_at'])) ?></small>
                                    </td>
                                    <td>
                                        <?php 
                                        $payment_icons = ['cod' => 'truck', 'bank' => 'university', 'vnpay' => 'credit-card'];
                                        $pm = $order['payment_method'] ?? 'cod';
                                        $pm_labels = ['cod' => 'COD', 'bank' => 'Chuyển khoản', 'vnpay' => 'VNPay'];
                                        ?>
                                        <i class="fas fa-<?= $payment_icons[$pm] ?? 'money-bill' ?> me-2 text-primary"></i>
                                        <?= $pm_labels[$pm] ?? htmlspecialchars($pm) ?>
                                    </td>
                                    <td class="text-center">
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
                                        <span class="badge bg-<?= $config['class'] ?> status-badge">
                                            <i class="fas fa-<?= $config['icon'] ?> me-1"></i><?= $config['text'] ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="fw-bold text-danger" style="font-size: 1.1rem;">
                                            <?php
                                            $total_sql = "SELECT SUM(price * quantity) as total FROM order_items WHERE order_id = {$order['id']}";
                                            $total = mysqli_fetch_assoc(mysqli_query($conn, $total_sql))['total'] ?? 0;
                                            echo number_format($total) . '₫';
                                            ?>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <a href="order_detail.php?order_id=<?= $order['id'] ?>" class="btn btn-primary btn-sm rounded-3">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include_once(__DIR__ . '/../layouts/partials/footer.php'); ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
