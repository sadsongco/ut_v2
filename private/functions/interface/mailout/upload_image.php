<?php

include_once('includes/mailout_includes.php');
include_once('includes/media_upload.php');
require(base_path("../secure/env/config.php"));

$files = $_FILES['image_upload'];

foreach ($files['name'] as $key=>$filename) {
    $image_file_type = strtolower(pathinfo($filename,PATHINFO_EXTENSION));
    $uploaded_file = uploadMedia($files, $key, $db, 'MailoutImages', $image_file_type);
}

echo $m->render('uploadedFile', ["uploaded_file"=>$uploaded_file]);