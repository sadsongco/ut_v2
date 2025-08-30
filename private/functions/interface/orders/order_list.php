<?php

require_once(__DIR__."/includes/order_includes.php");

$filter = $_POST['orderFilter'] ?? null;

$filter_text = "WHERE New_Orders.rm_tracking_number IS NULL";

switch ($filter) {
    case 'all':
        $filter_text = "";
        break;
    case 'failed':
        $filter_text = "WHERE New_Orders.transaction_id IS NULL";
        break;
    case 'dispatched':
        $filter_text = "WHERE New_Orders.rm_tracking_number IS NOT NULL";
        break;
    
    default:
        break;
}

try {
    $query = "SELECT
                    New_Orders.order_id,
                    CONCAT(DATE_FORMAT(New_Orders.order_date, '%y%m%d'), '-', New_Orders.order_id) AS disp_order_id,
                    New_Orders.transaction_id,
                    DATE_FORMAT(New_Orders.dispatched, '%e/%c/%y %k:%i') AS dispatched,
                    DATE_FORMAT(New_Orders.order_date, '%D %M %Y') AS order_date,
                    New_Orders.rm_tracking_number,
                    Customers.name,
                    Customers.address_1,
                    Customers.address_2,
                    Customers.city,
                    Customers.postcode,
                    Countries.name as country
                FROM New_Orders
                JOIN Customers ON New_Orders.customer_id = Customers.customer_id
                JOIN Countries ON Customers.country = Countries.country_id
                $filter_text
                ORDER BY New_Orders.order_date DESC
            ;";
    $result = $db->query($query)->fetchAll(PDO::FETCH_ASSOC);
    foreach ($result AS &$row) {
        $row["items"] = getItemData($row["order_id"], $db);
        $bundle_query = "SELECT
                            Bundles.bundle_id,
                            Order_bundles.quantity,
                            Order_bundles.order_bundle_id,
                            FORMAT(Bundles.price, 2) AS price,
                            FORMAT(Bundles.price * Order_bundles.quantity, 2) AS bundle_total
                            FROM Order_bundles
                            LEFT JOIN Bundles ON Order_bundles.bundle_id = Bundles.bundle_id
                            WHERE Order_bundles.order_id = ?;";
        $row["bundles"] = $db->query($bundle_query, [$row["order_id"]])->fetchAll();
        foreach ($row["bundles"] as &$bundle) {
            $bundle["items"] = getItemData($row["order_id"], $db, $bundle["order_bundle_id"]);
        }
    }
    
}

catch (PDOException $e) {
    echo $e->getMessage();
}

// p_2($result);

$params["orders"] = $result;
$params["filter_target"] = ".orderContainer";
$params["num_orders"] = sizeof($result);

echo $m->render("orderList", $params);

function getItemData($order_id, $db, $bundle_id = null) {
    $params = [];
    $cond = "IS NULL";
    $params[] = $order_id;
    if ($bundle_id) {
        $cond = "= ?";
        $params[] = $bundle_id;
    }
    $query = "SELECT
            Items.name,
            New_Order_items.quantity,
            FORMAT(New_Order_items.order_price, 2) AS price,
            FORMAT(New_Order_items.order_price * New_Order_items.quantity, 2) AS item_total
            FROM New_Order_items
            LEFT JOIN Items ON New_Order_items.item_id = Items.item_id
            WHERE New_Order_items.order_id = ?
            AND New_Order_items.order_bundle_id $cond;";
    return $db->query($query, $params)->fetchAll();
}
