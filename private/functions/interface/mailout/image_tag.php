<?php

include_once(__DIR__ . "/../../../../functions/functions.php");
require(base_path("../secure/env/config.php"));
require_once(base_path("classes/Database.php"));
require_once('includes/mailout_includes.php');
require_once('includes/mailout_create.php');
require_once(base_path('../lib/mustache.php-main/src/Mustache/Autoloader.php'));
Mustache_Autoloader::register();
if (!isset($m)) {
    $m = new Mustache_Engine(array(
        'loader' => new Mustache_Loader_FilesystemLoader(base_path('private/views/mailout/')),
        'partials_loader' => new Mustache_Loader_FilesystemLoader(base_path('private/views/mailout/partials/'))
    ));
}

use Database\Database;
if (!isset($db)) $db = new Database('admin');

try {
    $query = "SELECT * FROM MailoutImages WHERE img_id = ?";
    $result = $db->query($query, [$_GET['img']])->fetchAll();
}
catch (PDOException $e) {
    exit("Problem retrieving mailout images: ".$e->getMessage());
}

$result[0]['tag'] = "<!--{{i::".$result[0]['img_id']."}}-->";
$result[0]['path'] = getHost()."/".MAILOUT_IMAGE_PATH.$result[0]['url'];

echo $m->render('existingImage', $result[0]);