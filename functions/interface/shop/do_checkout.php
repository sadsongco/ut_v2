<?php

session_start();

p_2($_SESSION);

include("../../functions.php");
require(base_path("classes/Database.php"));
require(base_path("classes/SUCheckout.php"));
require(base_path("functions/shop/get_cart_items.php"));
require(base_path("functions/shop/calculate_cart_subtotal.php"));
require(base_path("functions/interface/shop/calculate_shipping.php"));
require(base_path("functions/shop/insert_order_into_db.php"));

use Database\Database;
$db = new Database('orders');

$cc_no = str_replace(" ", "", $_POST['cc_number']);

$order_details = $_POST;

$order_details['items'] = getCartItems($_SESSION['items'], $db);
$order_details['totals']['subtotal'] = calculateCartSubtotal($order_details['items']);
$order_details['totals']['shipping'] = calculateShipping($order_details['items'], $db, $_SESSION['rm_zone'], $_SESSION['postage_method']);
$order_details['totals']['total'] = $order_details['totals']['subtotal'] + $order_details['totals']['shipping'];
$order_details['totals']['vat'] = $order_details['totals']['total'] - ($order_details['totals']['total'] / 1.2);

dd($order_details);

$saved_order = insertOrderIntoDB($order_details, $db);

use SUCheckout\SUCheckout;
$checkout = new SUCheckout($saved_order);

p_2($checkout->createCheckout()->getResponse());
p_2($checkout->processCheckout()->retrieveCheckout()->getResponse());