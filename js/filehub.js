var dragCount = 0;
var newFolderInput = false;
var forwardButtonModal = true;
var backwardButtonModal = true;
var spinner = null;

/**
 * Disables the back button
 */
function disableBackButton() {
    $('#backButton').css('opacity', 0.4);
}

/**
 * Enables the back button
 */
function enableBackButton() {
    $('#backButton').css('opacity', 1);
}

/**
 * Disables the forward button
 */
function disbaleForwardButton() {
    $('#forwardButton').css('opacity', 0.4);
}

/**
 * Enables the forward button
 */
function enableForwardButton() {
    $('#forwardButton').css('opacity', 1);
}

/**
 * Activates the edit button
 */
function activateEditButton() {
    $("#editText").css("color", "#FF9800");
    $("#editButton").css("border", "1px solid #FF9800");
}

/**
 * Deactivates the edit button
 */
function deactivateEditButton() {
    $("#editText").css("color", "#607D8B");
    $("#editButton").css("border", "1px solid #607D8B");
}

/**
 *
 */
function disableMediaViewForwardButton() {
    $("#forwardButtonModal").css("opacity", "0.4");
    forwardButtonModal = false;
}

/**
 *
 */
function disableMediaViewBackButton() {
    $("#backButtonModal").css("opacity", "0.4");
    backwardButtonModal = false;
}

/**
 *
 */
function enableMediaViewForwardButton() {
    $("#forwardButtonModal").css("opacity", "0.8");
    forwardButtonModal = true;
}

/**
 *
 */
function enableMediaViewBackButton() {
    $("#backButtonModal").css("opacity", "0.8");
    backwardButtonModal = true;
}


/**
 * Drag enter and leave screen function
 *
 * @param e the event when a drag enters or leaves the screen
 */
function dragenterDragleave(e) {
    e.preventDefault();
    dragCount += (e.type === "dragenter" ? 1 : -1);
    if (e.type === "dragenter") {
        grayOut();
    } else if (dragCount % 2 === 0) {
        removeGrayOut();
    }
}

/**
 * Increments the drag count by 1
 */
function incrementDragCount() {
    dragCount++;
}

/**
 * Grays the screen out except for the pictureBox
 */
function grayOut() {
    var screen = document.createElement("div");
    screen.id = "screen";
    document.body.appendChild(screen);
    $("#screen").css({
        position: 'absolute',
        left: 0,
        top: 0,
        'background-color': '#000000',
        opacity: 0.5,
        'width': $(document).width(),
        'height': $(document).height(),
        'z-index': 99
    });
    $("body").css({"overflow": "hidden"});
}

/**
 * Removes the gray out of the screen
 */
function removeGrayOut() {
    $("#screen").remove();
    $("body").css({"overflow": "visible"});
}

/**
 * Makes the file box flash red one time
 */
function flashRed() {
    $("#toolbar").css("background-color", "#EF9A9A");
    $("#fileBox").css("border", "1px solid #EF9A9A");
    $("#statusBar").css("background-color", "#EF9A9A");
    $(".flashRed").fadeTo(300, 0.2, function () {
        $("#toolbar").css("background-color", "#E4E2E3");
        $("#fileBox").css("border", "1px solid #9E9E9E");
        $("#statusBar").css("background-color", "#E4E2E3");
        $(".flashRed").fadeTo(300, 1, function () {
        });
    });
}

/**
 * This function handles requests the create new folders. This includes animations, and the actual sending of the request
 * to the server
 */
function makeNewFolder() {
    var folderName = $('#newFolderTextInput').val();
    if (folderName !== "") {
        addFolder(folderName);
    }

    // Only animate if on a computer
    if ($(window).width() > 960) {
        $("#newFolderTextInput").animate({
            width: "0em",
            marginLeft: "0em"
        }, 1000, function () {
            $("#newFolderTextInput").animate({
                opacity: "0"
            }, 100)
        });
        $("#currentDirText").animate({
            width: "32.5em"
        }, 1000);

        document.getElementById("newFolderTextInput").value = '';
        $("#newFolderText").text("New Folder");
    }
}

