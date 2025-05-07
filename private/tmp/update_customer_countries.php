<?php

require_once(__DIR__ . "/../../functions/functions.php");
require(base_path("classes/Database.php"));

use Database\Database;
$db = new Database('orders');

$customer_countries = $db->query("SELECT customer_id, country FROM Customers")->fetchAll();

foreach ($customer_countries as $customer_country) {
    if (is_numeric($customer_country['country'])) continue;
    if ($customer_country['country'] == "United States") $customer_country['country'] = "USA";
    $country_id = $db->query("SELECT country_id FROM Countries WHERE name = ?", [$customer_country['country']])->fetch()['country_id'];
    $db->query("UPDATE Customers SET country = ? WHERE customer_id = ?", [$country_id, $customer_country['customer_id']]);
    echo "Updated customer " . $customer_country['customer_id'] . " with country " . $customer_country['country'] . " to " . $country_id . "<br>";
}