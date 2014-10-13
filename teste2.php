<?php
require 'vendor/autoload.php';


$cnpjGratis = new \JansenFelipe\CnpjGratis\CnpjGratis();

$empresa = $cnpjGratis->consulta($_POST['cnpj'], $_POST['captcha'], $_POST['viewstate'], $_POST['cookie']);
var_dump($empresa);