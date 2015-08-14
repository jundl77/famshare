<?php
$configs = include('./config/server/server_config.php');

if (isset($_POST["filePath"]) && !empty($_POST["filePath"])) {
    $configs = $GLOBALS["configs"];

    // Get root directories
    $rootDir = $configs["root_upload_dirs"]["upload_data"];

    if (!file_exists($rootDir)) {
        echo json_encode(array('state' => "error", 'content' => "A root directory does not exist"));
        exit;
    }

    $fullPath = $rootDir . sanitize($_POST["filePath"]);

    if (!is_file($fullPath)) {
        exit;
    }
    $fileNameParts = explode("/", $fullPath);
    $fileName = $fileNameParts[sizeof($fileNameParts) - 1];
    header("Content-disposition: attachment; filename=" . $fileName);
    header("Content-Type: " . mime_content_type($fullPath));
    readfile($fullPath);
    exit();
}

function sanitize($val) {
    if (!preg_match_all("/^([\w ]*[.]*[(]*[)]*[-]*[\/]*)+$/", $val) && $val !== "") {
        die("Error, cannot download file.");
    }

    $val = trim($val);
    $val = strip_tags($val);
    $val = htmlentities($val, ENT_QUOTES, 'UTF-8'); // convert funky chars to html entities
    $pat = array("\r\n", "\n\r", "\n", "\r"); // remove returns
    $val = str_replace($pat, '', $val);
    $pat = array('/^\s+/', '/\s{2,}/', '/\s+\$/'); // remove multiple whitespaces
    $rep = array('', ' ', '');
    $val = preg_replace($pat, $rep, $val);
    return trim($val);
}