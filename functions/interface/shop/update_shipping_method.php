<?php

session_start();

include_once(__DIR__ . "/../../functions.php");

header("HX-Trigger: shippingMethodUpdated");
$_SESSION['shipping_method'] = $_POST['shippingMethod'];