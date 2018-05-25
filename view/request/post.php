<?php
use \Helpers\Check;
use \LinkControl\Route;

if (Check::ajax()) {

    $lib = strip_tags(trim(filter_input(INPUT_POST, "lib", FILTER_DEFAULT)));
    $url = strip_tags(trim(filter_input(INPUT_POST, "file", FILTER_DEFAULT)));

    if (!$lib) {
        $route = new Route();
        $route->checkRouteAjax($url);
        include_once $route->getRoute();

    } else {

        $data = ["response" => 1, "error" => "", "data" => ""];

        $include = PATH_HOME . (!DEV || $lib !== DOMINIO ? "vendor/conn/{$lib}/" : "") . "ajax/{$url}.php";
        if(file_exists($include))
            include_once $include;

        echo json_encode($data);
    }

}