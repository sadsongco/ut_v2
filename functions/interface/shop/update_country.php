<?php

session_start();

include_once(__DIR__ . "/../../functions.php");
include_once(base_path("functions/shop/get_shipping_methods.php"));
include_once(base_path("classes/Database.php"));

use Database\Database;
$db = new Database('orders');

// load mustache template engine
require base_path('../lib/mustache.php-main/src/Mustache/Autoloader.php');
Mustache_Autoloader::register();
$m = new Mustache_Engine(array(
    'loader' => new Mustache_Loader_FilesystemLoader(base_path('views')),
    'partials_loader' => new Mustache_Loader_FilesystemLoader(base_path('views/partials'))
));

$country = $db->query("SELECT rm_zone FROM countries WHERE country_id = ?", [$_POST['delivery-country']])->fetch();
$_SESSION['rm_zone'] = $country['rm_zone'];
$_SESSION['zone'] = $country['rm_zone'] == "UK" ? "UK" : "ROW";

$shipping_options = getShippingMethods($country['rm_zone'], $db);
$default_shipping_method = $shipping_options[0]['shipping_method_id'];
$_SESSION['shipping_method'] = $default_shipping_method;

header("HX-Trigger: shippingMethodUpdated");
echo $m->render('shop/shipping_method_select', ["shipping_options"=>$shipping_options, "default_shipping_method"=>$default_shipping_method]);
