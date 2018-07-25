<?php

/**
 * Responável por gerenciar e fornecer informações sobre o link url!
 *
 * @copyright (c) 2018, Edinei J. Bauer
 */

namespace LinkControl;

use ConnCrud\Read;
use EntityForm\Dicionario;
use Helpers\Helper;
use MatthiasMullie\Minify;

class Link
{
    private $url;
    private $param;
    private $dicionario;

    /**
     * Link constructor.
     * @param string $lib
     * @param string $file
     * @param $var
     */
    function __construct(string $lib, string $file, $var = null)
    {
        $this->devLibrary = "http://dev.ontab.com.br";

        $this->param = $this->getBaseParam($lib, $file);
        if (empty($this->param['title']))
            $this->param['title'] = $this->getTitle($lib, $file, $var);
        else
            $this->param['title'] = $this->prepareTitle($this->param['title'], $file);

        $this->getParamCore();
        $this->createMinFilesVendor();
        $this->param["vendor"] = VENDOR;
        $this->param["url"] = $file . (!empty($var) ? "/{$var}" : "");
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

    private function createMinFilesVendor()
    {
        foreach (Helper::listFolder(PATH_HOME . VENDOR) as $lib) {
            foreach (Helper::listFolder(PATH_HOME . VENDOR . $lib . "/assets") as $file) {
                $ext = pathinfo($file, PATHINFO_EXTENSION);
                $name = pathinfo($file, PATHINFO_BASENAME);
                if (preg_match('/(^\.min)\.[js|css]$/i', $file) && !file_exist(PATH_HOME . VENDOR . $lib . "/assets/{$name}.min.{$ext}")) {
                    if (preg_match('/\.js$/i', $file))
                        $minifier = new Minify\JS(file_get_content(PATH_HOME . VENDOR . $lib . "/assets/{$file}"));
                    else
                        $minifier = new Minify\CSS(file_get_content(PATH_HOME . VENDOR . $lib . "/assets/{$file}"));

                    $minifier->minify(PATH_HOME . VENDOR . $lib . "/assets/{$name}.min.{$ext}");
                }
            }
        }
    }

    /**
     * @param string $lib
     * @param string $file
     */
    private function getBaseParam(string $lib, string $file)
    {
        $base = [
            "version" => VERSION,
            "meta" => "",
            "css" => "",
            "js" => "",
            "font" => "",
            "analytics" => defined("ANALYTICS") ? ANALYTICS : ""
        ];

        if (file_exists(PATH_HOME . ($lib === DOMINIO ? "" : VENDOR . "{$lib}/") . "param/{$file}.json"))
            $base = array_merge($base, json_decode(file_get_contents(PATH_HOME . ($lib === DOMINIO ? "" : VENDOR . "{$lib}/") . "param/{$file}.json"), true));

        return $base;
    }

    /**
     * @param string $lib
     * @param string $file
     * @param null $var
     * @return string
     */
    private function getTitle(string $lib, string $file, $var = null): string
    {
        $entity = str_replace("-", "_", $file);
        if (file_exists(PATH_HOME . "entity/cache/{$entity}.json") && $var) {
            $this->dicionario = new Dicionario($entity);
            $where = "WHERE id = {$var}";
            if ($linkId = $this->dicionario->getInfo()['link']) {
                $where .= " || " . $this->dicionario->search($linkId)->getColumn() . " = '{$var}'";

                $read = new Read();
                $read->exeRead($entity, $where);
                if ($read->getResult()) {
                    return $read->getResult()[0][$this->dicionario->search($this->dicionario->getInfo()['title'])->getColumn()] . " | " . SITENAME;
                }
            }
        }

        return ($file === "index" ? SITENAME . (defined('SITESUB') && !empty(SITESUB) ? " | " . SITESUB : "") : ucwords(str_replace(['-', "_"], " ", $file)) . " | " . SITENAME);
    }

    /**
     * Obtém os parâmetros do Core da aplicação Singular
     */
    private function getParamCore()
    {
        $f = ['js' => $this->param['js'], 'css' => $this->param['css'], 'meta' => $this->param['meta'], 'font' => $this->param['font']];
        unset($this->param['js'], $this->param['css'], $this->param['meta'], $this->param['font']);
        if(file_exists(PATH_HOME . "_config/param.json"))
            $f = array_merge($f, json_decode(file_get_contents(PATH_HOME . "_config/param.json"), true));

        $this->param['js'] = $this->minimizeJS($f['js']);
        $this->param['css'] = $this->minimizeCSS($f['css']);
        $this->param['font'] = (!empty($f['icon']) && !empty($f['font']) ? $this->prepareFont($f['font'], $f['icon']) : (!empty($f['font']) ? $this->prepareFont($f['font']) : (!empty($f['icon']) ? $this->prepareFont(null, $f['icon']) : "")));
        $this->param['meta'] = $this->prepareMeta($f['meta'] ?? null);
    }

    /**
     * Minimiza lista de Javascript files em um arquivo único
     *
     * @param array $jsList
     * @param string $name
     * @return string
     */
    private function minimizeJS(array $jsList, string $name = "core"): string
    {
        $assets = "assets" . (DEV ? "Public" : "");
        if (!file_exists(PATH_HOME . $assets . "/{$name}.min.js")) {
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
    private function minimizeCSS(array $cssList, string $name = "core"): string
    {
        $assets = "assets" . (DEV ? "Public" : "");
        if (!file_exists(PATH_HOME . $assets . "/{$name}.min.css")) {
            $minifier = new Minify\CSS("");
            $minifier->setMaxImportSize(30);
            foreach ($cssList as $css)
                $minifier->add(PATH_HOME . $this->checkAssetsExist($css, 'css'));

            $minifier->minify(PATH_HOME . $assets . "/{$name}.min.css");
        }
        return "<link rel='stylesheet' href='" . HOME . $assets . "/{$name}.min.css?v=" . VERSION . "' >\n";
    }

    /**
     * Prepara o formato do título caso tenha variáveis
     *
     * @param string $title
     * @return string
     */
    private function prepareTitle(string $title, string $file): string
    {
        if (preg_match('/{{/i', $title)) {
            $data = [
                "sitename" => SITENAME,
                "SITENAME" => SITENAME,
                "sitesub" => SITESUB,
                "SITESUB" => SITESUB,
                "title" => !empty($this->dicionario) ? $this->dicionario->getRelevant()->getValue() : ucwords(str_replace(['-', "_"], " ", $file)),
                "file" => ucwords(str_replace(['-', "_"], " ", $file))
            ];

            foreach (explode('{{', $title) as $i => $item) {
                if ($i > 0) {
                    $variavel = explode('}}', $item)[0];
                    $title = str_replace('{{' . $variavel . '}}', (!empty($data[$variavel]) ? $data[$variavel] : ""), $title);
                }
            }
        }
        return $title;
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

        if (!file_exists(PATH_HOME . $path)) {
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

        return "<link rel='stylesheet' href='" . HOME . $path . "?v=" . VERSION . "' type='text/css' media='all'/>";
    }

    /**
     * @param null $param
     * @return string
     */
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

            if ($extensao === 'js')
                $mini = new Minify\JS(file_get_contents("{$this->devLibrary}/{$lib}/{$lib}" . ".{$extensao}"));
            else
                $mini = new Minify\CSS(file_get_contents("{$this->devLibrary}/{$lib}/{$lib}" . ".{$extensao}"));

            $mini->minify(PATH_HOME . $file);
        }

        return $file;
    }

    /**
     * @param string $file
     */
    private function createFolderAssetsLibraries(string $file)
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