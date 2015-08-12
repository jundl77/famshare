<?php
$rootDir = './uploadData/';

if (isset($_POST["filePath"]) && !empty($_POST["filePath"])) {
    $rootDir = $GLOBALS["rootDir"];
    if (!file_exists($rootDir)) {
        $rootDir = "./uploadData/";
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