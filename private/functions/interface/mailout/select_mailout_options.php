<?php

include_once(__DIR__ . "/../../../../functions/functions.php");
require(base_path("classes/Database.php"));
use Database\Database;
$db = new Database('admin');

// Load Mustache
require_once(base_path('../lib/mustache.php-main/src/Mustache/Autoloader.php'));
Mustache_Autoloader::register();

$m = new Mustache_Engine(array(
    'loader' => new Mustache_Loader_FilesystemLoader(base_path('private/views/mailout/')),
    'partials_loader' => new Mustache_Loader_FilesystemLoader(base_path('private/views/mailout/partials/'))
));

try {
    $query = "SELECT id, DATE_FORMAT(date, '%Y%m%d') AS `date` FROM mailouts ORDER BY date DESC";
    $mailouts = $db->query($query)->fetchAll();
} catch (PDOException $e) {
    exit("Couldn't retrieve mailouts: ".$e->getMessage());
}

echo $m->render("selectMailoutOptions", ["options"=>$mailouts]);