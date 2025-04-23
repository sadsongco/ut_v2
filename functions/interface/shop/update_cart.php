<?php

include_once(__DIR__ . "/../../functions.php");

session_start();
$cart_item_id = (int)$_POST['item_id'];

foreach($_SESSION['items'] AS $key=>$cart_item) {
    $item_id = (int)$cart_item['item_id'];
    if ($cart_item['item_id'] == $cart_item_id) {
        unset($_SESSION['items'][$key]);
    }
}

header("HX-Trigger: cartUpdated");
echo "Item removed from cart";