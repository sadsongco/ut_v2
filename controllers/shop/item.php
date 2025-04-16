<?php

use Database\Database;
$db = new Database('orders');

$query = "SELECT *
    FROM Items
    WHERE item_id = ?";

$item = $db->query($query, [$paths[2]])->fetch();

$item_options = $db->query("SELECT * FROM Item_options WHERE item_id = ?", [$paths[2]])->fetchAll();

$item['option'] = sizeof($item_options) > 0 ? ['options'=>$item_options] : false;

echo $this->renderer->render('shop/item', ["item"=>$item, "stylesheets"=>["shop"]]);