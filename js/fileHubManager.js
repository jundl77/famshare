/**
 * The file system manager grabs the current file system of the server upload section and turns it into a graph
 * which is used as a map to track the current directory
 */
var currentDir = null;
var currentFiles = null;
var currentViewObject = null;
var fileSystem;
var forwardDir = null;
var backDir = null;
var doneLoadingFiles = false;
var editing = false;
var videoView = false;
var imageView = false;

/**
 * Returns true if a video is currently being displayed, otherwise returns false
 *
 * @returns {boolean} true if a video is currently being displayed, otherwise false
 */
function isVideoView() {
    return videoView;
}

/**
 * To be set to true if the video view is open and false if it has been closed
 *
 * @param value true if the video view is open and false if it has been closed
 */
function setVideoView(value) {
    videoView = value;
}

/**
 * Returns true if an image is currently being displayed, otherwise returns false
 *
 * @returns {boolean} true if an image is currently being displayed, otherwise false
 */
function isImageView() {
    return imageView;
}

/**
 * To be set to true if the image view is open and false if it has been closed
 *
 * @param value true if the image view is open and false if it has been closed
 */
function setImageView(value) {
    imageView = value;
}

/**
 * Updates the client side file system structure of the server upload section
 */
function updateFileStructure() {
    var postData = {};
    postData['command'] = "fileStructure";

    // post the dataUrl to php
    $.ajax({
        url: "php/fileSystemHandler.php",
        type: "POST",
        dataType: 'json',
        data: postData,
        success: function (response) {
            if (response['state'] === "success") {
                parseFileStructure(response['content']);
                showCurrentDirectoryOnScreen();
            } else {
                flashRed();
                $("#statusText").text(response['content']);
            }
        }
    });
}

/**
 * Function that takes JSON data and parses it into a graph made of folders on the client side that can be easily
 * traversed
 *
 * @param fileStructure the raw JSON fileStructure to parse
 */
var parseFileStructure = function (fileStructure) {
    var obj = JSON.parse(fileStructure);
    fileSystem = parseFileStructureHelper(obj, null);
};

/**
 * Helper function to the parseFileStructure that parses the JSON file structure data recursively
 *
 * @param folderArray the of the current folder array to turn into folder objects
 * @param parentDir the parent directory of the folder
 * @returns {Folder} the folderArray as a folder object
 */
var parseFileStructureHelper = function (folderArray, parentDir) {
    if (folderArray['content'] == null) {
        return new Folder(folderArray['name'], parentDir);
    } else {
        var folder = new Folder(folderArray['name'], parentDir);
        var childDirectories = [];
        for (var i = 0; i < folderArray['content'].length; i++) {
            childDirectories.push(parseFileStructureHelper(folderArray['content'][i], folder));
        }
        folder.childDirectories = childDirectories;

        return folder;
    }
};

/**
 * Shows the current file structure on the screen
 */
function showCurrentDirectoryOnScreen() {
    if (currentDir == null) {
        currentDir = fileSystem;
        $("#currentDirText").text("/");
    }

    $("#fileBox").empty();
    showCurrentFolders();
    showCurrentFiles();
}

/**
 * Shows all folders in the current folder
 */
function showCurrentFolders() {
    doneLoadingFiles = false;
    var childDirectories = currentDir.childDirectories;
    for (var i = 0; i < childDirectories.length; i++) {
        var dir = childDirectories[i];
        var container = getContainter();
        var template = "<div class=\"folderIcon\"><div class=\"folder-delete\"></div><div class=\"folderName\">"
            + dir.name + "</div></div>";
        var templateObj = Dropzone.createElement(template.trim());
        container.appendChild(templateObj);
        (function (dirIn, template) {
            var cross = template.getElementsByClassName("folder-delete");
            templateObj.addEventListener('click', function (event) {
                if (doneLoadingFiles && !editing) {
                    backDir = currentDir;
                    currentDir = dirIn;
                    forwardDir = null;
                    var path = $("#currentDirText").text();
                    path += dirIn.name + "/";
                    $("#currentDirText").text(path);
                    disbaleForwardButton();
                    enableBackButton();
                    showCurrentDirectoryOnScreen();
                }
            });
            cross[0].addEventListener('click', function (event) {
                if (doneLoadingFiles && editing) {
                    deleteFolder(dirIn);
                }
            })
        })(dir, templateObj);
    }
}

