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

    if (!preg_match_all("/^([\w ]*[.]*[(]*[)]*[-]*[\/]*)+$/", $_POST["filePath"]) && $_POST["filePath"] !== "") {
        echo 'Error, cannot download file.';
        exit;
    }

    $fullPath = $rootDir . $_POST["filePath"];

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