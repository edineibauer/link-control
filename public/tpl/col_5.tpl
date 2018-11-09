<div class="col {$class}" {$attr} {($id!=="")?'id="' + $id + '"':''}>
    {foreach item=c key=k from=$content}
        {if $k%2 != 0}
            <div class="col s12 m3 padding-small">{$c}</div>
        {else}
            <div class="col s12 m2 padding-small">{$c}</div>
        {/if}
    {/foreach}
</div>