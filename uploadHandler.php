<?php
$rootDir = './uploadData/';

if (!empty($_FILES)) {
    $files = array();
    $rootDir = $GLOBALS["rootDir"];
    if (!file_exists($rootDir)) {
        $rootDir = "./uploadData/";
    }
    $thumbDir = str_replace("uploadData","uploadDataThumb", $rootDir);

    if (!preg_match_all("/^([\w ]*[.]*[(]*[)]*[-]*[\/]*)+$/", $_POST["filePath"]) && $_POST["filePath"] !== "") {
        header('HTTP/1.1 500 Internal Server Error');
        header('Content-type: text/plain');
        exit("File directory does not exist");
    }
    $path = $_POST["filePath"];
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

        //the following are all the allowed file types:
        $exts = array('jpg', 'jpeg', 'gif', 'png', 'txt', 'mp3', 'mp4', 'mpg', 'mov', 'm4v', 'pdf', 'doc', 'docx',
        'ppt', 'wmv');
        $imgExts = array('jpg', 'jpeg', 'gif', 'png');
        $fileExt = strtolower(end(explode('.', $file_name)));
        $correctExt = in_array($fileExt, $exts);
        if ($file_name === '') {
            header('HTTP/1.1 500 Internal Server Error');
            header('Content-type: text/plain');
            exit("Please select a file");
        } elseif (!$correctExt) {
            header('HTTP/1.1 500 Internal Server Error');
            header('Content-type: text/plain');
            exit("Wrong file type selected");
        } elseif ($file_size >= 10737418240) {
            header('HTTP/1.1 500 Internal Server Error');
            header('Content-type: text/plain');
            exit("File is too large (max. 10 GB)");
        } elseif (!file_exists($rootDir . $path)) {
            header('HTTP/1.1 500 Internal Server Error');
            header('Content-type: text/plain');
            exit("File directory does not exist");
        } else {
            ini_set('upload_max_filesize', '10000M');
            ini_set('post_max_size', '10000M');
            ini_set('max_input_time', 36000);
            ini_set('max_execution_time', 36000);
            $storePath = $rootDir . $path . $file_name;
            $thumbPath = $thumbDir . $path . $file_name;
            move_uploaded_file($file_tmp_name, $storePath);
            if (in_array($fileExt, $imgExts)) {
                make_thumb($storePath, $thumbPath, 200);
            }
        }
    }
}

function make_thumb($src, $dest, $desired_width) {
    /* read the source image */
    $source_image = imagecreatefromjpeg($src);
    $width = imagesx($source_image);
    $height = imagesy($source_image);

    /* find the "desired height" of this thumbnail, relative to the desired width  */
    $desired_height = floor($height * ($desired_width / $width));

    /* create a new, "virtual" image */
    $virtual_image = imagecreatetruecolor($desired_width, $desired_height);

    /* copy source image at a resized size */
    imagecopyresampled($virtual_image, $source_image, 0, 0, 0, 0, $desired_width, $desired_height, $width, $height);

    /* create the physical thumbnail image to its destination */
    imagejpeg($virtual_image, $dest);
}
