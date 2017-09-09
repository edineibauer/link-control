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
        $this->library = "http://dev.buscaphone.com/library";
        $this->param = array("title" => SITENAME, "meta" => "", "css" => "", "js" => "", "font" => "");
        $this->url = explode('/', strip_tags(trim(filter_input(INPUT_GET, 'url', FILTER_DEFAULT))));
        parent::checkRoute((isset($this->url[0]) && !empty($this->url[0]) ? $this->url[0] : 'index'), $this->url[1] ?? null);
        $this->checkParamPage();
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
        if (parent::getLib()) {
            if (file_exists(PATH_HOME . "vendor/conn/" . parent::getLib() . "/param/" . parent::getFile() . ".json")) {
                $file = file_get_contents(PATH_HOME . "vendor/conn/" . parent::getLib() . "/param/" . parent::getFile() . ".json");
                if (strlen($file) > 5) {
                    $this->param =  $this->prepareDependencies($file);
                }
            }
        }
    }

    private function prepareDependencies($file)
    {
        $file = json_decode($file, true);

        $file['js'] = $this->prepareJs(true, $file['libraries']['js'] ?? null);
        $file['js'] .= $this->prepareJs(false, $file['dependencies']['js'] ?? null);

        $file['css'] = $this->prepareCss(true, $file['libraries']['css'] ?? null);
        $file['css'] .= $this->prepareCss(false, $file['dependencies']['css'] ?? null);

        $file['font'] = $this->prepareIcon($file['libraries']['icon'] ?? null);
        $file['font'] .= $this->prepareFont($file['libraries']['font'] ?? null);
        $file['meta'] = $this->prepareMeta($file['dependencies']['meta'] ?? null);

        unset($file['dependencies'], $file['libraries']);

        return $file;
    }

    private function prepareJs($lib, $param = null)
    {
        $return = "";

        if ($param) {
            foreach ($param as $dependency) {
                $js = $this->getLinkName($dependency, 'js', $lib);
                $js = $this->checkIfExist($js, $dependency, 'js', $lib);
                $return .= "<script src='{$js}' defer ></script>\n";
            }
        }

        return $return;
    }

    private function prepareCss($lib, $param = null)
    {
        $return = "";

        if ($param) {
            foreach ($param as $dependency) {
                $css = $this->getLinkName($dependency, 'css', $lib);
                $css = $this->checkIfExist($css, $dependency, 'css', $lib);
                $return .= "<link rel='stylesheet' href='{$css}'>\n";
            }
        }

        return $return;
    }

    private function prepareIcon($param = null)
    {
        $return = "";

        if ($param) {
            foreach ($param as $item) {
                switch ($item) {
                    case 'materialize':
                        $item = 'https://fonts.googleapis.com/icon?family=Material+Icons';
                        break;
                    case 'material':
                        $item = 'https://fonts.googleapis.com/icon?family=Material+Icons';
                        break;
                }
                $return .= "<link rel='stylesheet' href='{$item}' type='text/css' media='all'/>";
            }
        }

        return $return;
    }

    private function prepareFont($param = null)
    {
        $return = "";

        if ($param) {
            foreach ($param as $item) {
                $return .= "<link rel='stylesheet' href='https://fonts.googleapis.com/css?family=" . ucfirst($item) . ":100,300,400,700' type='text/css' media='all'/>";
            }
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

    private function getLinkName($dependency, $type, $lib) {
        return "assets/" . ($lib ? "{$dependency}/{$type}/{$dependency}.min" : "{$type}/" . $dependency) . ".{$type}";
    }

    private function checkIfExist($file, $dependency, $type, $lib)
    {
        if($lib) {
            if(!file_exists(PATH_HOME . $file)) {
                $this->createFolderAssetsLibraries($file);
                copy("{$this->library}/{$type}/{$dependency}.min.{$type}", PATH_HOME . $file);
            }

            return HOME . $file;

        } else {

            return HOME . "vendor/conn/" . parent::getLib() . "/" . $file;
        }
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
}
