<?php
require_once __DIR__ . '/../../vendor/autoload.php';

$client = new Google_Client();
$client->setClientId('725962520097-0ijpa5nuc4d79i4qddrenhl2iqo6t6ee.apps.googleusercontent.com'); // Thay bằng client id của bạn
$client->setClientSecret('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'); // Thay bằng client secret của bạn
$client->setRedirectUri('http://localhost:3000/frontend/pages/google-callback.php');
$client->addScope('email');
$client->addScope('profile');

header('Location: ' . $client->createAuthUrl());
exit;
