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
    private $routes;
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
        $this->file = $file;

        if ($lib && (!DEV || (DEV && $lib !== DOMINIO)))
            $this->libPatch($lib);
        elseif (DEV)
            $this->route = PATH_HOME . "ajax/{$this->file}.php";
        else
            $this->searchRouteAjax($lib);

        if (!$this->route)
            $this->show404();
    }

    /**
     * @param string $lib
     */
    private function libPatch(string $lib)
    {
        if (file_exists(PATH_HOME . "vendor/conn/{$lib}/ajax/{$this->file}.php"))
            $this->route = PATH_HOME . "vendor/conn/{$lib}/ajax/{$this->file}.php";
    }

    /**
     * @param string $lib
     */
    private function searchRouteAjax(string $lib)
    {
        foreach (json_decode(file_get_contents(PATH_HOME . "_config/route.json"), true) as $libr) {
            if (file_exists(PATH_HOME . "vendor/conn/{$libr}/ajax/{$this->file}.php")) {
                $this->route = PATH_HOME . "vendor/conn/{$lib}/ajax/{$this->file}.php";
                break;
            }
        }
    }

    protected function checkRoute($file, $content = null)
    {
        $this->routes = $this->getRouteFile();

        foreach ($this->routes as $this->lib) {
            if ($this->route = $this->checkViewFile($file, $content))
                return true;
        }

        $this->show404();
    }

    /**
     * Retorna o link de acesso para DESENVOLVIMENTO ou PRODUÇÃO
     *
     * @return string
    */
    protected function getDir() :string {
        return !DEV || $this->lib !== DOMINIO ? "vendor/conn/{$this->lib}/" : "";
    }

    private function checkViewFile($file, $content = null)
    {
        $this->file = !empty($content) ? $content : $file;
        $path = PATH_HOME . $this->getDir() . "view/" . ($content ? $file . "/" : "") . $this->file . ".php";

        if (file_exists($path))
            return $path;

        if($content){
            $this->file = $file;
            $path = PATH_HOME . $this->getDir() . "view/" .  $this->file . ".php";

            if (file_exists($path))
                return $path;
        }

        return null;
    }

    private function show404()
    {
        $this->file = "404";
        $this->lib = "link-control";
        $this->route = PATH_HOME . (DEV && DOMINIO === "link-control" ? "" : "vendor/conn/link-control/") . "view/404.php";
    }

    /**
     * @return array
    */
    private function getRouteFile()
    {
        return json_decode(file_get_contents(PATH_HOME . "_config/route.json"), true);
    }
}