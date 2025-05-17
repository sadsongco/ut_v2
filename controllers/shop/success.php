<?php

session_start();

include(__DIR__ . "/../../functions/functions.php");
require (base_path("../secure/env/config.php"));
require (base_path("/functions/utility/create_unique_token.php"));
require (base_path("/functions/utility/send_customer_email.php"));
require (base_path("/functions/utility/create_order_pdf.php"));
require (base_path('functions/shop/make_order_pdf.php'));

//Load Composer's autoloader
require (base_path('../lib/vendor/autoload.php'));

use Database\Database;
$db = new Database('orders');

$order_db_id = explode("-", $_SESSION['order_id'])[1];

$download_tokens = [];
foreach ($_SESSION['items'] AS $item) {
    $query = "SELECT name, download FROM Items WHERE item_id = ?";
    $res = $db->query($query, [$item['item_id']])->fetch();
    if (!isset($res['download']) || $res['download'] == "") continue;
    $item_name = $res['name'];
    $query = "SELECT download_token_id FROM download_tokens WHERE order_id = ? AND item_id = ?";
    $res = $db->query($query, [$order_db_id, $item['item_id']])->fetch();
    if (isset($res['download_token_id'])) {
        $download_token = createUniqueToken($res['download_token_id']);
    }
    else {
        $query = "INSERT INTO Download_tokens (order_id, item_id) VALUES (?, ?)";
        $db->query($query, [$order_db_id, $item['item_id']]);
        $download_token = createUniqueToken($db->lastInsertId());
    }
    $download_tokens[] = ["name"=>$item_name, "download_token"=>$download_token];
}

$query = "SELECT customer_id FROM Orders WHERE order_id = ?";
$customer_id = $db->query($query, [$order_db_id])->fetch()['customer_id'];
$customer_token = createUniqueToken($customer_id);

try {
    $query = "SELECT
            CONCAT(DATE_FORMAT(Orders.order_date, '%y%m%d'), '-', Orders.order_id) AS order_id,
            Shipping_methods.service_name,
            Customers.name,
            Customers.email
        FROM Orders
        JOIN Customers ON Orders.customer_id = Customers.customer_id
        JOIN Shipping_methods ON Orders.shipping_method = Shipping_methods.shipping_method_id
        WHERE Orders.order_id = ?";
    $order = $db->query($query, [$order_db_id])->fetch();

    $query = "SELECT
            Items.name,
            Order_items.amount,
            Order_items.order_price
        FROM `Order_items`
        JOIN `Items` ON `Order_items`.`item_id` = `Items`.`item_id`
        WHERE `Order_items`.`order_id` = ?";
    $items = $db->query($query, [$order_db_id])->fetchAll();
    $items_to_send = [];
    foreach ($items as $item) {
        $order["items"][] = [
            "name" => $item["name"],
            "amount" => $item["amount"]
        ];
    }
    sendCustomerEmail($order, "success", $db, $this->renderer);
}
catch (Exception $e) {
    echo $e->getMessage();
}

echo $this->renderer->render('shop/success', ["order_id"=>$_SESSION['order_id'], "download_tokens"=>$download_tokens, "order_db_id"=>$order_db_id, "customer_token"=>$customer_token, "stylesheets"=>["shop"]]);

// session_destroy();