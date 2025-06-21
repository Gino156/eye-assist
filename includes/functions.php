<?php
function generateOTP($length = 6)
{
    return str_pad(random_int(0, 999999), $length, '0', STR_PAD_LEFT);
}

function encryptData($data)
{
    $key = "eyeassist2025key!!"; // 16/24/32 chars only
    $iv = openssl_random_pseudo_bytes(16);
    $ciphertext = openssl_encrypt($data, 'AES-256-CBC', $key, 0, $iv);
    return base64_encode($iv . $ciphertext);
}

function decryptData($encrypted)
{
    $key = "eyeassist2025key!!";
    $data = base64_decode($encrypted);
    $iv = substr($data, 0, 16);
    $ciphertext = substr($data, 16);
    return openssl_decrypt($ciphertext, 'AES-256-CBC', $key, 0, $iv);
}
