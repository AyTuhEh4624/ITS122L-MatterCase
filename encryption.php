<?php

// Encryption key and method
$key = 'somebodyoncetoldmetheworldwasgonnarollmeiaintthesharpesttoolintheshed';
$method = 'AES-256-CBC';

function encryptData($data, $key, $method) {
    // Generate an initialization vector (IV)
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($method));

    // Encrypt the data
    $encryptedData = openssl_encrypt($data, $method, $key, 0, $iv);

    // Return the IV and encrypted data, encoded in base64
    return base64_encode($iv . $encryptedData);
}
?>