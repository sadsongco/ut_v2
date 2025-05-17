<?php

//Import PHPMailer classes into the global namespace
//These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

function sendCustomerEmail($order, $template, $db, $m) {
    // create pdf for order
    $order_db_id = explode("-", $order['order_id'])[1];
    $filename = createOrderPDF($order_db_id, $db);

    // send email
    require(base_path("../secure/mailauth/ut.php"));

    $email = $m->render("emails/customer/$template", ["order"=>$order]);

    // mail auth
    $from_name = "Unbelievable Truth shop";

    $send_to = ENV == "production" ? $order['email'] : "info@unbelievabletruth.co.uk";

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
    $mail->addAddress($send_to, $order['name']);
    // $mail->addAddress("nigel@thesadsongco.com", "Nigel");
    $mail->addBCC("info@unbelievabletruth.co.uk");
    $mail->addAttachment(base_path(ORDER_PDF_PATH) . $filename, $filename);
    $mail->send();
    unlink(base_path(ORDER_PDF_PATH) . $filename);
}