<?php
include "../includes/sanitize.php";
$configs = include('../config/server/server_config.php');

/**
 * The FileSystemHandler handles all the server side functionality to the file hub. For example, it worries about
 * creating new folders, deleting files and folders, getting the file structure etc. However it has nothing to do
 * with file uploads.
 */

/**
 * Check for request to get file structure
 */
if (isset($_POST["command"]) && !empty($_POST["command"]) && $_POST["command"] == "fileStructure") {
    $configs = $GLOBALS["configs"];

    // Get root directories
    $rootDir = $configs["root_upload_dirs"]["upload_data"];

    if (!file_exists($rootDir)) {
        echo json_encode(array('state' => "error", 'content' => "Error, a root directory does not exist"));
        exit;
    }

    $fileStructure = getFileSystem($rootDir, null);
    $jsonFileStructure = json_encode($fileStructure);

    if ($jsonFileStructure != null) {
        echo json_encode(array('state' => "success", 'content' => $jsonFileStructure));
    } else {
        echo json_encode(array('state' => "error", 'content' => "Error, unable to get file structure"));
    }
}

/**
 * Check for request to get files in a folder
 */
else if (isset($_POST["command"]) && !empty($_POST["command"]) && $_POST["command"] == "files") {
    $configs = $GLOBALS["configs"];

    // Get root directories
    $rootDir = $configs["root_upload_dirs"]["upload_data"];
    $thumbDir = $configs["root_upload_dirs"]["upload_data_thumb"];

    if (!file_exists($rootDir) || !file_exists($thumbDir)) {
        echo json_encode(array('state' => "error", 'content' => "Error, a root directory does not exist"));
        exit;
    }

    $dataPath = $rootDir . sanitize($_POST["path"]);
    $thumbPath = $thumbDir . sanitize($_POST["path"]);

    if (!file_exists($dataPath) || !file_exists($thumbPath)) {
        echo json_encode(array('state' => "error", 'content' => "Error, new path does not exist"));
        exit;
    }

    $files = getFilesInFolder($dataPath, $thumbPath);
    $jsonFiles = json_encode($files);

    if ($jsonFiles != null) {
        echo json_encode(array('state' => "success", 'content' => $jsonFiles));
    } else {
        echo json_encode(array('state' => "error", 'content' => "Error, unable to get files"));
    }
}

/**
 * Check for request to make a new folder
 */
else if (isset($_POST["command"]) && !empty($_POST["command"]) && $_POST["command"] == "newFolder") {
    $configs = $GLOBALS["configs"];

    // Get root directories
    $rootDir = $configs["root_upload_dirs"]["upload_data"];
    $thumbDir = $configs["root_upload_dirs"]["upload_data_thumb"];

    if (!file_exists($rootDir) || !file_exists($thumbDir)) {
        echo json_encode(array('state' => "error", 'content' => "Error, a root directory does not exist"));
        exit;
    }

    $path = sanitize($_POST["path"]);
    if (!$path) {
        echo json_encode(array('state' => "error", 'content' => "Error, invalid folder name"));
        die();
    }

    $successNormal = mkdir($rootDir . $path);
    $successThumbnail = mkdir($thumbDir . $path);

    if ($successNormal && $successThumbnail) {
        echo json_encode(array('state' => "success", 'content' => "Created new folder"));
    } else {
        echo json_encode(array('state' => "error", 'content' => "Error, unable to create new directory"));
    }
}

/**
 * Check for request to delete a folder
 */
else if (isset($_POST["command"]) && !empty($_POST["command"]) && $_POST["command"] == "deleteFolder") {
    $configs = $GLOBALS["configs"];

    // Get root directories
    $rootDir = $configs["root_upload_dirs"]["upload_data"];
    $thumbDir = $configs["root_upload_dirs"]["upload_data_thumb"];

    if (!file_exists($rootDir) || !file_exists($thumbDir)) {
        echo json_encode(array('state' => "error", 'content' => "Error, a root directory does not exist"));
        exit;
    }

    $path = sanitize($_POST["path"]);
    if (!$path) {
        echo json_encode(array('state' => "error", 'content' => "Error, unable to delete folder"));
        die();
    }

    try {
        // Check if the file is a temp file
        if (!file_exists($rootDir . $path)) {
            $pathArray = explode("/", $path);

            $parentDir = $rootDir;
            $fileBeginning = array_pop($pathArray);
            for ($i = 0; $i < sizeof($pathArray); $i++) {
                $parentDir .= $pathArray[$i] . "/";
            }

            $dirArray = scandir($parentDir);
            foreach ($dirArray as $file) {
                if ($tempFile = strpos($file, $fileBeginning) !== false) {
                    deleteDir($parentDir . "/" . $file);

                    exit(json_encode(array('state' => "success", 'content' => "Deleted file successfully")));
                }
            }
        } else {
            deleteDir($rootDir . $path);
            deleteDir($thumbDir . $path);
            echo json_encode(array('state' => "success", 'content' => "Deleted folder successfully"));
        }
    } catch (Exception $e) {
        echo json_encode(array('state' => "error", 'content' => "Error, unable to delete folder"));
    }
}

