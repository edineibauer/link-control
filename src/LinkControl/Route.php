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
    private function searchRoute(array $paths, string $dir = 'view')
    {
        if ($dir === 'view') {
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
                        if ($dir === "view") {
                            var_dump("Erro: Site não possúi arquivo 404 padrão. Crie o arquivo 'view/404.php'");
                            die;
                        }
                    }
                }
            }
        } elseif ($dir === 'ajax') {
            if (count($paths) > 1) {
                $this->lib = $paths[0];
                unset($paths[0]);
                $this->file = implode("/", $paths);
            } else {
                $this->file = $paths[0] ?? "index";
            }
            if (!empty($this->lib) && file_exists(PATH_HOME . ($this->lib === DOMINIO ? "public/" : VENDOR . $this->lib . "/") . "{$dir}/" . $this->file . ".php"))
                $this->route = ($this->lib === DOMINIO ? "public/" : VENDOR . $this->lib . "/") . "{$dir}/" . $this->file . ".php";
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
        $libsPath = ["public"];
        if (!empty($_SESSION['userlogin'])) {
            $libsPath[] = "public/{$_SESSION['userlogin']['setor']}";
            $libsPath = array_merge($libsPath, array_map(function ($class) {
                return VENDOR . $_SESSION['userlogin']['setor'] . "/" . $class;
            }, $this->getRouteFile()));
        }
        $libsPath = array_merge($libsPath, array_map(function ($class) {
            return VENDOR . $class;
        }, $this->getRouteFile()));

        foreach ($libsPath as $item) {
            if (file_exists(PATH_HOME . "{$item}/{$dir}/{$path}.php")) {
                if (!empty($_SESSION['userlogin']))
                    $this->lib = str_replace([VENDOR . "{$_SESSION['userlogin']['setor']}/", VENDOR, "public/{$_SESSION['userlogin']['setor']}", 'public'], ['', '', DOMINIO, DOMINIO], $item);
                else
                    $this->lib = str_replace([VENDOR, 'public'], ['', DOMINIO], $item);

                return "{$item}/{$dir}/{$path}.php";
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