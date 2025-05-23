<?php

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

if (!isset($_SESSION['order_id'])) exit($this->renderer->render('shop/success', ["stylesheets"=>["shop"]]));
$order_db_id = explode("-", $_SESSION['order_id'])[1];

use RoyalMail\RoyalMail;
$rm = new RoyalMail($order_db_id, $db);
$rm->createRMOrder();
$rm->submitRMOrder();


$download_tokens = [];
$preorder_items = [];

if (!isset($_SESSION['items'])) $_SESSION['items'] = [];
foreach ($_SESSION['items'] AS $item) {
    checkItemForDownloadRelease($item, $order_db_id, $db, $download_tokens, $preorder_items);
}

if (!isset($_SESSION['bundles'])) $_SESSION['bundles'] = [];
foreach($_SESSION['bundles'] AS $bundle) {
    foreach ($bundle['items'] AS $item) {
        checkItemForDownloadRelease($item, $order_db_id, $db, $download_tokens, $preorder_items);
    }
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

    sendCustomerEmail($order, "success", $db, $this->renderer);
}
catch (Exception $e) {
    echo $e->getMessage();
}

echo $this->renderer->render('shop/success', [
    "order_id"=>$_SESSION['order_id'],
    "download_tokens"=>$download_tokens,
    "preorder_items"=>$preorder_items,
    "order_db_id"=>$order_db_id,
    "customer_token"=>$customer_token,
    "stylesheets"=>["shop"]]);

session_destroy();

function checkItemForDownloadRelease($item, $order_db_id, $db, &$download_tokens, &$preorder_items)
{

    $query = "SELECT name, download, release_date, DATE_FORMAT(release_date, '%D %b %Y') as disp_release_date FROM Items WHERE item_id = ?";
    $res = $db->query($query, [$item['item_id']])->fetch();
    if (!isset($res['download']) || $res['download'] == "") return false;
    if (isset($res['release_date']) && $res['release_date'] > date("Y-m-d")) {
        $preorder_items[] = ["preorder"=>true, "release_date"=>$res['disp_release_date'], "name"=>$res['name']];
        return false;
    }
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
    return true;
}