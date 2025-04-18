<?php

session_start();

include_once(__DIR__ . "/../../functions/functions.php");
include_once(base_path("functions/interface/shop/get_cart_items.php"));
include_once(base_path("functions/interface/shop/calculate_cart_subtotal.php"));

use Database\Database;
$db = new Database('orders');

$countries = $db->query('SELECT * FROM `countries` ORDER BY `name` ASC')->fetchAll();

if (!isset($_SESSION['items']) || sizeof($_SESSION['items']) == 0) {
    echo "no items in cart";
    exit();
}

$cart_items = getCartItems($_SESSION['items'], $db);
$subtotal = calculateCartSubtotal($cart_items);

echo $this->renderer->render('shop/checkout', ["cart_items"=>$cart_items, "subtotal"=>$subtotal, "countries"=>$countries, "stylesheets"=>["shop"]]);
