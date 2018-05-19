<label class="row {$class}" {$attr}>
    {($content!="")?"<span class='col padding-4'>{$content}</span>":""}
    <div class="input-icon">
        <i class="material-icons icon-form font-xxlarge padding-top color-text-teal" style="width: 40px">{$icon}</i>
        <input type="text" class="rest font-xlarge" {($id!=="")?'id="' + $id + '"':''}>
    </div>
</label>