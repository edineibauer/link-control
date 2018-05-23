<?php
use \Helpers\Check;
use \LinkControl\Route;

if (Check::ajax()) {

    $lib = strip_tags(trim($link->getUrl()[2]));
    $url = "";
    for ($i = 3; $i < count($link->getUrl()); $i++)
        $url .= (!empty($url) ? "/" : "") . $link->getUrl()[$i];

    if (!$lib) {
        $route = new Route();
        $route->checkRouteAjax($url);
        include_once $route->getRoute();

    } else {

        $data = ["response" => 1, "error" => "", "data" => ""];
        $include = PATH_HOME . (!DEV || $lib !== DOMINIO ? "vendor/conn/{$lib}/" : "") . "ajax/{$url}.php";
        if (file_exists($include)) {
            include_once $include;

            if (!empty($data['data']['title']) && preg_match('/^view\//i', $url) && file_exists(PATH_HOME . (!DEV || $lib !== DOMINIO ? "vendor/conn/{$lib}/" : "") . "param/{$url}.json")) {
                $file = json_decode(file_get_contents(PATH_HOME . (!DEV || $lib !== DOMINIO ? "vendor/conn/{$lib}/" : "") . "param/{$url}.json"), true);

                if ($file['title'])
                    $data['data']['title'] = $this->prepareTitle($file['title']);
            }
        } elseif(preg_match('/^view\//i', $url)) {
            include_once PATH_HOME . "vendor/conn/link-control/ajax/view/404.php";
        }
        echo json_encode($data);
    }
}