/**
 * Check for request to delete a file
 */
else if (isset($_POST["command"]) && !empty($_POST["command"]) && $_POST["command"] == "deleteFile") {
    $configs = $GLOBALS["configs"];

    // Get root directories
    $rootDir = $configs["root_upload_dirs"]["upload_data"];
    $thumbDir = $configs["root_upload_dirs"]["upload_data_thumb"];

    if (!file_exists($rootDir) || !file_exists($thumbDir)) {
        echo json_encode(array('state' => "error", 'content' => "Error, a root directory does not exist"));
        exit;
    }

    $path = sanitize($_POST["path"]);
    if (!$path) {
        echo json_encode(array('state' => "error", 'content' => "Error, unable to delete file"));
        die();
    }

    $successNormal = unlink($rootDir . $path);

    $successThumbnail = true;
    if (file_exists($thumbDir . $path)) {
        $successThumbnail = unlink($thumbDir . $path);
    }

    if ($successNormal && $successThumbnail) {
        echo json_encode(array('state' => "success", 'content' => "Deleted file successfully."));
    } else {
        echo json_encode(array('state' => "error", 'content' => "Error, unable to delete file"));
    }
}

/**
 * Recursively get the file structure of the root folder set in the config file and return it as an array
 *
 * @param $path string the path of the root folder - so where to start getting the file structure
 * @param $fileSystem array the current array of the fileSystem, exists because of recursion
 * @return array the final file structure as a graph in an array
 */
function getFileSystem($path, $fileSystem)
{
    // On first time search root directory
    if ($fileSystem == null) {
        $fileSystemArray = scandir($path);

        // Remove ., .. and .DS_Store objects
        $tempFileSystemArray = array();
        $size = count($fileSystemArray);
        for ($i = 0; $i < $size; $i++) {
            $newPath = $path . $fileSystemArray[$i] . "/";
            $tempFile = strpos($fileSystemArray[$i], "73mp") !== false;
            if ($fileSystemArray[$i] != "." && $fileSystemArray[$i] != ".." && !$tempFile
                && $fileSystemArray[$i] != ".DS_Store" && is_dir($newPath)
            ) {
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
            $tempFile = strpos($newFileSystemArray[$j], "73mp") !== false;
            if ($newFileSystemArray[$j] != "." && $newFileSystemArray[$j] != ".." && !$tempFile
                && $newFileSystemArray[$j] != ".DS_Store" && is_dir($newPathLocal)
            ) {
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

/**
 * Get the files in a folder as an array
 *
 * @param $dataPath string the path to the parent folder
 * @param $thumbPath string the path to the parent folder where the thumbnails are kept for images
 * @return array a final array containing a list of files in the given directory with some extra data for each file
 */
function getFilesInFolder($dataPath, $thumbPath)
{
    $result = array();
    $exts = array('jpg', 'jpeg', 'gif', 'png', 'wbmp');
    $files = scandir($dataPath);

    if (false !== $files) {
        foreach ($files as $file) {
            $newDataPath = $dataPath . $file;
            $newThumbPath = $thumbPath . $file;
            $tempFile = strpos($file, "73mp") !== false;
            if (('.' != $file && '..' != $file && $file != ".DS_Store" && !is_dir($newDataPath)) || $tempFile) {
                if ($tempFile) {
                    $pathArray = explode("XX4242XX", $file);
                    $obj['name'] = $pathArray[1];

                    $io = popen ( '/usr/bin/du -sk ' . $newDataPath, 'r' );
                    $size = fgets ( $io, 4096);
                    $size = substr ( $size, 0, strpos ( $size, "\t" ) );
                    pclose ( $io );
                    $obj['size'] = $size;

                    $obj['type'] = "temp";
                } else {
                    $fileExt = strtolower(end(explode('.', $file)));
                    $correctExt = in_array($fileExt, $exts);
                    $obj['name'] = $file;

                    $obj['size'] = filesize($newDataPath);

                    if ($correctExt && file_exists($newThumbPath)) {
                        $obj['type'] = "image";
                    } else {
                        $obj['type'] = "null";
                    }
                }

                $result[] = $obj;
            }
        }
    }

    return array_values($result);
}

/**
 * Delete the directory at the given path recursively
 *
 * @param $dirPath string the location of the directory to delete
 */
function deleteDir($dirPath)
{
    if (!is_dir($dirPath)) {
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

