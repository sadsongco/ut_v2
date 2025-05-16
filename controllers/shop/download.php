<?php

include_once(__DIR__ . "/../../functions/functions.php");
include_once(base_path("functions/shop/get_categories.php"));
require(base_path("../secure/env/config.php"));
require(base_path("functions/utility/decrypt_token.php"));

use Database\Database;
$db = new Database('orders');

$download_token = isset($paths[2]) ? $paths[2] : false;

if (!$download_token) exit("No Token");

$id = decryptUniqueToken($download_token);
if (!$id || !is_numeric($id)) exit("Invalid Token");

$query = "SELECT download
    FROM Download_tokens
    JOIN Items ON Items.item_id = Download_tokens.item_id
    WHERE download_token_id = ?";
$item = $db->query($query, [$id])->fetch();
$filename = isset($item['download']) ? $item['download'] : false;
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
