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
        $this->param['login'] = ($this->param['loged'] ? $_SESSION['userlogin'] : "");
        $this->param['email'] = defined("EMAIL") && !empty(EMAIL) ? EMAIL : "contato@" . DOMINIO;
        $this->param['menu'] = "";
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
        $this->param['font'] .= (!empty($f['icon']) && !empty($f['font']) ? $this->prepareFont($f['font'], $f['icon']) : (!empty($f['font']) ? $this->prepareFont($f['font']) : (!empty($f['icon']) ? $this->prepareFont(null, $f['icon']) : "")));
        $this->param['meta'] .= $this->prepareMeta($f['meta'] ?? null);
        if ($f['title'])
            $this->param['title'] = $this->prepareTitle($f['title']);

        if (parent::getLib()) {
            $path = PATH_HOME . (!DEV || parent::getLib() !== DOMINIO ? "vendor/conn/" . parent::getLib() . "/" : "") . "param/" . parent::getFile() . ".json";
            if (file_exists($path))
                $this->prepareDependencies($path);
        }

        if (DEV) {
            if (file_exists(PATH_HOME . parent::getDir() . "assets/" . parent::getFile() . ".js"))
                $this->param['js'] .= "<script src='" . HOME . parent::getDir() . "assets/" . parent::getFile() . ".js?v=" . VERSION . "' defer ></script>\n";

            if (file_exists(PATH_HOME . parent::getDir() . "assets/" . parent::getFile() . ".css"))
                $this->param['css'] .= "<link rel='stylesheet' href='" . HOME . parent::getDir() . "assets/" . parent::getFile() . ".css?v=" . VERSION . "'>\n";
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
        $assets = "assets" . (DEV ? "Public" : "");
        if(!file_exists(PATH_HOME . $assets . "/{$name}.min.js")) {
            $minifier = new Minify\JS("");
            foreach ($jsList as $js)
                $minifier->add(PATH_HOME . $this->checkAssetsExist($js, 'js'));

            $minifier->minify(PATH_HOME . $assets . "/{$name}.min.js");
        }
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
        $assets = "assets" . (DEV ? "Public" : "");
        if(!file_exists(PATH_HOME . $assets . "/{$name}.min.css")) {
            $minifier = new Minify\CSS("");
            $minifier->setMaxImportSize(30);
            foreach ($cssList as $css)
                $minifier->add(PATH_HOME . $this->checkAssetsExist($css, 'css'));

            $minifier->minify(PATH_HOME . $assets . "/{$name}.min.css");
        }
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

    /**
     * @param string $item
     * @param string $tipo
     * @return string
     */
    private function getFontIcon(string $item, string $tipo): string
    {
        $data = "";
        $assets = (DEV ? "assetsPublic/" : "assets/");
        $urlOnline = $tipo === "font" ? "https://fonts.googleapis.com/css?family=" . ucfirst($item) . ":100,300,400,700" : "https://fonts.googleapis.com/icon?family=" . ucfirst($item) . "+Icons";
        if (Helper::isOnline($urlOnline)) {
            $data = file_get_contents($urlOnline);
            foreach (explode('url(', $data) as $i => $u) {
                if ($i > 0) {
                    $url = explode(')', $u)[0];
                    if (!file_exists(PATH_HOME . $assets . "fonts/" . pathinfo($url, PATHINFO_BASENAME))) {
                        if (Helper::isOnline($url)) {
                            Helper::createFolderIfNoExist(PATH_HOME . $assets . "fonts");
                            $f = fopen(PATH_HOME . $assets . "fonts/" . pathinfo($url, PATHINFO_BASENAME), "w+");
                            fwrite($f, file_get_contents($url));
                            fclose($f);
                            $data = str_replace($url, HOME . $assets . "fonts/" . pathinfo($url, PATHINFO_BASENAME), $data);
                        } else {
                            $before = "@font-face" . explode("@font-face", $u[$i - 1])[1] . "url(";
                            $after = explode("}", $u)[0];
                            $data = str_replace($before . $after, "", $data);
                        }
                    } else {
                        $data = str_replace($url, HOME . $assets . "fonts/" . pathinfo($url, PATHINFO_BASENAME), $data);
                    }
                }
            }
        }
        return $data;
    }

    /**
     * @param mixed $fontList
     * @param mixed $iconList
     * @return string
     */
    private function prepareFont($fontList, $iconList = null): string
    {
        $assets = (DEV ? "assetsPublic/" : "assets/");
        $path = $assets . "fonts.min.css";
        $fonts = "";

        if(!file_exists(PATH_HOME . $path)) {
            if ($fontList) {
                foreach ($fontList as $item)
                    $fonts .= $this->getFontIcon($item, "font");
            }

            if ($iconList) {
                foreach ($iconList as $item)
                    $fonts .= $this->getFontIcon($item, "icon");
            }

            $m = new Minify\CSS($fonts);
            $m->minify(PATH_HOME . $path);
        }

        return "<link rel='stylesheet' href='" . HOME . $path . "?v=" . VERSION. "' type='text/css' media='all'/>";
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
                $mini = new Minify\JS(file_get_contents("{$this->devLibrary}/{$lib}/{$lib}" . ".{$extensao}"));
            else
                $mini = new Minify\CSS(file_get_contents("{$this->devLibrary}/{$lib}/{$lib}" . ".{$extensao}"));

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
}