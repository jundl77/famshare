<?php

function sanitize($val) {
    if (!preg_match_all("/^([\w ]*[.]*[(]*[)]*[-]*[\/]*)+$/", $val) && $val !== "") {
        die("False credentials");
    }

    $val = trim($val);
    $val = strip_tags($val);
    $val = htmlentities($val, ENT_QUOTES, 'UTF-8'); // convert funky chars to html entities
    $pat = array("\r\n", "\n\r", "\n", "\r"); // remove returns
    $val = str_replace($pat, '', $val);
    $pat = array('/^\s+/', '/\s{2,}/', '/\s+\$/'); // remove multiple whitespaces
    $rep = array('', ' ', '');
    $val = preg_replace($pat, $rep, $val);
    return trim($val);
}