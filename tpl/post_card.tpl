<article class="col card color-white {$class}" {$attr} {($id!=="")?'id="' + $id + '"':''}>
    {if $href != ""}
    <a href="{$href}">
        {/if}
        <header class="col display-container">
            <img src="{$src}" class="col {$srcClass}" alt="{$alt}" title="{$title}" style="height: 250px;{$srcStyle}">
            <h1 class="display-bottomleft padding-medium font-xlarge color-text-white text-shadow {$titleClass}">{$title}</h1>
        </header>
        {if $href != ""}
    </a>
    {/if}
    <div class="col padding-medium padding-24 font-light overflow-hidden margin-bottom {$contentClass}"
         style="height: 109px;{$contentStyle}">
        {$content}
    </div>
    {if $href != ""}
        <div class="col padding-medium">
            <a class="btn hover-shadow upper theme-l2 opacity hover-opacity-off {$hrefClass}" style="{$hrefStyle}"
               href="{$href}">{$hrefText}</a>
        </div>
    {/if}
</article>