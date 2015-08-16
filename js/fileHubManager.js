
/**
 * The file system manager grabs the current file system of the server upload section and turns it into a graph
 * which is used as a map to track the current directory
 */
var currentDir = null;
var fileSystem;
var forwardDir = null;
var backDir = null;
var doneLoadingFiles = false;
var editing = false;

/**
 * Updates the client side file system structure of the server upload section
 */
function updateFileStructure() {
    var postData = {};
    postData['command'] = "fileStructure";

    // post the dataUrl to php
    $.ajax({
        url: "fileSystemHandler.php",
        type: "POST",
        dataType : 'json',
        data: postData,
        success: function (response) {
            if (response['state'] === "success") {
                parseFileStructure(response['content']);
                showCurrentDirectoryOnScreen();
            } else {
                flashRed();
                $("#statusText").text("An error occurred while the getting file structure: " + response['content']);
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
var parseFileStructure = function(fileStructure) {
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
var parseFileStructureHelper = function(folderArray, parentDir) {
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
        (function(dirIn, template) {
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
        url: "fileSystemHandler.php",
        type: "POST",
        dataType : 'json',
        data: postData,
        success: function (response) {
            if (response['state'] === "success") {
                var files = JSON.parse(response['content']);
                showCurrentFilesHelper(files);
                doneLoadingFiles = true;
                handleDeleteCrosses();
            } else {
                flashRed();
                $("#statusText").text("An error occurred while getting files: " + response['content']);
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
        var fileThumbData = file.thumb_data;
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

        // Add template for thumbnail
        var childDivs = templateObj.children;
        for (var j = 0; j < childDivs.length; j++) {
            var childDiv = childDivs[j];
            if (childDiv.classList.contains("dz-image") && fileThumbData !== null) {
                var img = childDiv.getElementsByTagName('img')[0];
                img.src = "data:image/png;base64," + fileThumbData;
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
        (function(filePath, obj, crossIn, isImgIn) {
            var childDivs = obj.children;
            templateObj.addEventListener('click', function (event) {
                downloadFile(filePath);
            });
            if (isImgIn) {
                templateObj.addEventListener('mouseover', function (event) {
                    for (var j = 0; j < childDivs.length; j++) {
                        var childDiv = childDivs[j];
                        if (childDiv.classList.contains("dz-details")) {
                            childDiv.style.visibility = "visible";
                        }
                    }
                });
                templateObj.addEventListener('mouseleave', function (event) {
                    for (var j = 0; j < childDivs.length; j++) {
                        var childDiv = childDivs[j];
                        if (childDiv.classList.contains("dz-details")) {
                            childDiv.style.visibility = "hidden";
                        }
                    }
                });
            }
            crossIn.addEventListener('click', function (event) {
                if (editing) {
                    deleteFile(filePath);
                }
            });
        })(path, templateObj, cross, isImg);
    }

    // Make each image fill the div completely
    $(".dz-image").each(function(){
        var refH = $(this).height();
        var refW = $(this).width();
        var refRatio = refW/refH;

        var imgH = $(this).children("img").height();
        var imgW = $(this).children("img").width();

        if ( (imgW/imgH) < refRatio ) {
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
        url: "fileSystemHandler.php",
        type: "POST",
        dataType : 'json',
        data: postData,
        success: function (response) {
            if (response['state'] === "success") {
                var newFolder = new Folder(name, currentDir);
                currentDir.childDirectories.push(newFolder);
                showCurrentDirectoryOnScreen();
            } else {
                flashRed();
                $("#statusText").text("An error occurred while creating a new directory: " + response['content']);
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
            folderCrosses[i].style.visibility="visible";
        }
        for (var j = 0; j < fileCrosses.length; j++) {
            fileCrosses[j].style.visibility="visible";
        }
    } else {
        deactivateEditButton();
        for (var i2 = 0; i2 < folderCrosses.length; i2++) {
            folderCrosses[i2].style.visibility="hidden";
        }
        for (var j2 = 0; j2 < fileCrosses.length; j2++) {
            fileCrosses[j2].style.visibility="hidden";
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
        url: "fileSystemHandler.php",
        type: "POST",
        dataType : 'json',
        data: postData,
        success: function (response) {
            if (response['state'] === "success") {
                removeByAttr(currentDir.childDirectories, "name", folder.name);
                showCurrentDirectoryOnScreen();
            } else {
                flashRed();
                $("#statusText").text("An error occurred while deleting a directory: " + response['content']);
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
        url: "fileSystemHandler.php",
        type: "POST",
        dataType : 'json',
        data: postData,
        success: function (response) {
            if (response['state'] === "success") {
                showCurrentDirectoryOnScreen();
            } else {
                flashRed();
                $("#statusText").text("An error occurred while deleting a directory: " + response['content']);
            }
        }
    });

    return false;
}

function removeByAttr(arr, attr, value) {
    var i = arr.length;
    while (i--){
        if (arr[i] && arr[i].hasOwnProperty(attr) && (arguments.length > 2 && arr[i][attr] === value)) {
            arr.splice(i,1);
        }
    }
    return arr;
}

/**
 * Returns true if the the file hub is being edited, else false
 *
 * @returns {boolean} true if the the file hub is being edited, else false
 */
function isEditing() {
    return editing;
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