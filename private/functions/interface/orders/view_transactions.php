<?php

include(__DIR__ . "/../../../../functions/functions.php");
include(base_path("classes/SUCheckout.php"));
include(base_path("classes/Database.php"));
include(base_path("../lib/mustache.php-main/src/Mustache/Autoloader.php"));

echo ENV;

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

foreach($transactions->items as &$transaction) {
    $query = "SELECT * FROM Orders WHERE transaction_id = ?";
    $result = $db->query($query, [$transaction->id]);
    $transaction->orders = $result->fetchAll();
    p_2($transaction);
}

echo $m->render("transactionList", ["transactions"=>$transactions->items]);

if(isset($transactions->links)) echo '<button hx-get="/private/functions/interface/orders/view_transactions.php?' . $transactions->links[0]->href .'" hx-target="#orderResult" hx-trigger="click" onclick="disableButton(event)">Next Transactions</button>';
