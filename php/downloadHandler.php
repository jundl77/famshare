<?php
include "../includes/sanitize.php";
$configs = include('../config/server/server_config.php');

if (isset($_POST["filePath"]) && !empty($_POST["filePath"])) {
    $configs = $GLOBALS["configs"];

    // Get root directories
    $rootDir = $configs["root_upload_dirs"]["upload_data"];

    if (!file_exists($rootDir)) {
        die("A root directory does not exist");
    }

    $fullPath = $rootDir . sanitize($_POST["filePath"]);

    if (!is_file($fullPath)) {
        die("File does not exist");
    }

    $fileNameParts = explode("/", $fullPath);
    $fileName = $fileNameParts[sizeof($fileNameParts) - 1];
    header("Content-disposition: attachment; filename=" . $fileName);
    header("Content-Type: " . mime_content_type($fullPath));
    readfile($fullPath);
    exit();
}