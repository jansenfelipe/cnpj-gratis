<?php

namespace JansenFelipe\CnpjGratis;

use Exception;
use Goutte\Client;
use JansenFelipe\Utils\Utils as Utils;
use Symfony\Component\DomCrawler\Crawler;

class CnpjGratis {

    /**
     * Metodo para capturar o captcha e viewstate para enviar no metodo
     * de consulta
     *
     * @param  string $cnpj CNPJ
     * @throws Exception
     * @return array Link para ver o Captcha e Cookie
     */
    public static function getParams() {
        $client = new Client();        
        $client->request('GET', 'http://www.receita.fazenda.gov.br/pessoajuridica/cnpj/cnpjreva/Cnpjreva_Solicitacao2.asp');
       
        $response = $client->getResponse();

        $headers = $response->getHeaders();
        $cookie = $headers['Set-Cookie'][0];
        
        $ch = curl_init("http://www.receita.fazenda.gov.br/pessoajuridica/cnpj/cnpjreva/captcha/gerarCaptcha.asp");
        $options = array(
            CURLOPT_COOKIEJAR => 'cookiejar',
            CURLOPT_HTTPHEADER => array(
                "Pragma: no-cache",
                "Origin: http://www.receita.fazenda.gov.br",
                "Host: www.receita.fazenda.gov.br",
                "User-Agent: Mozilla/5.0 (Windows NT 6.1; rv:32.0) Gecko/20100101 Firefox/32.0",
                "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
                "Accept-Language: pt-BR,pt;q=0.8,en-US;q=0.5,en;q=0.3",
                "Accept-Encoding: gzip, deflate",
                "Referer: http://www.receita.fazenda.gov.br/pessoajuridica/cnpj/cnpjreva/cnpjreva_solicitacao2.asp",
                "Cookie: flag=1; $cookie",
                "Connection: keep-alive"
            ),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => 1,
            CURLOPT_BINARYTRANSFER => TRUE
        );

        curl_setopt_array($ch, $options);
        $img = curl_exec($ch);
        curl_close($ch);
        
        if(@imagecreatefromstring($img)==false)
            throw new Exception('Não foi possível capturar o captcha');

        return array(
            'cookie' => $cookie,
            'captchaBase64' => 'data:image/png;base64,' . base64_encode($img)
        );
    }

