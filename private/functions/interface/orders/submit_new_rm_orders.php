<?php

include(__DIR__ . "/../../../../functions/functions.php");
include_once(__DIR__."/includes/order_includes.php");
include(base_path("classes/RoyalMail.php"));

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

use RoyalMail\RoyalMail;

switch ($_POST['order_zone']) {
    case 0:
        $country = "AND Customers.country = 31";
        break;
    case 1:
        $country = "AND Customers.country != 31";
        break;
    default:
        $country = "";
        break;
}

try {
    $query = "SELECT
        New_Orders.order_id,
        New_Orders.sumup_id,
        TRIM(New_Orders.shipping_method) AS shipping_method,
        New_Orders.shipping,
        New_Orders.subtotal,
        New_Orders.vat,
        New_Orders.total,
        New_Orders.order_date,
        Customers.name,
        Customers.address_1,
        Customers.address_2,
        Customers.city,
        Customers.postcode,
        Customers.country,
        Customers.email,
        Countries.rm_zone
    FROM New_Orders
    JOIN Customers ON New_Orders.customer_id = Customers.customer_id
        $country
    JOIN Countries ON Customers.country = Countries.country_id
    WHERE `rm_order_identifier` IS NULL
    ORDER BY New_Orders.order_date ASC
    LIMIT 2000";
    $orders = $db->query($query)->fetchAll();
} catch (PDOException $e) {
    echo $e->getMessage(); 
}

$ship_items = [];
foreach ($orders as &$order) {
    $order['country_code'] = $order['country'];
    $order['items'] = getOrderItems($order, $db); // gets all items in an order whether bundled or not
    $order['weight'] = 0;
    foreach ($order['items'] as &$item) {
        $item['weight'] = (int)$item['weight'];
        $item['weight'] *= 1000; // convert to grams from kg
        $order['weight'] += $item['weight'] * $item['quantity']; // total package weight
    }
    // $shipping = calculateShipping($db, $order['rm_zone'], ["shipping_method_id"=>$order['shipping_method']]);
    $rm = new RoyalMail($order['order_id'], $db);
    $rm->createRMOrder();
    $rm->submitRMOrder();
    $order_outcomes[] = $rm->getOrderOutcomes();
}
echo $m->render("orderOutcomes", ["outcomes"=>$order_outcomes]);


function getOrderItems($order, $db) {
    try {
        $query = "SELECT
            New_Order_items.order_price,
            New_Order_items.quantity,
            Items.*
        FROM New_Order_items
        JOIN Items ON New_Order_items.item_id = Items.item_id
        WHERE New_Order_items.order_id = ?";
        $params = [$order["order_id"]];
        return $db->query($query, $params)->fetchAll();
    } catch (PDOException $e) {
        echo $e->getMessage();
    }
}

function getCountryCode($country, $db) {
    $query = "SELECT country_code FROM Countries WHERE name = ?";
    $params = [$country];
    $result = $db->query($query, $params)->fetch();
    return $result['country_code'];
}