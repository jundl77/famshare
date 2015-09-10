<?php
include "../includes/sanitize.php";
$configs = include('../config/server/server_config.php');

/**
 * The downloadHandler manages download request. It sends the content of a file on request back to the client.
 */

if (isset($_POST["filePath"]) && !empty($_POST["filePath"])) {
    $configs = $GLOBALS["configs"];

    // Get root directories
    $rootDir = $configs["root_upload_dirs"]["upload_data"];

    if (!file_exists($rootDir)) {
        header("Location: ../errorScreen.php");
        die();
    }

    $fullPath = $rootDir . sanitize($_POST["filePath"]);

    if (!is_file($fullPath)) {
        header("Location: ../errorScreen.php");
        die();
    }

    $fileNameParts = explode("/", $fullPath);
    $fileName = $fileNameParts[sizeof($fileNameParts) - 1];
    header("Content-disposition: attachment; filename=" . $fileName);
    header("Content-Type: " . mime_content_type($fullPath));
    readfile($fullPath);
    exit();
}