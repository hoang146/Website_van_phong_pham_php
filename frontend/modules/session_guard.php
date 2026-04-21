<?php
// Đã loại bỏ kiểm tra timeout và hủy session tự động
if (isset($_SESSION['user_id'])) {
    $_SESSION['last_activity'] = time();
}
?>
