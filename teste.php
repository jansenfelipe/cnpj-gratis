<?php
require 'vendor/autoload.php';

$cnpj = '11355632000194';

$cnpjGratis = new \JansenFelipe\CnpjGratis\CnpjGratis();
$params = $cnpjGratis->getParams($cnpj);
?>


<img src="<?php echo $params['captcha']; ?>" /><br />

<form action="teste2.php" method="POST">
    <input type="text" name="cnpj" value="<?php echo $cnpj; ?>" />
    <input type="text" name="captcha" />
    <input type="text" name="cookie" value="<?php echo $params['cookie']; ?>" />
    <textarea name="viewstate"><?php echo $params['viewstate']; ?></textarea>
    <input type="submit" />
</form>

