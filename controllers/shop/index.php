<?php
use Database\Database;
$db = new Database('orders');

$query = "SELECT
    Items.item_id, Items.name, Items.price, Items.image,
    Items.stock + (SELECT IFNULL(SUM(Item_options.option_stock), 0) FROM Item_options WHERE Item_options.item_id = Items.item_id) AS stock
    FROM Items
    GROUP BY Items.item_id";

$res = $db->query($query)->fetchAll();

echo $this->renderer->render('shop/index', ["items"=>$res, "stylesheets"=>["shop"]]);