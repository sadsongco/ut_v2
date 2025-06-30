<?php

function getItemData($item, $item_details, $db)
{
        if (isset($item['option_id']) && $item['option_id']) {
            $query = "SELECT
                $item_details,
                Item_options.option_stock as stock,
                Item_options.item_option_id as option_id,
                Item_options.option_name,
                Item_options.option_price,
                Item_options.option_weight,
                Items.release_date,
                Items.e_delivery,
                Items.packaging_classification
            FROM Items
            JOIN Item_options ON Item_options.item_option_id = ?
            WHERE Items.item_id = ?";
            $params = [$item['option_id'], $item['item_id']];
        } else {
            $query = "SELECT $item_details FROM Items WHERE item_id = ?";
            $params = [$item['item_id']];
        }
        $cart_item = $db->query($query, $params)->fetch();
        if (isset($cart_item['option_price']) && $cart_item['option_price']) $cart_item['price'] = number_format($cart_item['option_price'], 2);
        if ($cart_item['image'] == "") unset($cart_item['image']);
        else $cart_item['image_path'] = "/serve/" . SHOP_ASSET_PATH . "images/" . str_replace(".", "/", $cart_item['image']);

        return [...$cart_item, "quantity"=>$item['quantity']]; // add quantity to $cart_item;
}