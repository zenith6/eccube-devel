<?php

$lib_dir = dirname(__FILE__) . "/library";
ini_set('include_path', ini_get('include_path') . PATH_SEPARATOR . $lib_dir);

if (!function_exists('h')) {
    function h($string) {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
}
