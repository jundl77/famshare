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

    // Login cancelled
    die("Login cancelled");
}

// analyze the PHP_AUTH_DIGEST variable
if (!($data = http_digest_parse($_SERVER['PHP_AUTH_DIGEST'])) || !isset($users[sanitize($data['username'])])) {

    // False credentials
    die("False credentials");
}

// generate the valid response
$A1 = md5($username . ':' . $realm . ':' . $users[sanitize($data['username'])]);
$A2 = md5($_SERVER['REQUEST_METHOD'] . ':' . sanitize($data['uri']));
$valid_response = md5($A1 . ':' . sanitize($data['nonce']) . ':' . sanitize($data['nc']) . ':' . sanitize($data['cnonce'])
    . ':' . sanitize($data['qop']) . ':'
    . $A2);

if (sanitize($data['response']) != $valid_response) {

    // False credentials
    die("False credentials");
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
