<?php

use Database\Database;
$db = new Database('orders');

$query = "SELECT * FROM Items";
$items = $db->query($query)->fetchAll();

$query = "SELECT DISTINCT category FROM Items";
$categories = $db->query($query)->fetchAll();

foreach ($items as &$item) {
    $item['featured'] = $item['featured'] == 1 ? "checked" : null;
    $item['categories'] = $categories;
    foreach ($item['categories'] as &$category) {
        $category['selected'] = "";
        if ($category['category'] == $item['category']) {
            $category['selected'] = "selected";
        }
    }
}

echo $this->renderer->render('stock/index', ["items"=>$items, "stylesheets"=>["stock"]]);