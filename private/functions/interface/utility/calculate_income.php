<?php

include(__DIR__ . "/../../../../functions/functions.php");
include(base_path("classes/Database.php"));
include(base_path("../lib/mustache.php-main/src/Mustache/Autoloader.php"));

use Database\Database;
$db = new Database('orders');

Mustache_Autoloader::register();

$m = new Mustache_Engine(array(
    'loader' => new Mustache_Loader_FilesystemLoader(base_path('private/views/utility')),
    'partials_loader' => new Mustache_Loader_FilesystemLoader(base_path('private/views/utility/partials'))
));

$period = isset($_POST['period']) ? $_POST['period'] : date("Y");
$new_income = calculateNewIncome($db, $period);
$old_income = calculateOldIncome($db, $period);

$income = [
    "subtotal" => number_format($new_income['subtotal'] + $old_income['subtotal'], 2),
    "shipping" => number_format($new_income['shipping'] + $old_income['shipping'], 2),
    "gross" => number_format($new_income['subtotal'] + $new_income['shipping'] + $old_income['subtotal'] + $old_income['shipping'], 2),
    "vat" => number_format($new_income['vat'] + $old_income['vat'], 2),
    "total" => number_format($new_income['total'] + $old_income['total'], 2)
];

echo $m->render('income', ["income"=>$income, "period"=>$period]);

function calculateNewIncome($db, $period) {
    try {
        $range_start = $period . "-04-05";
        $range_end = $period + 1 . "-04-04";
        $query = "SELECT
            SUM(subtotal) AS subtotal,
            SUM(shipping) AS shipping,
            SUM(vat) AS vat,
            SUM(total) AS total
        FROM New_Orders WHERE order_date BETWEEN ? AND ?";
        $params = [$range_start, $range_end];
        $result = $db->query($query, $params)->fetch();
        return $result;
    } catch (PDOException $e) {
        error_log($e);
        return 0;
    }
}

function calculateOldIncome($db, $period) {
    try {
        $range_start = $period . "-04-05";
        $range_end = $period + 1 . "-04-04";
        $query = "SELECT
            SUM(subtotal) AS subtotal,
            SUM(shipping) AS shipping,
            SUM(vat) AS vat,
            SUM(total) AS total
        FROM Orders WHERE order_date BETWEEN ? AND ?";
        $params = [$range_start, $range_end];
        $result = $db->query($query, $params)->fetch();
        return $result;
    } catch (PDOException $e) {
        error_log($e);
        return 0;
    }
}