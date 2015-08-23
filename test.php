<!DOCTYPE html>
<html>
<head lang="en">
    <meta charset="UTF-8">
    <title></title>
    <link rel="stylesheet" href="css/foundation.css">
</head>
<body>
<a href="#" data-reveal-id="myModal">Click Me For A Modal</a>

<div id="myModal" class="reveal-modal" data-reveal aria-labelledby="modalTitle" aria-hidden="true" role="dialog">
    <h2 id="modalTitle">Awesome. I have it.</h2>
    <p class="lead">Your couch.  It is mine.</p>
    <p>I'm a cool paragraph that lives inside of an even cooler modal. Wins!</p>
    <a class="close-reveal-modal" aria-label="Close">&#215;</a>
</div>

<script type="text/javascript" src="js/api/jquery-2.1.1.js"></script>
<script type="text/javascript" src="js/api/foundation.js"></script>
<script type="text/javascript" src="js/api/foundation.reveal.js"></script>

<script>
    $(document).foundation();
</script>

</body>
</html>