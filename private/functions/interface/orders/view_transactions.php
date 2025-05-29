<?php

include(__DIR__ . "/../../../../functions/functions.php");
include(base_path("classes/SUCheckout.php"));
include(base_path("classes/Database.php"));
include(base_path("../lib/mustache.php-main/src/Mustache/Autoloader.php"));

Mustache_Autoloader::register();
$m = new Mustache_Engine(array(
    'loader' => new Mustache_Loader_FilesystemLoader(base_path('private/views/orders')),
    'partials_loader' => new Mustache_Loader_FilesystemLoader(base_path('private/views/orders/partials'))
));

use Database\Database;
$db = new Database('orders');

use SUCheckout\SUCheckout;
$checkout = new SUCheckout();
$transactions = $checkout->listTransactions($_GET)->getResponse();

$refunded_ids = [];
foreach($transactions->items as &$transaction) {
    if ($transaction->status == "FAILED") continue;
    if ($transaction->status == "REFUNDED") $refunded_ids[] = $transaction->id;
    if ($transaction->status == "SUCCESSFUL" && in_array($transaction->transaction_id, $refunded_ids)) $transaction->refunded = true;
    $transaction_time = new DateTime($transaction->timestamp);
    $transaction->time = $transaction_time->format("jS M Y H:i");
    $transaction_order = getOrderByTransactionId($transaction->id, $db);
    if ($transaction_order)$transaction->order = $transaction_order;
    else continue;

    if ($transaction->order['total'] == $transaction->amount) {
        $transaction->amount_match = true;
    }
}

if (isset($transactions->links)) {
    $transactions->href = $transactions->links[0]->href;
}

// p_2($transactions);
echo $m->render("transactionList", ["transactions"=>$transactions]);


function getOrderByTransactionId($transactionId, $db) {
    $query = "SELECT
    New_Orders.order_id,
    New_Orders.total,
    DATE_FORMAT(New_Orders.order_date, '%D %M %Y') AS order_date,
    Customers.name as customer_name,
    Customers.city as customer_city,
    Countries.name as customer_country,
    Customers.email as customer_email,
    Shipping_methods.service_name,
    NOT ISNULL(New_Orders.rm_order_identifier) AS submitted,
    NOT ISNULL(New_Orders.rm_tracking_number) AS shipped
    FROM New_Orders
    JOIN Customers ON New_Orders.customer_id = Customers.customer_id
    JOIN Countries ON Customers.country = Countries.country_id
    JOIN Shipping_methods ON New_Orders.shipping_method = Shipping_methods.shipping_method_id
    WHERE transaction_id = ?";
    return $db->query($query, [$transactionId])->fetch();
}