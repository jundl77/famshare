<?php
/**
 * This is the implementation of the server side part of
 * Resumable.js client script, which sends/uploads files
 * to a server in several chunks.
 *
 * The script receives the files in a standard way as if
 * the files were uploaded using standard HTML form (multipart).
 *
 * This PHP script stores all the chunks of a file in a temporary
 * directory (`temp`) with the extension `_part<#ChunkN>`. Once all
 * the parts have been uploaded, a final destination file is
 * being created from all the stored parts (appending one by one).
 *
 * @author Gregory Chris (http://online-php.com)
 * @email www.online.php@gmail.com
 */

include "../includes/sanitize.php";
include "../includes/utility.php";
$configs = include('../config/server/server_config.php');

// Check if request is GET and the requested chunk exists or not. this makes testChunks work
if (isset($_GET["resumableIdentifier"]) && !empty($_GET["resumableIdentifier"])) {
    $configs = $GLOBALS["configs"];

    // Get root directories
    $rootDir = $configs["root_upload_dirs"]["upload_data"];
    $thumbDir = $configs["root_upload_dirs"]["upload_data_thumb"];

    if (!file_exists($rootDir) || !file_exists($thumbDir)) {
        header('HTTP/1.1 500 Internal Server Error');
        header('Content-type: text/plain');
        exit("A root directory does not exist");
    }

    $path = sanitize($_GET["filePath"]);
    if ($path == false && !empty($val)) {
        header('HTTP/1.1 500 Internal Server Error');
        header('Content-type: text/plain');
        exit("Invalid file path: " . $path . ".");
    }

    $resumableIdentifier = sanitize($_GET['resumableIdentifier']);
    $resumableFilename = sanitize($_GET['resumableFilename']);
    $resumableChunkNumber = sanitize($_GET['resumableChunkNumber']);

    if (!$resumableIdentifier || !$resumableFilename || !$resumableChunkNumber) {
        die();
    }

    $temp = "73mp_";
    $fullDir = $rootDir . $path . $temp . $resumableIdentifier;
    $chunkFile = $fullDir . '/' . $resumableFilename . '.part' . $resumableChunkNumber;

    if (file_exists($chunkFile)) {
        header("HTTP/1.0 200 Ok");
    } else {
        header("HTTP/1.0 404 Not Found");
    }
}

