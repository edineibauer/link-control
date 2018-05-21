<?php
$base = HOME . "vendor/conn/";
$baseAssets = HOME . (DEV ? "assetsPublic/" : "assets/");

//Base, index, Logo & favicon
$list = [HOME, HOME . "index.php", HOME . LOGO, HOME . FAVICON];

//base assets public
foreach (\Helpers\Helper::listFolder($baseAssets) as $asset) {
    if (file_exists($baseAssets . $asset . "/{$asset}.min.js"))
        $list[] = $baseAssets . $asset . "/{$asset}.min.js";
    elseif(file_exists($baseAssets . $asset . "/{$asset}.js"))
        $list[] = $baseAssets . $asset . "/{$asset}.js";

    if (file_exists($baseAssets . $asset . "/{$asset}.min.css"))
        $list[] = $baseAssets . $asset . "/{$asset}.min.css";
    elseif (file_exists($baseAssets . $asset . "/{$asset}.css"))
        $list[] = $baseAssets . $asset . "/{$asset}.css";
}

//templates front
foreach (\Helpers\Helper::listFolder(PATH_HOME . "vendor/conn/link-control/tplFront") as $tpl)
    $list[] = HOME . "vendor/conn/link-control/tplFront/{$tpl}";

/*
//assets theme lib
foreach (\Helpers\Helper::listFolder(PATH_HOME . "vendor/conn") as $lib) {
    if (file_exists(PATH_HOME . "vendor/conn/{$lib}/view/index.php")) {
        if (file_exists(PATH_HOME . "vendor/conn/{$lib}/view/inc/footer.php"))
            $list[] = $base . $lib . "/view/inc/footer.php";

        //todas as páginas
        foreach (\Helpers\Helper::listFolder(PATH_HOME . "vendor/conn/{$lib}/view") as $view) {
            if (preg_match('/[\.php|\.html]$/i', $view))
                $list[] = $base . $lib . "/view/{$view}";
        }

        //assets do tema
        foreach (\Helpers\Helper::listFolder(PATH_HOME . "vendor/conn/{$lib}/assets") as $asset)
            $list[] = $base . $lib . "/assets/{$asset}";

        //param do tema
        foreach (\Helpers\Helper::listFolder(PATH_HOME . "vendor/conn/{$lib}/param") as $param)
            $list[] = $base . $lib . "/param/{$param}";
    }
}*/
