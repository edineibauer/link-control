<?php

/**
 * Link.class [ MODEL ]
 * Responável por gerenciar e fornecer informações sobre o link url!
 *
 * @copyright (c) 2017, Edinei J. Bauer
 */

namespace LinkControl;

use Helpers\Helper;

class Link extends Route
{
    private $url;
    private $param;
    private $library;

    function __construct()
    {
        $this->library = "http://dev.ontab.com.br";
        $this->param = ["title" => SITENAME, "version" => VERSION, "meta" => "", "css" => "", "js" => "", "font" => ""];
        $this->url = explode('/', strip_tags(trim(filter_input(INPUT_GET, 'url', FILTER_DEFAULT))));
        parent::checkRoute(!empty($this->url[0]) ? $this->url[0] : 'index', $this->url[1] ?? null);
        $this->checkParamPage();
        new EntityImport(parent::getLib());
        new Sessao();
    }

    /**
     * @return array
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return mixed
     */
    public function getParam()
    {
        return $this->param;
    }

    private function checkParamPage()
    {
        $this->prepareDependencies(PATH_HOME . "_config/param.json");

        if (parent::getLib()) {
            $path = PATH_HOME . (!DEV || parent::getLib() !== DOMINIO ? "vendor/conn/" . parent::getLib() . "/" : "") . "param/" . parent::getFile() . ".json";
            if (file_exists($path))
                $this->prepareDependencies($path);
        }

        if(file_exists(PATH_HOME . parent::getDir() . "assets/" . parent::getFile() . $this->getMinify() . ".js"))
            $this->param['js'] .= "<script src='" . HOME . parent::getDir() . "assets/" . parent::getFile() . $this->getMinify() . ".js?v=" . VERSION . "' defer ></script>\n";
        elseif(file_exists(PATH_HOME . parent::getDir() . "assets/" . parent::getFile() . ".js"))
            $this->param['js'] .= "<script src='" . HOME . parent::getDir() . "assets/" . parent::getFile() . ".js?v=" . VERSION . "' defer ></script>\n";

        if(file_exists(PATH_HOME . parent::getDir() . "assets/" . parent::getFile() . $this->getMinify() . ".css"))
            $this->param['css'] .= "<link rel='stylesheet' href='" . HOME . parent::getDir() . "assets/" . parent::getFile() . $this->getMinify() . ".css?v=" . VERSION . "'>\n";
        elseif(file_exists(PATH_HOME . parent::getDir() . "assets/" . parent::getFile() . ".css"))
            $this->param['css'] .= "<link rel='stylesheet' href='" . HOME . parent::getDir() . "assets/" . parent::getFile() . ".css?v=" . VERSION . "'>\n";

    }

    /**
     * @param string $file
    */
    private function prepareDependencies(string $file)
    {
        $file = json_decode(file_get_contents($file), true);

        $this->param['js'] .= !empty($file['js']) ? $this->prepareDependency($file['js'], 'js') : null;
        if(!empty($file['css']))
            $this->param['css'] .= $this->prepareDependency($file['css'], 'css');
        $this->param['font'] .= (!empty($file['icon']) ? $this->prepareIcon($file['icon']) : "") . (!empty($file['font']) ? $this->prepareFont($file['font']) : null);
        $this->param['meta'] .= $this->prepareMeta($file['meta'] ?? null);
    }

    private function prepareDependency(array $param, string $extensao): string
    {
        $return = "";
        foreach ($param as $dependency) {
            $return .= $this->getLinkDependency($dependency, $extensao);
        }
        return $return;
    }

    private function prepareIcon(array $param): string
    {
        $return = "";
        foreach ($param as $item) {
            $return .= "<link rel='stylesheet' href='https://fonts.googleapis.com/icon?family=" . ucfirst($item) . "+Icons' type='text/css' media='all'/>";
        }
        return $return;
    }

    private function prepareFont(array $param): string
    {
        $return = "";
        foreach ($param as $item) {
            $return .= "<link rel='stylesheet' href='https://fonts.googleapis.com/css?family=" . ucfirst($item) . ":100,300,400,700' type='text/css' media='all'/>";
        }
        return $return;
    }

    private function prepareMeta($param = null)
    {
        $return = "";

        if ($param) {
            foreach ($param as $dependency) {
                $return .= "<meta " . (isset($dependency['name']) ? "name='{$dependency['name']}' " : "") . (isset($dependency['property']) ? "property='{$dependency['property']}' " : "") . "content='{$dependency['content']}'>";
            }
        }

        return $return;
    }

    private function getLinkDependency($library, $extensao)
    {
        $file = $this->getAssets() . "/{$library}/{$library}" . $this->getMinify($library) . ".{$extensao}";
        if (!file_exists($file)) {
            $this->createFolderAssetsLibraries($file);
            copy("{$this->library}/{$library}/{$library}" . $this->getMinify($library) . ".{$extensao}", PATH_HOME . $file);
        }

        return $extensao === "js" ? "<script src='" . HOME . $file . "?v=" . VERSION . "' defer ></script>\n" : "<link rel='stylesheet' href='" . HOME . $file . "?v=" . VERSION . "'>\n";
    }

    private function createFolderAssetsLibraries($file)
    {
        $link = PATH_HOME;
        $split = explode('/', $file);
        foreach ($split as $i => $peca) {
            if ($i < count($split) - 1) {
                $link .= ($i > 0 ? "/" : "") . $peca;
                Helper::createFolderIfNoExist($link);
            }
        }
    }

    /**
     * Retorna .min para PRODUÇÃO ou vazio para DESENVOLVIMENTO
     *
     * @return string
     */
    private function getMinify($library = null) :string {
        return !DEV || in_array($library, ["angular", "jquery", "materialize", "bootstrap"]) ? ".min" : "";
    }

    /**
     * Retorna o Assets para produçao ou Desenvolvimento
     *
     * @return string
    */
    private function getAssets() :string
    {
        return DEV ? "assetsPublic" : "assets";
    }
}