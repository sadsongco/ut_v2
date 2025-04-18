<?php

session_start();

include("../../functions.php");
require(base_path("classes/Database.php"));
require(base_path("../secure/env/config.php"));
require(base_path("functions/interface/shop/get_cart_items.php"));
require(base_path("functions/interface/shop/calculate_cart_subtotal.php"));
require(base_path("functions/shop/insert_order_into_db.php"));

use Database\Database;
$db = new Database('orders');

$cc_no = str_replace(" ", "", $_POST['cc-in']);

$order_details = $_POST;

$order_details['items'] = getCartItems($_SESSION['items'], $db);
$order_details['totals']['subtotal'] = calculateCartSubtotal($order_details['items']);

$missing_info = insertOrderIntoDB($order_details, $db);

dd($missing_info);

$headers = [
    "Authorization: Bearer " . SU_API_KEY,
    "Content-Type: application/json"
];

$trans_url = "https://api.sumup.com/v0.1/checkouts";
// $trans_url = "https://api.sumup.com/v0.1/me";

$post_body = json_encode([
    'checkout_reference' => 'AAA123',
    'merchant_code' => 'MDKGXUMF',
    'currency' => 'GBP',
    'amount' => $total
]);

$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_URL, $trans_url);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post_body);
$res = curl_exec($ch);
curl_close($ch);

$transactions = json_decode($res);

p_2($transactions);