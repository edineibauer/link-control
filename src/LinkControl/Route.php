<?php

/**
 * Route.class [ MODEL ]
 * Valida endereços de urls solicitadas e direciona para seu caminho!
 *
 * @copyright (c) 2017, Edinei J. Bauer
 */

namespace LinkControl;

use Helpers\Helper;

abstract class Route
{

    private $url;
    private $route;
    private $result;

    function __construct($url = null)
    {
        if ($url) {
            $this->setUrl($url);
        }
    }

    /**
     * @param mixed $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

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
    public function getResult()
    {
        return $this->result;
    }

    public function checkRoute($file, $content = null)
    {
        foreach (Helper::listFolder(PATH_HOME . "vendor/conn/") as $folder) {
            if($this->route = $this->checkDir(PATH_HOME . "vendor/conn/{$folder}/view/", $file, $content)) {
                return true;
                break;
            }
        }

        $this->route = PATH_HOME . "vendor/conn/link-control/view/404.php";
        return false;

    }

    private function checkDir($folder, $file, $content = null)
    {
        if (!empty($content) && file_exists($folder . $file . "/{$content}.php")) {
            return $folder . $file . "/{$content}.php";
        } elseif (file_exists($folder . $file . ".php")) {
            return $folder . $file . ".php";
        }

        return null;
    }
}