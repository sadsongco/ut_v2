<?php

function getItems($db, $category=null)
{
    $where = "";
    $params = [];
    if ($category) {
        $where = "WHERE Items.category = ?";
        $params = [$category];
    }
    $query = "SELECT
    Items.item_id, Items.name, Items.price, Items.image, Items.featured,
    Items.stock + (SELECT IFNULL(SUM(Item_options.option_stock), 0) FROM Item_options WHERE Item_options.item_id = Items.item_id) AS stock
    FROM Items
    $where
    GROUP BY Items.item_id
    ORDER BY featured DESC";
    $items = $db->query($query, $params)->fetchAll();
    foreach ($items as &$item) {
        $item_options = $db->query("SELECT * FROM Item_options WHERE item_id = ? AND option_stock > 0", [$item['item_id']])->fetchAll();
        $item['option'] = sizeof($item_options) > 0 ? ['options'=>$item_options] : false;
    }
    return $items;
}