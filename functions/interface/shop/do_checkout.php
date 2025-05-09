<?php

session_start();


include("../../functions.php");
require(base_path("classes/Database.php"));
require(base_path("classes/SUCheckout.php"));
require(base_path("functions/shop/get_cart_contents.php"));
require(base_path("functions/shop/calculate_cart_subtotal.php"));
require(base_path("functions/interface/shop/calculate_shipping.php"));
require(base_path("functions/shop/insert_order_into_db.php"));

use Database\Database;
$db = new Database('orders');

$cc_no = str_replace(" ", "", $_POST['cc_number']);

$order_details = $_POST;
$country_code = $db->query("SELECT country_code FROM Countries WHERE country_id = ?", [$_POST['billing-country']])->fetch();
$order_details['billing-country-code'] = $country_code['country_code'];

$order_details['items'] = getCartContents($db);
$order_details['totals']['subtotal'] = calculateCartSubtotal($order_details['items']);
$order_details['totals']['shipping'] = calculateShipping($db, $_SESSION['rm_zone'], $_SESSION['shipping_method']);
$order_details['totals']['total'] = $order_details['totals']['subtotal'] + $order_details['totals']['shipping'];
$order_details['totals']['vat'] = $order_details['totals']['total'] - ($order_details['totals']['total'] / 1.2);
$order_details['shipping_method'] = $_SESSION['shipping_method']['shipping_method_id'];

$saved_order = insertOrderIntoDB($order_details, $db);

use SUCheckout\SUCheckout;
$checkout = new SUCheckout($saved_order);

$checkout->createCheckout();
echo "Processed checkout:<br>";
// p_2($checkout->retrieveCheckout()->getResponse());
p_2($checkout->processCheckout()->retrieveCheckout()->getResponse());