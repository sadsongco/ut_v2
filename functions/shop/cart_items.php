<?php

session_start();

include_once(__DIR__ . "/../../functions/functions.php");
include_once(base_path("functions/interface/shop/get_cart_items.php"));
include_once(base_path("functions/interface/shop/calculate_cart_subtotal.php"));

require base_path('classes/Database.php');

use Database\Database;
$db = new Database('orders');

// load mustache for all controllers
require base_path('../lib/mustache.php-main/src/Mustache/Autoloader.php');
Mustache_Autoloader::register();
$m = new Mustache_Engine(array(
    'loader' => new Mustache_Loader_FilesystemLoader(base_path('views')),
    'partials_loader' => new Mustache_Loader_FilesystemLoader(base_path('views/partials'))
));

$checkout = false;
if (isset($_GET['checkout'])) $checkout = true;

$cart_items = getCartItems($_SESSION['items'], $db);
$subtotal = calculateCartSubtotal($cart_items);

echo $m->render('shop/cart_items', ["cart_items"=>$cart_items, "checkout"=>$checkout, "subtotal"=>$subtotal]);