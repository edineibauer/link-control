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
        $this->param = array("title" => SITENAME);
        $this->library = ["angular", "materialize", "jquery"];
        $this->url = explode('/', strip_tags(trim(filter_input(INPUT_GET, 'url', FILTER_DEFAULT))));
        parent::checkRoute((!empty($this->url[0]) ? $this->url[0] : 'index'), $this->url[1] ?? null);
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
        $file['js'] = $this->prepareJs($file['dependencies']['js'] ?? null);
        $file['css'] = $this->prepareCss($file['dependencies']['css'] ?? null);
        $file['font'] = $this->prepareFont($file['dependencies']['font'] ?? null);
        $file['meta'] = $this->prepareMeta($file['dependencies']['meta'] ?? null);

        unset($file['dependencies']);

        return $file;
    }

    private function prepareJs($param = null)
    {
        $return = "<script src='" . HOME . "assets/system/js/boot.js" . "' defer ></script>\n";

        if ($param) {
            foreach ($param as $dependency) {
                $js = $this->checkIfExist($this->checkLinks($dependency, 'js'), $dependency);
                $return .= "<script src='{$js}' defer ></script>\n";
            }
        }

        return $return;
    }

    private function prepareCss($param = null)
    {
        $return = "";

        if ($param) {
            foreach ($param as $dependency) {
                $css = $this->checkIfExist($this->checkLinks($dependency, 'css'), $dependency);
                $return .= "<link rel='stylesheet' href='{$css}'>\n";
            }
        }

        return $return;
    }

    private function prepareFont($param = null)
    {
        $return = "";

        if ($param) {
            $this->param['font'] = "<link rel='stylesheet' href='" . implode("' type='text/css' media='all'/>\n<link rel='stylesheet' href='", $this->param['dependencies']['font']) . "' type='text/css' media='all'/>";
        }

        return $return;
    }

    private function prepareMeta($param = null)
    {
        $return = "";

        if ($param) {
            foreach ($param as $dependency) {
                $this->param['meta'] .= "<meta " . (isset($dependency['name']) ? "name='{$dependency['name']}' " : "") . (isset($dependency['property']) ? "property='{$dependency['property']}' " : "") . "content='{$dependency['content']}'>";
            }
        }

        return $return;
    }

    private function checkLinks($js, $type) {
        return "assets/" . (in_array($js, $this->library) ? "{$js}/{$type}/{$js}.min" : "my/{$type}/" . $js) . ".{$type}";
    }

    private function checkIfExist($file, $dependency)
    {
        if(in_array($dependency, $this->library)) {
            if(!file_exists(PATH_HOME . $file)) {
                $link = PATH_HOME;
                foreach (explode('/', $file) as $i => $peca) {
                    if (!preg_match('/\./', $peca)) {
                        $link .= ($i > 0 ? "/" : "") . $peca;
                        Helper::createFolderIfNoExist($link);
                    }
                }
                copy(PATH_HOME . "vendor/conn/" . parent::getLib() . "/" . $file, PATH_HOME . $file);
            }

            return HOME . $file;

        } else {

            return HOME . "vendor/conn/" . parent::getLib() . "/" . $file;
        }
    }
}
