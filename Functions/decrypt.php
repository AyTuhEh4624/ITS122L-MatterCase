<?php
// Define the encryption key and method as variables
$key = 'somebodyoncetoldmetheworldwasgonnarollmeiaintthesharpesttoolintheshed';
$method = 'AES-256-CBC';

function decryptData($encryptedData, $key, $method) {
    // Decode the base64 encoded data
    $data = base64_decode($encryptedData);
    
    // Extract the IV from the data
    $ivLength = openssl_cipher_iv_length($method);
    $iv = substr($data, 0, $ivLength);
    
    // Extract the encrypted data
    $encrypted = substr($data, $ivLength);
    
    // Decrypt the data
    $decrypted = openssl_decrypt($encrypted, $method, $key, 0, $iv);
    
    return $decrypted;
}
?>