/**
 * Shows all files in the current folder
 */
function showCurrentFiles() {
    var postData = {};
    postData['command'] = "files";
    var path = $("#currentDirText").text();
    path = path.substring(1);
    postData['path'] = path;

    // post the dataUrl to php
    $.ajax({
        url: "php/fileSystemHandler.php",
        type: "POST",
        dataType: 'json',
        data: postData,
        success: function (response) {
            if (response['state'] === "success") {
                currentFiles = JSON.parse(response['content']);
                showCurrentFilesHelper(currentFiles);
                doneLoadingFiles = true;
                handleDeleteCrosses();
            } else {
                flashRed();
                $("#statusText").text(response['content']);
            }
        }
    });
}

/**
 * Helper function that actually shows all the files in the current folder
 *
 * @param fileArray the array of files in the folder received from the php
 */
function showCurrentFilesHelper(fileArray) {
    // Create the thumbnail for each img
    for (var i = 0; i < fileArray.length; i++) {
        var file = fileArray[i];
        var fileName = file.name;
        var fileSize = file.size;
        var isImg = file.is_image;

        var path = $("#currentDirText").text() + fileName;

        var container = getContainter();
        var templateObj = Dropzone.createElement(getTemplate().trim());
        container.appendChild(templateObj);
        var active = document.querySelector(".dz-progress");
        active.classList.remove("dz-progress");

        // Add name (from dropzone.js)
        var _len, _len1, node, _i, _j;
        var _ref = templateObj.querySelectorAll("[data-dz-name]");
        for (_i = 0, _len = _ref.length; _i < _len; _i++) {
            node = _ref[_i];
            node.textContent = fileName;
        }

        // Add file size (from dropzone.js)
        var _ref1 = templateObj.querySelectorAll("[data-dz-size]");
        for (_j = 0, _len1 = _ref1.length; _j < _len1; _j++) {
            node = _ref1[_j];
            node.innerHTML = getDropzone().filesize(fileSize);
        }

        // Add includes for thumbnail
        var childDivs = templateObj.children;
        for (var j = 0; j < childDivs.length; j++) {
            var childDiv = childDivs[j];
            if (childDiv.classList.contains("dz-image")) {
                var img = childDiv.getElementsByTagName('img')[0];
                loadThumbnail(img, path);
            }

            // Add file info if it is not an image
            if (childDiv.classList.contains("dz-details") && isImg) {
                childDiv.style.visibility = "hidden";
            } else if (childDiv.classList.contains("dz-details")) {
                childDiv.style.visibility = "visible";
            }
        }

        // Add deletion cross
        var cross = document.createElement("div");
        cross.className = "file-delete";
        templateObj.appendChild(cross);

        // Add events (mouse over, mouse leave and click) and hide file size and name by default
        (function (filePath, obj, crossIn, isImgIn) {
            //var dzDetails = templateObj.getElementsByClassName("dz-details");

            var childDivs = obj.children;
            var dzDetailsDiv;
            var dzDownloadDiv;
            for (var j = 0; j < childDivs.length; j++) {
                var childDiv = childDivs[j];
                if (childDiv.classList.contains("dz-details")) {
                    dzDetailsDiv = childDiv;
                } else if (childDiv.classList.contains("dz-download")) {
                    dzDownloadDiv = childDiv;
                }
            }

            dzDetailsDiv.addEventListener('click', function (event) {
                if (loadViewMedia(filePath, fileName)) {
                    $('#viewModal').foundation('reveal', 'open');
                }
            });
            dzDownloadDiv.addEventListener('click', function (event) {
                downloadFile(filePath);
            });
            templateObj.addEventListener('mouseover', function (event) {
                for (var j = 0; j < childDivs.length; j++) {
                    var childDiv = childDivs[j];
                    if (childDiv.classList.contains("dz-details") && isImgIn) {
                        childDiv.style.visibility = "visible";
                    }

                    if (childDiv.classList.contains("dz-download")) {
                        childDiv.style.visibility = "visible";
                    }
                }
            });
            templateObj.addEventListener('mouseleave', function (event) {
                for (var j = 0; j < childDivs.length; j++) {
                    var childDiv = childDivs[j];
                    if (childDiv.classList.contains("dz-details") && isImgIn) {
                        childDiv.style.visibility = "hidden";
                    }

                    if (childDiv.classList.contains("dz-download")) {
                        childDiv.style.visibility = "hidden";
                    }
                }
            });
            crossIn.addEventListener('click', function (event) {
                if (editing) {
                    deleteFile(filePath);
                }
            });
        })(path, templateObj, cross, isImg);
    }

    // Make each image fill the div completely
    $(".dz-image").each(function () {
        var refH = $(this).height();
        var refW = $(this).width();
        var refRatio = refW / refH;

        var imgH = $(this).children("img").height();
        var imgW = $(this).children("img").width();

        if ((imgW / imgH) < refRatio) {
            $(this).addClass("portrait");
        } else {
            $(this).addClass("landscape");
        }
    })
}


