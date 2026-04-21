<?php
session_start();
include_once(__DIR__ . '/../../config/database.php');
require_once(__DIR__ . '/PayLib.php');
require_once(__DIR__ . '/Util.php');

$vnp_TmnCode = "VVQPTZC4";
$vnp_HashSecret = "QQ1V5O6TS0CCRCWFKTQ21H6NUS5DEDFP";
$vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";

// Lấy thông tin từ session
$name = $_SESSION['checkout_name'] ?? '';
$phone = $_SESSION['checkout_phone'] ?? '';
$address = $_SESSION['checkout_address'] ?? '';
$selected_products = $_SESSION['checkout_products'] ?? [];
$cart = $_SESSION['cart'] ?? [];

// Tính tổng tiền
$total = 0;
foreach ($selected_products as $pid) {
    $pid = intval($pid);
    $quantity = $cart[$pid] ?? 1;
    $product_sql = "SELECT * FROM products WHERE id = $pid";
    $product = mysqli_fetch_assoc(mysqli_query($conn, $product_sql));
    if (!$product) continue;
    $price = (isset($product['price_sale']) && $product['price_sale'] > 0) ? $product['price_sale'] : $product['price'];
    $subtotal = $price * $quantity;
    $total += $subtotal;
}

// Tạo đối tượng PayLib
$pay = new PayLib();

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$baseUrl = $scheme . "://" . $host;
$vnp_Returnurl = "http://localhost:3000/frontend/pages/vnpay_return.php";

$txnRef = time(); // Sử dụng timestamp làm mã giao dịch tạm

$pay->AddRequestData("vnp_Version", "2.1.0");
$pay->AddRequestData("vnp_Command", "pay");
$pay->AddRequestData("vnp_TmnCode", $vnp_TmnCode);
$pay->AddRequestData("vnp_Amount", $total * 100);
$pay->AddRequestData("vnp_CreateDate", date('YmdHis'));
$pay->AddRequestData("vnp_CurrCode", "VND");
$pay->AddRequestData("vnp_IpAddr", Util::GetIpAddress());
$pay->AddRequestData("vnp_Locale", "vn");
$pay->AddRequestData("vnp_OrderInfo", "Thanh toán đơn hàng");
$pay->AddRequestData("vnp_OrderType", "billpayment");
$pay->AddRequestData("vnp_ReturnUrl", $vnp_Returnurl);
$pay->AddRequestData("vnp_TxnRef", $txnRef);

$paymentUrl = $pay->CreateRequestUrl($vnp_Url, $vnp_HashSecret);
file_put_contents(__DIR__ . '/vnpay_debug.log', "[".date('Y-m-d H:i:s')."] $paymentUrl\n", FILE_APPEND);
header('Location: ' . $paymentUrl);
exit;
?>
