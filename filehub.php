<?php
include "./includes/sanitize.php";

$configs = include('./config/server/server_config.php');

$realm = 'Restricted area';
$username = "user";
$password = $configs["password"];

//user => password
$users = array($username => $password);

if (empty($_SERVER['PHP_AUTH_DIGEST'])) {
    header('HTTP/1.1 401 Unauthorized');
    header('WWW-Authenticate: Digest realm="' . $realm .
        '",qop="auth",nonce="' . uniqid() . '",opaque="' . md5($realm) . '"');
    die();
}

// analyze the PHP_AUTH_DIGEST variable
if (!($data = http_digest_parse($_SERVER['PHP_AUTH_DIGEST'])) || !isset($users[sanitize($data['username'])])) {

    // False credentials
    header("Location: errorScreen.php");
    die();
}

// generate the valid response
$A1 = md5($username . ':' . $realm . ':' . $users[sanitize($data['username'])]);
$A2 = md5($_SERVER['REQUEST_METHOD'] . ':' . sanitize($data['uri']));
$valid_response = md5($A1 . ':' . sanitize($data['nonce']) . ':' . sanitize($data['nc']) . ':' . sanitize($data['cnonce'])
    . ':' . sanitize($data['qop']) . ':'
    . $A2);

if (sanitize($data['response']) != $valid_response) {

    // False credentials
    header("Location: errorScreen.php");
    die($password);
}

// ok, valid username & password

// function to parse the http auth header
function http_digest_parse($txt) {
    // protect against missing data
    $needed_parts = array('nonce' => 1, 'nc' => 1, 'cnonce' => 1, 'qop' => 1, 'username' => 1, 'uri' => 1, 'response' => 1);
    $data = array();
    $keys = implode('|', array_keys($needed_parts));

    preg_match_all('@(' . $keys . ')=(?:([\'"])([^\2]+?)\2|([^\s,]+))@', $txt, $matches, PREG_SET_ORDER);

    foreach ($matches as $m) {
        $data[$m[1]] = $m[3] ? $m[3] : $m[4];
        unset($needed_parts[$m[1]]);
    }

    return $needed_parts ? false : $data;
}

function error_found(){
    header("Location: errorScreen.php");
    die();
}

set_error_handler('error_found');
?>

<?php
include("includes/top.php");
?>

<link rel="stylesheet" href="css/foundation.css">
<link rel="stylesheet" href="css/dropzone.css">
<link type="text/css" rel="stylesheet" href="css/filehub/filehub.css"/>
<link rel="stylesheet" type="text/css" media="screen and (min-width:768px) and (max-width:960px)"
      href="css/filehub/filehub_960.css">
<link rel="stylesheet" type="text/css" media="screen and (min-width:480px) and (max-width:767px)"
      href="css/filehub/filehub_767.css">
<link rel="stylesheet" type="text/css" media="screen and (min-width:431px) and (max-width:479px)"
      href="css/filehub/filehub_479.css">
<link rel="stylesheet" type="text/css" media="screen and (min-width:320px) and (max-width:430px)"
href="css/filehub/filehub_430.css">
<link rel="stylesheet" type="text/css" media="screen and (max-width:319px)"
      href="css/filehub/filehub_319.css">
<script type="text/javascript" src="js/api/jquery-2.1.1.js"></script>
<script type="text/javascript" src="config/client/client_config.js"></script>
<script type="text/javascript" src="js/api/dropzone.js"></script>
<script type="text/javascript" src="js/api/exif.js"></script>
<script type="text/javascript" src="js/fileHubManager.js"></script>
<script type="text/javascript" src="js/filehub.js"></script>
<title>FamShare - Filehub</title>

<?php
include("includes/middle.php");
?>

<?php
include("includes/header.php");
?>

<div id="uploadTitle">
    <div id="titleText">File Hub</div>
</div>
<div id="introText">Welcome to the file hub. Here you can upload and download your files. To upload a file,
    just drag and drop it into the box below or click inside the box to open an upload dialog. But be careful,
    once a file has been added to the box it will be uploaded, no going back! To download a file, just click
    on the desired file.
</div>
<div id="mobileFailText">Sorry, your device seems to be to small</div>
<div id="toolbar" class="flashRed">
    <div id="backButton"><img src="images/back-arrow.png"></div>
    <div id="forwardButton"><img src="images/forward-arrow.png"></div>
    <div id="currentDirText">/</div>
    <div id="editButton">
        <div id="editText">Edit</div>
    </div>
    <div id="newFolderButton">
        <div id="newFolderText">New Folder</div>
    </div>
    <input type="text" name="newFolderTextInput" id="newFolderTextInput">
    <div id="seperator"></div>
</div>
<form action="php/uploadHandler.php" enctype= multipart/form-data id="fileBox" class="dropzone flashRed"></form>
<div id="statusBar">
    <div id="statusDiv" class="flashRed">
        <div id="statusTitle">Status:</div>
        <div id="statusText">Going well</div>
    </div>
</div>

<form action="php/downloadHandler.php" method="post" name="downloadForm" id="downloadForm">
    <input type="hidden" name="filePath" value="">
</form>

<div id="viewModal" class="reveal-modal" data-reveal aria-labelledby="modalTitle" aria-hidden="true" role="dialog">

    <a class="close-reveal-modal" aria-label="Close">&#215;</a>
</div>

<script type="text/javascript" src="js/api/foundation.js"></script>
<script type="text/javascript" src="js/api/foundation.reveal.js"></script>

<?php
include("includes/bottom.php");
?>