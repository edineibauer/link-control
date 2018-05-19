<div class="col {$class}" {$attr} {($id!=="")?'id="' + $id + '"':''}>
    {foreach item=c from=$content}
        <div class="col s12 m4 padding-small">{$c}</div>
    {/foreach}
</div>