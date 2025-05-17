<?php

function createOrderPDF($order_id, $db) {
    try {
        $query = "SELECT CONCAT(DATE_FORMAT(New_Orders.order_date, '%y%m%d'), '-', New_Orders.order_id) AS order_id, New_Orders.subtotal, New_Orders.vat, New_Orders.total,
                        Customers.name, Customers.address_1, Customers.address_2, Customers.city, Customers.postcode, Countries.name as country,
                        DATE_FORMAT(New_Orders.order_date, '%D %M %Y') AS order_date,
                        New_Orders.shipping, New_Orders.shipping_method
                    FROM New_Orders
                    LEFT JOIN Customers ON New_Orders.customer_id = Customers.customer_id
                    LEFT JOIN Countries ON Customers.country = Countries.country_id
                    WHERE New_Orders.order_id = ?
                ;";
            $result = $db->query($query, [$order_id])->fetch();
            $sub_query = "SELECT Items.name, 
                            New_Order_items.amount,
                            FORMAT(New_Order_items.order_price * New_Order_items.amount, 2) AS item_total,
                            FORMAT(New_Order_items.order_price, 2) AS price
                            FROM New_Order_items
                            LEFT JOIN Items ON New_Order_items.item_id = Items.item_id
                            WHERE New_Order_items.order_id = ?;";
            $result["items"] = $db->query($sub_query, [$order_id])->fetchAll();
        
    }
    
    catch (PDOException $e) {
        echo $e->getMessage();
    }

    
    $total = $result["shipping"];
    $subtotal = 0;
    foreach ($result["items"] as $item) {
        $total += $item["price"] * $item["amount"];
        $subtotal += $item["price"] * $item["amount"];
    }
    
    $result["subtotal"] = round($subtotal, 4);
    $result["total"] = round($total, 4);
    $result["vat"] = round($result["total"] * 0.2, 4);
    
    return makeOrderPDF($result, 'F', base_path(ORDER_PDF_PATH));
}