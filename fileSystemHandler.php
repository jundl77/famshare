<?php
$rootDir = "./uploadData/";

if (isset($_POST["command"]) && !empty($_POST["command"]) && $_POST["command"] == "fileStructure") {
    $rootDir = $GLOBALS["rootDir"];
    if (!file_exists($rootDir)) {
        $rootDir = "./uploadData/";
    }

    $fileStructure = getFileSystem($rootDir, null);
    $jsonFileStructure = json_encode($fileStructure);

    if ($jsonFileStructure != null) {
        echo json_encode(array('state' => "success", 'content' => $jsonFileStructure));
    } else {
        echo json_encode(array('state' => "error", 'content' => "Unable to get file structure."));
    }
} else if (isset($_POST["command"]) && !empty($_POST["command"]) && $_POST["command"] == "files") {
    $rootDir = $GLOBALS["rootDir"];
    if (!preg_match_all("/^([\w ]*[.]*[(]*[)]*[-]*[\/]*)+$/", $_POST["path"]) &&  $_POST["path"] !== "") {
        echo json_encode(array('state' => "error", 'content' => "Invalid path received."));
        exit;
    }

    $path = $rootDir . $_POST["path"];
    if (!file_exists($rootDir)) {
        $rootDir = "./uploadData/";
    }

    if (!file_exists($path)) {
        echo json_encode(array('state' => "error", 'content' => "New path does not exist."));
        exit;
    }

    $files = getFilesInFolder($path);
    $jsonFiles = json_encode($files);

    if ($jsonFiles != null) {
        echo json_encode(array('state' => "success", 'content' => $jsonFiles));
    } else {
        echo json_encode(array('state' => "error", 'content' => "Unable to get files."));
    }
} else if (isset($_POST["command"]) && !empty($_POST["command"]) && $_POST["command"] == "newFolder") {
    $rootDir = $GLOBALS["rootDir"];
    if (!file_exists($rootDir)) {
        $rootDir = "./uploadData/";
    }
    $thumbDir = str_replace("uploadData","uploadDataThumb", $rootDir);

    if (!preg_match_all("/^([\w ]*[.]*[(]*[)]*[-]*[\/]*)+$/", $_POST["path"]) && $_POST["path"] !== "") {
        echo json_encode(array('state' => "error", 'content' => "Invalid path received."));
        exit;
    }
    $path = $_POST["path"];
    $successNormal = mkdir($rootDir . $path);
    $successThumbnail = mkdir($thumbDir . $path);

    if ($successNormal && $successThumbnail) {
        echo json_encode(array('state' => "success", 'content' => "Created new folder"));
    } else {
        echo json_encode(array('state' => "error", 'content' => "Unable to create new directory."));
    }
} else if (isset($_POST["command"]) && !empty($_POST["command"]) && $_POST["command"] == "deleteFolder") {
    $rootDir = $GLOBALS["rootDir"];
    if (!file_exists($rootDir)) {
        $rootDir = "./uploadData/";
    }
    $thumbDir = str_replace("uploadData","uploadDataThumb", $rootDir);

    if (!preg_match_all("/^([\w ]*[.]*[(]*[)]*[-]*[\/]*)+$/", $_POST["path"]) && $_POST["path"] !== "") {
        echo json_encode(array('state' => "error", 'content' => "Invalid path received."));
        exit;
    }
    $path = $_POST["path"];
    try {
        deleteDir($rootDir . $path);
        deleteDir($thumbDir . $path);
        echo json_encode(array('state' => "success", 'content' => "Deleted folder successfully."));
    } catch (Exception $e) {
        echo json_encode(array('state' => "error", 'content' => "Unable to delete folder: " . $e));
    }
} else if (isset($_POST["command"]) && !empty($_POST["command"]) && $_POST["command"] == "deleteFile") {
    $rootDir = $GLOBALS["rootDir"];
    if (!file_exists($rootDir)) {
        $rootDir = "./uploadData/";
    }
    $thumbDir = str_replace("uploadData","uploadDataThumb", $rootDir);

    if (!preg_match_all("/^([\w ]*[.]*[(]*[)]*[-]*[\/]*)+$/", $_POST["path"]) && $_POST["path"] !== "") {
        echo json_encode(array('state' => "error", 'content' => "Invalid path received."));
        exit;
    }
    $path = $_POST["path"];
    $successNormal = unlink($rootDir . $path);

    $successThumbnail = true;
    if (file_exists($thumbDir . $path)) {
        $successThumbnail = unlink($thumbDir . $path);
    }

    if ($successNormal && $successThumbnail) {
        echo json_encode(array('state' => "success", 'content' => "Deleted file successfully."));
    } else {
        echo json_encode(array('state' => "error", 'content' => "Unable to delete file: " . $rootDir . $path));
    }
}


