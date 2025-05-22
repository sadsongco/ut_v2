<?php

include(__DIR__ . "/../../../../../functions/functions.php");
include(base_path("classes/Database.php"));
include(base_path("../lib/mustache.php-main/src/Mustache/Autoloader.php"));

use Database\Database;

$db = new Database('orders');

Mustache_Autoloader::register();

$m = new Mustache_Engine(array(
    'loader' => new Mustache_Loader_FilesystemLoader(base_path('private/views/orders')),
    'partials_loader' => new Mustache_Loader_FilesystemLoader(base_path('private/views/orders/partials'))
));
