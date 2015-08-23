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
    $(".flashRed").fadeTo(300, 0.2, function() {
        $("#toolbar").css("background-color", "#E4E2E3");
        $("#fileBox").css("border", "1px solid #9E9E9E");
        $("#statusBar").css("background-color", "#E4E2E3");
        $(".flashRed").fadeTo(300, 1, function() {
        });
    });
}

$(document).ready(function() {

    $(document).foundation();

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
    });
    $("#editButton").mouseenter(function() {
        $(this).css( 'cursor', 'pointer' );
    });
    $("#editButton").mouseleave(function() {
        $(this).css( 'cursor', 'default' );
    });

    $("#newFolderButton").click(function() {
        if (newFolderInput || $(window).width() <= 960) {
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
        } else if ($(window).width() > 960) {   // Again, only animate if on a computer
            $("#newFolderTextInput").animate({
                opacity: "0.87"
            }, 100, function() {
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
    $("#newFolderButton").mouseenter(function() {
        $(this).css( 'cursor', 'pointer' );
    });
    $("#newFolderButton").mouseleave(function() {
        $(this).css( 'cursor', 'default' );
    });

    $("#viewModalClose").mousedown(function() {
        $('#viewModal').foundation('reveal', 'close');
    });
});
