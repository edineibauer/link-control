<?php

/**
 * Route.class [ MODEL ]
 * Valida endereços de urls solicitadas e direciona para seu caminho!
 *
 * @copyright (c) 2017, Edinei J. Bauer
 */

namespace LinkControl;

use Helpers\Helper;

class Route
{
    private $route;

    /**
     * @return mixed
     */
    public function getRoute(): string
    {
        return $this->route;
    }

    public function checkRouteAjax($file)
    {
        foreach (Helper::listFolder(PATH_HOME . "vendor/conn/") as $lib) {
            if($this->route = $this->checkDir(PATH_HOME . "vendor/conn/{$lib}/ajax/", $file)) {
                return true;
                break;
            }
        }

        $this->route = PATH_HOME . "vendor/conn/link-control/view/404.php";
        return false;

    }

    protected function checkRoute($file, $content = null)
    {
        foreach (Helper::listFolder(PATH_HOME . "vendor/conn/") as $lib) {
            if($this->route = $this->checkDir(PATH_HOME . "vendor/conn/{$lib}/view/", $file, $content)) {
                return true;
                break;
            }
        }

        $this->route = PATH_HOME . "vendor/conn/link-control/view/404.php";
        return false;

    }

    protected function checkDir($folder, $file, $content = null)
    {
        if (!empty($content) && file_exists($folder . $file . "/{$content}.php")) {
            return $folder . $file . "/{$content}.php";
        } elseif (file_exists($folder . $file . ".php")) {
            return $folder . $file . ".php";
        }

        return null;
    }
}