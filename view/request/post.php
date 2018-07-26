<?php

use \Helpers\Check;
use \LinkControl\Route;

if (Check::ajax()) {
    $lib = strip_tags(trim(filter_input(INPUT_POST, "lib", FILTER_DEFAULT)));
    $url = strip_tags(trim(filter_input(INPUT_POST, "file", FILTER_DEFAULT)));

    $include = PATH_HOME . ($lib !== DOMINIO ? VENDOR . "{$lib}/" : "") . "ajax/{$url}.php";
    if (file_exists($include)) {
        include_once $include;
    } elseif(!empty($_SESSION['userlogin'])) {
        $include = PATH_HOME . ($lib !== DOMINIO ? VENDOR . "{$lib}/" : "") . "ajax/{$_SESSION['userlogin']['setor']}/{$url}.php";
        if (file_exists($include))
            include_once $include;
        else
            $data = "";
    } else {
        $data = "";
    }
}