<?php

include_once(__DIR__ . "/../functions/functions.php");
include(base_path("functions/utility/trigger_download.php"));

$filetype = array_pop($paths);

foreach ($paths as $path) {
    if ($path === "." || $path === "..") {
      exit("NOT A VALID RESOURCE");  
    }
}
$file_path = base_path(WEB_ASSET_PATH . implode("/", $paths) . "." . $filetype);
$filename = array_pop($paths) . "." . $filetype;

triggerDownload($filename, $file_path);