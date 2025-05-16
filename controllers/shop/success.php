<?php

session_start();

include(__DIR__ . "/../../functions/functions.php");

use Database\Database;
$db = new Database('orders');

echo $this->renderer->render('shop/success', ["order_id"=>$_SESSION['order_id'], "stylesheets"=>["shop"]]);
p_2($_SESSION);

// session_destroy();