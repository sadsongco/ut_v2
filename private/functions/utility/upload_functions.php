<?php
function insertMediaDB ($files, $key, $db, $table_name) {
    try {
        $query = "INSERT INTO $table_name VALUES (0, ?, ?, ?);";
        $db->query($query, [$_POST["title"][$key], $files["name"][$key], $_POST["notes"][$key]]);
        return $db->lastInsertId();
    }
    catch (PDOException $e) {
        throw new Exception($e->getMessage());
    }
}

function fileExists($filename, $table, $tag, $db) {
    $id = $table == "media" ? "media_id" : "image_id";
    try {
        $query = "SELECT $id FROM $table WHERE filename=?;";
        $result = $db->query($query, [$filename])->fetch();
    }
    catch (PDOException $e) {
        return ["success"=>false, "message"=>"Database error: ".$e->getMessage()];
    }
    $media_id = $result[$id];
    return ["success"=>false, "message"=>"File exists! Either rename the file or insert the existing version.", "tag"=>"{{".$tag."::".$media_id."}}"];
}

function resizeImage($image, $file_path, $image_file_type) {
    $resized_image = imagescale($image, ARTICLE_MAX_IMAGE_WIDTH);
    switch ($image_file_type) {
        case "jpg":
        case "jpeg":
            return imagejpeg($resized_image, $file_path);
            break;
        case "png":
            return imagepng($resized_image, $file_path);
            break;
        case "gif":
            return imagegif($resized_image, $file_path);
            break;
        default:
            throw new Exception("unsupported image type");
            break;
    }
}

function saveThumbnail($image, $filename, $image_file_type) {
    $thumbnail = imagescale($image, ARTICLE_THUMBNAIL_WIDTH);
    $file_path = base_path(ARTICLE_IMAGE_PATH."thumbnails/".$filename);
    switch ($image_file_type) {
        case "jpg":
        case "jpeg":
            return imagejpeg($thumbnail, $file_path);
            break;
        case "png":
            return imagepng($thumbnail, $file_path);
            break;
        case "gif":
            return imagegif($thumbnail, $file_path);
            break;
        default:
            throw new Exception("unsupported image type");
            break;
    }
}

function uploadMedia($files, $key, $db, $table, $upload_path, $tag=null, $image_file_type = null) {
    // this is for uploads too large - change to throw a reasonable error
    if ($files["tmp_name"][$key] == "") die ("NO TMP_NAME:<br />..");
    $tag  = $table == "images" ? "i" : "a";
    $files["name"][$key] = str_replace(" ", "_", $files["name"][$key]);
    if (file_exists($upload_path.$files["name"][$key])) {
        return fileExists($files["name"][$key], $table, $tag, $db);
    }
    $uploaded_file = $files["tmp_name"][$key];

    // save image to filesystem
    try {
        $image = null;
        $image_fnc = "";
        switch ($image_file_type) {
            case "jpg":
            case "jpeg":
                $image = imagecreatefromjpeg($uploaded_file);
                $image_fnc = "imagejpeg";
                break;
            case "png":
                $image = imagecreatefrompng($uploaded_file);
                $image_fnc = "imagepng";
                break;
            case "gif":
                $image = imagecreatefromgif($uploaded_file);
                $image_fnc = "imagegif";
                break;
            default:
                $image = null;
        }
        if ($image) {
            // resize images and save thumbnails
            $image_size = getimagesize($uploaded_file);
            try {
                if ($image_size[0] > ARTICLE_MAX_IMAGE_WIDTH) {
                    if (!resizeImage($image, base_path(ARTICLE_IMAGE_PATH.$files["name"][$key]), $image_file_type)) {
                        throw new Exception("Failed to resize image");
                    }
                }
                move_uploaded_file($uploaded_file, $upload_path.$files["name"][$key]);
                saveThumbnail($image, $files["name"][$key], $image_file_type);
            }
            catch (Exception $e) {
                throw new Exception("Failed to save image: ".$e->getMessage());
            }
        } else {
            // save audio
            move_uploaded_file($uploaded_file, $upload_path.$files["name"][$key]);
            unlink($uploaded_file);
        }
    }
    catch (Exception $e) {
        return ["success"=>false, "message"=>"File copy error: ".$e->getMessage()];
    }

    // add image to images database
    try {
        $media_id = insertMediaDB($files, $key, $db, $table);
    }
    catch (PDOException $e) {
        return ["success"=>false, "message"=>"Database error: ".$e->getMessage()];
    }
    return ["success"=>true, "filename"=>$files["name"][$key], "tag"=>"{{".$tag."::".$media_id."}}"];
}
