var passBoxView = false;

function getHTTPObject() {
    var xmlhttp = false;
    if (typeof XMLHttpRequest != 'undefined') {
        try {
            xmlhttp = new XMLHttpRequest();
        } catch (e) {
            xmlhttp = false;
        }
    } else {
    }
    return xmlhttp;
}

function login(password) {
    var username = "user";
    var http = getHTTPObject();
    var url = "filehub.php";
    http.open("get", url, false, username, password);
    http.send("");
    if (http.status == 200) {
        document.location = url;
    } else {
        alert("Incorrect password!");
    }
    return false;
}

$(document).ready(function() {
    if (gOptions.enabled) {
        $('#title').text(gOptions.name + "Share");
    }

    $("#passwordBox").keypress(function(event) {
        if (event.which == 13) {
            event.preventDefault();
            login(document.getElementById("passwordBox").value);
        }
    });

    $("#enterButton").click(function() {
        if (passBoxView) {
            login(document.getElementById("passwordBox").value);
        } else {
            $('#buttonText').animate({
                fontSize: "1.5em"
            }, 1000);

            $(this).animate({
                height: "3em",
                width: "6em",
                marginTop: "0.5em"
            }, 1000);

            $('#passwordBox').css("display", "block");
            $('#passwordBox').animate({
                height: "2em",
                opacity: 0.87
            }, 1000);

            passBoxView = true;
        }
    });

    $("#enterButton").mouseenter(function() {
        $(this).css( 'cursor', 'pointer' );
        $(this).fadeTo(100, 0.7, function() {
            $(this).css("background-color", "#263238");
        }).fadeTo(100, 1);
    });
    $("#enterButton").mouseleave(function() {
        $(this).css( 'cursor', 'pointer' );
        $(this).fadeTo(100, 0.7, function() {
            $(this).css("background-color", "#607D8B");
        }).fadeTo(100, 1);
    });
});