/**
 * Adds a new folder to the current folder. The folder name can only contain letters and numbers.
 *
 * @param name the name of the new folder, can only contain letters and numbers
 */
function addFolder(name) {
    if (name === null || name === "") {
        return;
    }

    // make new path
    if (name.length > 20) {
        flashRed();
        $("#statusText").text("Name is too long, max. length is 20 characters");
        return;
    }

    var postData = {};
    postData['command'] = "newFolder";
    postData['path'] = $("#currentDirText").text().substring(1) + name;

    // post the dataUrl to php
    $.ajax({
        url: "php/fileSystemHandler.php",
        type: "POST",
        dataType: 'json',
        data: postData,
        success: function (response) {
            if (response['state'] === "success") {
                var newFolder = new Folder(name, currentDir);
                currentDir.childDirectories.push(newFolder);
                showCurrentDirectoryOnScreen();
            } else {
                flashRed();
                $("#statusText").text(response['content']);
            }
        }
    });
}

/**
 * Downloads the file at the given path
 *
 * @param path the path of the file to download
 */
function downloadFile(path) {
    if (!editing) {
        document.downloadForm.filePath.value = path;
        document.downloadForm.submit();
    } else {
        // Uncommented because there is no way to differentiate between download and delete at the moment
        // $("#statusText").text("Please stop editing to download an image");
    }
}

/**
 * Manages the editing function
 */
function editFileHubContent() {
    editing = !editing;
    handleDeleteCrosses();
}

/**
 * Shows the deletion crosses if they are to be shown, and hides them when they are to be hidden
 */
function handleDeleteCrosses() {
    var folderCrosses = document.getElementsByClassName("folder-delete");
    var fileCrosses = document.getElementsByClassName("file-delete");
    if (editing) {
        activateEditButton();
        for (var i = 0; i < folderCrosses.length; i++) {
            folderCrosses[i].style.visibility = "visible";
        }
        for (var j = 0; j < fileCrosses.length; j++) {
            fileCrosses[j].style.visibility = "visible";
        }
    } else {
        deactivateEditButton();
        for (var i2 = 0; i2 < folderCrosses.length; i2++) {
            folderCrosses[i2].style.visibility = "hidden";
        }
        for (var j2 = 0; j2 < fileCrosses.length; j2++) {
            fileCrosses[j2].style.visibility = "hidden";
        }
    }
}

/**
 * Removes the folder that was clicked on at the given path
 */
function deleteFolder(folder) {
    var postData = {};
    postData['command'] = "deleteFolder";
    postData['path'] = $("#currentDirText").text().substring(1) + folder.name;

    // post the dataUrl to php
    $.ajax({
        url: "php/fileSystemHandler.php",
        type: "POST",
        dataType: 'json',
        data: postData,
        success: function (response) {
            if (response['state'] === "success") {
                removeByAttr(currentDir.childDirectories, "name", folder.name);
                showCurrentDirectoryOnScreen();
            } else {
                flashRed();
                $("#statusText").text(response['content']);
            }
        }
    });

    return false;
}

/**
 * Removes the file that was clicked on at the given path
 */
function deleteFile(fileName) {
    var postData = {};
    postData['command'] = "deleteFile";
    postData['path'] = fileName.substring(1);

    // post the dataUrl to php
    $.ajax({
        url: "php/fileSystemHandler.php",
        type: "POST",
        dataType: 'json',
        data: postData,
        success: function (response) {
            if (response['state'] === "success") {
                showCurrentDirectoryOnScreen();
            } else {
                flashRed();
                $("#statusText").text(response['content']);
            }
        }
    });

    return false;
}

