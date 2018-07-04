<?php
/**
 * @param string $template
 * @param null $var
 * @return string
 */
function template(string $template, $var = []): string
{
    $default = [
        "class" => "",
        "id" => "",
        "attr" => "",
        "content" => "",
        "contentClass" => "",
        "background" => "",
        "height" => 300,
        "width" => "100%",
        "icon" => "send",
        "iconClass" => "send",
        "src" => "",
        "srcClass" => "",
        "alt" => "",
        "title" => "",
        "titleClass" => "",
        "href" => "",
        "hrefClass" => "",
        "hrefText" => "saiba mais"
    ];
    $tpl = new \Helpers\Template("link-control");
    return $tpl->getShow($template, array_merge($default, $var));
}

//containers
define("TPL_SECTION_LARGE", "section_large");
define("TPL_SECTION_MEDIUM", "section_medium");
define("TPL_SECTION_SMALL", "section_small");
define("TPL_SECTION_FULL", "section_full");

//colunas
define("TPL_COL_1", "col_1");
define("TPL_COL_2", "col_2");
define("TPL_COL_3", "col_3");
define("TPL_COL_4", "col_4");
define("TPL_COL_5", "col_5");
define("TPL_COL_6", "col_6");

//titulos
define("TPL_H1", "h1");
define("TPL_H2", "h2");
define("TPL_H3", "h3");
define("TPL_H4", "h4");
define("TPL_H5", "h5");
define("TPL_H6", "h6");

//post
define("TPL_POST_CARD", "post_card");
define("TPL_POST_CARD_LIVRE", "post_card_livre");
define("TPL_POST_FLAT", "post_flat");

//files
define("TPL_SCRIPT", "script");
define("TPL_STYLE", "style");

//containers personalizados
define("TPL_PARALLAX", "parallax");

//button
define("TPL_BUTTON_ICON", "button_icon");

//input
define("TPL_INPUT_ICON", "input_icon");
define("TPL_UL", "ul");

//img
define("TPL_IMG", "img");
