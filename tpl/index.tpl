<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="robots" content="index, follow"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$title}</title>
    <link rel="canonical" href="{$home}">
    <link rel="shortcut icon" href="{$favicon}">
    <link rel="manifest" href="{$home}manifest.json">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="apple-mobile-web-app-title" content="{$sitename}">
    <link rel="apple-touch-icon" href="{$favicon}">
    <meta name="msapplication-TileImage" content="{$favicon}">
    <meta name="msapplication-TileColor" content="#2F3BA2">
    <link rel='stylesheet' href='{$home}assetsPublic/core.min.css?v={$version}' >
    <link rel='stylesheet' href='{$home}assetsPublic/fonts.min.css?v={$version}' type='text/css' media='all'/>
    {$font}

    <script>
        const HOME = '{$home}';
        const DOMINIO = '{$dominio}';
        const VERSION = {$version};
        const VENDOR = '{$vendor}';
        const ROUTE = '{$url}';
    </script>
    <script src='{$home}assetsPublic/core.min.js?v={$version}' defer ></script>
</head>
<body>
<div class="col padding-small theme z-depth-2 no-selec header relative" style="z-index: 19">
    <div class="col {if !$loged}container-1200{/if}">
        <header class="left padding-tiny header-logo">
            <a href="{$home}" class="left">

                {if $logo != "" && $logo != $home}
                    <img src="{$logo}" alt="logo do site {$sitename}"
                         title="{$sitename} {($sitesub != "") ? " - $sitesub" : ""}" class="col" height="39"
                         style="height: 39px;width: auto">
                    <h1 class="padding-0" style="font-size:0">{$sitename}</h1>
                {elseif $favicon && $favicon != $home}
                    <img src="{$favicon}" class="left padding-right" height="35" style="height: 35px">
                    <h1 class="font-xlarge padding-0 left">{$sitename}</h1>
                {else}
                    <h1 class="font-xlarge padding-0">{$sitename}</h1>
                {/if}
            </a>
        </header>
        <nav class="right padding-tiny" role="navigation">
            <ul class="right upper header-nav hide-medium hide-small">
                {* <li class="left padding-0 padding-right col relative" style="width: 400px">
                     <input type="text" placeholder="buscar.."
                            class="col left font-large color-white margin-0 search padding-left radius"
                            style="width: 400px">
                     <button class="right btn-floating theme-d2 opacity hover-opacity-off"
                             style="position:absolute;right: 0;margin: 0;height: 39px;border-radius: 3px;"><i
                                 class="material-icons">search</i></button>
                 </li>*}
                {if $loged}
                    <li class="left padding-0">
                        <a href="{$home}dashboard" class="right padding-medium">minha conta</a>
                    </li>
                    <li class="left padding-0 pointer">
                        <span onclick="logoutDashboard()"
                              class="right padding-medium opacity hover-opacity-off">SAIR
                        </span>
                    </li>
                {else}
                    <li class="left padding-0">
                        <a href="{$home}login" class="right padding-medium">login</a>
                    </li>
                {/if}
            </ul>
            <span class="open-menu hide-large right hover-shadow pointer font-large btn-flat"
                  style="padding: 6px 15px 1px">
                <i class="material-icons">menu</i>
            </span>

            {*<span class="app-search btn-flat hide-large theme right hover-shadow pointer font-large"
                  style="padding: 6px 15px 1px">
                <i class="material-icons">search</i>
            </span>*}
        </nav>
    </div>
</div>

<div class="col animate-left" id="app-sidebar">
    <div class="col padding-medium theme color-grayscale-min" id="main-header-app-sidebar">
        <div class="col padding-medium perfil-sidebar">
            {if $loged}
                {if $login.imagem}
                    <img src="{$home}image/{$login.imagem}&h=100&w=100" height="80" width="80"
                         class="radius-circle margin-bottom z-depth-2">
                {else}
                    <div class="col s4"><i class="material-icons font-jumbo">people</i></div>
                {/if}
                <div class="col font-medium font-light">
                    <span class="left">
                        {$login.email}
                    </span>
                    <button id="btn-editLogin" style="margin-top: -13px"
                            class="right color-white opacity z-depth-0 border hover-opacity-off radius padding-small color-grey-light">
                        <i class="material-icons left font-large">edit</i>
                        <span class="left" style="padding-right: 5px">perfil</span>
                    </button>
                </div>
            {else}
                <i class="material-icons font-jumbo margin-bottom">people</i>
                <div class="col font-large font-bold">
                    Anônimo
                </div>
                <div class="col font-medium font-light">{$email}</div>
            {/if}
        </div>
    </div>

    <div class="col" id="main-app-sidebar">
        <ul class="col" id="applications"></ul>
        {* <ul class="col border-bottom padding-bottom" id="actions">
             <li class="col pointer color-hover-grey-light">
                 <a href="{$home}dashboard" class="col padding-small padding-16">
                     <i class="material-icons left padding-right font-xlarge">notifications</i>
                     <span class="left padding-tiny">Notificações</span>
                 </a>
             </li>
         </ul>*}

        <ul class="col border-top" id="menu">
            {$menu}
            {if $loged}
                <li class="col pointer color-hover-grey-light">
                    <a href="{$home}dashboard" class="col padding-large opacity hover-opacity-off">
                        Minha Conta
                    </a>
                </li>
                <li class="col pointer color-hover-grey-light">
                    <span onclick="logoutDashboard()" class="col padding-large opacity hover-opacity-off">
                        sair
                    </span>
                </li>
            {else}
                <li class="col pointer color-hover-grey-light">
                    <a href="{$home}login" class="col padding-large">login</a>
                </li>
            {/if}
        </ul>
    </div>
</div>

<div class="overlay hide-large animate-opacity" id="myOverlay"></div>

<div class="loader">
    <svg viewBox="0 0 32 32" width="32" height="32">
        <circle id="spinner" style="stroke: {$theme}" cx="16" cy="16" r="14" fill="none"></circle>
    </svg>
</div>

<section class="col space-header" id="content"></section>

{if $analytics != ""}
    <script async src="https://www.googletagmanager.com/gtag/js?id={$analytics}"></script>
    <script>
        {literal}
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }

        gtag('js', new Date());
        {/literal}
        gtag('config', '{$analytics}');
    </script>
{/if}
</body>
</html>