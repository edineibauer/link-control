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

    /**
     * Route constructor.
     * @param string|null $url
     * @param string $dir
     */
    public function __construct(string $url = null, string $dir = "view")
    {
        if (!$url)
            $url = strip_tags(trim(filter_input(INPUT_GET, 'url', FILTER_DEFAULT)));

        $paths = array_filter(explode('/', $url));
        $this->searchRoute($paths, $dir);
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
    private function searchRoute(array $paths, string $dir)
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
        $libsPath[] = [DOMINIO => "public/{$dir}"];
        if (!empty($_SESSION['userlogin'])) {
            $libsPath[][DOMINIO] = "public/{$dir}/{$_SESSION['userlogin']['setor']}";
            $libsPath = array_merge($libsPath, array_map(function ($class) use ($dir) {
                return [$class => VENDOR . $class . "/{$dir}/{$_SESSION['userlogin']['setor']}"];
            }, $this->getRouteFile()));
        }
        $libsPath = array_merge($libsPath, array_map(function ($class) use ($dir) {
            return [$class => VENDOR . $class . "/{$dir}"];
        }, $this->getRouteFile()));

        foreach ($libsPath as $lib) {
            foreach ($lib as $this->lib => $item) {
                if (file_exists(PATH_HOME . "{$item}/{$path}.php"))
                    return "{$item}/{$path}.php";
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