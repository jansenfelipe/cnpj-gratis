# CNPJ Grátis

----------------------
Com esse pacote você poderá realizar consultas de CNPJ no site da Receita Federal do Brasil gratuitamente.

Atenção: Esse pacote não possui leitor de captcha, mas captura o mesmo para ser digitado pelo usuário

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

Agora chame o método `getParams()` para retornas os dados necessários para enviar no método `consulta()` 

    $params = CnpjGratis::getParams(); //Output: array('captcha', 'viewstate', 'cookie')

Obs: Na resposta, a chave `captcha` contém a URL da imagem.

    $dadosEmpresa = CnpjGratis::consulta(
        '45.543.915/0001-81',
        'INFORME_AS_LETRAS_DO_CAPTCHA',
        $params['viewstate'],
        $params['cookie']
    );


### (No-Laravel)

Adicione o autoload.php do composer no seu arquivo PHP.

    require_once 'vendor/autoload.php';  

Agora chame o método `getParams()` para retornas os dados necessários para enviar no método `consulta()` 

    $params = CnpjGratis::getParams(); //Output: array('captcha', 'viewstate', 'cookie')

Obs: Na resposta, a chave `captcha` contém a URL da imagem.

    $dadosEmpresa = CnpjGratis::consulta(
        '45.543.915/0001-81',
        'INFORME_AS_LETRAS_DO_CAPTCHA',
        $params['viewstate'],
        $params['cookie']
    );