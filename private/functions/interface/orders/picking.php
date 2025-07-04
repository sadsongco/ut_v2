<?php

include_once(__DIR__."/includes/order_includes.php");

try {
    $query = "SELECT Items.name, SUM(New_Order_items.quantity) AS quantity
    FROM New_Order_items
        JOIN Items ON New_Order_items.item_id = Items.item_id
        JOIN New_Orders ON New_Order_items.order_id = New_Orders.order_id
        AND New_Orders.dispatched IS NULL
        AND New_Orders.transaction_id IS NOT NULL
    GROUP BY Items.item_id";
    $result = $db->query($query)->fetchAll();
} catch (PDOException $e) { 
    echo $e->getMessage();
}

echo $m->render("picking", ["items"=>$result]);