    /**
     * Metodo para realizar a consulta
     *
     * @param  string $cnpj CNPJ
     * @param  string $captcha CAPTCHA
     * @param  string $stringCookie COOKIE
     * @throws Exception
     * @return array  Dados da empresa
     */
    public static function consulta($cnpj, $captcha, $stringCookie) {

        $result = array();

        $arrayCookie = explode(';', $stringCookie);

        if (!Utils::isCnpj($cnpj))
            throw new Exception('O CNPJ informado não é válido');

        $client = new Client();
        $client->setHeader('Host', 'www.receita.fazenda.gov.br');
        $client->setHeader('User-Agent', 'Mozilla/5.0 (Windows NT 6.1; rv:32.0) Gecko/20100101 Firefox/32.0');
        $client->setHeader('Accept', 'text/html,application/xhtml+xml,application/xml;q=0.9, */* ;q=0.8');
        $client->setHeader('Accept-Language', 'pt-BR,pt;q=0.8,en-US;q=0.5,en;q=0.3');
        $client->setHeader('Accept-Encoding', 'gzip, deflate');
        $client->setHeader('Referer', 'http://www.receita.fazenda.gov.br/pessoajuridica/cnpj/cnpjreva/valida.asp');
        $client->setHeader('Cookie', $arrayCookie[0]);
        $client->setHeader('Connection', 'keep-alive');

        $param = array(
            'origem' => 'comprovante',
            'cnpj' => Utils::unmask($cnpj),
            'txtTexto_captcha_serpro_gov_br' => $captcha,
            'submit1' => 'Consultar',
            'search_type' => 'cnpj'
        );

        $crawler = $client->request('POST', 'http://www.receita.fazenda.gov.br/pessoajuridica/cnpj/cnpjreva/valida.asp', $param);
        

        if ($crawler->filter('body > div > table:nth-child(3) > tr:nth-child(2) > td > b > font')->count() > 0)
            throw new Exception('Erro ao consultar. O CNPJ informado não existe no cadastro.', 99);

        $td = $crawler->filter('body > div > table:nth-child(3) > tr > td');

        foreach ($td->filter('td') as $td) {
            $td = new Crawler($td);

            if ($td->filter('font:nth-child(1)')->count() > 0) {
                $key = trim(preg_replace('/\s+/', ' ', $td->filter('font:nth-child(1)')->html()));

                switch ($key) {
                    case 'NOME EMPRESARIAL': $key = 'razao_social';
                        break;
                    case 'TÍTULO DO ESTABELECIMENTO (NOME DE FANTASIA)': $key = 'nome_fantasia';
                        break;
                    case 'CÓDIGO E DESCRIÇÃO DA ATIVIDADE ECONÔMICA PRINCIPAL': $key = 'cnae_principal';
                        break;
                    case 'CÓDIGO E DESCRIÇÃO DAS ATIVIDADES ECONÔMICAS SECUNDÁRIAS': $key = 'cnaes_secundario';
                        break;
                    case 'CÓDIGO E DESCRIÇÃO DA NATUREZA JURÍDICA' : $key = 'natureza_juridica';
                        break;
                    case 'LOGRADOURO': $key = 'logradouro';
                        break;
                    case 'NÚMERO': $key = 'numero';
                        break;
                    case 'COMPLEMENTO': $key = 'complemento';
                        break;
                    case 'CEP': $key = 'cep';
                        break;
                    case 'BAIRRO/DISTRITO': $key = 'bairro';
                        break;
                    case 'MUNICÍPIO': $key = 'cidade';
                        break;
                    case 'UF': $key = 'uf';
                        break;
                    case 'SITUAÇÃO CADASTRAL': $key = 'situacao_cadastral';
                        break;
                    case 'DATA DA SITUAÇÃO CADASTRAL': $key = 'situacao_cadastral_data';
                        break;
                    case 'MOTIVO DE SITUAÇÃO CADASTRAL': $key = 'motivo_situacao_cadastral';
                        break;
                    case 'SITUAÇÃO ESPECIAL': $key = 'situacao_especial';
                        break;
                    case 'DATA DA SITUAÇÃO ESPECIAL': $key = 'situacao_especial_data';
                        break;
                    case 'TELEFONE': $key = 'telefone';
                        break;
                    case 'ENDEREÇO ELETRÔNICO': $key = 'email';
                        break;
                    case 'ENTE FEDERATIVO RESPONSÁVEL (EFR)': $key = 'ente_federativo_responsavel';
                        break;
                    case 'DATA DE ABERTURA': $key = 'data_abertura';
                        break;
                    default: $key = null;
                        break;
                }


                if (!is_null($key)) {
                    $bs = $td->filter('font > b');
                    foreach ($bs as $b) {
                        $b = new Crawler($b);

                        $str = trim(preg_replace('/\s+/', ' ', $b->html()));
                        $attach = htmlspecialchars_decode($str);

                        if ($bs->count() == 1)
                            $result[$key] = $attach;
                        else
                            $result[$key][] = $attach;
                    }
                }
            }
        }
        
        if(isset($result['telefone']) && $result['telefone'] != '') {
            $posBarra = strpos($result['telefone'], '/');
            if ($posBarra > 0) {
                $result['telefone2'] = substr($result['telefone'], $posBarra + 1, strlen($result['telefone']) - $posBarra);
                $result['telefone'] = substr($result['telefone'], 0, $posBarra - 1);
            }
        }

        return $result;
    }

}
