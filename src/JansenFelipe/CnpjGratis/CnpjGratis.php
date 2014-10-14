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

        require_once __DIR__ . '\phpQuery-onefile.php';
        \phpQuery::newDocumentHTML($body, $charset = 'utf-8');

        $viewstate = \phpQuery::pq("#viewstate")->val();

        if ($viewstate == "")
            throw new Exception('Erro ao recuperar viewstate');

        $imgcaptcha = \phpQuery::pq("#imgcaptcha")->attr('src');

        return array(
            'captcha' => 'http://www.receita.fazenda.gov.br' . $imgcaptcha,
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

        require_once __DIR__ . '\phpQuery-onefile.php';
        \phpQuery::newDocumentHTML($html, $charset = 'utf-8');

        $Bs = pq('b');

        $result = array();
        foreach ($Bs as $b)
            $result[] = trim(pq($b)->html());

        if (isset($result[4]) && Utils::isCnpj($result[4])) {
            return(array(
                'cnpj' => Utils::unmask($result[4]),
                'tipo' => $result[5],
                'data_abertura' => $result[7],
                'razao_social' => $result[8],
                'nome_fantasia' => $result[9],
                'cnae' => array($result[10], $result[11]),
                'natureza_juridica' => $result[12],
                'logradouro' => $result[13],
                'numero' => $result[14],
                'complemento' => $result[15],
                'bairro' => $result[17],
                'cidade' => $result[18],
                'uf' => $result[19],
                'cep' => Utils::unmask($result[16]),
                'situacao_cadastral' => $result[20],
                'situacao_cadastral_data' => $result[21],
                'consulta_data' => $result[25],
                'consulta_hora' => $result[26],
            ));
        } else
            throw new \Exception('Aconteceu um erro ao fazer a consulta. Envie os dados novamente.');
    }

}
