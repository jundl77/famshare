<?php

/**
 * This function makes sure the value given is safe to use (free of code injections, etc.)
 *
 * @param $val string the value to check for safety
 * @return string the safe value to use, or false if it failed to make the value safe
 */
function sanitize($val)
{
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