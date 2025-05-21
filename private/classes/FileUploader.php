<?php

namespace FileUploader;

class FileUploader
{
    protected $upload_dir;
    protected $files_to_upload;
    protected $response;
    private $subdirs = [
        "jpeg" => "images",
        "jpg" => "images",
        "png" => "images",
        "gif" => "images",
        "mp3" => "audio",
    ];
    private $uploaded_file;
    private $upload_path;
    private $image_fnc;
    private $image;
    private $max_width;
    private $thumb_width;

    public function __construct($path, $max_width=false, $thumb_width=80) {
        $this->upload_dir = base_path($path);
        $this->max_width = $max_width;
        $this->thumb_width = $thumb_width;
        $this->response = [];
        if (!isset($_FILES) || !isset($_FILES["files"])) {
            $this->response = ["success"=>false, "messsage"=>"No files uploaded"];
            return $this;
        } else {
            $this->files_to_upload = $_FILES["files"];
            return $this;
        }
    }

    public function checkFileSizes() {
        $post_size = 0;
        foreach ($this->files_to_upload["name"] as $key=>$filename) {
            if ($this->files_to_upload["size"][$key] > MAX_FILE_SIZE) {
                unset($this->files_to_upload["tmp_name"][$key]);
                $this->response[] = ["success"=> false, "message"=>"File $filename is too big"];
                continue;
            }
            $post_size += $this->files_to_upload["size"][$key];
            if ($post_size > MAX_POST_SIZE) {
                $this->response[] = ["success"=> false, "message"=>"File upload size too big - try doing them one at a time"];
                return false;
            }        
        }
        return $this;
    }

    public function uploadFiles() {
        foreach($this->files_to_upload["name"] as $key=>$filename) {
            if (!isset($this->files_to_upload["tmp_name"][$key])) {
                $this->response[] = ["success"=>false, "message"=>"NO TMP_NAME:<br />.."];
                continue;
            }
            $this->uploaded_file = $this->files_to_upload["tmp_name"][$key];
            if ($this->uploaded_file == "") {
                $this->response[] = ["success"=>false, "message"=>"NO TMP_NAME:<br />.."];
                continue;
            }
            $image_file_type = strtolower(pathinfo($filename,PATHINFO_EXTENSION));
            if (!in_array($image_file_type, array_keys($this->subdirs))) {
                $this->response[] = ["success"=> false, "message"=>$filename.": $image_file_type file types are not supported"];
                return $this;   
            }
            $subdir = $this->subdirs[$image_file_type];
            $filename = str_replace(" ", "_", $filename);
            $this->upload_path = $this->upload_dir . $subdir . "/" . $filename;
            if (file_exists($this->upload_path)) {
                $this->response[] = ["success"=>false, "message"=>$filename." already exists"];
                continue;
            }
            if ($subdir === "images") {
                $this->image = $this->createImage($this->uploaded_file, $image_file_type);
                if (!$this->image) {
                    $this->response[] = ["success"=>false, "message"=>$filename." is not an image"];
                }
                $this->resizeImage($this->max_width);
                if (($this->image_fnc)($this->image, $this->upload_path)) {
                    $this->makeThumbnail($filename);
                    $this->response[] = [
                        "success"=>true,
                        "message"=>$filename." uploaded",
                        "filename"=>$filename,
                        "type"=>$subdir,
                        "key"=>$key
                    ];
                }
            }
        }
        return $this;
    }

    private function createImage($uploaded_file, $image_file_type) {
        $image = null;
        $image_fnc = "";
        switch ($image_file_type) {
            case "jpg":
            case "jpeg":
                $this->image_fnc = "imagejpeg";
                return imagecreatefromjpeg($uploaded_file);
                break;
            case "png":
                $this->image_fnc = "imagepng";
                return imagecreatefrompng($uploaded_file);
                break;
            case "gif":
                $this->image_fnc = "imagegif";
                return imagecreatefromgif($uploaded_file);
                break;
            default:
                return false;
        }
    }

    private function resizeImage() {
        if (!$this->max_width) return;
        $image_size = getimagesize($this->uploaded_file);
        if ($image_size[0] <= $this->max_width) return;
        return imagescale($this->image, $this->max_width);
    }

    private function makeThumbnail($filename) {
        $path = $this->upload_dir . "images/thumbnails/$filename";
        $this->image = imagescale($this->image, $this->thumb_width);
        ($this->image_fnc)($this->image, $path);
    }

    public function getResponse() {
        return $this->response;
    }
}