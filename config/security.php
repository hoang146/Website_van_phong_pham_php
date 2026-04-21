<?php
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) ? 1 : 0);
    ini_set('session.cookie_samesite', 'Strict');
    session_start();
}

function verifyCaptcha($secret, $response) {
    if (empty($response)) return false;

    $data = [
        'secret'   => $secret,
        'response' => $response,
        'remoteip' => $_SERVER['REMOTE_ADDR'] ?? null
    ];

    $options = [
        'http' => [
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($data),
            'timeout' => 5
        ]
    ];

    $context  = stream_context_create($options);
    $result   = @file_get_contents("https://www.google.com/recaptcha/api/siteverify", false, $context);

    if ($result === false) {
        return (object)['success' => false, 'score' => 0, 'error-codes' => ['no_response']];
    }

    return json_decode($result);
}
