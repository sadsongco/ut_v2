<?php

include_once(__DIR__ . "/../../functions/functions.php");
include(base_path("functions/utility/trigger_download.php"));

$filetype = array_pop($paths);

// get rid of the blank element and the controller
array_shift($paths);
array_shift($paths);

$file_path = base_path(RESOURCE_ASSET_PATH . implode("/", $paths) . "." . $filetype);
$filename = array_pop($paths) . "." . $filetype;
triggerDownload($filename, $file_path);
