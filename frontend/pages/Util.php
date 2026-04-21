<?php
class Util
{
    public static function HmacSHA512($key, $inputData)
    {
        return hash_hmac('sha512', $inputData, $key);
    }

    public static function GetIpAddress()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }
}
?>
