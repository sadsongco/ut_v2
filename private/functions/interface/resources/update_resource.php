<?php

include(__DIR__ . "/../../../../functions/functions.php");
include_once("includes/resource_includes.php");
include_once(base_path("private/classes/FileUploader.php"));

use FileUploader\FileUploader;
$uploader = new FileUploader(RESOURCE_ASSET_PATH . $_POST['resource_dir'], true);
$uploaded_files = $uploader->checkFileSizes()->getResponse();

if ((isset($uploaded_files["success"]) && !$uploaded_files["success"])
    || isset($uploaded_files[0]["success"]) && !$uploaded_files[0]["success"]) {
    echo $m->render("partials/resourceForm", ["dir"=>$_POST["resource_dir"], "error"=>$uploaded_files]);
}
$fields = null;
if (isset($_POST["meta_filename"]) && $_POST["meta_filename"] != "") {
    $file_path = $parent_dir . $_POST["resource_dir"] . "/" . $_POST["meta_filename"] . ".txt";
    $fields = $update_map[$_POST["meta_filename"]];
    $res_str_arr = [];
    foreach ($fields as $field) {
        $res_str_arr[] = $_POST[$field];
    }
    $res_str = implode("|", $res_str_arr) . "\n";
    file_put_contents($file_path, $res_str, FILE_APPEND);
    echo "<h2>Resource meta file updated</h2>";
}
$meta_file = $_POST['meta_filename'] ?? null;

echo $m->render("partials/resourceForm", ["dir"=>$_POST["resource_dir"], "meta_file"=>$meta_file, "fields"=>$fields, "uploaded_files"=>$uploaded_files]);
