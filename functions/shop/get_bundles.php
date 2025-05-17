<?php

function getBundles($db, $category=null)
{
    $where = "";
    $params = [];
    if ($category) {
        $where = "
        JOIN `Bundle_items` ON Bundle_items.bundle_id = Bundles.bundle_id
        JOIN `Items` ON Bundle_items.item_id = Items.item_id AND Items.category = ?";
        $params = [$category];
    }
    $query = "SELECT Bundles.bundle_id, Bundles.price
    FROM `Bundles`
    $where
    WHERE `active` = 1";

    $bundles = $db->query($query, $params)->fetchAll();
    foreach ($bundles as &$bundle) {
        $params = [$bundle['bundle_id']];
        $query = "SELECT
        Bundle_items.item_id,
        Items.item_id, Items.name, Items.price, Items.image, Items.featured,
        Items.stock + (SELECT IFNULL(SUM(Item_options.option_stock), 0) FROM Item_options WHERE Item_options.item_id = Items.item_id) AS stock
        FROM Bundle_items
        JOIN Items ON Bundle_items.item_id = Items.item_id
        WHERE Bundle_items.bundle_id = ?
        ";
        $bundle['items'] = $db->query($query, $params)->fetchAll();
        $bundle['raw_price'] = 0;
        foreach ($bundle['items'] as &$item) {
            $bundle['raw_price'] += $item['price'];
            $item_options = $db->query("SELECT * FROM Item_options WHERE item_id = ? AND option_stock > 0", [$item['item_id']])->fetchAll();
            $item['option'] = sizeof($item_options) > 0 ? ['options'=>$item_options] : false;
        }
        $bundle['saving'] = $bundle['raw_price'] - $bundle['price'];
        $bundle['disp_price'] = number_format($bundle['price'], 2);
        $bundle['is_bundle'] = true;
    }
    return $bundles;
}