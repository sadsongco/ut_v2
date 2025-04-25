<?php

session_start();

include_once(__DIR__ . "/../../functions/functions.php");
include_once(base_path("functions/interface/shop/get_cart_items.php"));
include_once(base_path("functions/interface/shop/calculate_cart_subtotal.php"));
include_once(base_path("functions/interface/shop/calculate_shipping.php"));

use Database\Database;
if (!isset($db)) $db = new Database('orders');

$countries = $db->query('SELECT * FROM `countries` ORDER BY `name` ASC')->fetchAll();

if (!isset($_SESSION['items']) || sizeof($_SESSION['items']) == 0) {
    echo "no items in cart";
    exit();
}

$default_zone = 'UK';
$methods = getShippingMethods($default_zone, $db);
$default_method = $methods[0];

$cart_items = getCartItems($_SESSION['items'], $db);
$subtotal = calculateCartSubtotal($cart_items);
$shipping = calculateShipping($cart_items, $db, $default_zone, $default_method);
$_SESSION['shipping'] = round($shipping, 2);
$shipping_disp = number_format($shipping, 2);
$total = $subtotal + $shipping;
$total_disp = number_format($total, 2);

echo $this->renderer->render('shop/checkout', ["cart_items"=>$cart_items, "subtotal"=>$subtotal, "shipping"=>$shipping_disp, "total"=>$total_disp, "countries"=>$countries, "stylesheets"=>["shop"]]);