/**
* Creates a new spinner on the target object target. A spinner is a spinning wheel used to illustrate waiting times
*
* @returns {*} the spinner object
*/
function initSpinner(target) {
    var opts = {
        lines: 13 // The number of lines to draw
        , length: 28 // The length of each line
        , width: 14 // The line thickness
        , radius: 42 // The radius of the inner circle
        , scale: 1 // Scales overall size of the spinner
        , corners: 1 // Corner roundness (0..1)
        , color: '#000' // #rgb or #rrggbb or array of colors
        , opacity: 0.25 // Opacity of the lines
        , rotate: 0 // The rotation offset
        , direction: 1 // 1: clockwise, -1: counterclockwise
        , speed: 1 // Rounds per second
        , trail: 60 // Afterglow percentage
        , fps: 20 // Frames per second when using setTimeout() as a fallback for CSS
        , zIndex: 2e9 // The z-index (defaults to 2000000000)
        , className: 'spinner' // The CSS class to assign to the spinner
        , top: '50%' // Top position relative to parent
        , left: '50%' // Left position relative to parent
        , shadow: false // Whether to render a shadow
        , hwaccel: false // Whether to use hardware acceleration
        , position: 'absolute' // Element positioning
    };

    spinner = new Spinner(opts).spin(target);
    return spinner;
}

/**
 * Gets the current spinner. A spinner is a spinning wheel used to illustrate waiting times
 *
 * @returns {*} the spinner object
 */
function getSpinner() {
    return spinner;
}

$(document).ready(function () {

    $(document).foundation();

    document.addEventListener("dragenter", dragenterDragleave);
    document.addEventListener("dragleave", dragenterDragleave);

    setDocHeight($(document).height());

    disableBackButton();
    disbaleForwardButton();
    updateFileStructure();

    $("#backButton").click(function () {
        back();
    });
    $("#backButton").mouseenter(function () {
        if (getBackState()) {
            $(this).css('cursor', 'pointer');
        }
    });
    $("#backButton").mouseleave(function () {
        $(this).css('cursor', 'default');
    });

    $("#forwardButton").click(function () {
        forward();
    });
    $("#forwardButton").mouseenter(function () {
        if (getForwardState()) {
            $(this).css('cursor', 'pointer');
        }
    });
    $("#forwardButton").mouseleave(function () {
        $(this).css('cursor', 'default');
    });

    $("#editButton").click(function () {
        editFileHubContent();
    });
    $("#editButton").mouseenter(function () {
        $(this).css('cursor', 'pointer');
    });
    $("#editButton").mouseleave(function () {
        $(this).css('cursor', 'default');
    });

    $("#newFolderButton").click(function () {
        if (newFolderInput || $(window).width() <= 960) {
            makeNewFolder();
        } else if ($(window).width() > 960) {   // Again, only animate if on a computer
            $("#newFolderTextInput").animate({
                opacity: "0.87"
            }, 100, function () {
                $("#newFolderTextInput").animate({
                    width: "10em",
                    marginLeft: "1em"
                }, 1000);
                $("#currentDirText").animate({
                    width: "21.25em"
                }, 1000)
            });

            $("#newFolderText").text("Create");
        }

        newFolderInput = !newFolderInput;
    });
    $("#newFolderButton").mouseenter(function () {
        $(this).css('cursor', 'pointer');
    });
    $("#newFolderButton").mouseleave(function () {
        $(this).css('cursor', 'default');
    });

    $("#backButtonModal").mouseenter(function () {
        if (backwardButtonModal) {
            $(this).css('cursor', 'pointer');
        }
    });
    $("#backButtonModal").mouseleave(function () {
        if (backwardButtonModal) {
            $(this).css('cursor', 'default');
        }
    });
    $("#backButtonModal").click(function () {
        if (backwardButtonModal) {
            previousViewObject();
        }
    });

    $("#forwardButtonModal").mouseenter(function () {
        if (forwardButtonModal) {
            $(this).css('cursor', 'pointer');
        }
    });
    $("#forwardButtonModal").mouseleave(function () {
        if (forwardButtonModal) {
            $(this).css('cursor', 'default');
        }
    });
    $("#forwardButtonModal").click(function () {
        if (forwardButtonModal) {
            nextViewObject();
        }
    });

    $("#downloadButtonModal").mouseenter(function () {
        $(this).css('cursor', 'pointer');
    });
    $("#downloadButtonModal").mouseleave(function () {
        $(this).css('cursor', 'default');
    });
    $("#downloadButtonModal").click(function () {
        var path = $("#currentDirText").text() + getCurrentViewObject();
        downloadFile(path);
    });

    $("body").keypress(function (event) {
        if (event.which == 32 && isVideoView()) {
            event.preventDefault();
            var player = document.getElementById('videoView');
            if (player.paused) {
                player.play();
            } else {
                player.pause();
            }
        }
    });

    $("#newFolderTextInput").keypress(function (event) {
        if (event.which == 13) {
            event.preventDefault();
            makeNewFolder();
        }
    });

    $(document).on('close.fndtn.reveal', '[data-reveal]', function () {
        setVideoView(false);
        setImageView(false);
        $("#statusBar").css("margin-bottom", 0 + "px");
    });

    $(document).on('opened.fndtn.reveal', '[data-reveal]', function () {
        initSpinner(document.getElementById('mainSectionModal'));
    });
});
