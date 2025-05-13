<?php

include("../../functions.php");

if (isset($_POST['status']) && $_POST['status'] == 'FAILED') {
    $response = [
        'status' => 'failed',
    ];
    echo json_encode($response);
    exit();
}

echo json_encode($_POST);