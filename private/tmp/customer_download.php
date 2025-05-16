<?php

include("../../functions/functions.php");
require(base_path("../secure/env/config.php"));
require(base_path("classes/Database.php"));

use Database\Database;
$db = new Database("orders");

if (!isset($_GET['token'])) exit("No Token");

$id = decryptUniqueToken($_GET['token']);

if (!$id || !is_numeric($id)) exit("Invalid Token");

$query = "SELECT item_id FROM download_tokens WHERE download_token_id = ?";
$res = $db->query($query, [$id])->fetch();
$item_id = isset($res['item_id']) ? $res['item_id'] : false;
if (!$item_id) exit("Invalid Token");

$query = "SELECT download FROM Items WHERE item_id = ?";
$res = $db->query($query, [$item_id])->fetch();
$filename = isset($res['download']) ? $res['download'] : false;
if (!$filename) exit("Invalid Token");

$file_path = base_path(DOWNLOAD_PATH . $filename);

try {
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Cache-Control: private",false);
    header("Content-Type: " . mime_content_type($file_path));
    header("Content-Disposition: attachment; filename=".$filename);
    header("Content-Transfer-Encoding: binary");
    header("Content-Length: ".filesize($file_path));
    readfile($file_path);
}
catch (Exception $e) {
    error_log($e);
    exit("There was an error. Please try again or contact info@unbelievabletruth.co.uk");
}

$query = "DELETE FROM download_tokens WHERE download_token_id = ?";
$db->query($query, [$id]);

function decryptUniqueToken($token) {
    $encrypt_method = DOWNLOAD_CIPHER;
    $iv = substr(hash('sha256', DOWNLOAD_SALT), 0, 16);
    return openssl_decrypt(base64_decode($token), $encrypt_method, DOWNLOAD_SALT, 0, $iv);
}