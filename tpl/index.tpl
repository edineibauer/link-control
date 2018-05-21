<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="robots" content="index, follow" />
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

    {$meta}
    {$css}
    {$font}
    <script>const HOME = '{$home}';const ISDEV = false;const VERSION = {$version};</script>
</head>
<body>
    <header class="col theme padding-medium card header margin-0">
        <div class="col container-1200">
            <a href="{$home}" class="left padding-tiny header-logo">
                {if $logo != ""}
                    <img src="{$logo}" alt="logo do site {$sitename}" title="{$sitename} {($sitesub != "") ? " - $sitesub" : ""}" class="col" height="39" style="height: 39px;width: auto">
                    <h1 class="padding-0" style="font-size:0">{$sitename}</h1>
                {else}
                    <h1>{$sitename}</h1>
                {/if}
            </a>
            <nav class="right padding-tiny" role="navigation">
                <header>
                    <h2 class="padding-0" style="font-size:0">Menu</h2>
                </header>
                <ul class="right upper header-nav">
                    <li class="left padding-0 padding-right">
                        <a href="{$home}sobre" class="right padding-medium">sobre nós</a>
                    </li>
                    <li class="left padding-0 padding-right">
                        <a href="{$home}nossos-servicos" class="right padding-medium">nossos serviços</a>
                    </li>
                    <li class="left padding-0 padding-right">
                        <a href="{$home}atendimento" class="right padding-medium">atendimento</a>
                    </li>
                    <li class="left padding-0">
                        <a href="{$home}login" class="right padding-medium">login</a>
                    </li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="col" id="content">
        <div class="loader">
            <svg viewBox="0 0 32 32" width="32" height="32">
                <circle id="spinner" style="stroke: {$theme}" cx="16" cy="16" r="14" fill="none"></circle>
            </svg>
        </div>
    </div>

    {$js}
    {if $analytics != ""}
        <script async src="https://www.googletagmanager.com/gtag/js?id={$analytics}"></script>
        <script>
            {literal}
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            {/literal}
            gtag('config', '{$analytics}');
        </script>
    {/if}
</body>
</html>