<?php

session_start();

include_once(__DIR__ . "/../../functions/functions.php");
include_once(base_path("functions/interface/shop/get_categories.php"));

use Database\Database;
$db = new Database('orders');

$query = "SELECT item_id, name, price, image
    FROM Items
    WHERE category = ?";

$res = $db->query($query, [$paths[2]])->fetchAll();

$categories = getCategories($db);

echo $this->renderer->render('shop/index', ["items"=>$res, "categories"=>$categories, "stylesheets"=>["shop"]]);