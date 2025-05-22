<?php

require_once(__DIR__."/includes/order_includes.php");

$filter = "new";
if (isset($_POST['orderFilter'])) $filter = $_POST['orderFilter'];

$filter_text = "";
switch($filter) {
    case "new":
        $filter_text = " WHERE New_Orders.printed = 0 AND New_Orders.label_printed = 0 AND New_Orders.dispatched IS NULL ";
        break;
    case "printed":
        $filter_text = " WHERE New_Orders.printed = 1 AND New_Orders.label_printed = 0 AND New_Orders.dispatched IS NULL ";
        break;
    case "label printed":
        $filter_text = " WHERE New_Orders.printed = 1 AND New_Orders.label_printed = 1 AND New_Orders.dispatched IS NULL ";
        break;
    case "dispatched":
        $filter_text = " WHERE New_Orders.printed = 1 AND New_Orders.label_printed = 1 AND New_Orders.dispatched IS NOT NULL ";
        break;
    case "all":
        $filter_text = "";
        break;
}


if (isset($_POST['nameFilter'])) $filter_text = "WHERE name LIKE '%".$_POST['nameFilter']."%'";

try {
    $query = "SELECT
                    New_Orders.order_id,
                    New_Orders.sumup_id,
                    New_Orders.dispatched,
                    New_Orders.printed,
                    DATE_FORMAT(New_Orders.dispatched, '%e/%c/%y %k:%i') AS dispatched,
                    DATE_FORMAT(New_Orders.order_date, '%D %M %Y') AS order_date,
                    New_Orders.rm_tracking_number,
                    Customers.name,
                    Customers.address_1,
                    Customers.address_2,
                    Customers.city,
                    Customers.postcode,
                    Customers.country
                FROM New_Orders
                JOIN Customers ON New_Orders.customer_id = Customers.customer_id
                $filter_text
                ORDER BY New_Orders.sumup_id DESC
            ;";
    $result = $db->query($query)->fetchAll(PDO::FETCH_ASSOC);
    foreach ($result AS &$row) {
        $sub_query = "SELECT
                        Items.name,
                        New_Order_items.amount,
                        FORMAT(New_Order_items.order_price, 2) AS price,
						FORMAT(New_Order_items.order_price * New_Order_items.amount, 2) AS item_total
                        FROM New_Order_items
                        LEFT JOIN Items ON New_Order_items.item_id = Items.item_id
                        WHERE New_Order_items.order_id = ?;";
        $row["items"] = $db->query($sub_query, [$row["order_id"]])->fetchAll();
        $bundle_query = "SELECT
                            Bundles.name,
                            Order_bundles.amount,
                            FORMAT(Bundles.price, 2) AS price,
                            FORMAT(Bundles.price * Order_bundles.amount, 2) AS bundle_total
                            FROM Order_bundles
                            LEFT JOIN Bundles ON Order_bundles.bundle_id = Bundles.bundle_id
                            WHERE Order_bundles.order_id = ?;";
        $row["bundles"] = $db->query($bundle_query, [$row["order_id"]])->fetchAll();
    }
    
}

catch (PDOException $e) {
    echo $e->getMessage();
}

p_2($result);

$params["orders"] = $result;

echo $m->render("orderList", $params);
