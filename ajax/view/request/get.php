<?php
$data = ["response" => 1, "error" => "", "data" => ""];
$path = null;
$url = "";
for ($i = 2; $i < count($link->getUrl()); $i++)
    $url .= (!empty($url) ? "/" : "") . $link->getUrl()[$i];

foreach (\Helpers\Helper::listFolder(PATH_HOME . "vendor/conn") as $lib) {
    if (file_exists(PATH_HOME . "vendor/conn/{$lib}/ajax/{$url}.php")) {
        $data['data']['lib'] = $lib;
        $path = PATH_HOME . "vendor/conn/{$lib}/ajax/{$url}.php";
        break;
    }
}

if (!$path && DEV) {
    foreach (\Helpers\Helper::listFolder(PATH_HOME . "ajax") as $lib) {
        if (file_exists(PATH_HOME . "ajax/{$url}.php")) {
            $data['data']['lib'] = $lib;
            $path = PATH_HOME . "ajax/{$url}.php";
            break;
        }
    }
}

if ($path) {
    include_once $path;

    if (empty($data['data']['title']) && preg_match('/^view\//i', $url) && file_exists(PATH_HOME . (!DEV || $lib !== DOMINIO ? "vendor/conn/{$lib}/" : "") . "param/{$url}.json")) {
        $file = json_decode(file_get_contents(PATH_HOME . (!DEV || $lib !== DOMINIO ? "vendor/conn/{$lib}/" : "") . "param/{$url}.json"), true);

        if ($file['title'])
            $data['data']['title'] = $this->prepareTitle($file['title']);
    }
}

echo json_encode($data);