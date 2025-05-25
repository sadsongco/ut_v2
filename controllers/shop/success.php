<?php

function classifyItem($item, $order_db_id,$db, &$shipping_items, &$download_items, &$preorder_items)
{
    if (isset($item['release_date']) && $item['release_date'] > date("Y-m-d")) {
        if (isset($item['download']) && $item['download'] != "") $item["download_token"] = createUniqueToken($db->lastInsertId());
        if ($item['e_delivery']) $preorder_items["e_delivery"][] = $item;
        else $preorder_items['shipping'][] = $item;
        return;
    }
    if (!$item['e_delivery']) {
        $shipping_items[] = $item;
    }
    if ($item['download']) {
        $query = "SELECT download_token_id FROM Download_tokens WHERE order_id = ? AND item_id = ?";
        $res = $db->query($query, [$order_db_id, $item['item_id']])->fetch();
        if (isset($res['download_token_id'])) {
            $download_token = createUniqueToken($res['download_token_id']);
        }
        else {
            $query = "INSERT INTO Download_tokens (order_id, item_id) VALUES (?, ?)";
            $db->query($query, [$order_db_id, $item['item_id']]);
            $download_token = createUniqueToken($db->lastInsertId());
        }
        $item["download_token"] = $download_token;
        $download_items[] = $item;
    }
}

function updateItemData(&$item, $db)
{
    $query = "SELECT name, e_delivery, download, release_date, DATE_FORMAT(release_date, '%D %M %Y') AS disp_release_date FROM Items WHERE item_id = ?";
    $res = $db->query($query, [$item['item_id']])->fetch();
    $item['name'] = $res['name'];
    $item['e_delivery'] = $res['e_delivery'];
    $item['download'] = $res['download'];
    $item['release_date'] = $res['release_date'];
    $item['disp_release_date'] = $res['disp_release_date'];
    if ($item['option_id']) {
        $query = "SELECT option_name FROM Item_options WHERE item_option_id = ?";
        $res = $db->query($query, [$item['option_id']])->fetch();
        $item['option_name'] = $res['option_name'];
    }
}

session_start();

include(__DIR__ . "/../../functions/functions.php");
require (base_path("/functions/utility/create_unique_token.php"));
require (base_path("/functions/utility/send_customer_email.php"));
require (base_path("/functions/utility/create_order_pdf.php"));
require (base_path('functions/shop/get_cart_contents.php'));
require (base_path('functions/shop/make_order_pdf.php'));
require(base_path("classes/RoyalMail.php"));

//Load Composer's autoloader
require (base_path('../lib/vendor/autoload.php'));

use Database\Database;
$db = new Database('orders');

// if not coming after an order
if (!isset($_SESSION['order_id'])) exit($this->renderer->render('shop/success', ["stylesheets"=>["shop"]]));


// get database id from order id
$order_db_id = explode("-", $_SESSION['order_id'])[1];

$shipping_items = [];
$download_items = [];
$preorder_items = [];
$shipping_all = false;

$query = "SELECT * FROM New_Orders WHERE order_id = ?";
$order = $db->query($query, [$order_db_id])->fetch();

if (!isset($_SESSION['items'])) $_SESSION['items'] = [];
foreach ($_SESSION['items'] AS $item) {
    updateItemData($item, $db);
    classifyItem($item, $order_db_id, $db, $shipping_items, $download_items, $preorder_items);
}


if (!isset($_SESSION['bundles'])) $_SESSION['bundles'] = [];
foreach($_SESSION['bundles'] AS $bundle) {
    foreach ($bundle['items'] AS $item) {
        updateItemData($item, $db);
        classifyItem($item, $order_db_id,$db, $shipping_items,$download_items, $preorder_items);
    }
}

if (!empty($shipping_items) && empty($preorder_items)) $shipping_all = true;
if (!empty($shipping_items) && !empty($preorder_items)) $preorder_items['held'] = [...$shipping_items];

use RoyalMail\RoyalMail;
if ($shipping_all) {
    $rm = new RoyalMail($order_db_id, $db);
    $rm->createRMOrder();
    $rm->submitRMOrder();
}

$query = "SELECT customer_id FROM New_Orders WHERE order_id = ?";
$customer_id = $db->query($query, [$order_db_id])->fetch()['customer_id'];
$customer_token = createUniqueToken($customer_id);

try {
    $query = "SELECT
            CONCAT(DATE_FORMAT(New_Orders.order_date, '%y%m%d'), '-', New_Orders.order_id) AS order_id,
            Shipping_methods.service_name,
            Customers.name,
            Customers.email
        FROM New_Orders
        JOIN Customers ON New_Orders.customer_id = Customers.customer_id
        JOIN Shipping_methods ON New_Orders.shipping_method = Shipping_methods.shipping_method_id
        WHERE New_Orders.order_id = ?";
    $order = $db->query($query, [$order_db_id])->fetch();

    $order['shipping_items'] = $shipping_all;
    $order['download_items'] = $download_items;
    $order['preorder_items'] = $preorder_items;

    sendCustomerEmail($order, "success", $db, $this->renderer);
}
catch (Exception $e) {
    echo $e->getMessage();
}

echo $this->renderer->render('shop/success', [
    "order"=>$order,
    "order_db_id"=>$order_db_id,
    "customer_token"=>$customer_token,
    "stylesheets"=>["shop"]]
);

session_destroy();

