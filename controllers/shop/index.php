<?php

session_start();

use Database\Database;
$db = new Database('orders');

include_once(__DIR__ . "/../../functions/functions.php");
include_once(base_path("functions/shop/get_items.php"));
include_once(base_path("functions/shop/get_bundles.php"));
include_once(base_path("functions/shop/get_categories.php"));

$items = getItems($db);
$bundles = getBundles($db);
$categories = getCategories($db);

echo $this->renderer->render('shop/index', ["items"=>$items, "bundles"=>$bundles,"categories"=>$categories, "stylesheets"=>["shop"]]);