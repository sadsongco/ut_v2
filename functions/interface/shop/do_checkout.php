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

// load mustache template engine
require base_path('../lib/mustache.php-main/src/Mustache/Autoloader.php');
Mustache_Autoloader::register();
$m = new Mustache_Engine(array(
    'loader' => new Mustache_Loader_FilesystemLoader(base_path('views')),
    'partials_loader' => new Mustache_Loader_FilesystemLoader(base_path('views/partials'))
));

$cc_no = str_replace(" ", "", $_POST['cc_number']);


$order_details = $_POST;
$country_code = $db->query("SELECT country_code FROM Countries WHERE country_id = ?", [$_POST['billing-country']])->fetch();
$order_details['billing-country-code'] = $country_code['country_code'];

p_2($_SESSION['shipping_method']);

$order_details['items'] = getCartContents($db);
$order_details['totals']['subtotal'] = calculateCartSubtotal($order_details['items']);
$order_details['totals']['shipping'] = calculateShipping($db, $_SESSION['rm_zone'], $_SESSION['shipping_method']);
$order_details['totals']['total'] = $order_details['totals']['subtotal'] + $order_details['totals']['shipping'];
$order_details['totals']['vat'] = $order_details['totals']['total'] - ($order_details['totals']['total'] / 1.2);
$order_details['shipping_method'] = $_SESSION['shipping_method']['shipping_method_id'];

$saved_order = insertOrderIntoDB($order_details, $db);

use SUCheckout\SUCheckout;
$checkout = new SUCheckout($saved_order);

$checkout->createCheckout($order_details);

$response = $checkout->processCheckout()->retrieveCheckout()->getResponse();

$countries = $db->query('SELECT * FROM `Countries` ORDER BY `name` ASC')->fetchAll();

foreach($countries as &$country) {
    if ($country['country_id'] == $order_details['billing-country']) {
        $country['selected'] = ' selected';
    }
}

$transaction_response = $response->transactions[0];
if ($transaction_response->status == 'FAILED') {
    echo $m->render("partials/checkout_form", [
        "failed"=> true,
        "name"=>$order_details['name'],
        "email"=>$order_details['email'],
        "address1"=>$order_details['delivery-address1'],
        "address2"=>$order_details['delivery-address2'],
        "town"=>$order_details['delivery-town'],
        "postcode"=>$order_details['delivery-postcode'],
        "country"=>$order_details['delivery-country'],
        "billing-address1"=>$order_details['billing-address1'],
        "billing-address2"=>$order_details['billing-address2'],
        "billing-town"=>$order_details['billing-town'],
        "billing-postcode"=>$order_details['billing-postcode'],
        "billing-country"=>$order_details['billing-country'],
        "cc_name"=>$order_details['cc_name'],
        "cc_number"=>$order_details['cc_number'],
        "cc_exp_month"=>$order_details['cc_exp_month'],
        "cc_exp_year"=>$order_details['cc_exp_year'],
        "countries"=>$countries
    ]);
}

