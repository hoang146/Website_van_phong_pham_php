<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

function send_order_confirmation_email($to, $orderId, $orderDetails, $total, $orderUrl, $plainBody = null, $attachments = [])
{
    $autoload = __DIR__ . '/../../vendor/autoload.php';
    if (file_exists($autoload)) require_once $autoload;

    $const_file = __DIR__ . '/../../config/constants.php';
    if (file_exists($const_file)) include_once $const_file;

    $fromEmail = defined('MAIL_FROM_EMAIL') ? MAIL_FROM_EMAIL : 'dhpdh146@gmail.com';
    $fromName  = defined('MAIL_FROM_NAME') ? MAIL_FROM_NAME : 'Văn Phòng Phẩm Online';
    // $shopLogoUrl = 'https://yourdomain.com/assets/img/logo.jpg'; // Logo đã bỏ

    if (class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
        $mail = new PHPMailer(true);
        try {
            $mail->CharSet = 'UTF-8'; // Đảm bảo tiếng Việt
            if (defined('MAIL_SMTP_HOST') && MAIL_SMTP_HOST) {
                $mail->isSMTP();
                $mail->Host = MAIL_SMTP_HOST;
                $mail->SMTPAuth = true;
                $mail->Username = MAIL_SMTP_USER;
                $mail->Password = MAIL_SMTP_PASS;
                $mail->SMTPSecure = MAIL_SMTP_SECURE;
                $mail->Port = MAIL_SMTP_PORT;
            }

            $mail->setFrom($fromEmail, $fromName);
            $mail->addAddress($to);

            if (defined('MAIL_ADMIN_EMAIL') && MAIL_ADMIN_EMAIL) {
                $mail->addBCC(MAIL_ADMIN_EMAIL);
            }

                        $mail->isHTML(true);
                        $mail->Subject = '✅ Xác nhận đơn hàng #' . $orderId . ' - Văn Phòng Phẩm Online';
                        
                        // Tạo bảng sản phẩm với styling hiện đại
                        $productRows = '';
                        foreach ($orderDetails as $item) {
                                $productRows .= '<tr>
                                        <td style="padding:12px 16px;border-bottom:1px solid #eaeaea;">
                                            <span style="font-weight:600;color:#2c3e50;">'.htmlspecialchars($item['name']).'</span>
                                        </td>
                                        <td style="padding:12px 16px;border-bottom:1px solid #eaeaea;text-align:center;">
                                            <span style="background:#667eea;color:#fff;padding:4px 12px;border-radius:6px;font-weight:600;">'.$item['quantity'].'</span>
                                        </td>
                                        <td style="padding:12px 16px;border-bottom:1px solid #eaeaea;text-align:right;color:#6c757d;">'.number_format($item['price']).'₫</td>
                                        <td style="padding:12px 16px;border-bottom:1px solid #eaeaea;text-align:right;">
                                            <span style="font-weight:700;color:#28a745;">'.number_format($item['price'] * $item['quantity']).'₫</span>
                                        </td>
                                </tr>';
                        }
                        
                        $mail->Body = '
                        <!DOCTYPE html>
                        <html>
                        <head>
                            <meta charset="UTF-8">
                            <meta name="viewport" content="width=device-width, initial-scale=1.0">
                        </head>
                        <body style="margin:0;padding:0;font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,\'Helvetica Neue\',Arial,sans-serif;background:#f4f7fa;">
                            <table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f7fa;padding:40px 20px;">
                                <tr>
                                    <td align="center">
                                        <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,0.08);">
                                            <!-- Header with Gradient -->
                                            <tr>
                                                <td style="background:linear-gradient(135deg, #667eea 0%, #764ba2 100%);padding:40px 30px;text-align:center;">
                                                    <div style="background:rgba(255,255,255,0.2);width:80px;height:80px;margin:0 auto 20px;border-radius:50%;display:flex;align-items:center;justify-content:center;">
                                                        <span style="font-size:48px;color:#fff;">✓</span>
                                                    </div>
                                                    <h1 style="margin:0;color:#ffffff;font-size:28px;font-weight:700;">Đơn hàng đã được xác nhận!</h1>
                                                    <p style="margin:10px 0 0;color:rgba(255,255,255,0.95);font-size:16px;">Cảm ơn bạn đã tin tưởng Văn Phòng Phẩm Online</p>
                                                </td>
                                            </tr>
                                            
                                            <!-- Order Info -->
                                            <tr>
                                                <td style="padding:30px;">
                                                    <div style="background:linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);border-left:4px solid #667eea;border-radius:8px;padding:20px;margin-bottom:30px;">
                                                        <h2 style="margin:0 0 15px;color:#2c3e50;font-size:18px;">📝 Thông tin đơn hàng #' . $orderId . '</h2>
                                                        <table width="100%" cellpadding="8" cellspacing="0" style="font-size:14px;">
                                                            <tr>
                                                                <td width="40%" style="color:#6c757d;padding:8px 0;"><strong>👤 Họ tên:</strong></td>
                                                                <td style="color:#2c3e50;padding:8px 0;">' . htmlspecialchars($GLOBALS['name']) . '</td>
                                                            </tr>
                                                            <tr>
                                                                <td style="color:#6c757d;padding:8px 0;"><strong>📞 Số điện thoại:</strong></td>
                                                                <td style="color:#2c3e50;padding:8px 0;">' . htmlspecialchars($GLOBALS['phone']) . '</td>
                                                            </tr>
                                                            <tr>
                                                                <td style="color:#6c757d;padding:8px 0;"><strong>📍 Địa chỉ:</strong></td>
                                                                <td style="color:#2c3e50;padding:8px 0;">' . htmlspecialchars($GLOBALS['address']) . '</td>
                                                            </tr>
                                                            <tr>
                                                                <td style="color:#6c757d;padding:8px 0;"><strong>💳 Thanh toán:</strong></td>
                                                                <td style="color:#2c3e50;padding:8px 0;">' . htmlspecialchars($GLOBALS['payment_method']) . '</td>
                                                            </tr>
                                                            <tr>
                                                                <td style="color:#6c757d;padding:8px 0;"><strong>📅 Ngày đặt:</strong></td>
                                                                <td style="color:#2c3e50;padding:8px 0;">' . date('d/m/Y H:i') . '</td>
                                                            </tr>
                                                        </table>
                                                    </div>
                                                    
                                                    <!-- Products Table -->
                                                    <h3 style="margin:0 0 15px;color:#2c3e50;font-size:18px;">📦 Chi tiết sản phẩm</h3>
                                                    <table width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #eaeaea;border-radius:8px;overflow:hidden;">
                                                        <thead>
                                                            <tr style="background:linear-gradient(135deg, #2c3e50 0%, #34495e 100%);">
                                                                <th style="padding:12px 16px;text-align:left;color:#ffffff;font-size:14px;font-weight:600;">Sản phẩm</th>
                                                                <th style="padding:12px 16px;text-align:center;color:#ffffff;font-size:14px;font-weight:600;">SL</th>
                                                                <th style="padding:12px 16px;text-align:right;color:#ffffff;font-size:14px;font-weight:600;">Đơn giá</th>
                                                                <th style="padding:12px 16px;text-align:right;color:#ffffff;font-size:14px;font-weight:600;">Thành tiền</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>' . $productRows . '</tbody>
                                                        <tfoot>
                                                            <tr style="background:#f8f9fa;">
                                                                <td colspan="3" style="padding:16px;text-align:right;font-weight:700;color:#2c3e50;font-size:16px;">Tổng cộng:</td>
                                                                <td style="padding:16px;text-align:right;font-weight:700;color:#28a745;font-size:18px;">' . number_format($total) . '₫</td>
                                                            </tr>
                                                        </tfoot>
                                                    </table>
                                                    
                                                    <!-- CTA Button -->
                                                    <div style="text-align:center;margin:30px 0;">
                                                        <a href="' . $orderUrl . '" style="display:inline-block;background:linear-gradient(135deg, #667eea 0%, #764ba2 100%);color:#ffffff;padding:14px 40px;border-radius:10px;text-decoration:none;font-weight:700;font-size:16px;box-shadow:0 4px 12px rgba(102,126,234,0.3);">
                                                            Xem chi tiết đơn hàng →
                                                        </a>
                                                    </div>
                                                    
                                                    <!-- Support Info -->
                                                    <div style="background:#f8f9fa;border-radius:8px;padding:20px;margin-top:30px;text-align:center;">
                                                        <p style="margin:0 0 10px;color:#6c757d;font-size:14px;">👨‍💻 Cần hỗ trợ? Liên hệ với chúng tôi:</p>
                                                        <p style="margin:0;">
                                                            <a href="tel:0123456789" style="color:#667eea;text-decoration:none;font-weight:600;margin:0 10px;">📞 0123 456 789</a>
                                                            <a href="mailto:support@vpponline.vn" style="color:#667eea;text-decoration:none;font-weight:600;margin:0 10px;">✉️ support@vpponline.vn</a>
                                                        </p>
                                                    </div>
                                                </td>
                                            </tr>
                                            
                                            <!-- Footer -->
                                            <tr>
                                                <td style="background:linear-gradient(135deg, #2c3e50 0%, #34495e 100%);padding:20px 30px;text-align:center;">
                                                    <p style="margin:0;color:rgba(255,255,255,0.8);font-size:13px;">
                                                        © ' . date('Y') . ' ' . htmlspecialchars($fromName, ENT_QUOTES, 'UTF-8') . '. All rights reserved.
                                                    </p>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </body>
                        </html>
                        ';
                        $mail->AltBody = $plainBody ?? 'Đơn hàng #' . $orderId . ' tại ' . $fromName . '. Tổng cộng: ' . number_format($total) . '₫.';

            foreach ($attachments as $att) {
                if (is_string($att) && file_exists($att)) $mail->addAttachment($att);
            }

            $mail->send();
            return ['success' => true];
        } catch (Exception $e) {
            file_put_contents(__DIR__ . '/../../logs/email_errors.log', date('c') . " - " . ($mail->ErrorInfo ?: $e->getMessage()) . "\n", FILE_APPEND);
            return ['success' => false, 'error' => $mail->ErrorInfo ?: $e->getMessage()];
        }
    }

    // Fallback: PHP mail()
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8\r\n";
    $headers .= "From: {$fromName} <{$fromEmail}>\r\n";
    $success = mail($to, $subject, $htmlBody, $headers);
    if ($success) return ['success' => true];
    file_put_contents(__DIR__ . '/../../logs/email_errors.log', date('c') . " - Mail() gửi thất bại cho {$to}\n", FILE_APPEND);
    return ['success' => false, 'error' => 'mail() returned false'];
}
