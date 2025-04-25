<?php

function getShippingMethods($zone, $db)
{
    try {
        $query = "SELECT
            Shipping_methods.shipping_method_id,
            Shipping_methods.service_name,
            Shipping_methods.service_code
        FROM Shipping_prices
        INNER JOIN Shipping_methods ON Shipping_methods.shipping_method_id = Shipping_prices.shipping_method_id
        WHERE Shipping_prices.rm_zone = ?
        GROUP BY Shipping_methods.shipping_method_id";
        return $db->query($query, [$zone])->fetchAll();
    
    } catch (PDOException $e) {
        throw new Exception($e);
    }
}