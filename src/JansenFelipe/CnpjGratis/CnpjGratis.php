<?php

namespace JansenFelipe\CnpjGratis;

use JansenFelipe\Utils\Utils as Utils;

class CnpjGratis {

    /**
     * Metodo para capturar o captcha e viewstate para enviar no metodo
     * de consulta
     *
     * @param  string $cnpj CNPJ
     * @throws Exception
     * @return array Link para ver o Captcha e Viewstate
     */
    public static function getParams() {
        $ch = curl_init('www.receita.fazenda.gov.br/pessoajuridica/cnpj/cnpjreva/Cnpjreva_Solicitacao2.asp');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);

        $response = curl_exec($ch);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        curl_close($ch);

        $header = substr($response, 0, $header_size);
        $body = substr($response, $header_size);

        $out = preg_split("|(?:\r?\n){1}|m", $header);

        foreach ($out as $line) {
            @list($key, $val) = explode(": ", $line, 2);
            if ($val != null) {
                if (!array_key_exists($key, $headers))
                    $headers[$key] = trim($val);
            } else
                $headers[] = $key;
        }

        if (!method_exists('phpQuery', 'newDocumentHTML'))
            require_once __DIR__ . DIRECTORY_SEPARATOR . 'phpQuery-onefile.php';

        \phpQuery::newDocumentHTML($body, $charset = 'utf-8');

        $viewstate = \phpQuery::pq("#viewstate")->val();

        if ($viewstate == "")
            throw new Exception('Erro ao recuperar viewstate');

        $imgcaptcha = \phpQuery::pq("#imgcaptcha")->attr('src');
        $urlCaptcha = 'http://www.receita.fazenda.gov.br' . $imgcaptcha;

        $captchaBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($urlCaptcha));

        return array(
            'captcha' => $urlCaptcha,
            'captchaBase64' => $captchaBase64,
            'viewstate' => $viewstate,
            'cookie' => $headers['Set-Cookie']
        );
    }

    /**
     * Metodo para realizar a consulta
     *
     * @param  string $cnpj CNPJ
     * @param  string $captcha CAPTCHA
     * @throws Exception
     * @return array  Dados da empresa
     */
    public static function consulta($cnpj, $captcha, $viewstate, $stringCookie) {
        $arrayCookie = explode(';', $stringCookie);


        if (!Utils::isCnpj($cnpj))
            throw new \Exception('O CNPJ informado não é válido');

        $ch = curl_init("http://www.receita.fazenda.gov.br/pessoajuridica/cnpj/cnpjreva/valida.asp");

        $param = array(
            'origem' => 'comprovante',
            'viewstate' => $viewstate,
            'cnpj' => Utils::unmask($cnpj),
            'captcha' => $captcha,
            'captchaAudio' => '',
            'submit1' => 'Consultar',
            'search_type' => 'cnpj'
        );

        $options = array(
            CURLOPT_COOKIEJAR => 'cookiejar',
            CURLOPT_HTTPHEADER => array(
                "Host: www.receita.fazenda.gov.br",
                "User-Agent: Mozilla/5.0 (Windows NT 6.1; rv:32.0) Gecko/20100101 Firefox/32.0",
                "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
                "Accept-Language: pt-BR,pt;q=0.8,en-US;q=0.5,en;q=0.3",
                "Accept-Encoding: gzip, deflate",
                "Referer: http://www.receita.fazenda.gov.br/pessoajuridica/cnpj/cnpjreva/Cnpjreva_Solicitacao2.asp",
                "Cookie: ' . $arrayCookie[0] . '",
                "Connection: keep-alive"
            ),
            CURLOPT_POSTFIELDS => http_build_query($param),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => 1
        );

        curl_setopt_array($ch, $options);
        $html = curl_exec($ch);
        curl_close($ch);

        if (!method_exists('phpQuery', 'newDocumentHTML'))
            require_once __DIR__ . DIRECTORY_SEPARATOR . 'phpQuery-onefile.php';

        \phpQuery::newDocumentHTML($html, $charset = 'utf-8');

        $tr = pq('body > table:eq(1)')->find('table:eq(1) > tr:eq(0)');
        $result['cnpj'] = pq($tr)->find('td:eq(0) > font > b:eq(0)')->html();

        if (!Utils::isCnpj($result['cnpj']))
            throw new \Exception('Erro ao consultar. Verifique se digitou corretamente o captcha.', 99);

        $result['tipo'] = pq($tr)->find('td:eq(0) > font > b:eq(1)')->html();
        $result['data_abertura'] = pq($tr)->find('td:eq(2) > font > b:eq(0)')->html();

        $tds = pq('body > table:gt(0)')->find('table:gt(0) > tr:gt(0) > td');

        foreach ($tds as $td) {
            $key = trim(preg_replace('/\s+/', ' ', pq($td)->find('font:first')->html()));

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
            }

            $bs = pq($td)->find('font > b');

            foreach ($bs as $b) {
                if (count($bs) == 1)
                    $result[$key] = trim(preg_replace('/\s+/', ' ', pq($b)->html()));
                else
                    $result[$key][] = trim(preg_replace('/\s+/', ' ', pq($b)->html()));
            }
        }

        return $result;
    }

}
