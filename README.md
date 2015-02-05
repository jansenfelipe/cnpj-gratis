# CNPJ Grátis
[![Travis](https://travis-ci.org/jansenfelipe/cnpj-gratis.svg?branch=2.0)](https://travis-ci.org/jansenfelipe/cnpj-gratis)
[![Latest Stable Version](http://img.shields.io/packagist/v/jansenfelipe/cnpj-gratis.svg?style=flat)](https://packagist.org/packages/jansenfelipe/cnpj-gratis)
[![Total Downloads](http://img.shields.io/packagist/dt/jansenfelipe/cnpj-gratis.svg?style=flat)](https://packagist.org/packages/jansenfelipe/cnpj-gratis)
[![License](http://img.shields.io/packagist/l/jansenfelipe/cnpj-gratis.svg?style=flat)](https://packagist.org/packages/jansenfelipe/cnpj-gratis)

Com esse pacote você poderá realizar consultas de CNPJ no site da Receita Federal do Brasil gratuitamente.

Atenção: Esse pacote não possui leitor de captcha, mas captura o mesmo para ser digitado pelo usuário

### Como usar

Adicione no seu arquivo `composer.json` o seguinte registro na chave `require`

    "jansenfelipe/cnpj-gratis": "2.0.*@dev"

Execute

    $ composer update

Adicione o autoload.php do composer no seu arquivo PHP.

    require_once 'vendor/autoload.php';  

Primeiro chame o método `getParams()` para retornar os dados necessários para enviar no método `consulta()` 

    $params = CnpjGratis::getParams(); //Output: array('audio', 'captcha', 'captchaBase64', 'viewstate', 'cookie')

Agora chame o método `consulta()`

    $dadosEmpresa = CnpjGratis::consulta(
        '45.543.915/0001-81',
        'INFORME_AS_LETRAS_DO_CAPTCHA',
        $params['viewstate'],
        $params['cookie']
    );


### Frameworks

##### (Laravel 4.2)

Abra seu arquivo `config/app.php` e adicione `'JansenFelipe\CnpjGratis\CnpjGratisServiceProvider'` ao final do array `$providers`

    'providers' => array(

        'Illuminate\Foundation\Providers\ArtisanServiceProvider',
        'Illuminate\Auth\AuthServiceProvider',
        ...
        'JansenFelipe\CnpjGratis\CnpjGratisServiceProvider',
    ),

Adicione também `'CnpjGratis' => 'JansenFelipe\CnpjGratis\Facade'` no final do array `$aliases`

    'aliases' => array(

        'App'        => 'Illuminate\Support\Facades\App',
        'Artisan'    => 'Illuminate\Support\Facades\Artisan',
        ...
        'CnpjGratis'    => 'JansenFelipe\CnpjGratis\Facade',

    ),
