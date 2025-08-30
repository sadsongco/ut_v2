<?php

include_once(__DIR__."/includes/order_includes.php");
//Load Composer's autoloader
require base_path('../lib/vendor/autoload.php');
require base_path('functions/shop/make_order_pdf.php');

//Import PHPMailer classes into the global namespace
//These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$unsent_orders = getUnsentNew_Orders($db);
if (empty($unsent_orders)) {
    exit("No orders to update.");
}

exit();

$unsent_orders_string = "";
foreach ($unsent_orders as $unsent_order) {
    $unsent_orders_string .= '"orderIdentifier";' . urlencode($unsent_order["rm_order_identifier"]) . ';';
}

$url = $path = RM_BASE_URL."/orders/" . $unsent_orders_string;
$headers = [
    "Authorization: " . RM_API_KEY,
    "Content-Type: application/json"
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $path);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);
$responseObj = json_decode($response);
$output = "";

foreach ($responseObj as $order) {
    if (!isset($order->trackingNumber)) {
        $output = "No tracking number for order " . $order->orderReference . "<br>";
        continue;
    }
    if (!isset($order->orderReference)) {
        $output = "No order reference for order " . $order->orderReference . "<br>";
        continue;
    }
    $shippedOn = isset($order->shippedOn) ? $order->shippedOn : NULL;
    if (!$shippedOn) {
        $output = "Order " . $order->orderReference . " not marked dispatched on Royal Mail portal.<br>";
        continue;
    }
    try {
        $query = "UPDATE New_Orders
        SET
        `dispatched` = ?,
        `rm_order_identifier` = ?,
        `rm_created` = ?,
        `rm_tracking_number` = ?
        WHERE `order_id` = ?";
        $stmt = $db->prepare($query);
        $params = [
            $shippedOn,
            (int)$order->orderIdentifier,
            $order->createdOn,
            $order->trackingNumber,
            (int)$order->orderReference
        ];
        $stmt->execute($params);
        sendCustomerShippedEmail($order->orderReference, $order->trackingNumber, $db, $m);
        sleep(5);
        $output .=  "Updated order " . $order->orderReference . "<br>";
    } catch (Exception $e) {
        $output .=  "Couldn't update order " . $order->orderReference . ": " . $e->getMessage();
    }
}

$output .=  "<p>New_Orders Updated from Royal Mail</p>";
// header ('HX-Trigger:updateOrderList');
echo $output;

function getUnsentNew_Orders($db) {
    $query = "SELECT rm_order_identifier FROM New_Orders WHERE rm_tracking_number IS NULL AND rm_order_identifier IS NOT NULL";
    return $db->query($query)->fetchAll();
}

function sendCustomerShippedEmail($order_id, $tracking_number, $db, $m) {
    // create pdf for order
    $filename = createOrderPDF($order_id, $db);

    // send email
    require(base_path("../secure/mailauth/ut.php"));
    try {
        $query = "SELECT New_Orders.*, Customers.*, DATE_FORMAT(New_Orders.dispatched, '%D %M %Y') AS disp_dispatched_date
        FROM New_Orders
        JOIN Customers ON New_Orders.customer_id = Customers.customer_id
        WHERE New_Orders.order_id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$order_id]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $order = $result[0];
        $query = "SELECT
        Items.name,
        New_Order_items.quantity,
        New_Order_items.order_price
        FROM `New_Order_items`
        JOIN `Items` ON `New_Order_items`.`item_id` = `Items`.`item_id`
        WHERE `New_Order_items`.`order_id` = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$order_id]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $items_to_send = [];
        foreach ($items as $item) {
            $order["items"][] = [
                "name" => $item["name"],
                "quantity" => $item["quantity"]
            ];
        }
    } catch (PDOException $e) {
        throw new Exception($e);
    }

    $email = $m->render("customerShippedEmail", ["order"=>$order, "tracking_number"=>$tracking_number]);

    // mail auth
    $from_name = "Unbelievable Truth shop";

    $send_to = ENV == "production" ? $order['email'] : "nigel@thesadsongco.com";

    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = $mail_auth['host'];
    $mail->SMTPAuth = true;
    $mail->SMTPKeepAlive = false; //SMTP connection will not close after each email sent, reduces SMTP overhead
    $mail->Port = 25;
    $mail->Username = $mail_auth['username'];
    $mail->Password = $mail_auth['password'];
    $mail->setFrom($mail_auth['from']['address'], $from_name);
    $mail->addReplyTo($mail_auth['reply']['address'], $from_name);
    $mail->Subject = "Unbelievable Truth - your order has shipped";
    $mail->msgHTML($email);
    $mail->addAddress($send_to);
    // $mail->addAddress("nigel@thesadsongco.com", "Nigel");
    $mail->addBCC("info@unbelievabletruth.co.uk");
    $mail->addAttachment(base_path(ORDER_PDF_PATH) . $filename, $filename);
    $mail->send();
}

function createOrderPDF($order_id, $db) {
    try {
        $query = "SELECT New_Orders.order_id, New_Orders.subtotal, New_Orders.vat, New_Orders.total,
                        Customers.name, Customers.address_1, Customers.address_2, Customers.city, Customers.postcode, Customers.country,
                        DATE_FORMAT(New_Orders.order_date, '%D %M %Y') AS order_date,
                        New_Orders.shipping, New_Orders.shipping_method
                    FROM New_Orders
                    LEFT JOIN Customers ON New_Orders.customer_id = Customers.customer_id
                    WHERE New_Orders.order_id = ?
                ;";
            $stmt = $db->prepare($query);
            $stmt->execute([$order_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $sub_query = "SELECT Items.name, 
                            New_Order_items.quantity,
                            FORMAT(New_Order_items.order_price * New_Order_items.quantity, 2) AS item_total,
                            FORMAT(New_Order_items.order_price, 2) AS price
                            FROM New_Order_items
                            LEFT JOIN Items ON New_Order_items.item_id = Items.item_id
                            WHERE New_Order_items.order_id = ?;";
            $stmt = $db->prepare($sub_query);
            $stmt->execute([$result["order_id"]]);
            $result["items"] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    }
    
    catch (PDOException $e) {
        echo $e->getMessage();
    }

    $total = $result["shipping"];
    $subtotal = 0;
    foreach ($result["items"] as $item) {
        $total += $item["price"] * $item["quantity"];
        $subtotal += $item["price"] * $item["quantity"];
    }

    $result["subtotal"] = round($subtotal, 4);
    $result["total"] = round($total, 4);
    $result["vat"] = round($result["total"] * 0.2, 4);
    
    return makeOrderPDF($result, 'F', base_path(ORDER_PDF_PATH));
}