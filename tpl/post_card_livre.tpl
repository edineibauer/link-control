<article class="col card color-white {$class}" {$attr} {($id!=="")?'id="' + $id + '"':''}>
    {if $href != ""}
    <a href="{$href}">
        {/if}
        <header class="col display-container">
            <img src="{$src}" class="col {$srcClass}" alt="{$alt}" title="{$title}" style="height: auto">
            <h1 class="display-bottomleft padding-medium font-xlarge color-text-white text-shadow {$titleClass}">{$title}</h1>
        </header>
        {if $href != ""}
    </a>
    {/if}
    <div class="col padding-medium padding-24 font-light overflow-hidden {$contentClass}">
        {$content}
    </div>
    {if $href != ""}
        <div class="col padding-medium">
            <a class="btn hover-shadow upper theme-l2 opacity hover-opacity-off {$hrefClass}"
               href="{$href}">{$hrefText}</a>
        </div>
    {/if}
</article>