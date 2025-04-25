<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once(__DIR__ . "/../../functions.php");
include(base_path("functions/shop/get_shipping_methods.php"));
include_once(base_path("classes/Database.php"));

if (!isset($m)) {
    // load mustache for all controllers
    require base_path('../lib/mustache.php-main/src/Mustache/Autoloader.php');
    Mustache_Autoloader::register();
    $m = new Mustache_Engine(array(
        'loader' => new Mustache_Loader_FilesystemLoader(base_path('views')),
        'partials_loader' => new Mustache_Loader_FilesystemLoader(base_path('views/partials'))
    ));
}

use Database\Database;
$db = new Database('orders');

if (isset($_POST['country'])) {
    $query = "SELECT rm_zone FROM Countries WHERE country_id = ?";
    $result = $db->query($query, [$_POST['country']])->fetch();
    $zone = $result['rm_zone'];
    $_SESSION['rm_zone'] = $zone;
    $_SESSION['zone'] = $zone == "UK" ? "UK" : "ROW";
    $shipping_methods = getShippingMethods($result['rm_zone'], $db);
} else {
    $shipping_methods = getShippingMethods("UK", $db);
}

echo $m->render('shop/shipping_methods', ["shipping_methods"=>$shipping_methods]);