if (!empty($_FILES)) {
    $configs = $GLOBALS["configs"];

    // Get root directories
    $rootDir = $configs["root_upload_dirs"]["upload_data"];
    $thumbDir = $configs["root_upload_dirs"]["upload_data_thumb"];

    // Get allowed file types
    $exts = $configs["legal_exts"];

    // Get allowed file size
    $max_size_script = $configs["max_size_byte_script"];

    // 1 GB in bytes
    $ONE_GB = 1073741824;

    if (!file_exists($rootDir) || !file_exists($thumbDir)) {
        header('HTTP/1.1 500 Internal Server Error');
        header('Content-type: text/plain');
        exit("A root directory does not exist");
    }

    $path = sanitize($_POST["filePath"]);
    if ($path == false && !empty($path)) {
        header('HTTP/1.1 500 Internal Server Error');
        header('Content-type: text/plain');
        exit("Invalid file path: " . $path . ".");
    }

    foreach ($_FILES as $file) {
        $resumableFilename = sanitize($_POST['resumableFilename']);
        if (!$resumableFilename) {
            header('HTTP/1.1 500 Internal Server Error');
            header('Content-type: text/plain');
            exit("Invalid file name");
        }

        $resumableTotalSize = sanitize($_POST['resumableTotalSize']);
        if (!$resumableTotalSize) {
            header('HTTP/1.1 500 Internal Server Error');
            header('Content-type: text/plain');
            exit("Invalid file size");
        }

        $fileExt = strtolower(end(explode('.', $resumableFilename)));
        if ($resumableFilename === '') {
            header('HTTP/1.1 500 Internal Server Error');
            header('Content-type: text/plain');
            exit("Please select a file");
        } elseif (!in_array($fileExt, $exts)) {
            header('HTTP/1.1 500 Internal Server Error');
            header('Content-type: text/plain');
            exit("Wrong file type selected");
        } elseif ($resumableTotalSize >= $max_size_script) {
            header('HTTP/1.1 500 Internal Server Error');
            header('Content-type: text/plain');
            exit("File is too large (max. " . $max_size_script / $ONE_GB . " GB)");
        } elseif (!file_exists($rootDir . $path)) {
            header('HTTP/1.1 500 Internal Server Error');
            header('Content-type: text/plain');
            exit("File directory does not exist");
        } else if ($file['error'] !== UPLOAD_ERR_OK) {
            header('HTTP/1.1 500 Internal Server Error');
            header('Content-type: text/plain');
            exit("Upload failed with error code " . sanitize($_FILES['file']['error']));
        } else {
            $resumableIdentifier = sanitize($_POST['resumableIdentifier']);
            $resumableChunkNumber = sanitize($_POST['resumableChunkNumber']);
            $resumableChunkSize = sanitize($_POST['resumableChunkSize']);

            if (!$resumableIdentifier || !$resumableChunkNumber || !$resumableChunkSize) {
                die();
            }

            // Init the destination file (format <filename.ext>.part<#chunk>
            // The file is stored in a temporary directory
            $temp = "73mp_";
            $fullDir = $rootDir . $path . $temp . $resumableIdentifier;
            $dest_file = $fullDir . '/' . $resumableFilename . '.part' . $resumableChunkNumber;

            // Create the temporary directory
            if (!is_dir($fullDir)) {
                mkdir($fullDir, 0777, true);
            }

            // Move the temporary file
            if (!move_uploaded_file($file['tmp_name'], $dest_file)) {
                header('HTTP/1.1 500 Internal Server Error');
                header('Content-type: text/plain');
                exit("Error during upload part");
            } else {
                $storePath = $rootDir . $path . $resumableFilename;
                $thumbPath = $thumbDir . $path . $resumableFilename;

                // Check if all the parts present, and assemble the final destination file
                if (create_file_from_chunks($fullDir, $rootDir . $path, $resumableFilename, $resumableChunkSize,
                    $resumableTotalSize)) {
                    make_thumb($storePath, $thumbPath, $fileExt, 200);
                } else {
                    header('HTTP/1.1 500 Internal Server Error');
                    header('Content-type: text/plain');
                    exit("Cannot assemble destination file");
                }
            }
        }
    }
}

/**
 * Check if all the parts exist, and gather all the parts of the file together
 *
 * @param string $tempDir - the temporary directory holding all the parts of the file
 * @param string @rootDir - the root directory where all files are saved too
 * @param string $fileName - the original file name
 * @param string $chunkSize - each chunk size (in bytes)
 * @param string $totalSize - original file size (in bytes)
 * @return bool true if the file was created from the chunks, else false
 */
function create_file_from_chunks($tempDir, $rootDir, $fileName, $chunkSize, $totalSize)
{
    // Count all the parts of this file
    $total_files = 0;
    foreach (scandir($tempDir) as $file) {
        if (stripos($file, $fileName) !== false) {
            $total_files++;
        }
    }

    // Check that all the parts are present
    // The size of the last part is between chunkSize and 2*$chunkSize
    if ($total_files * $chunkSize >= ($totalSize - $chunkSize + 1)) {

        // create the final destination file
        if (($fp = fopen($rootDir . $fileName, 'w')) !== false) {
            for ($i = 1; $i <= $total_files; $i++) {
                fwrite($fp, file_get_contents($tempDir . '/' . $fileName . '.part' . $i));
            }
            fclose($fp);
        } else {
            return false;
        }

        // Rename the temporary directory (to avoid access from other
        // Concurrent chunks uploads) and than delete it
        if (rename($tempDir, $tempDir . '_UNUSED')) {
            remove_dir_rec($tempDir . '_UNUSED');
        } else {
            remove_dir_rec($tempDir);
        }
    }

    return true;
}

/**
 * Delete a directory RECURSIVELY
 *
 * @param string $dir - directory path
 * @link http://php.net/manual/en/function.rmdir.php
 */
function remove_dir_rec($dir)
{
    if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (filetype($dir . "/" . $object) == "dir") {
                    remove_dir_rec($dir . "/" . $object);
                } else {
                    unlink($dir . "/" . $object);
                }
            }
        }
        reset($objects);
        rmdir($dir);
    }
}