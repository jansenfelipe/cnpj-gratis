<?php

namespace JansenFelipe\CnpjGratis\Providers;

use Exception;
use JansenFelipe\CnpjGratis\Clients\CurlHttpClient;
use JansenFelipe\CnpjGratis\Company;
use JansenFelipe\CnpjGratis\Contracts\HttpClientContract;
use JansenFelipe\CnpjGratis\Contracts\ProviderContract;

class SiteReceitaProvider implements ProviderContract
{
    /**
     * @var HttpClientContract
     */
    private $client;

    /**
     * @var string
     */
    private $cookie;

    /**
     * @var string
     */
    private $captchaBase64;

    /**
     * @var string
     */
    private $captchaSolved;

    /**
     * Constructor SiteReceitaProvider.
     */
    public function __construct()
    {
        $this->client = new CurlHttpClient();
    }

    /**
     * @return void
     */
    public function prepare()
    {
        $this->client->get('http://www.receita.fazenda.gov.br/pessoajuridica/cnpj/cnpjreva/Cnpjreva_Solicitacao2.asp');

        $headers = $this->client->getHeaders();

        $this->cookie = $headers['Set-Cookie'];

        /*
         * Get Captcha
         */
        $this->client->setHeaders([
            "Pragma: no-cache",
            "Origin: http://www.receita.fazenda.gov.br",
            "Host: www.receita.fazenda.gov.br",
            "User-Agent: Mozilla/5.0 (Windows NT 6.1; rv:32.0) Gecko/20100101 Firefox/32.0",
            "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
            "Accept-Language: pt-BR,pt;q=0.8,en-US;q=0.5,en;q=0.3",
            "Accept-Encoding: gzip, deflate",
            "Referer: http://www.receita.fazenda.gov.br/pessoajuridica/cnpj/cnpjreva/cnpjreva_solicitacao2.asp",
            "Cookie: flag=1; $this->cookie",
            "Connection: keep-alive"
        ]);

        $image = $this->client->get('http://www.receita.fazenda.gov.br/pessoajuridica/cnpj/cnpjreva/captcha/gerarCaptcha.asp');

        if(@imagecreatefromstring($image)==false)
            throw new Exception('Não foi possível capturar o captcha');

        $this->captchaBase64 = base64_encode($image);
    }

    /**
     * @return Company
     */
    public function getCompany($cnpj)
    {
        $this->client->setHeaders([
            "Host: www.receita.fazenda.gov.br",
            "User-Agent: Mozilla/5.0 (Windows NT 6.1; rv:32.0) Gecko/20100101 Firefox/32.0",
            "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
            "Accept-Language: pt-BR,pt;q=0.8,en-US;q=0.5,en;q=0.3",
            "Accept-Encoding: gzip, deflate",
            "Referer: http://www.receita.fazenda.gov.br/pessoajuridica/cnpj/cnpjreva/cnpjreva_solicitacao2.asp",
            "Cookie: $this->cookie",
            "Connection: keep-alive"
        ]);

        $response = $this->client->post('http://www.receita.fazenda.gov.br/pessoajuridica/cnpj/cnpjreva/valida.asp', [
            'origem' => 'comprovante',
            'cnpj' => $cnpj,
            'txtTexto_captcha_serpro_gov_br' => $this->captcha,
            'submit1' => 'Consultar',
            'search_type' => 'cnpj'
        ]);

        return $response;
    }

    /**
     * @return string
     */
    public function getCookie()
    {
        return $this->cookie;
    }

    /**
     * @param string $captchaSolved
     */
    public function setCaptchaSolved($captchaSolved)
    {
        $this->captchaSolved = $captchaSolved;
    }

    /**
     * @return string
     */
    public function getCaptchaBase64()
    {
        return $this->captchaBase64;
    }

    /**
     * @param HttpClientContract $client
     */
    public function setClient($client)
    {
        $this->client = $client;
    }
}