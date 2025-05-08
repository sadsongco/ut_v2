<?php

require(__DIR__ . "/../../../../functions/functions.php");
require(base_path("classes/Database.php"));

use Database\Database;
$db = new Database('orders');

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

echo "Item Updated";