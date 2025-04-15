<?php

use Database\Database;

$db = new Database();

$res = $db->query("SELECT * FROM comments")->fetchAll();

echo $this->renderer->render('index');
