<?php

include_once(__DIR__ . "/../../../../functions/functions.php");
require(base_path("classes/Database.php"));
use Database\Database;
$db = new Database('admin');

include_once(base_path('../lib/mustache.php-main/src/Mustache/Autoloader.php'));
Mustache_Autoloader::register();

$m = new Mustache_Engine(array(
    'loader' => new Mustache_Loader_FilesystemLoader(base_path('private/views/mailout/')),
    'partials_loader' => new Mustache_Loader_FilesystemLoader(base_path('private/views/mailout/partials/'))
));

$filename = date("ymd");

echo $m->render("createMailout", ["filename"=>$filename]);