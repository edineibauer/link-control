<?php

/**
 * Link.class [ MODEL ]
 * Responável por gerenciar e fornecer informações sobre o link url!
 *
 * @copyright (c) 2017, Edinei J. Bauer
 */

namespace LinkControl;

use ConnCrud\Read;
use EntityForm\Dicionario;
use Helpers\Helper;
use MatthiasMullie\Minify;


class Link extends Route
{
    private $url;
    private $param;
    private $dicionario;

    function __construct()
    {
        $url = strip_tags(trim(filter_input(INPUT_GET, 'url', FILTER_DEFAULT)));
        $this->devLibrary = "http://dev.ontab.com.br";
        $this->param = ["title" => SITENAME, "version" => VERSION, "meta" => "", "css" => "", "js" => "", "font" => "", "analytics" => defined("ANALYTICS") ? ANALYTICS : ""];
        $this->url = explode('/', $url);
        parent::checkRoute(!empty($this->url[0]) ? $this->url[0] : 'index', $this->url[1] ?? null);
        $this->checkParamPage();
        new Sessao();
        $this->param["url"] = $url;
        $this->param['loged'] = !empty($_SESSION['userlogin']);
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
    public function getDicionario()
    {
        return $this->dicionario;
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
        $this->checkDicionarioContent();

        $f = json_decode(file_get_contents(PATH_HOME . "_config/param.json"), true);
        $this->param['js'] .= $this->minimizeJS($f['js']);
        $this->param['css'] .= $this->minimizeCSS($f['css']);
        $this->param['font'] .= (!empty($f['icon']) ? $this->prepareIcon($f['icon']) : "") . (!empty($f['font']) ? $this->prepareFont($f['font']) : null);
        $this->param['meta'] .= $this->prepareMeta($f['meta'] ?? null);
        if ($f['title'])
            $this->param['title'] = $this->prepareTitle($f['title']);

        if (parent::getLib()) {
            $path = PATH_HOME . (!DEV || parent::getLib() !== DOMINIO ? "vendor/conn/" . parent::getLib() . "/" : "") . "param/" . parent::getFile() . ".json";
            if (file_exists($path))
                $this->prepareDependencies($path);
        }

        if (DEV) {
            if (file_exists(PATH_HOME . parent::getDir() . "assets/" . parent::getFile() . ".min.js"))
                $this->param['js'] .= "<script src='" . HOME . parent::getDir() . "assets/" . parent::getFile() . ".min.js?v=" . VERSION . "' defer ></script>\n";

            if (file_exists(PATH_HOME . parent::getDir() . "assets/" . parent::getFile() . ".min.css"))
                $this->param['css'] .= "<link rel='stylesheet' href='" . HOME . parent::getDir() . "assets/" . parent::getFile() . ".min.css?v=" . VERSION . "'>\n";
        }
    }

    /**
     * Minimiza lista de Javascript files em um arquivo único
     *
     * @param array $jsList
     * @param string $name
     * @return string
     */
    private function minimizeJS(array $jsList, string $name = "linkControl"): string
    {
        $minifier = new Minify\JS( HOME . "vendor/conn/link-control/assets/app.min.js");
        foreach ($jsList as $js)
            $minifier->add(HOME . $this->checkAssetsExist($js, 'js'));

        $assets = "assets" . (DEV ? "Public" : "");
        $minifier->gzip(PATH_HOME . $assets . "/{$name}.js");
        $minifier->minify(PATH_HOME . $assets . "/{$name}.min.js");
        return "<script src='" . HOME . $assets . "/{$name}.min.js?v=" . VERSION . "' defer ></script>\n";
    }

    /**
     * Minimiza lista de Styles files em um arquivo único
     *
     * @param array $cssList
     * @param string $name
     * @return string
     */
    private function minimizeCSS(array $cssList, string $name = "linkControl"): string
    {
        $minifier = new Minify\CSS( HOME . "vendor/conn/link-control/assets/app.min.css");
        $minifier->setMaxImportSize(30);
        foreach ($cssList as $css)
            $minifier->add(HOME . $this->checkAssetsExist($css, 'css'));

        $assets = "assets" . (DEV ? "Public" : "");
        $minifier->gzip(PATH_HOME . $assets . "/{$name}.css");
        $minifier->minify(PATH_HOME . $assets . "/{$name}.min.css");
        return "<link rel='stylesheet' href='" . HOME . $assets . "/{$name}.min.css?v=" . VERSION . "' >\n";
    }


    /**
     * @param string $file
     */
    private function prepareDependencies(string $file)
    {
        $file = json_decode(file_get_contents($file), true);

        if ($file['title'])
            $this->param['title'] = $this->prepareTitle($file['title']);

        $this->param['js'] .= !empty($file['js']) ? $this->prepareDependency($file['js'], 'js') : null;

        if (!empty($file['css']))
            $this->param['css'] .= $this->prepareDependency($file['css'], 'css');

        $this->param['font'] .= (!empty($file['icon']) ? $this->prepareIcon($file['icon']) : "") . (!empty($file['font']) ? $this->prepareFont($file['font']) : null);
        $this->param['meta'] .= $this->prepareMeta($file['meta'] ?? null);
    }

    /**
     * @param string $entity
     * @param string $name
     * @return mixed
     */
    private function checkEntityExist(string $entity, string $name)
    {
        if (file_exists(PATH_HOME . "entity/cache/{$entity}.json") && !empty($name))
            return [$entity, $name];

        return [null, null];
    }

    private function checkDicionarioContent()
    {
        if (!empty($this->url[1])) {
            list($entity, $name) = $this->checkEntityExist($this->url[0], $this->url[1]);

            if (empty($entity) && preg_match('/s$/i', $this->url[0]))
                list($entity, $name) = $this->checkEntityExist(substr($this->url[0], 0, -1), $this->url[1]);
            elseif (empty($entity))
                list($entity, $name) = $this->checkEntityExist($this->url[0] . "s", $this->url[1]);

            if (empty($entity) && !empty($this->url[2])) {
                list($entity, $name) = $this->checkEntityExist($this->url[1], $this->url[2]);

                if (empty($entity) && preg_match('/s$/i', $this->url[1]))
                    list($entity, $name) = $this->checkEntityExist(substr($this->url[1], 0, -1), $this->url[2]);
                elseif (empty($entity))
                    list($entity, $name) = $this->checkEntityExist($this->url[1] . "s", $this->url[2]);
            }

            if (!empty($entity) && !empty($name)) {
                $read = new Read();
                $d = new Dicionario($entity);
                if (!empty($d->getInfo()["link"]) && $meta = $d->search($d->getInfo()["link"])) {
                    $read->exeRead($entity, "WHERE {$meta->getColumn()} = :c", "c={$name}");
                    if ($read->getResult()) {
                        $d->setData($read->getResult()[0]['id']);
                        $this->dicionario = $d;
                    }
                }
            }
        }
    }

    /**
     * Prepara o formato do título caso tenha variáveis
     *
     * @param string $title
     * @return string
     */
    private function prepareTitle(string $title): string
    {
        if (preg_match('/{\$/i', $title)) {
            $data = [
                "sitename" => SITENAME,
                "sitesub" => SITESUB,
                "relevant" => !empty($this->dicionario) ? $this->dicionario->getRelevant()->getValue() : ""
            ];

            foreach (explode('{$', $title) as $i => $item) {
                if ($i > 0) {
                    $variavel = explode('}', $item)[0];
                    $title = str_replace('{$' . $variavel . '}', (!empty($data[$variavel]) ? $data[$variavel] : ""), $title);
                }
            }
        }
        return $title;
    }

    private function prepareDependency(array $param, string $extensao): string
    {
        $return = "";
        foreach ($param as $dependency)
            $return .= $this->getLinkDependency($dependency, $extensao);

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
            foreach ($param as $dependency)
                $return .= "<meta " . (isset($dependency['name']) ? "name='{$dependency['name']}' " : "") . (isset($dependency['property']) ? "property='{$dependency['property']}' " : "") . "content='{$dependency['content']}'>";
        }

        return $return;
    }

    /**
     * Verifica se uma lib existe no sistema, se não existir, baixa do server
     *
     * @param string $lib
     * @param string $extensao
     * @return string
     */
    private function checkAssetsExist(string $lib, string $extensao): string
    {
        $file = (DEV ? "assetsPublic" : "assets") . "/{$lib}/{$lib}" . ".min.{$extensao}";
        if (!file_exists($file)) {
            $this->createFolderAssetsLibraries($file);
            if (!Helper::isOnline("{$this->devLibrary}/{$lib}/{$lib}" . ".{$extensao}"))
                return "";

            if($extensao === 'js')
                $mini = Minify\JS("{$this->devLibrary}/{$lib}/{$lib}" . ".{$extensao}");
            else
                $mini = Minify\CSS("{$this->devLibrary}/{$lib}/{$lib}" . ".{$extensao}");

            $mini->minify(PATH_HOME . $file);
        }

        return $file;
    }

    private function getLinkDependency($library, $extensao)
    {
        $file = $this->checkAssetsExist($library, $extensao);
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
     * Retorna o Assets para produçao ou Desenvolvimento
     * @return string
     */
    private function getAssets(): string
    {
        return DEV ? "assetsPublic" : "assets";
    }
}