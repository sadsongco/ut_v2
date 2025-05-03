<?php

session_start();

use Database\Database;
$db = new Database('orders');

include_once(__DIR__ . "/../../functions/functions.php");
include_once(base_path("functions/shop/get_categories.php"));

$query = "SELECT
    Items.item_id, Items.name, Items.price, Items.image, Items.featured,
    Items.stock + (SELECT IFNULL(SUM(Item_options.option_stock), 0) FROM Item_options WHERE Item_options.item_id = Items.item_id) AS stock
    FROM Items
    GROUP BY Items.item_id
    ORDER BY featured DESC";

$res = $db->query($query)->fetchAll();

$query = "SELECT `bundle_id`, `price`
    FROM `Bundles`
    WHERE `active` = 1";

$bundles = $db->query($query)->fetchAll();

foreach ($bundles as &$bundle) {
    $query = "SELECT
    Bundle_items.item_id,
    Items.item_id, Items.name, Items.price, Items.image, Items.featured,
    Items.stock + (SELECT IFNULL(SUM(Item_options.option_stock), 0) FROM Item_options WHERE Item_options.item_id = Items.item_id) AS stock
    FROM Bundle_items
    JOIN Items ON Bundle_items.item_id = Items.item_id
    WHERE Bundle_items.bundle_id = ?
    ";
    $bundle['items'] = $db->query($query, [$bundle['bundle_id']])->fetchAll();
    $bundle['raw_price'] = 0;
    foreach ($bundle['items'] as &$item) {
        $bundle['raw_price'] += $item['price'];
        $item_options = $db->query("SELECT * FROM Item_options WHERE item_id = ?", [$item['item_id']])->fetchAll();
        $item['option'] = sizeof($item_options) > 0 ? ['options'=>$item_options] : false;
    }
    $bundle['saving'] = $bundle['raw_price'] - $bundle['price'];
    $bundle['disp_price'] = number_format($bundle['price'], 2);
    $bundle['is_bundle'] = true;
}

$categories = getCategories($db);

echo $this->renderer->render('shop/index', ["items"=>$res, "bundles"=>$bundles,"categories"=>$categories, "stylesheets"=>["shop"]]);