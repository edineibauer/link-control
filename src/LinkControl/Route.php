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
    private $lib;
    private $file;

    /**
     * @return mixed
     */
    public function getRoute(): string
    {
        return $this->route;
    }

    /**
     * @return mixed
     */
    public function getLib()
    {
        return $this->lib;
    }

    /**
     * @return mixed
     */
    public function getFile()
    {
        return $this->file;
    }

    public function checkRouteAjax($file, $lib = null)
    {
        if($lib) {
            if (file_exists(PATH_HOME . "vendor/conn/{$lib}/ajax/{$file}.php")) {
                $this->file = $file;
                $this->route = PATH_HOME . "vendor/conn/{$lib}/ajax/{$file}.php";
                return true;
            }
        } else {
            foreach (Helper::listFolder(PATH_HOME . "vendor/conn/") as $libr) {
                if (file_exists(PATH_HOME . "vendor/conn/{$libr}/ajax/{$file}.php")) {
                    $this->file = $file;
                    $this->route = PATH_HOME . "vendor/conn/{$lib}/ajax/{$file}.php";
                    return true;
                }
            }
        }

        $this->route = PATH_HOME . "vendor/conn/link-control/view/404.php";
        return false;

    }

    protected function checkRoute($file, $content = null)
    {
        foreach (Helper::listFolder(PATH_HOME . "vendor/conn/") as $lib) {
            if($this->route = $this->checkDir(PATH_HOME . "vendor/conn/{$lib}/view/", $file, $content)) {
                $this->lib = $lib;
                return true;
            }
        }

        $this->file = "404";
        $this->lib = "link-control";
        $this->route = PATH_HOME . "vendor/conn/link-control/view/404.php";
        return false;

    }

    protected function checkDir($folder, $file, $content = null)
    {
        if (!empty($content) && file_exists($folder . $file . "/{$content}.php")) {
            $this->file = $content;
            return $folder . $file . "/{$content}.php";
        } elseif (file_exists($folder . $file . ".php")) {
            $this->file = $file;
            return $folder . $file . ".php";
        }

        return null;
    }
}