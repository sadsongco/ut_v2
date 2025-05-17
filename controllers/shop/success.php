<?php

session_start();

include(__DIR__ . "/../../functions/functions.php");
require (base_path("../secure/env/config.php"));
require (base_path("/functions/utility/create_unique_token.php"));
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

echo $this->renderer->render('shop/success', ["order_id"=>$_SESSION['order_id'], "download_tokens"=>$download_tokens, "order_id"=>$order_db_id, "customer_token"=>$customer_token, "stylesheets"=>["shop"]]);

// session_destroy();