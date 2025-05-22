<?php

include(__DIR__ . "/../../functions/functions.php");
include(base_path("classes/Database.php"));
include(base_path("functions/shop/create_rm_order.php"));
include(base_path("functions/shop/get_cart_contents.php"));

use Database\Database;
$db = new Database('orders');

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
        Customers.email
    FROM New_Orders
    LEFT JOIN Customers ON New_Orders.customer_id = Customers.customer_id
    WHERE `label_printed` = 0
    ORDER BY New_Orders.order_date ASC
    LIMIT 2000";

    $orders = $db->query($query)->fetchAll();
} catch (PDOException $e) {
    echo $e->getMessage(); 
}
p_2($orders);
exit();

$ship_items = [];
foreach ($orders as &$order) {
    $order['country_code'] = getCountryCode($order['country'], $db);
    $order['items'] = getOrderItems($order, $db);
    $order['weight'] = 0;
    foreach ($order['items'] as &$item) {
        $item['weight'] *= 1000; // convert to grams from kg
        $order['weight'] += $item['weight'] * $item['amount']; // total package weight
    }
    $cart_contents = getCartContents($db);
    $items = createRMOrder($order, $db);
    if (!$items) {
        echo "Order " . $order['order_id'] . " couldn't identify a shipping method.<br>";
        continue;
    }
    $ship_items[] = $items;
}

$data = [
    "items"=>[
        ...$ship_items
    ]
];

if (sizeof($data['items']) == 0) {
    echo "No orders to submit.<br>";
    exit();
}

$path = RM_BASE_URL."/orders";
// $path = RM_BASE_URL."/version";
$headers = [
    "Authorization: " . RM_API_KEY,
    "Content-Type: application/json"
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $path);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
// curl_setopt($ch, CURLOPT_POSTFIELDS, $rm_order);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);
$responseObj = json_decode($response);

$order_outcomes = [];

if (isset($responseObj->createdNew_Orders)) {
    foreach($responseObj->createdNew_Orders as $successful_order) {
        $query = "UPDATE `New_Orders`
        SET `label_printed` = 1,
        `rm_order_identifier` = ?,
        `rm_created` = ?
        WHERE `order_id` = ?";
        $stmt = $db->prepare($query);
        $params = [
            (int)$successful_order->orderIdentifier,
            $successful_order->createdOn,
            (int)$successful_order->orderReference,
        ];
        $stmt->execute($params);
        if ($stmt->rowCount() == 0) {
            $order_outcomes[] = "FAILED to update database for " . $successful_order->orderReference . " : " . $db->error;
            continue;
        }
        $order_outcomes[] = "Order id " . $successful_order->orderReference . " submitted to Royal Mail";
    }
}

if (isset($responseObj->failedNew_Orders)) {
    foreach($responseObj->failedNew_Orders as $failed_order) {
        $order_outcomes[] = "FAILED TO CREATE ORDER: " . $failed_order->errors[0]->errorMessage;
    };
}

echo $m->render("orderOutcomes", ["outcomes"=>$order_outcomes]);


function getOrderItems($order, $db) {

    try {
        $query = "SELECT
            Order_items.order_price,
            Order_items.amount,
            Items.*
        FROM Order_items
        JOIN Items ON Order_items.item_id = Items.item_id
        WHERE Order_items.order_id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$order["order_id"]]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo $e->getMessage();
    }
}

function getCountryCode($country, $db) {
    $query = "SELECT country_code FROM Countries WHERE name = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$country]);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $result[0]['country_code'];
}