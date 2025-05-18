<?php

require(__DIR__ . "/../../../../functions/functions.php");
require(base_path("classes/Database.php"));

use Database\Database;
$db = new Database('orders');

// upload image
$target_dir = base_path("assets/images/shop/item_images/");
$target_file = $target_dir . basename($_FILES["image"]["name"]);
move_uploaded_file($_FILES["image"]["tmp_name"], $target_file);
$_POST['image'] = basename($_FILES["image"]["name"]);

$update = [];
$params = [];
foreach ($_POST as $field=>$value) {
    $params[$field] = $value;
    if ($field == 'item_id') continue;
    $update[] = $field;
}
$columns = "(" . implode(", ", $update) . ")";
$values = "(:" . implode(", :", $update) . ")";
$query = "INSERT INTO Items $columns VALUES $values";

$db->query($query, $params);

echo "<h1>New Item Added</h1>";