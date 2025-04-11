<?php

use Database\Database;

$db = new Database();

$res = $db->query("SELECT * FROM Orders WHERE order_id = 1")->fetch();

echo $this->renderer->render('index');