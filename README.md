# CNPJ Grátis

----------------------
Com esse pacote você poderá realizar consultas de CNPJ no site da receita gratuitamente.

Atenção: Esse pacote não possui leitor de captcha, mas captura o mesmo para ser digitado.

### Para utilizar

Adicione no seu arquivo `composer.json` o seguinte registro na chave `require`

    "jansenfelipe/cnpj-gratis": "dev-master"

Execute

    $ composer update

## (Laravel)

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

Agora basta chamar

    $dados = CnpjGratis::consultar('31030080');


### (No-Laravel)

Adicione o autoload.php do composer no seu arquivo PHP.

    require_once 'vendor/autoload.php';  

Agora basta chamar o metodo consultar($cnpj) da classe JansenFelipe\CnpjGratis

    $cnpjGratis = new JansenFelipe\CnpjGratis();
    $dados = $cnpjGratis->consultar('31030080');