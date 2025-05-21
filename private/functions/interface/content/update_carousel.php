<?php

include(__DIR__ . "/../../../../functions/functions.php");
include(base_path("classes/Database.php"));
include(base_path("private/classes/FileUploader.php"));
include(base_path("../lib/mustache.php-main/src/Mustache/Autoloader.php"));
Mustache_Autoloader::register();
$m = new Mustache_Engine(array(
    'loader' => new Mustache_Loader_FilesystemLoader(base_path('private/views/content/')),
    'partials_loader' => new Mustache_Loader_FilesystemLoader(base_path('private/views/content/partials/'))
));

use Database\Database;
$db = new Database('admin');

use FileUploader\FileUploader;
$uploader = new FileUploader(CAROUSEL_ASSET_PATH, CAROUSEL_MAX_IMAGE_WIDTH, CAROUSEL_THUMBNAIL_WIDTH);
$uploaded_files = $uploader->checkFileSizes()->uploadFiles()->getResponse();
$uploaded_file = $uploaded_files[0];

if ($uploaded_file["success"]) {
    $media_id = updateCarouselDB($uploaded_file, $db);
}

echo $m->render("carouselForm", ["uploaded_file"=>$uploaded_file]);


function updateCarouselDB($uploaded_file, $db) {
    $query = "INSERT INTO carousel (img_url, tile_text) VALUES (?, ?);";
    $params = [
        $uploaded_file['filename'],
        $_POST['tileText'][$uploaded_file['key']]
    ];
    $db->query($query, $params);
    return $db->lastInsertId();
}