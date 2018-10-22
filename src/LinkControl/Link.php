<?php

/**
 * Responável por gerenciar e fornecer informações sobre o link url!
 *
 * @copyright (c) 2018, Edinei J. Bauer
 */

namespace LinkControl;

use ConnCrud\Read;
use Dashboard\UpdateDashboard;
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
        $pathFile = ($lib === DOMINIO ? "public/" : VENDOR . "{$lib}/");
        $this->param = $this->getBaseParam($lib, $file, $pathFile);

        if (empty($this->param['title']))
            $this->param['title'] = $this->getTitle($file, $var);
        else
            $this->param['title'] = $this->prepareTitle($this->param['title'], $file);

        /* Se não existir os assets Core, cria eles */
        if (!file_exists(PATH_HOME . "assetsPublic/core.min.js") || !file_exists(PATH_HOME . "assetsPublic/core.min.css"))
            new UpdateDashboard(['assets']);

        if (!file_exists(PATH_HOME . "assetsPublic/view/{$file}.min.js") || !file_exists(PATH_HOME . "assetsPublic/view/{$file}.min.css")) {
            if (!empty($this->param['js']) || !empty($this->param['css'])) {
                $list = implode('/', array_unique(array_merge($this->param['js'], $this->param['css'])));
                $data = json_decode(file_get_contents(REPOSITORIO . "app/library/{$list}"), true);
                $data = $data['response'] === 1 && !empty($data['data']) ? $data['data'] : [];
            } else {
                $data = [];
            }

            Helper::createFolderIfNoExist(PATH_HOME . "assetsPublic");
            Helper::createFolderIfNoExist(PATH_HOME . "assetsPublic/view");

            /* Se não existir os assets View, cria eles */
            if (!file_exists(PATH_HOME . "assetsPublic/view/{$file}.min.js"))
                $this->createPageJs($file, $data, $pathFile);

            /* Se não existir os assets View, cria eles */
            if (!file_exists(PATH_HOME . "assetsPublic/view/{$file}.min.css"))
                $this->createPageCss($file, $data, $pathFile);
        }

        /* Adiciona o arquivo css da view na variável */
        $this->param['css'] = file_get_contents(PATH_HOME . "assetsPublic/view/{$file}.min.css");
        $this->param['js'] = HOME . "assetsPublic/view/{$file}.min.js";
        $this->param["vendor"] = VENDOR;
        $this->param["url"] = $file . (!empty($var) ? "/{$var}" : "");
        $this->param['loged'] = !empty($_SESSION['userlogin']);
        $this->param['login'] = ($this->param['loged'] ? $_SESSION['userlogin'] : "");
        $this->param['email'] = defined("EMAIL") && !empty(EMAIL) ? EMAIL : "contato@" . DOMINIO;
        $this->param['menu'] = "";
    }

    /**
     * Cria View Assets JS
     * @param string $name
     * @param array $data
     * @param string $pathFile
     */
    private function createPageJs(string $name, array $data, string $pathFile)
    {
        $minifier = new Minify\JS("");

        foreach ($data as $datum) {
            if (in_array($datum['nome'], $this->param['js'])) {
                foreach ($datum['arquivos'] as $file) {
                    if ($file['type'] === "text/javascript")
                        $minifier->add($file['content']);
                }
            }
        }

        if (file_exists(PATH_HOME . $pathFile . "assets/{$name}.min.js"))
            $minifier->add(file_get_contents(PATH_HOME . $pathFile . "assets/{$name}.min.js"));
        elseif (file_exists(PATH_HOME . $pathFile . "assets/{$name}.js"))
            $minifier->add(file_get_contents(PATH_HOME . $pathFile . "assets/{$name}.js"));

        $minifier->minify(PATH_HOME . "assetsPublic/view/{$name}.min.js");
    }

    /**
     * Cria View Assets CSS
     * @param string $name
     * @param array $data
     * @param string $pathFile
     */
    private function createPageCss(string $name, array $data, string $pathFile)
    {
        $minifier = new Minify\CSS("");

        foreach ($this->param['css'] as $item) {
            $datum = array_values(array_filter(array_map(function ($d) use ($item) {
                return $d['nome'] === $item ? $d : [];
            }, $data)));

            if (!empty($datum[0])) {
                $datum = $datum[0];

                if (!empty($datum['arquivos'])) {
                    foreach ($datum['arquivos'] as $file) {
                        if ($file['type'] === "text/css")
                            $minifier->add($file['content']);
                    }
                }
            }
        }

        if (file_exists(PATH_HOME . $pathFile . "assets/{$name}.min.css"))
            $minifier->add(file_get_contents(PATH_HOME . $pathFile . "assets/{$name}.min.css"));
        elseif (file_exists(PATH_HOME . $pathFile . "assets/{$name}.css"))
            $minifier->add(file_get_contents(PATH_HOME . $pathFile . "assets/{$name}.css"));

        $minifier->minify(PATH_HOME . "assetsPublic/view/{$name}.min.css");
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

    /**
     * @param string $lib
     * @param string $file
     * @param string $pathFile
     * @return array
     */
    private function getBaseParam(string $lib, string $file, string $pathFile)
    {
        $base = [
            "version" => VERSION,
            "meta" => "",
            "css" => [],
            "js" => [],
            "font" => "",
            "descricao" => "",
            "data" => 0,
            "analytics" => defined("ANALYTICS") ? ANALYTICS : ""
        ];

        if (file_exists(PATH_HOME . $pathFile . "param/{$file}.json")) {
            $param = json_decode(file_get_contents(PATH_HOME . $pathFile . "param/{$file}.json"), true);
            if (!empty($param))
                $base = array_merge($base, $param);
        }

        return $base;
    }

    /**
     * Obtém os Assets da View
     * @param string $lib
     * @param string $file
     * @return array
     */
    private function getViewParam(string $lib, string $file): array
    {
        $base = [
            "css" => [],
            "js" => [],
            "font" => ""
        ];

        $pathFile = ($lib === DOMINIO ? "public/" : VENDOR . "{$lib}/");
        if (file_exists(PATH_HOME . $pathFile . "param/{$file}.json"))
            $base = array_merge($base, json_decode(file_get_contents(PATH_HOME . ($lib === DOMINIO ? "public/" : VENDOR . "{$lib}/") . "param/{$file}.json"), true));

        return $base;
    }

    /**
     * @param string $file
     * @param null $var
     * @return string
     */
    private function getTitle(string $file, $var = null): string
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
}