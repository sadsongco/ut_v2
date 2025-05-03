<?php

function getCartBundles($bundles, $db) {
    $cart_bundles = [];
    foreach($bundles as $cart_bundle) {
        $query = "SELECT bundle_id, price FROM Bundles WHERE bundle_id = ?";
        $bundle = $db->query($query, [$cart_bundle['bundle_id']])->fetch();
        $query = "SELECT
        Items.item_id, Items.name, Items.image
        FROM Bundle_items
        JOIN Items ON Bundle_items.item_id = Items.item_id
        WHERE Bundle_items.bundle_id = ?";
        $bundle['items'] = $db->query($query, [$cart_bundle['bundle_id']])->fetchAll();
        $bundle['price'] = number_format($bundle['price'], 2);
        $cart_bundles[] = [...$bundle, "quantity"=>$cart_bundle['quantity']];
    }
    return $cart_bundles;
}