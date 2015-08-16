<?php
include("includes/auth.php");
?>

<?php
include("includes/top.php");
?>

<meta name="Keywords" content="famshare, file, sharing, free, family, home">
<meta name="Description" content="A free file sharing website for the family to run at home! Where you and not Big
    Brother control your data.">
<link rel="stylesheet" href="css/dropzone.css">
<link type="text/css" rel="stylesheet" href="css/filehub/filehub.css"/>
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
<script type="text/javascript" src="config/client/client_config.js"></script>
<script type="text/javascript" src="js/api/dropzone.js"></script>
<script type="text/javascript" src="js/api/jquery-2.1.1.js"></script>
<script type="text/javascript" src="js/fileHubManager.js"></script>
<script type="text/javascript" src="js/filehub.js"></script>
<title>FamShare - Filehub</title>

<?php
include("includes/middle.php");
?>

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
<form action="uploadHandler.php" enctype= multipart/form-data id="fileBox" class="dropzone flashRed"></form>
<div id="statusBar">
    <div id="statusDiv" class="flashRed">
        <div id="statusTitle">Status:</div>
        <div id="statusText">Going well</div>
    </div>
</div>

<?php
include("includes/bottom.php");
?>