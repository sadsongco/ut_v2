<?php

include_once(__DIR__."/includes/order_includes.php");
require (base_path("/functions/utility/send_customer_email.php"));
//Load Composer's autoloader
require base_path('../lib/vendor/autoload.php');
require base_path('functions/shop/get_item_data.php');
require base_path('functions/utility/create_unique_token.php');

//Import PHPMailer classes into the global namespace
//These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$unsent_orders = getUnsentNew_Orders($db);
if (empty($unsent_orders)) {
    exit("No orders to update.");
}

$unsent_orders_arr = [];
echo "Querying Royal Mail for order nos: <br>";
foreach ($unsent_orders as $unsent_order) {
    echo $unsent_order["rm_order_identifier"] . "<br>";
    $unsent_orders_arr[] = $unsent_order["rm_order_identifier"];
}

$unsent_orders_string = implode(";", $unsent_orders_arr) . "/";

$url = $path = RM_BASE_URL."/orders/" . $unsent_orders_string;

$headers = [
    "Authorization: " . RM_API_KEY,
    "Content-Type: application/json"
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $path);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);
$responseObj = json_decode($response);
$output = '<div class="pickingList">';

foreach ($responseObj as $order) {
    $output .= '<div class="pickingListRow">';
    if (isset($order->code)) {
        $output .= "Error: " . $order->code . ": " . $order->message . "<br>";
        $output .= '</div>';
        continue;
    }
    if (!isset($order->trackingNumber)) {
        $output .= "No tracking number for order " . $order->orderReference . "<br>";
        $output .= '</div>';
        continue;
    }
    if (!isset($order->orderReference)) {
        $output .= "No order reference for royal mail id " . $order->orderIdentifier . "<br>";
        $output .= '</div>';
        continue;
    }
    $shippedOn = isset($order->shippedOn) ? $order->shippedOn : NULL;
    if (!$shippedOn) {
        $output .= "Order " . $order->orderReference . " not marked dispatched on Royal Mail portal.<br>";
        $output .= '</div>';
        continue;
    }

    try {
        updateOrderWithRMData($shippedOn, $order, $db);
        $order_array = createOrderArray($order->orderReference, $db);
        $order_array['items'] = getOrderItems($order_array, $db);
        $shipping_items = [];
        $download_items = [];
        $preorder_items = [];
        $shipping_all = false;
        foreach ($order_array['items'] as &$item) {
            updateItemData($item, $db);
            classifyItem($item, $order_array['order_id'], $db, $shipping_items, $download_items, $preorder_items);
        }
        $order_array['shipping_items'] = $shipping_all;
        $order_array['download_items'] = $download_items;
        $shipped_display_date = strtotime($shippedOn);
        $order_array['shipped_on'] = date("jS F Y", $shipped_display_date);
        sendCustomerEmail($order_array, "shipped", $db, $m);
        sleep(5);
        $output .=  "Updated order " . $order->orderReference . "<br>";
    } catch (PDOException $e) {
        $output .=  "Couldn't update order " . $order->orderReference . ": " . $e->getMessage();
    }
    $output .= '</div>';
}
$output = "</div>";

header ('HX-Trigger:updateOrderList');
echo $output;

function getUnsentNew_Orders($db) {
    $query = "SELECT rm_order_identifier FROM New_Orders
        WHERE transaction_id IS NOT NULL
        AND rm_tracking_number IS NULL
        AND rm_order_identifier IS NOT NULL
    ORDER BY order_id ASC
    LIMIT 100";
    return $db->query($query)->fetchAll();
}

function updateOrderWithRMData($shippedOn, $order, $db) {
    try {
        $query = "UPDATE New_Orders
        SET
        `dispatched` = ?,
        `rm_order_identifier` = ?,
        `rm_created` = ?,
        `rm_tracking_number` = ?
        WHERE `order_id` = ?";
        $params = [
            $shippedOn,
            (int)$order->orderIdentifier,
            $order->createdOn,
            $order->trackingNumber,
            (int)$order->orderReference
        ];
        $db->query($query, $params);
    } catch (PDOException $e) {
        throw new PDOException($e);
    }
}

function createOrderArray($order_id, $db) {
    try {
        $query = "SELECT
            New_orders.order_id,
            New_orders.subtotal,
            New_orders.shipping,
            New_orders.vat,
            New_orders.total,
            New_orders.order_date,
            New_orders.rm_tracking_number,
            New_orders.mg,
            Customers.name,
            Customers.address_1,
            Customers.address_2,
            Customers.city,
            Customers.postcode,
            Customers.email,
            Countries.name AS country
        FROM New_orders
        JOIN Customers ON New_orders.customer_id = Customers.customer_id
        JOIN Countries ON Customers.country = Countries.country_id
        WHERE New_orders.order_id = ?";
        $order = $db->query($query, [$order_id])->fetch();
    } catch (PDOException $e) {
        throw new PDOException($e);
    }
    return $order;
    }