<?php

/**
 * Busca por arquivo a ser carregado em um request ao sistema Singular
 *
 * @copyright (c) 2018, Edinei J. Bauer
 */

namespace LinkControl;

use Helpers\Helper;
use Helpers\Check;

class Route
{
    private $route;
    private $lib;
    private $file;
    private $var;

    public function __construct()
    {
        $paths = array_filter(explode('/', strip_tags(trim(filter_input(INPUT_GET, 'url', FILTER_DEFAULT)))));
        $this->searchRoute($paths, 'view');
    }

    /**
     * @return mixed
     */
    public function getVar()
    {
        return $this->var;
    }

    /**
     * @return mixed
     */
    public function getRoute()
    {
        return $this->route ? PATH_HOME . $this->route : null;
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

    /**
     * @param string $dir
     */
    private function searchRoute(array $paths, string $dir = 'view')
    {
        if (count($paths) > 1) {
            $this->var = array_pop($paths);
            $this->file = array_pop($paths);
            if (!empty($paths))
                $path = implode('/', $paths) . '/' . $this->file;
            else
                $path = $this->file;
        } else {
            $this->file = $path = $paths[0] ?? "index";
        }

        if (!$this->route = $this->findRoute($path, $dir)) {
            //busca rota, considerando var como caminho
            if ($this->var) {
                $path .= "/{$this->var}";
                $this->file = $this->var;
                $this->var = null;
                $this->route = $this->findRoute($path, $dir);
            }

            if (!$this->route && !Check::ajax()) {
                $this->file = $path = "404";
                if (!$this->route = $this->findRoute($path, $dir)) {
                    var_dump("Erro: Site não possúi arquivo 404 padrão. Crie o arquivo 'view/404.php'");
                    die;
                }
            }
        }
    }

    /**
     * Busca por rota
     *
     * @param string $path
     * @param string $dir
     * @return null|string
     */
    private function findRoute(string $path, string $dir)
    {
        //interno
        if (file_exists(PATH_HOME . "{$dir}/{$path}.php")) {
            $this->lib = defined('DOMINIO') ? DOMINIO : '';
            return "{$dir}/{$path}.php";
        }

        //interno login setor
        if (!empty($_SESSION['userlogin']) && file_exists(PATH_HOME . "{$dir}/{$_SESSION['userlogin']['setor']}/{$path}.php")) {
            $this->lib = defined('DOMINIO') ? DOMINIO : '';
            return "{$dir}/{$path}.php";
        }

        //libs
        foreach ($this->getRouteFile() as $this->lib) {
            if (file_exists(PATH_HOME . VENDOR . "{$this->lib}/{$dir}/{$path}.php"))
                return VENDOR . "{$this->lib}/{$dir}/{$path}.php";
        }

        //libs login setor
        if (!empty($_SESSION['userlogin'])) {
            foreach ($this->getRouteFile() as $this->lib) {
                if (file_exists(PATH_HOME . VENDOR . "{$this->lib}/{$dir}/{$_SESSION['userlogin']['setor']}/{$path}.php"))
                    return VENDOR . "{$this->lib}/{$dir}/{$_SESSION['userlogin']['setor']}/{$path}.php";
            }
        }

        return null;
    }

    /**
     * Retorna rotas aceitas nas libs do vendor
     * @return array
     */
    private function getRouteFile(): array
    {
        return file_exists(PATH_HOME . "_config/route.json") ? json_decode(file_get_contents(PATH_HOME . "_config/route.json"), true) : [];
    }
}