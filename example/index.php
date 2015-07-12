<?php

require_once '../vendor/autoload.php';

use JansenFelipe\CnpjGratis\CnpjGratis;


if(isset($_POST['captcha']) && isset($_POST['cookie']) && isset($_POST['cnpj'])){
    $dados = CnpjGratis::consulta($_POST['cnpj'], $_POST['captcha'], $_POST['cookie']);
    var_dump($dados);
    die;
}else
    $params = CnpjGratis::getParams();
?>

<img src="<?php echo $params['captchaBase64'] ?>" />

<form method="POST">
    <input type="hidden" name="cookie" value="<?php echo $params['cookie'] ?>" />

    <input type="text" name="captcha" placeholder="Captcha" />
    <input type="text" name="cnpj" placeholder="CNPJ" />

    <button type="submit">Consultar</button>
</form>