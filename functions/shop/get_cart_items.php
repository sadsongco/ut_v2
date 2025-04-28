<?php

/**
 * Given a database connection, retrieve all items in the shopping cart.
 *
 * @param array $items array of cart item ids and option ids
 * @param Class $db
 * @return array       associative array of cart items
 */
function getCartItems($items, $db)
{
    $cart_items = [];
    foreach ($items AS $item) {
        if ($item['option_id']) {
            $query = "SELECT
                Items.*,
                Item_options.option_name,
                Item_options.option_price
            FROM Items
            JOIN Item_options ON Item_options.item_option_id = ?
            WHERE Items.item_id = ?";
            $params = [$item['option_id'], $item['item_id']];
        } else {
            $query = "SELECT * FROM Items WHERE item_id = ?";
            $params = [$item['item_id']];
        }
        $cart_item = $db->query($query, $params)->fetch();
        if ($cart_item['image'] == "") unset($cart_item['image']);
        $cart_item_option = isset($cart_item['option_name']) ? $cart_item['option_name'] : false;
        $cart_items[] = [...$cart_item, "quantity"=>$item['quantity'], "option"=>$cart_item_option]; // add quantity to $cart_item;
    }
    return $cart_items;
}