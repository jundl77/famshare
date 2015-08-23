<?php

function sanitize($val) {
    if (!preg_match_all("/^([\w]*[.()-\/,\s]*[\x{0080}-\x{00FF}]*)+$/", $val) && $val !== "") {
        return false;
    }

    $val = trim($val);
    $val = strip_tags($val);
    $pat = array("\r\n", "\n\r", "\n", "\r"); // remove returns
    $val = str_replace($pat, '', $val);
    $pat = array('/^\s+/', '/\s{2,}/', '/\s+\$/'); // remove multiple whitespaces
    $rep = array('', ' ', '');
    $val = preg_replace($pat, $rep, $val);
    return trim($val);
}