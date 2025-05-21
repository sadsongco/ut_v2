<?php

use Database\Database;

$db = new Database();

$carousel_tiles = $db->query("SELECT * FROM carousel ORDER BY tile_order ASC")->fetchAll();
foreach ($carousel_tiles as &$tile) {
    $tile['path'] = CAROUSEL_ASSET_PATH . "images/" . $tile['img_url'];
}

echo $this->renderer->render('index', ['carousel_tiles'=>$carousel_tiles, 'nav'=>$this->nav]);
