<ul class="col {$class}" {$attr} {($id!=="")?'id="' + $id + '"':''}>
    {foreach key=i item=l from=$li}
        <li class="col">{$l}</li>
    {/foreach}
</ul>