<?php
include "../includes/sanitize.php";
$configs = include('../config/server/server_config.php');

if (!empty($_FILES)) {
    $configs = $GLOBALS["configs"];

    // Get root directories
    $rootDir = $configs["root_upload_dirs"]["upload_data"];
    $thumbDir = $configs["root_upload_dirs"]["upload_data_thumb"];

    // Get allowed file types
    $exts = $configs["legal_exts"];

    // Get allowed file size
    $max_size_script = $configs["max_size_byte_script"];

    // 1 GB in bytes
    $ONE_GB = 1073741824;

    $files = array();

    if (!file_exists($rootDir) || !file_exists($thumbDir)) {
        header('HTTP/1.1 500 Internal Server Error');
        header('Content-type: text/plain');
        exit("A root directory does not exist");
    }

    $path = sanitize($_POST["filePath"]);

    foreach ($_FILES as $file) {
        $file_name = $file['name'];
        if (!preg_match_all("/^([\w ]*[.]*[(]*[)]*[-]*[\/]*)+$/", $file_name)) {
            header('HTTP/1.1 500 Internal Server Error');
            header('Content-type: text/plain');
            exit("Invalid file name");
        }

        $file_size = $file['size'];
        if (!preg_match_all("/^([\w ]*[.]*[\/]*)+$/", $file_size)) {
            header('HTTP/1.1 500 Internal Server Error');
            header('Content-type: text/plain');
            exit("Invalid file size");
        }

        $file_tmp_name = $file['tmp_name'];

        $fileExt = strtolower(end(explode('.', $file_name)));
        if ($file_name === '') {
            header('HTTP/1.1 500 Internal Server Error');
            header('Content-type: text/plain');
            exit("Please select a file");
        } elseif (!in_array($fileExt, $exts)) {
            header('HTTP/1.1 500 Internal Server Error');
            header('Content-type: text/plain');
            exit("Wrong file type selected");
        } elseif ($file_size >= $max_size_script) {
            header('HTTP/1.1 500 Internal Server Error');
            header('Content-type: text/plain');
            exit("File is too large (max. " . $max_size_script / $ONE_GB . " GB)");
        } elseif (!file_exists($rootDir . $path)) {
            header('HTTP/1.1 500 Internal Server Error');
            header('Content-type: text/plain');
            exit("File directory does not exist");
        } else if ($file['error'] !== UPLOAD_ERR_OK) {
            header('HTTP/1.1 500 Internal Server Error');
            header('Content-type: text/plain');
            exit("Upload failed with error code " . $_FILES['file']['error']);
        } else {
            ini_set('upload_max_filesize', $configs["upload_max_filesize"]);
            ini_set('post_max_size', $configs["post_max_size"]);
            ini_set('max_input_time', $configs["max_input_time"]);
            ini_set('max_execution_time', $configs["max_execution_time"]);
            $storePath = $rootDir . $path . $file_name;
            $thumbPath = $thumbDir . $path . $file_name;

            if (move_uploaded_file($file_tmp_name, $storePath)) {
                make_thumb($storePath, $thumbPath, $fileExt, 200);
            } else {
                header('HTTP/1.1 500 Internal Server Error');
                header('Content-type: text/plain');
                exit("Unexpected error encountered");
            }
        }
    }
}

function make_thumb($src, $dest, $fileExt, $desired_width) {
    /* read the source image */
    if ($fileExt == 'gif') {
        $source_image = imagecreatefromgif($src);
    } elseif ($fileExt == 'jpg' || $fileExt == 'jpeg') {
        $source_image = imagecreatefromjpeg($src);
    } elseif ($fileExt == 'png') {
        $source_image = imagecreatefrompng($src);
    } elseif ($fileExt == 'wbmp') {
        $source_image = imagecreatefromwbmp($src);
    } else {
        // Return if not an image
        return;
    }

    $width = imagesx($source_image);
    $height = imagesy($source_image);

    /* find the "desired height" of this thumbnail, relative to the desired width  */
    $desired_height = floor($height * ($desired_width / $width));

    /* create a new, "virtual" image */
    $virtual_image = imagecreatetruecolor($desired_width, $desired_height);

    /* copy source image at a resized size */
    imagecopyresampled($virtual_image, $source_image, 0, 0, 0, 0, $desired_width, $desired_height, $width, $height);

    /* create the physical thumbnail image to its destination */
    if ($fileExt == 'gif') {
        imagegif($virtual_image, $dest);
    } elseif ($fileExt == 'jpg' || $fileExt == 'jpeg') {
        imagejpeg($virtual_image, $dest);
    } elseif ($fileExt == 'png') {
        imagepng($virtual_image, $dest);
    } elseif ($fileExt == 'bmp') {
        imagewbmp($virtual_image, $dest);
    }
}