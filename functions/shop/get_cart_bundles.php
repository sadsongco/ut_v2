<?php

function getCartBundles($bundles, $db) {
    $cart_bundles = [];
    foreach($bundles as $cart_bundle) {
        $query = "SELECT bundle_id, price FROM Bundles WHERE bundle_id = ?";
        $bundle = $db->query($query, [$cart_bundle['bundle_id']])->fetch();
        foreach($cart_bundle['items'] as &$item) {
            $query = "SELECT
            Items.item_id, Items.name, Items.image
            FROM Items
            WHERE Items.item_id = ?";
            $item_details = $db->query($query, [$item['item_id']])->fetch();
            if ($item['option_id']) {
                $query = "SELECT option_name FROM Item_options WHERE item_option_id = ?";
                $item_details['option_name'] =  $db->query($query, [$item['option_id']])->fetch()['option_name'];
                $item_details['option_id'] = $item['option_id'];
            }
            $bundle['items'][] = $item_details;
        }
        $bundle['price'] = number_format($bundle['price'], 2);
        $cart_bundles[] = [...$bundle, "quantity"=>$cart_bundle['quantity']];
    }
    return $cart_bundles;
}