function removeByAttr(arr, attr, value) {
    var i = arr.length;
    while (i--) {
        if (arr[i] && arr[i].hasOwnProperty(attr) && (arguments.length > 2 && arr[i][attr] === value)) {
            arr.splice(i, 1);
        }
    }
    return arr;
}

/**
 * Loads the thumbnail of the image 'img' which is located at the path 'path'. 'path' is just the local path on the
 * client path (location within the filehub)
 *
 * @param img the image to get the thumbnail for
 * @param path the path of the image
 */
function loadThumbnail(img, path) {
    var postData = {};
    postData['file'] = path;

    // post the dataUrl to php
    $.ajax({
        url: "php/thumbnailHandler.php",
        type: "POST",
        dataType: 'json',
        data: postData,
        success: function (response) {
            if (response['state'] === "success") {
                var thumbnail = JSON.parse(response['content']);
                img.src = "data:image/png;base64," + thumbnail['thumb_data'];
            } else {
                flashRed();
                $("#statusText").text(response['content']);
            }
        }
    });
}

/**
 * This function loads either an image or a video into the media modal (what is opened when you click on the non
 * download portion of a file icon in the file hub)
 *
 * @param filePath the file path of the object to load into the media modal
 * @param fileName the name of the file that is to be loaded into the media modal
 */
function loadViewMedia(filePath, fileName) {
    var fileExtension = filePath.toLowerCase().split('.').pop();
    var imgExtensions = ['jpg', 'jpeg', 'gif', 'png', 'wbmp'];
    if (fileExtension === "mp4") {
        var player = document.getElementById('videoView');
        var mp4Vid = document.getElementById('mp4Source');

        $("#imageView").css("display", "none");
        $("#mainSectionModal").css("width", "40em");
        $("#videoView").css("display", "inline-block");

        $(mp4Vid).attr('src', "php/mediaViewHandler.php?video=" + filePath);
        player.load();
        currentViewObject = fileName;
        videoView = true;
        imageView = false;
        return true;
    } else if ($.inArray(fileExtension, imgExtensions) !== -1) {
        var image = document.getElementById('imageView');

        $("#imageView").css("display", "inline-block");
        $("#videoView").css("display", "none");

        getDimensionAndLoadImage(filePath, image);
        currentViewObject = fileName;
        videoView = false;
        imageView = true;
        return true;
    }

    videoView = false;
    imageView = false;
    return false;
}

/**
 * Get the dimensions of the image that is to be loaded and adjusts the image tag accordingly to make it as big as
 * possible while still retaining image dimensions, and the loads the image into the image tag
 *
 * @param filePath the file path of the image to load into image object
 * @param image the image object that is to be filled with the actual image
 */
function getDimensionAndLoadImage(filePath, image) {
    var postData = {};
    postData['file'] = filePath;
    postData['type'] = "info";

    // Post the data to php
    var xhr = $.ajax({
        url: "php/mediaViewHandler.php",
        type: "POST",
        dataType: 'json',
        data: postData,
        success: function (response) {
            if (response['state'] === "success") {
                var info = response['content'];
                var width = info['width'];
                var height = info['height'];
                var revealModalWidth = $("#viewModal").width();

                $("#forwardButtonModal").css("margin-bottom", "0em");
                $("#backButtonModal").css("margin-bottom", "0em");

                if (width * 0.9 > revealModalWidth) {
                    var ratio = height / width;
                    width = revealModalWidth * 0.9;
                    height = width * ratio;
                }

                $("#mainSectionModal").css("width", width + "px");
                $("#imageView").css("width", width + "px");
                $("#imageView").css("height", height + "px");
                var revealModalHeight = $("#viewModal").height();

                var newDocHeight = revealModalHeight - $(document).height();

                if (newDocHeight > 0) {
                    $("#statusBar").css("margin-bottom", newDocHeight + $("#viewModal").offset().top + 300 + "px");
                }

                image.src = "php/mediaViewHandler.php?image=" + filePath;
            } else {
                flashRed();
                $("#statusText").text(response['content']);
            }
        }
    });
}

/**
 * Gets the next object that is viewable (image or video) and displays it in the media modal.
 *
 * This means that the function loops forwards in the current files array until it finds an object it can view.
 */
