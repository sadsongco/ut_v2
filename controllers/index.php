<?php

include(base_path("private/functions/utility/nl2p.php"));

use Database\Database;

$db = new Database();

$carousel_tiles = $db->query("SELECT img_url, tile_title, tile_text FROM carousel ORDER BY tile_order ASC")->fetchAll();
foreach ($carousel_tiles as &$tile) {
    $tile['path'] = "serve/" . CAROUSEL_ASSET_PATH . "images/" . str_replace(".", "/", $tile['img_url']);
    $tile['tile_text'] = nl2p($tile['tile_text']);
}

echo $this->renderer->render('index', ['carousel_tiles'=>$carousel_tiles, "nav"=>$this->nav]);