function getFileSystem($path, $fileSaystem) {
    // On first time search root directory
    if ($fileSystem == null) {
        $fileSystemArray = scandir($path);

        // Remove ., .. and .DS_Store objects
        $tempFileSystemArray = array();
        $size = count($fileSystemArray);
        for ($i = 0; $i < $size; $i++) {
            $newPath = $path . $fileSystemArray[$i] . "/";
            if ($fileSystemArray[$i] != "." && $fileSystemArray[$i] != ".."
                && $fileSystemArray[$i] != ".DS_Store" && is_dir($newPath)) {
                array_push($tempFileSystemArray, $fileSystemArray[$i]);
            }
        }
        $fileSystemArray = $tempFileSystemArray;
        $fileSystemArray = array_values($fileSystemArray);

        $fileSystem = array('name' => "/", 'content' => null);
        $size = count($fileSystemArray);
        if ($size > 0) {
            $fileSystem['content'] = $fileSystemArray;
            return getFileSystem($path, $fileSystem);
        }
    }

    // Search all folders in the folder again
    $fileSystemArray = $fileSystem['content'];
    $size = count($fileSystemArray);
    for ($i = 0; $i < $size; $i++) {
        $tempFileSystemArray = array();
        $newPath = $path . $fileSystemArray[$i] . "/";
        $newFileSystemArray = scandir($newPath);

        // Only keep folders, ignore everything else
        $newSize = count($newFileSystemArray);
        for ($j = 0; $j < $newSize; $j++) {
            $newPathLocal = $newPath . $newFileSystemArray[$j] . "/";
            if ($newFileSystemArray[$j] != "." && $newFileSystemArray[$j] != ".."
                && $newFileSystemArray[$j] != ".DS_Store" && is_dir($newPathLocal)) {
                array_push($tempFileSystemArray, $newFileSystemArray[$j]);
            }
        }
        $newFileSystemArray = $tempFileSystemArray;
        $newFileSystemArray = array_values($newFileSystemArray);

        // Connect the found folder to the existing structure
        $newSize = count($newFileSystemArray);
        $folder = array('name' => $fileSystemArray[$i], 'content' => null);
        if ($newSize > 0) {
            $folder['content'] = $newFileSystemArray;
            $fileSystem['content'][$i] = getFileSystem($newPath, $folder);
        } else {
            $fileSystem['content'][$i] = $folder;
        }
    }

    // Return the found folder
    return $fileSystem;
}

function getFilesInFolder($path) {
    $result  = array();
    $exts = array('jpg', 'jpeg', 'gif', 'png');
    $files = scandir($path);

    if (false !== $files) {
        foreach ($files as $file) {
            $newPath = $path . $file;
            if ( '.' != $file && '..' != $file && $file != ".DS_Store" && !is_dir($newPath)) {
                $fileExt = strtolower(end(explode('.', $file)));
                $correctExt = in_array($fileExt, $exts);
                $obj['name'] = $file;
                $obj['size'] = filesize($newPath);
                if ($correctExt) {
                    $obj['path'] = $newPath;
                } else {
                    $obj['path'] = null;
                }
                $result[] = $obj;
            }
        }
    }

    return array_values($result);
}

function deleteDir($dirPath) {
    if (! is_dir($dirPath)) {
        throw new InvalidArgumentException("$dirPath must be a directory");
    }
    if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
        $dirPath .= '/';
    }
    $files = glob($dirPath . '*', GLOB_MARK);
    foreach ($files as $file) {
        if (is_dir($file)) {
            deleteDir($file);
        } else {
            unlink($file);
        }
    }
    rmdir($dirPath);
}

