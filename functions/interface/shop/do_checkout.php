<?php

session_start();

include("../../functions.php");
require(base_path("classes/Database.php"));
require(base_path("classes/SUCheckout.php"));
require(base_path("functions/interface/shop/get_cart_items.php"));
require(base_path("functions/interface/shop/calculate_cart_subtotal.php"));
require(base_path("functions/shop/insert_order_into_db.php"));

use Database\Database;
$db = new Database('orders');

$cc_no = str_replace(" ", "", $_POST['cc_number']);

$order_details = $_POST;

$order_details['items'] = getCartItems($_SESSION['items'], $db);
$order_details['totals']['subtotal'] = calculateCartSubtotal($order_details['items']);

$saved_order = insertOrderIntoDB($order_details, $db);

use SUCheckout\SUCheckout;
$checkout = new SUCheckout($saved_order);

p_2($checkout->createCheckout()->getResponse());
p_2($checkout->processCheckout()->retrieveCheckout()->getResponse());