function nextViewObject() {
    // Go through all files
    for (var i = 0; i < currentFiles.length; i++) {
        var file = currentFiles[i];
        var fileName = file.name;

        // Pause the player in case it is playing
        var player = document.getElementById('videoView');
        player.pause();

        // Stop here if this is the current file
        if (fileName === currentViewObject) {

            // Now go and find the next available file to display that comes after the current file
            var done = false;
            while (!done) {
                var nextFileName = currentFiles[i].name;

                // Skip if the file found is the current file
                if (nextFileName === fileName) {

                    // Mod the index to loop
                    i = (i + 1) % currentFiles.length;
                    continue;
                }

                var path = $("#currentDirText").text() + nextFileName;
                if (loadViewMedia(path, nextFileName)) {
                    done = true;
                }

                // Mod the index to loop
                i = (i + 1) % currentFiles.length;
            }

            break;
        }
    }
}

/**
 * Gets the previous object that is viewable (image or video) and displays it in the media modal.
 *
 * This means that the function loops backwards in the current files array until it finds an object it can view.
 */
function previousViewObject() {
    // Go through all files
    for (var i = 0; i < currentFiles.length; i++) {
        var file = currentFiles[i];
        var fileName = file.name;

        // Pause the player in case it is playing
        var player = document.getElementById('videoView');
        player.pause();

        // Stop here if this is the current file
        if (fileName === currentViewObject) {

            // Now go and find the next available file to display that comes before the current file
            var done = false;
            while (!done) {
                var nextFileName = currentFiles[i].name;

                // Skip if the file found is the current file
                if (nextFileName === fileName) {

                    // Mod the index to loop and add currentFiles.length to avoid negative mod numbers
                    i = (i - 1 + currentFiles.length) % currentFiles.length;
                    continue;
                }

                var path = $("#currentDirText").text() + nextFileName;
                if (loadViewMedia(path, nextFileName)) {
                    done = true;
                }


                // Mod the index to loop and add currentFiles.length to avoid negative mod numbers
                i = (i - 1 + currentFiles.length) % currentFiles.length;
            }

            break;
        }
    }
}

/**
 * Gets the current object that is being viewed in the media modal
 *
 * @returns {*} the current object that is being viewed in the media modal
 */
function getCurrentViewObject() {
    return currentViewObject;
}

/**
 * Returns true if the back action is allowed, else false
 *
 * @returns {boolean} true if the back action is allowed, else false
 */
function getBackState() {
    return backDir != null;
}

/**
 * Returns true if the forward action is allowed, else false
 *
 * @returns {boolean} true if the forward action is allowed, else false
 */
function getForwardState() {
    return forwardDir != null;
}

/**
 * Goes back into the directory the user was just in
 */
function forward() {
    if (forwardDir != null && doneLoadingFiles && !editing) {
        var path = $("#currentDirText").text();
        path += forwardDir.name + "/";
        $("#currentDirText").text(path);

        backDir = currentDir;
        currentDir = forwardDir;
        forwardDir = null;
        disbaleForwardButton();
        enableBackButton();
        showCurrentDirectoryOnScreen();
    }

    if (editing) {
        flashRed();
        $("#statusText").text("Please stop editing first before going forward");
    }
}

/**
 * Goes into the parent directory of the current directory
 */
function back() {
    if (backDir !== null && doneLoadingFiles && !editing) {
        var path = $("#currentDirText").text();
        path = path.substring(1);
        var pathParts = path.split("/");
        path = "/";
        for (var i = 0; i < pathParts.length - 2; i++) {
            if (pathParts[i] !== "") {
                path += pathParts[i] + "/";
            }
        }
        $("#currentDirText").text(path);

        enableForwardButton();
        forwardDir = currentDir;
        currentDir = backDir;
        backDir = backDir.parentDirectory;
        if (backDir === null) {
            disableBackButton();
        }
        showCurrentDirectoryOnScreen();
    }

    if (editing) {
        flashRed();
        $("#statusText").text("Please stop editing first before going back");
    }
}

/**
 * Object to represent a folder, with it the file system graph is built.
 *
 * @param name the name of the directory
 * @param parentDirectory the parent directory of the directory
 * @constructor
 */
function Folder(name, parentDirectory) {
    this.name = name;
    this.parentDirectory = parentDirectory;
    this.childDirectories = [];
}