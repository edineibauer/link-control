<article class="col padding-medium margin-bottom {$class}" {$attr} {($id!=="")?'id="' + $id + '"':''}>
    <header class="col padding-bottom align-center">
        {($icon!="") ? "<div class='col {$iconClass}'><i class='material-icons font-xxlarge'>{$icon}</i></div>" : (($src !="") ? "<img src='{$src}' class='margin-bottom {$srcClass}' alt='{$alt}' title='{$title}' style='height: 60px'>" : "")}
        <h2 class="margin-0 padding-tiny font-xlarge upper font-light color-text-black {$titleClass}">{$title}</h2>
    </header>
    <div class="col color-text-grey-dark {$contentClass}" style="font-size:13px;letter-spacing: 0.6px">
        {$content}
    </div>
    {if $href != ""}
        <div class="col padding-24">
            <a class="btn hover-shadow upper theme-l2 opacity hover-opacity-off {$hrefClass}" href="{$href}">{$hrefText}</a>
        </div>
    {/if}
</article>