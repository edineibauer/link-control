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
        $this->param = ["title" => SITENAME, "meta" => "", "css" => "", "js" => "", "font" => ""];
        $this->url = explode('/', strip_tags(trim(filter_input(INPUT_GET, 'url', FILTER_DEFAULT))));
        parent::checkRoute(!empty($this->url[0] ? $this->url[0] : 'index'), $this->url[1] ?? null);
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
                $file = json_decode(file_get_contents(PATH_HOME . "vendor/conn/" . parent::getLib() . "/param/" . parent::getFile() . ".json"), true);
                if (!empty($file))
                    $this->param = $this->prepareDependencies($file);
            }
        }
    }

    private function prepareDependencies($file)
    {
        $file['js'] = !empty($file['libraries']['js']) ? $this->prepareDependency($file['libraries']['js'], 'js') : null;
        $file['css'] = !empty($file['libraries']['css']) ? $this->prepareDependency($file['libraries']['css'], 'css') : null;
        $file['font'] = (!empty($file['libraries']['icon']) ? $this->prepareIcon($file['libraries']['icon']) : "") . (!empty($file['libraries']['font']) ? $this->prepareFont($file['libraries']['font']) : null);
        $file['meta'] = $this->prepareMeta($file['meta'] ?? null);

        $file['js'] .= $this->getLinkDependency(parent::getFile(), 'js');
        $file['css'] .= $this->getLinkDependency(parent::getFile(), 'css');

        unset($file['dependencies'], $file['libraries']);

        return $file;
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
        $file = PATH_HOME . "assets/{$library}/{$library}.min.{$extensao}";
        if (!file_exists($file)) {
            $this->createFolderAssetsLibraries("assets/{$library}/{$library}.min.{$extensao}");
            copy("{$this->library}/assets/{$library}/{$library}.min.{$extensao}", $file);
        }

        return $extensao === "js" ? "<script src='{$file}' defer ></script>\n" : "<link rel='stylesheet' href='{$file}'>\n";
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
