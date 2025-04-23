<?php

session_start();

include_once(__DIR__ . "/../../functions.php");

$option = isset($_POST['option']) ?? false;

$_SESSION['items'][] = ['item_id'=>$_POST['item_id'], 'option_id'=>$option];

header("HX-Trigger: cartUpdated");

echo "Item added to cart";