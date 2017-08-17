<?php

/**
 * Link.class [ MODEL ]
 * Responável por gerenciar e fornecer informações sobre o link url!
 *
 * @copyright (c) 2017, Edinei J. Bauer
 */

namespace LinkControl;

class Link extends Route {
    private $url;

    function __construct() {
        $this->url = explode('/', strip_tags(trim(filter_input(INPUT_GET, 'url', FILTER_DEFAULT))));
        parent::checkRoute((!empty($this->url[0]) ? $this->url[0] : 'index'), $this->url[1] ?? null);
    }

    /**
     * @return array
     */
    public function getUrl() {
        return $this->url;
    }

}
