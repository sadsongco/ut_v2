<?php

include("../../functions/functions.php");
require(base_path("../secure/env/config.php"));
require(base_path("classes/Database.php"));

use Database\Database;
$db = new Database("orders");

$order_no = 254;
$item_no = 7;

$query = "INSERT INTO Download_tokens (order_id, item_id) VALUES (?, ?)";
$db->query($query, [$order_no, $item_no]);
$token_id = $db->lastInsertId();

$download_token = createUniqueToken($token_id);

echo $download_token;

$decrypted = decryptUniqueToken($download_token);

echo $decrypted;

function createUniqueToken($input) {
    $output = false;
    $encrypt_method = DOWNLOAD_CIPHER;
    $iv = substr(hash('sha256', DOWNLOAD_SALT), 0, 16);
    $output = openssl_encrypt($input, $encrypt_method, DOWNLOAD_SALT, 0, $iv);
    $output = base64_encode($output);
    return $output;
}

function decryptUniqueToken($token) {
    $encrypt_method = DOWNLOAD_CIPHER;
    $iv = substr(hash('sha256', DOWNLOAD_SALT), 0, 16);
    return openssl_decrypt(base64_decode($token), $encrypt_method, DOWNLOAD_SALT, 0, $iv);
}