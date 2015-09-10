<?php
include "../includes/sanitize.php";
$configs = include('../config/server/server_config.php');

/**
 * The thumbnailHandler sends back the right thumbnail for a given file. There are four types of thumbnails.
 *  1. Real thumbnails of images
 *  2. Thumbnails for file types if the file type is has a thumbnail for it and the file type is not an image
 *  3. Thumbails for temporary files fromt the resumable upload
 *  4. A blank thumbnail if all else failed
 */

if (isset($_POST["file"]) && !empty($_POST["file"])) {
    $configs = $GLOBALS["configs"];

    $thumbDir = $configs["root_upload_dirs"]["upload_data_thumb"];

    if (!file_exists($thumbDir)) {
        echo json_encode(array('state' => "error", 'content' => "A root directory does not exist"));
        exit;
    }

    $file = sanitize($_POST["file"]);
    if (!$file) {
        echo json_encode(array('state' => "error", 'content' => "Error, unable to load thumbnail " . $file));
        die();
    }

    $type = sanitize($_POST["type"]);
    if (!$type) {
        echo json_encode(array('state' => "error", 'content' => "Error, unable to load thumbnail " . $file));
        die();
    }

    $file = substr($file, 1);

    $exts = array('jpg', 'jpeg', 'gif', 'png', 'wbmp');
    $ICON_FOLDER = "../images/file_icons/";

    $fileExt = strtolower(end(explode('.', $file)));
    $correctExt = in_array($fileExt, $exts);

    if ($correctExt && file_exists($thumbDir . $file)) {
        $obj['thumb_data'] = base64_encode(file_get_contents($thumbDir . $file));
    } else if (is_file($ICON_FOLDER . $fileExt . ".png")) {
        $obj['thumb_data'] = base64_encode(file_get_contents($ICON_FOLDER . $fileExt . ".png"));
    } else if ($type === "temp") {
        $obj['thumb_data'] = base64_encode(file_get_contents($ICON_FOLDER . "_temp.png"));
    } else {
        $obj['thumb_data'] = base64_encode(file_get_contents($ICON_FOLDER . "_blank.png"));
    }

    if ($obj != null) {
        $json_obj = json_encode($obj);
        echo json_encode(array('state' => "success", 'content' => $json_obj));
    } else {
        echo json_encode(array('state' => "error", 'content' => "Error, unable to load thumbnail"));
    }
}