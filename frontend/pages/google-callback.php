<?php
require_once __DIR__ . '/../../vendor/autoload.php';
session_start();

$client = new Google_Client();
$client->setClientId('725962520097-0ijpa5nuc4d79i4qddrenhl2iqo6t6ee.apps.googleusercontent.com'); // Thay bằng client id của bạn
include_once(__DIR__ . '/../../config/database.php');
$client->setClientSecret('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'); // Thay bằng client secret của bạn
$client->setRedirectUri('http://localhost:3000/frontend/pages/google-callback.php');

if (isset($_GET['code'])) {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    $client->setAccessToken($token['access_token']);

    $oauth = new Google_Service_Oauth2($client);
    $userInfo = $oauth->userinfo->get();

    // $userInfo->email, $userInfo->name, $userInfo->id
    // Kiểm tra email trong DB, nếu chưa có thì tạo user mới, nếu có thì đăng nhập
    // Ví dụ:
    $email = mysqli_real_escape_string($conn, $userInfo->email);
    $name  = mysqli_real_escape_string($conn, $userInfo->name);

    // Chỉ cho phép email @gmail.com
    if (!preg_match('/@gmail\.com$/', $email)) {
        echo "Chỉ chấp nhận email @gmail.com";
        exit;
    }

    // Kiểm tra user đã tồn tại chưa
    $check = "SELECT * FROM users WHERE email='$email' LIMIT 1";
    $result = mysqli_query($conn, $check);

    if (mysqli_num_rows($result) > 0) {
        // Đã có user, đăng nhập
        $user = mysqli_fetch_assoc($result);
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['username']  = $user['username'];
        $_SESSION['role']      = $user['role'];
    } else {
        // Tạo user mới
        $username = $name;
        $password = md5(uniqid()); // Tạo mật khẩu ngẫu nhiên
        $sql = "INSERT INTO users (username, email, password, role) VALUES ('$username', '$email', '$password', 'user')";
        if (mysqli_query($conn, $sql)) {
            $user_id = mysqli_insert_id($conn);
            $_SESSION['user_id']   = $user_id;
            $_SESSION['username']  = $username;
            $_SESSION['role']      = 'user';
        } else {
            echo "Lỗi tạo tài khoản: " . mysqli_error($conn);
            exit;
        }
    }
    // Chuyển hướng về trang chủ
    header('Location: ../../index.php');
    exit;
    // $_SESSION['user_id'] = ...;
    // header('Location: index.php');
}
