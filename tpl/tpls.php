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
define("TPL_1200", "div_1200");
define("TPL_900", "div_900");
define("TPL_SECTION", "section");

//colunas
define("TPL_COL_2", "col_2");
define("TPL_COL_3", "col_3");
define("TPL_COL_4", "col_4");
define("TPL_COL_5", "col_5");
define("TPL_COL_6", "col_6");

//post
define("TPL_POST_CARD", "post_card");
define("TPL_POST_FLAT", "post_flat");

//containers personalizados
define("TPL_PARALLAX", "parallax");

//button
define("TPL_BUTTON_ICON", "button_icon");

//input
define("TPL_INPUT_ICON", "input_icon");
define("TPL_UL", "ul");

//img
define("TPL_IMG_100", "img_100");
define("TPL_IMG_200", "img_200");

//text
define("TPL_TEXT", "text");
define("TPL_TEXT_CENTER", "text_center");