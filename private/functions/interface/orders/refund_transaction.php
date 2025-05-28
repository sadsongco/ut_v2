<?php

include(__DIR__ . "/../../../../functions/functions.php");
include(base_path("classes/SUCheckout.php"));
include(base_path("classes/Database.php"));

use Database\Database;
$db = new Database('orders');

use SUCheckout\SUCheckout;
$checkout = new SUCheckout();

$checkout->refundTransaction($_GET['transaction_id'], $db);
$result = $checkout->getResponse();

header("HX-Trigger: transactionListUpdated");
echo '<div id="updatedResult" hx-swap-oob="true">
'. print_r($result, true) . '
</div>';