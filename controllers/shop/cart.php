<?php

session_start();

use Database\Database;
$db = new Database('orders');

if (!isset($_SESSION['items']) || sizeof($_SESSION['items']) == 0) {
    echo "no items in cart";
    exit();
}
$cart_items = [];
foreach ($_SESSION['items'] AS $cart_item) {
    if ($cart_item['option_id']) {
        $query = "SELECT
            Items.*,
            Item_options.*
        FROM Items
        JOIN Item_options ON Item_options.item_option_id = ?
        WHERE Items.item_id = ?";
        $params = [$cart_item['option_id'], $cart_item['item_id']];
    } else {
        $query = "SELECT * FROM Items WHERE item_id = ?";
        $params = [$cart_item['item_id']];
    }

    $cart_items[] = $db->query($query, $params)->fetch();
}

p_2($cart_items);