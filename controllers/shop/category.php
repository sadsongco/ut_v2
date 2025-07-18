<?php

session_start();

include_once(__DIR__ . "/../../functions/functions.php");
include_once(base_path("functions/shop/get_featured.php"));
include_once(base_path("functions/shop/get_items.php"));
include_once(base_path("functions/shop/get_bundles.php"));
include_once(base_path("functions/shop/get_categories.php"));

use Database\Database;
$db = new Database('orders');

if ($paths[2] == "All") {
    $paths[2] = null;
}

$featured = getFeatured($db, $paths[2]);
$items = getItems($db, $paths[2]);
$bundles = getBundles($db, $paths[2]);

$categories = getCategories($db);

echo $this->renderer->render('shop/index', ["featured"=>$featured, "items"=>$items, "bundles"=>$bundles, "categories"=>$categories, "stylesheets"=>["shop"]]);