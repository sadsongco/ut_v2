<?php

include(__DIR__ . "/../../../../functions/functions.php");
require_once(base_path("classes/Database.php"));
include_once("includes/mailout_includes.php");

use Database\Database;
$db = new Database('admin');

$cond = "";
if (isset($_GET['nigel'])) $cond = " WHERE `email` LIKE '%sadsongco%'";

try {
    $query = "UPDATE test_mailing_list SET last_sent = 0$cond";
    $db->query($query);
}
catch (PDOException $e) {
    exit("Couldn't reset test mailing list: ".$e->getMessage());
}

echo "Mailing list reset $cond";