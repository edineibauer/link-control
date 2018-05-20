<?php
$list = [];
$base = HOME . "vendor/conn/";
$baseAssets = HOME . (DEV ? "assetsPublic/" : "assets/");

//base metas
$param = json_decode(file_get_contents(PATH_HOME . "_config/param.json"), true);
foreach ($param['js'] as $j)
    $list[] = $baseAssets . $j . "/{$j}.js";
foreach ($param['css'] as $css)
    $list[] = $baseAssets . $css . "/{$css}.css";

//find theme lib
foreach (\Helpers\Helper::listFolder(PATH_HOME . "vendor/conn") as $lib) {
    if (file_exists(PATH_HOME . "vendor/conn/{$lib}/view/index.php")) {
        if (file_exists(PATH_HOME . "vendor/conn/{$lib}/view/inc/footer.php"))
            $list[] = $base . $lib . "/view/inc/footer.php";
        if (file_exists(PATH_HOME . "vendor/conn/{$lib}/view/inc/header.php"))
            $list[] = $base . $lib . "/view/inc/header.php";

        //views
        foreach (\Helpers\Helper::listFolder(PATH_HOME . "vendor/conn/{$lib}/view") as $view) {
            if (preg_match('/[\.php|\.html]$/i', $view))
                $list[] = $base . $lib . "/view/{$view}";
        }

        //assets
        foreach (\Helpers\Helper::listFolder(PATH_HOME . "vendor/conn/{$lib}/assets") as $asset)
            $list[] = $base . $lib . "/assets/{$asset}";

        //param
        foreach (\Helpers\Helper::listFolder(PATH_HOME . "vendor/conn/{$lib}/param") as $param)
            $list[] = $base . $lib . "/param/{$param}";
    }
}