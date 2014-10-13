<?php

namespace JansenFelipe\CnpjGratis;

class CnpjGratis {

    /**
     * Metodo para capturar o captcha e viewstate para enviar no metodo
     * de consulta
     *
     * @param  string $cnpj CNPJ
     * @return array Link para ver o Captcha e Viewstate
     */
    public function getParams($cnpj) {

        if (!$this->isValid($cnpj))
            throw new \Exception('O CNPJ informado não é válido');

        $browser = new \Buzz\Browser();
        $response = $browser->get('www.receita.fazenda.gov.br/pessoajuridica/cnpj/cnpjreva/Cnpjreva_Solicitacao2.asp');

        require_once __DIR__ . '\phpQuery-onefile.php';
        \phpQuery::newDocumentHTML($response->getContent(), $charset = 'utf-8');

        $viewstate = \phpQuery::pq("#viewstate")->val();

        if ($viewstate == "")
            throw new Exception('Erro ao recuperar viewstate');

        $imgcaptcha = \phpQuery::pq("#imgcaptcha")->attr('src');

        return array(
            'captcha' => 'http://www.receita.fazenda.gov.br' . $imgcaptcha,
            'viewstate' => $viewstate,
            'cookie' => $response->getHeader('Set-Cookie')
        );
    }

    /**
     * Metodo para realizar a consulta
     *
     * @param  string $cnpj CNPJ
     * @param  string $captcha CAPTCHA
     * @return array  Dados da empresa
     */
    public function consulta($cnpj, $captcha, $viewstate, $cookie) {

        if (!$this->isValid($cnpj))
            throw new \Exception('O CNPJ informado não é válido');


        $request = new \Buzz\Message\Request();
        $request->setHost('http://www.receita.fazenda.gov.br/pessoajuridica/cnpj/cnpjreva/valida.asp');
        
        $request->setHeaders(array(
            'Cookie' => $cookie,
            'User-Agent' => 'Mozilla/5.0 (Windows NT 6.1; rv:32.0) Gecko/20100101 Firefox/32.0'
        ));
        
        $request->setContent(array(
            'viewstate' => $viewstate,
            'captcha' => $captcha,
            'captchaAudio' => '',
            'origem' => 'comprovante',
            'submit1' => 'Consultar',
            'cnpj' => $cnpj
        ));
        
        $jar = new \Buzz\Util\CookieJar();
        $jar->setCookies(array());
        $jar->addCookieHeaders($request);

        $browser = new \Buzz\Browser();
        $response = $browser->send($request);


        echo($response);
        die;



        require_once __DIR__ . '\phpQuery-onefile.php';
        \phpQuery::newDocumentHTML($html, $charset = 'utf-8');

        $resposta = array(
            'logradouro' => trim(\phpQuery::pq('.caixacampobranco .resposta:contains("Logradouro: ") + .respostadestaque:eq(0)')->html()),
            'bairro' => trim(\phpQuery::pq('.caixacampobranco .resposta:contains("Bairro: ") + .respostadestaque:eq(0)')->html()),
            'cep' => trim(\phpQuery::pq('.caixacampobranco .resposta:contains("CEP: ") + .respostadestaque:eq(0)')->html())
        );
        $aux = explode(" - ", $resposta['logradouro']);
        if (count($aux) == 2)
            $resposta['logradouro'] = $aux[0];

        $cidadeUF = explode("/", trim(\phpQuery::pq('.caixacampobranco .resposta:contains("Localidade / UF: ") + .respostadestaque:eq(0)')->html()));

        $resposta['cidade'] = trim($cidadeUF[0]);
        $resposta['uf'] = trim($cidadeUF[1]);

        return array_map('html_entity_decode', array_map('htmlentities', $resposta));
    }

