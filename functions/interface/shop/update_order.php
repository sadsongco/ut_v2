<?php

session_start();

include("../../functions.php");
require (base_path("classes/Database.php"));
use Database\Database;
$db = new Database('orders');

if (isset($_POST['status']) && $_POST['status'] == 'FAILED') {
    $order_id = explode("-",$_SESSION['order_id'])[1];
    $query = "DELETE FROM New_Orders WHERE order_id = ?";
    try {
        $db->query($query, [$order_id]);
    }
    catch (Exception $e) {
        error_log($e);
    }
    $response = [
        'status' => 'failed',
        'response'=> $_POST
    ];
    unset($_SESSION['order_id']);
    echo json_encode($response);
    exit();
}

$query = "UPDATE New_Orders SET transaction_id = ?";
try {
    $db->query($query, [$_POST['transaction_id']]);
}
catch (Exception $e) {
    error_log($e);
}

$response = [
    'status' => 'success',
    'response'=> $_POST
];
echo json_encode($response);
