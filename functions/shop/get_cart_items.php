<?php

/**
 * Given a database connection, retrieve all items in the shopping cart.
 *
 * @param array $items array of cart item ids and option ids
 * @param Class $db
 * @return array       associative array of cart items
 */
function getCartItems($items, $db, $details=true)
{
    $item_details = $details ? "Items.*" : "Items.item_id, Items.image, Items.price";
    $cart_items = [];
    foreach ($items AS $item) {
        if ($item['option_id']) {
            $query = "SELECT
                $item_details,
                Item_options.item_option_id,
                Item_options.option_name,
                Item_options.option_price,
                Items.release_date,
                Items.e_delivery
            FROM Items
            JOIN Item_options ON Item_options.item_option_id = ?
            WHERE Items.item_id = ?";
            $params = [$item['option_id'], $item['item_id']];
        } else {
            $query = "SELECT $item_details FROM Items WHERE item_id = ?";
            $params = [$item['item_id']];
        }
        $cart_item = $db->query($query, $params)->fetch();
        if ($cart_item['image'] == "") unset($cart_item['image']);
        $cart_item_option = isset($cart_item['option_name']) ? $cart_item['option_name'] : false;
        $cart_items[] = [...$cart_item, "quantity"=>$item['quantity'], "option"=>$cart_item_option]; // add quantity to $cart_item;
    }
    return $cart_items;
}