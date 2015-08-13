<?php
$configs = include('./config/server/server_config.php');

$realm = 'Restricted area';
$username = "user";
$password = $rootDir = $configs["password"];

//user => password
$users = array($username => $password);

if (empty($_SERVER['PHP_AUTH_DIGEST'])) {
    header('HTTP/1.1 401 Unauthorized');
    header('WWW-Authenticate: Digest realm="' . $realm .
        '",qop="auth",nonce="' . uniqid() . '",opaque="' . md5($realm) . '"');

    // Login cancelled
    die("Login cancelled");
}

// analyze the PHP_AUTH_DIGEST variable
if (!($data = http_digest_parse($_SERVER['PHP_AUTH_DIGEST'])) || !isset($users[$data['username']])) {

    // False credentials
    die("False credentials");
}

// generate the valid response
$A1 = md5($username . ':' . $realm . ':' . $users[$data['username']]);
$A2 = md5($_SERVER['REQUEST_METHOD'] . ':' . $data['uri']);
$valid_response = md5($A1 . ':' . $data['nonce'] . ':' . $data['nc'] . ':' . $data['cnonce'] . ':' . $data['qop'] . ':' . $A2);

if ($data['response'] != $valid_response) {

    // False credentials
    die("False credentials");
}

// ok, valid username & password

// function to parse the http auth header
function http_digest_parse($txt)
{
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

?>
<!DOCTYPE html>
<html>
<head lang="en">
    <link rel="apple-touch-icon" sizes="57x57" href="favicon/apple-touch-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="60x60" href="favicon/apple-touch-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="72x72" href="favicon/apple-touch-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="76x76" href="favicon/apple-touch-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="114x114" href="favicon/apple-touch-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="120x120" href="favicon/apple-touch-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="144x144" href="favicon/apple-touch-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="favicon/apple-touch-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="favicon/apple-touch-icon-180x180.png">
    <link rel="icon" type="image/png" href="favicon/favicon-32x32.png" sizes="32x32">
    <link rel="icon" type="image/png" href="favicon/android-chrome-192x192.png" sizes="192x192">
    <link rel="icon" type="image/png" href="favicon/favicon-96x96.png" sizes="96x96">
    <link rel="icon" type="image/png" href="favicon/favicon-16x16.png" sizes="16x16">
    <link rel="manifest" href="/favicon/manifest.json">
    <link rel="shortcut icon" href="/favicon/favicon.ico">
    <meta name="msapplication-TileColor" content="#da532c">
    <meta name="msapplication-TileImage" content="/favicon/mstile-144x144.png">
    <meta name="msapplication-config" content="/favicon/browserconfig.xml">
    <meta name="theme-color" content="#ffffff">
    <meta charset="UTF-8">
    <link rel="stylesheet" href="css/dropzone.css">
    <link type="text/css" rel="stylesheet" href="css/filehub/filehub.css"/>
    <script type="text/javascript" src="js/api/dropzone.js"></script>
    <link rel="stylesheet" type="text/css" media="screen and (min-width:768px) and (max-width:960px)"
          href="css/filehub/filehub_960.css">
    <link rel="stylesheet" type="text/css" media="screen and (min-width:480px) and (max-width:767px)"
          href="css/filehub/filehub_767.css">
    <link rel="stylesheet" type="text/css" media="screen and (min-width:422px) and (max-width:479px)"
          href="css/filehub/filehub_479.css">
    <link rel="stylesheet" type="text/css" media="screen and (min-width:320px) and (max-width:421px)"
    href="css/filehub/filehub_420.css">
    <link rel="stylesheet" type="text/css" media="screen and (max-width:319px)"
          href="css/filehub/filehub_319.css">
    <script src="config/client/client_config.js"></script>
    <title>FamShare - Filehub</title>
</head>
<body class="unselectable">
<div id="wrapper">
    <div id="content_wrapper">
        <div id="content_inner_wrapper">
            <div id="topDiv">
                <div id="homeLink"><a href="index.html" id="link">
                        <div id="icon"><img src="images/logo.png"></div>
                        <div id="title">FamShare</div>
                    </a></div>
            </div>
            <div id="uploadTitle">
                <div id="titleText">File Hub</div>
            </div>
            <div id="introText">Welcome to the file hub. Here you can upload and download your files. To upload a file,
                just drag and drop it into the box below or click inside the box to open an upload dialog. But be careful,
                once a file has been added to the box it will be uploaded, no going back! To download a file, just click
                on the desired file.
            </div>
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
            <form action="uploadHandler.php" id="fileBox" class="dropzone flashRed"></form>
            <div id="statusBar">
                <div id="statusDiv" class="flashRed">
                    <div id="statusTitle">Status:</div>
                    <div id="statusText">Going well</div>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="footer_wrapper">
    <div id="footer_inner_wrapper">
        <div id="bottomBar"></div>
    </div>
</div>
<form action="downloadHandler.php" method="post" name="downloadForm" id="downloadForm">
    <input type="hidden" name="filePath" value="">
</form>
<script type="text/javascript" src="js/api/jquery-2.1.1.js"></script>
<script type="text/javascript" src="js/fileHubManager.js"></script>
<script type="text/javascript" src="js/filehub.js"></script>
</body>
</html>