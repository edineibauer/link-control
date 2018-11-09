<div class="col {$class}" {$attr} {($id!=="")?'id="' + $id + '"':''}>
    {foreach item=c from=$content}
        <div class="col s12 m3 padding-small">{$c}</div>
    {/foreach}
</div>