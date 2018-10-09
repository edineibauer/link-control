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
        $this->param = $this->getBaseParam($lib, $file);
        if (empty($this->param['title']))
            $this->param['title'] = $this->getTitle($file, $var);
        else
            $this->param['title'] = $this->prepareTitle($this->param['title'], $file);

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
        if (!file_exists(PATH_HOME . "assetsPublic/core.min.js") || !file_exists(PATH_HOME . "assetsPublic/core.min.css"))
            new UpdateDashboard(['assets']);
    }

    /**
     * @param string $lib
     * @param string $file
     * @return array
     */
    private function getBaseParam(string $lib, string $file)
    {
        $base = [
            "version" => VERSION,
            "meta" => "",
            "css" => "",
            "js" => [],
            "font" => "",
            "descricao" => "",
            "data" => 0,
            "analytics" => defined("ANALYTICS") ? ANALYTICS : ""
        ];

        $pathFile = ($lib === DOMINIO ? "public/" : VENDOR . "{$lib}/");
        if (file_exists(PATH_HOME . $pathFile . "param/{$file}.json"))
            $base = array_merge($base, json_decode(file_get_contents(PATH_HOME . ($lib === DOMINIO ? "public/" : VENDOR . "{$lib}/") . "param/{$file}.json"), true));

        if (file_exists(PATH_HOME . $pathFile . "assets/{$file}.min.js")) {
            $base['js'][] = HOME . $pathFile . "assets/{$file}.min.js";
        } elseif (file_exists(PATH_HOME . $pathFile . "assets/{$file}.js")) {
            $minifier = new Minify\JS(file_get_contents(PATH_HOME . $pathFile . "assets/{$file}.js"));
            $minifier->minify(PATH_HOME . $pathFile . "assets/{$file}.min.js");
            $base['js'][] = HOME . $pathFile . "assets/{$file}.min.js";
        }

        if (file_exists(PATH_HOME . $pathFile . "assets/{$file}.min.css")) {
            $base['css'] .= file_get_contents(HOME . $pathFile . "assets/{$file}.min.css");
        } elseif (file_exists(PATH_HOME . $pathFile . "assets/{$file}.css")) {
            $minifier = new Minify\CSS(file_get_contents(PATH_HOME . $pathFile . "assets/{$file}.css"));
            $minifier->minify(PATH_HOME . $pathFile . "assets/{$file}.min.css");
            $base['css'] .= file_get_contents(HOME . $pathFile . "assets/{$file}.min.css");
        }

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