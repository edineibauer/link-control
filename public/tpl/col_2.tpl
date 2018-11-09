<div class="col {$class}" {$attr} {($id!=="")?'id="' + $id + '"':''}>
    {foreach item=c from=$content}
        <div class="col s12 m6 padding-medium">{$c}</div>
    {/foreach}
</div>