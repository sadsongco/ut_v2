<?php

session_start();

use Database\Database;
$db = new Database('orders');

include_once(__DIR__ . "/../../functions/functions.php");
include_once(base_path("functions/interface/shop/get_categories.php"));

$query = "SELECT
    Items.item_id, Items.name, Items.price, Items.image, Items.featured,
    Items.stock + (SELECT IFNULL(SUM(Item_options.option_stock), 0) FROM Item_options WHERE Item_options.item_id = Items.item_id) AS stock
    FROM Items
    GROUP BY Items.item_id
    ORDER BY featured DESC";

$res = $db->query($query)->fetchAll();

$categories = getCategories($db);

echo $this->renderer->render('shop/index', ["items"=>$res, "categories"=>$categories, "stylesheets"=>["shop"]]);