<?php
$configs = include('../config/server/server_config.php');

if (isset($_POST["file"]) && !empty($_POST["file"])) {
    $configs = $GLOBALS["configs"];

    $rootDir = $configs["root_upload_dirs"]["upload_data"];
    $thumbDir = $configs["root_upload_dirs"]["upload_data_thumb"];

    if (!file_exists($rootDir) || !file_exists($thumbDir)) {
        echo json_encode(array('state' => "error", 'content' => "A root directory does not exist"));
        exit;
    }

    $file = $_POST["file"];
    $file = substr($file, 1);
    $exts = array('jpg', 'jpeg', 'gif', 'png', 'wbmp');
    $ICON_FOLDER = "../images/file_icons/";

    $fileExt = strtolower(end(explode('.', $file)));
    $correctExt = in_array($fileExt, $exts);

    if ($correctExt) {
        $obj['thumb_data'] = base64_encode(file_get_contents($thumbDir . $file));
        $obj['is_image'] = true;
    } else if (is_file($ICON_FOLDER . $fileExt . ".png")) {
        $obj['thumb_data'] = base64_encode(file_get_contents($ICON_FOLDER . $fileExt . ".png"));
        $obj['is_image'] = false;
    } else {
        $obj['thumb_data'] = base64_encode(file_get_contents($ICON_FOLDER . "_blank.png"));
        $obj['is_image'] = false;
    }

    $json_obj = json_encode($obj);

    echo json_encode(array('state' => "success", 'content' => $json_obj));
}
