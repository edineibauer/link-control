<?php
ob_start();
?>
<div class="row">
    <div class="panel align-center" style="max-width: 900px; margin: auto; float: initial">

        <div class="row">
            <div class="col s12 m6">
                <br>
                <div class="col s12 m6">
                    <img src="<?=HOME?>/vendor/conn/link-control/assets/dino.png">
                </div>
                <div class="panel font-xlarge font-light padding-32">
                    Não há conexão com a Internet
                </div>
                <p>
                    conecte-se para acessar esta página
                </p>
            </div>
        </div>

        <br><br>
        <div class="align-center">
            <a class="btn-large opacity hover-shadow color-white" style="text-decoration: none; margin: auto; float: initial" href="<?= HOME ?>">
                <i class="material-icons padding-right left">home</i>
                <span class="left">Home</span>
            </a>
        </div>

    </div>
</div>
<?php
$data['data']['title'] = "Conexão Perdida";
$data['data']['content'] = ob_get_contents();
ob_end_clean();