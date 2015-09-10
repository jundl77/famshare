<?php
include "../includes/sanitize.php";
$configs = include('../config/server/server_config.php');

/**
 * The mediaViewHandler sends data to the media view of the file hub. It ensures that images are seen in full size and
 * that videos can be streamed directly from the file hub.
 */

/**
 * Checks if the incoming request is asking for information
 */
if (isset($_POST["file"]) && !empty($_POST["file"]) && $_POST["type"] == "info") {
    $configs = $GLOBALS["configs"];

    // Get root directories
    $rootDir = $configs["root_upload_dirs"]["upload_data"];

    if (!file_exists($rootDir)) {
        echo json_encode(array('state' => "error", 'content' => "Error, a root directory does not exist"));
        exit;
    }

    $file = sanitize($_POST["file"]);
    if (!$file) {
        die();
    }

    $file = substr($file, 1);

    $dataPath = $rootDir . $file;

    if (!file_exists($dataPath)) {
        echo json_encode(array('state' => "error", 'content' => "Error, path does not exist"));
        exit;
    }

    list($width, $height) = getimagesize($dataPath);
    $jsonData = array(
        "width" => $width,
        "height" => $height
    );

    if ($jsonData != null) {
        echo json_encode(array('state' => "success", 'content' => $jsonData));
    } else {
        echo json_encode(array('state' => "error", 'content' => "Error, unable to get files"));
    }
}

/**
 * Checks if the incoming request is asking for a video streaming
 */
else if (isset($_GET["video"]) && !empty($_GET["video"])) {
    $configs = $GLOBALS["configs"];

    // Get root directories
    $rootDir = $configs["root_upload_dirs"]["upload_data"];

    if (!file_exists($rootDir)) {
        die();
    }

    $file = sanitize($_GET["video"]);
    if (!$file) {
        die();
    }

    $file = substr($file, 1);
    $fileExt = strtolower(end(explode('.', $file)));

    $full_dir = $rootDir . $file;

    if (file_exists($full_dir) && $fileExt == 'mp4') {
        $stream = new VideoStream($full_dir);
        $stream->start();
        exit;
    }
}

/**
 * Checks if the incoming request is asking for a full size image
 */
else if (isset($_GET["image"]) && !empty($_GET["image"])) {
    $configs = $GLOBALS["configs"];

    // Get root directories
    $rootDir = $configs["root_upload_dirs"]["upload_data"];

    if (!file_exists($rootDir)) {
        die();
    }

    $file = sanitize($_GET["image"]);
    if (!$file) {
        die();
    }

    $file = substr($file, 1);
    $exts = array('jpg', 'jpeg', 'gif', 'png', 'wbmp');

    $fileExt = strtolower(end(explode('.', $file)));
    $correctExt = in_array($fileExt, $exts);

    if ($correctExt && file_exists($rootDir . $file)) {
        header('Content-Type: image/' . $fileExt);
        readfile($rootDir . $file);
        exit();
    }
}

/**
 * Description of VideoStream
 *
 * @author Rana
 * @link http://codesamplez.com/programming/php-html5-video-streaming-tutorial
 */
class VideoStream
{
    private $path = "";
    private $stream = "";
    private $buffer = 102400;
    private $start = -1;
    private $end = -1;
    private $size = 0;

    function __construct($filePath)
    {
        $this->path = $filePath;
    }

    /**
     * Open stream
     */
    private function open()
    {
        if (!($this->stream = fopen($this->path, 'rb'))) {
            die('Could not open stream for reading');
        }

    }

    /**
     * Set proper header to serve the video content
     */
    private function setHeader()
    {
        ob_get_clean();
        header("Content-Type: video/mp4");
        header("Cache-Control: max-age=2592000, public");
        header("Expires: " . gmdate('D, d M Y H:i:s', time() + 2592000) . ' GMT');
        header("Last-Modified: " . gmdate('D, d M Y H:i:s', @filemtime($this->path)) . ' GMT');
        $this->start = 0;
        $this->size = filesize($this->path);
        $this->end = $this->size - 1;
        header("Accept-Ranges: 0-" . $this->end);

        if (isset($_SERVER['HTTP_RANGE'])) {

            $c_start = $this->start;
            $c_end = $this->end;

            list(, $range) = explode('=', $_SERVER['HTTP_RANGE'], 2);
            if (strpos($range, ',') !== false) {
                header('HTTP/1.1 416 Requested Range Not Satisfiable');
                header("Content-Range: bytes $this->start-$this->end/$this->size");
                exit;
            }
            if ($range == '-') {
                $c_start = $this->size - substr($range, 1);
            } else {
                $range = explode('-', $range);
                $c_start = $range[0];

                $c_end = (isset($range[1]) && is_numeric($range[1])) ? $range[1] : $c_end;
            }
            $c_end = ($c_end > $this->end) ? $this->end : $c_end;
            if ($c_start > $c_end || $c_start > $this->size - 1 || $c_end >= $this->size) {
                header('HTTP/1.1 416 Requested Range Not Satisfiable');
                header("Content-Range: bytes $this->start-$this->end/$this->size");
                exit;
            }
            $this->start = $c_start;
            $this->end = $c_end;
            $length = $this->end - $this->start + 1;
            fseek($this->stream, $this->start);
            header('HTTP/1.1 206 Partial Content');
            header("Content-Length: " . $length);
            header("Content-Range: bytes $this->start-$this->end/" . $this->size);
        } else {
            header("Content-Length: " . $this->size);
        }

    }

    /**
     * Close curretly opened stream
     */
    private function end()
    {
        fclose($this->stream);
        exit;
    }

    /**
     * Perform the streaming of calculated range
     */
    private function stream()
    {
        $i = $this->start;
        set_time_limit(0);
        while (!feof($this->stream) && $i <= $this->end) {
            $bytesToRead = $this->buffer;
            if (($i + $bytesToRead) > $this->end) {
                $bytesToRead = $this->end - $i + 1;
            }
            $data = fread($this->stream, $bytesToRead);
            echo $data;
            flush();
            $i += $bytesToRead;
        }
    }

    /**
     * Start streaming video content
     */
    function start()
    {
        $this->open();
        $this->setHeader();
        $this->stream();
        $this->end();
    }
}