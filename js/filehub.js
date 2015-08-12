var dragCount = 0;
var fileAdded = false;
var newFolderInput = false;

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
    $("#editButton").css("border", "1px solid #607D8B");
    $("#editText").css("color", "1px solid #607D8B");
}

/**
 * Deactivates the edit button
 */
function deactivateEditButton() {
    $("#editButton").css("border", "1px solid ##C8C8C8");
    $("#editText").css("color", "1px solid ##C8C8C8");
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

$(document).ready(function() {
    if (gOptions.enabled) {
        $('#title').text(gOptions.name + "Share");
    }

    document.addEventListener("dragenter", dragenterDragleave);
    document.addEventListener("dragleave", dragenterDragleave);

    disableBackButton();
    disbaleForwardButton();
    updateFileStructure();

    $("#backButton").click(function() {
        back();
    });
    $("#backButton").mouseenter(function() {
        if (getBackState()) {
            $(this).css( 'cursor', 'pointer' );
        }
    });
    $("#backButton").mouseleave(function() {
        $(this).css( 'cursor', 'default' );
    });

    $("#forwardButton").click(function() {
        forward();
    });
    $("#forwardButton").mouseenter(function() {
        if (getForwardState()) {
            $(this).css('cursor', 'pointer');
        }
    });
    $("#forwardButton").mouseleave(function() {
        $(this).css( 'cursor', 'default' );
    });

    $("#editButton").click(function() {
        editFileHubContent();
        $(this).css( 'cursor', 'pointer' );
    });
    $("#editButton").mouseenter(function() {
        $(this).css( 'cursor', 'pointer' );
    });
    $("#editButton").mouseleave(function() {
        $(this).css( 'cursor', 'default' );
    });

    $("#newFolderButton").click(function() {
        if (newFolderInput) {
            var folderName = $('#newFolderTextInput').val();
            if (folderName !== "") {
                addFolder(folderName);
            }
            $("#newFolderTextInput").animate({
                width: "0em",
                marginLeft: "0em"
            }, 1000, function() {
                $("#newFolderTextInput").animate({
                    opacity: "0"
                }, 100)
            });
            $("#currentDirText").animate({
                width: "36.25em"
            }, 1000)
        } else {
            $("#newFolderTextInput").animate({
                opacity: "0.87"
            }, 100, function() {
                $("#newFolderTextInput").animate({
                    width: "10em",
                    marginLeft: "1em"
                }, 1000);
                $("#currentDirText").animate({
                    width: "25em"
                }, 1000)
            })
        }

        newFolderInput = !newFolderInput;
    });
    $("#newFolderButton").mouseenter(function() {
        $(this).css( 'cursor', 'pointer' );
    });
    $("#newFolderButton").mouseleave(function() {
        $(this).css( 'cursor', 'default' );
    });
});