    /**
     * Validando CNPJ
     *
     * @param string $cnpj CNPJ 
     * @return boolean
     */
    public function isValid($cnpj) {

        if (empty($cnpj) || $cnpj == '00000000000000')
            return false;

        $dig_1 = 0;
        $dig_2 = 0;
        $controle_1 = 5;
        $controle_2 = 6;
        $resto = 0;

        for ($i = 0; $i < 12; $i++) {
            $dig_1 = $dig_1 + (double) (substr($cnpj, $i, 1) * $controle_1);
            $controle_1 = $controle_1 - 1;
            if ($i == 3)
                $controle_1 = 9;
        }

        $resto = $dig_1 % 11;
        $dig_1 = 11 - $resto;
        if (($resto == 0) || ($resto == 1))
            $dig_1 = 0;

        for ($i = 0; $i < 12; $i++) {
            $dig_2 = $dig_2 + (int) (substr($cnpj, $i, 1) * $controle_2);
            $controle_2 = $controle_2 - 1;
            if ($i == 4)
                $controle_2 = 9;
        }

        $dig_2 = $dig_2 + (2 * $dig_1);
        $resto = $dig_2 % 11;
        $dig_2 = 11 - $resto;

        if (($resto == 0) || ($resto == 1))
            $dig_2 = 0;

        $dig_ver = ($dig_1 * 10) + $dig_2;
        return $dig_ver == (double) (substr($cnpj, strlen($cnpj) - 2, 2));
    }

    /**
     * Metodo para enviar a requisição
     * @return String HTML
     */
    private function curl($url, $post = array(), $get = array()) {
        $url = explode('?', $url, 2);
        if (count($url) === 2) {
            $temp_get = array();
            parse_str($url[1], $temp_get);
            $get = array_merge($get, $temp_get);
        }

        $ch = curl_init($url[0] . "?" . http_build_query($get));

        if (count($post) > 0) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
        }
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        curl_setopt($ch, CURLOPT_VERBOSE, true);

        return curl_exec($ch);
    }

    private function request($method = "POST", $host, $port = '80', $path = '/', $data = '', $cookie = '') {
        $_err = 'lib sockets::' . __FUNCTION__ . '(): ';
        $str = false;

        if (!empty($data)) {
            foreach ($data AS $k => $v) {
                $str .= urlencode($k) . '=' . urlencode($v) . '&';
            }
        }
        $str = substr($str, 0, -1);

        $d = false;

        $fp = fsockopen($host, $port, $errno, $errstr, $timeout = 30);
        if (!$fp)
            die($_err . $errstr . $errno);
        else {
            fputs($fp, "$method $path HTTP/1.1\r\n");
            fputs($fp, "Host: $host\r\n");
            fputs($fp, "Content-Type: application/x-www-form-urlencoded\r\n");
            fputs($fp, "User-Agent: Mozilla/5.0 (Windows NT 6.1; rv:32.0) Gecko/20100101 Firefox/32.0\r\n");
            fputs($fp, "Accept-Language: pt-BR,pt;q=0.8,en-US;q=0.5,en;q=0.3\r\n");
            fputs($fp, "Accept-Encoding: gzip, deflate\r\n");


            if (!empty($cookie))
                fputs($fp, "Cookie: $cookie; path=/\r\n\r\n");


            fputs($fp, "Referer: http://www.receita.fazenda.gov.br/pessoajuridica/cnpj/cnpjreva/Cnpjreva_Solicitacao2.asp \r\n");
            fputs($fp, "Content-Length: " . strlen($str) . "\r\n");
            fputs($fp, "Connection: keep-alive\r\n\r\n");
            fputs($fp, $str . "\r\n\r\n");

            $content = "";
            $headers = array(
                'response' => str_replace(PHP_EOL, '', fgets($fp, 4096))
            );

            do {
                $line = fgets($fp, 4096);
                $aux = explode(': ', $line);
                if (count($aux) != 2)
                    break;
                $headers[$aux[0]] = str_replace(PHP_EOL, '', $aux[1]);
            }while (true);

            $count = 0;
            while ($count <= $headers['Content-Length']) {
                $content .= fgets($fp, 4096);
                $count = count($content);
                ob_flush();
            }

            fclose($fp);
        }

        $resposta = explode(PHP_EOL, $d);

        if (isset($resposta[0]) && strpos($resposta[0], '302')) {
            $aux = explode(' ', $resposta[5]);

            if (isset($resposta[8]))
                preg_match_all('/Set-Cookie: (.*?);/is', $resposta[8], $result);

            unset($data['viewstate']);

            return $this->request("GET", $host, $port, '/pessoajuridica/cnpj/cnpjreva/' . $aux[1], '', $result[1][0]);
        }

        return $d;
    }

}
