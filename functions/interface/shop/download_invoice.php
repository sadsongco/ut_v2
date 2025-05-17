<?php

include("../../functions.php");
require (base_path("../secure/env/config.php"));
require(base_path("classes/Database.php"));
require (base_path("/functions/utility/decrypt_token.php"));
require (base_path('functions/shop/make_order_pdf.php'));


use Database\Database;
$db = new Database('orders');

if (!isset($_GET['token'])) exit("No Token");
if (!isset($_GET['id'])) exit("No ID");

$customer_id = decryptUniqueToken($_GET['token']);
$order_id = $_GET['id'];

$query = "SELECT customer_id FROM Orders WHERE order_id = ?";
$result = $db->query($query, [$order_id])->fetch();
if ($result['customer_id'] != $customer_id) exit("Invalid Token");

$filename = createOrderPDF($order_id, $db);

$file_path = base_path(ORDER_PDF_PATH . $filename);

try {
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Cache-Control: private",false);
    header("Content-Type: " . mime_content_type($file_path));
    header("Content-Disposition: attachment; filename=".$filename);
    header("Content-Transfer-Encoding: binary");
    header("Content-Length: ".filesize($file_path));
    readfile($file_path);
}
catch (Exception $e) {
    error_log($e);
    exit("There was an error. Please try again or contact info@unbelievabletruth.co.uk");
}

unlink($file_path);


function createOrderPDF($order_id, $db) {
    try {
        $query = "SELECT CONCAT(DATE_FORMAT(Orders.order_date, '%y%m%d'), '-', Orders.order_id) AS order_id, Orders.subtotal, Orders.vat, Orders.total,
                        Customers.name, Customers.address_1, Customers.address_2, Customers.city, Customers.postcode, Countries.name as country,
                        DATE_FORMAT(Orders.order_date, '%D %M %Y') AS order_date,
                        Orders.shipping, Orders.shipping_method
                    FROM Orders
                    LEFT JOIN Customers ON Orders.customer_id = Customers.customer_id
                    LEFT JOIN Countries ON Customers.country = Countries.country_id
                    WHERE Orders.order_id = ?
                ;";
            $result = $db->query($query, [$order_id])->fetch();
            $sub_query = "SELECT Items.name, 
                            Order_items.amount,
                            FORMAT(Order_items.order_price * Order_items.amount, 2) AS item_total,
                            FORMAT(Order_items.order_price, 2) AS price
                            FROM Order_items
                            LEFT JOIN Items ON Order_items.item_id = Items.item_id
                            WHERE Order_items.order_id = ?;";
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