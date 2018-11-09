<article class="col card color-white {$class}" {$attr} {($id!=="")?'id="' + $id + '"':''}>
    {($href != "") ? "<a href='{$href}'>" : ""}
    <header class="col display-container" {($click != "")? "onclick=\"{$click}\"" : ""}>
        {if $src != ""}
            <img src="{$src}" class="col pointer {$srcClass}" alt="{$alt}" title="{$title}" style="height: 250px;{$srcStyle}">
        {/if}
        {if $title != ""}
            <h1 class="display-bottomleft padding-medium font-xlarge color-text-white text-shadow {$titleClass}">{$title}</h1>
        {/if}
    </header>
    {($href != "") ? "</a>" : ""}

    {if $content != ""}
        <div class="col padding-medium padding-24 font-light overflow-hidden margin-bottom {$contentClass}"
             style="height: 109px;{$contentStyle}">
            {$content}
        </div>
    {/if}

    {if $href != "" || $click != ""}
        <div class="col padding-medium">
            {if $href != ""}
                <a class="btn hover-shadow upper theme-l2 opacity hover-opacity-off {$hrefClass}" style="{$hrefStyle}"
                   href="{$href}">{$hrefText}</a>
            {else}
                <span class="btn hover-shadow upper theme-l2 opacity hover-opacity-off {$hrefClass}"
                      style="{$hrefStyle}" {($click != "")? "onclick=\"{$click}\"" : ""}>
                    {$hrefText}
                </span>
            {/if}
        </div>
    {/if}
</article>