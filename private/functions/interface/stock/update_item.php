<?php

require(__DIR__ . "/../../../../functions/functions.php");
require(base_path("classes/Database.php"));

use Database\Database;
$db = new Database('orders');

if (isset($_POST['update_item'])) {
    unset ($_POST['update_item']);
    $query = "UPDATE Items SET ";
    $update = [];
    $params = [];
    foreach ($_POST as $field=>$value) {
        $params[$field] = $value;
        if ($field == 'item_id') continue;
        $update[] = "$field = :$field";
    }
    $query .= implode(", ", $update);
    $query .= " WHERE item_id = :item_id";
    $db->query($query, $params);
}

if (isset($_POST['update_option'])) {
    unset ($_POST['update_option']);
    $query = "UPDATE Item_options SET option_stock = ? WHERE item_option_id = ?";
    $db->query($query, [$_POST['option_stock'], $_POST['item_option_id']]);
}

if (isset($_POST['add_new_option'])) {
    unset($_POST['add_new_option']);
    $query = "INSERT INTO Item_options VALUES (NULL, ?, ?, ?, ?)";
    $db->query($query, [$_POST['item_id'], $_POST['option_name'], $_POST['option_price'], $_POST['option_stock']]);
}

if (isset($_POST['delete_item'])) {
    $query = "SELECT image FROM Items WHERE item_id = ?";
    $image = $db->query($query, [$_POST['item_id']])->fetch();
    unlink(base_path("assets/images/shop/item_images/" . $image['image']));
    $query = "DELETE FROM Items WHERE item_id = ?";
    $db->query($query, [$_POST['item_id']]);
}

echo "<h1>Item Updated</h1>";