<?php
require 'vendor/autoload.php';

if (isset($_POST['cnpj'])) {
    var_dump(\JansenFelipe\CnpjGratis\CnpjGratis::consulta($_POST['cnpj'], $_POST['captcha'], $_POST['cookie']));
    die;
}

$cnpj = '45543915000858';

$params = \JansenFelipe\CnpjGratis\CnpjGratis::getParams();

?>


<img src="<?php echo $params['captchaBase64']; ?>" /><br />

<form action="" method="POST">
    <input type="text" name="cnpj" value="<?php echo $cnpj; ?>" />
    <input type="text" name="captcha" />
    <input type="text" name="cookie" value="<?php echo $params['cookie']; ?>" />
    <input type="submit" />
</form>
