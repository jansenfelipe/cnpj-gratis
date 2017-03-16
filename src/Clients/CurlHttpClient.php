<?php

namespace JansenFelipe\CnpjGratis\Clients;

use JansenFelipe\CnpjGratis\Contracts\HttpClientContract;

class CurlHttpClient implements HttpClientContract
{
    /**
     * @var string[]
     */
    private $headers = [];

    /**
     * Send GET request.
     *
     * @return string|null
     */
    public function get($uri)
    {
        $curl = $this->createCurl($uri);

        return $this->executeCurl($curl);
    }

    /**
     * Send POST request.
     *
     * @return string
     */
    public function post($uri, $data = [])
    {
        $curl = $this->createCurl($uri, $data);

        return $this->executeCurl($curl);
    }

    /**
     * Create resource cURL.
     *
     * @param $uri
     * @param array $data
     *
     * @return resource
     */
    private function createCurl($uri, array $data = [])
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL            => $uri,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $this->headers,
            CURLOPT_HEADER => 1,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false
        ]);

        if (!empty($data)) {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
        }

        return $curl;
    }

    /**
     * Execute resource cURL.
     *
     * @param resource $curl
     *
     * @return string
     */
    private function executeCurl($curl)
    {
        $response = curl_exec($curl);
        $size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);

        curl_close($curl);

        $headers = [];

        foreach (explode(PHP_EOL, substr($response, 0, $size)) as $i)
        {
            $t = explode(':', $i, 2);

            if(isset($t[1]))
                $headers[trim($t[0])] = trim($t[1]);
        }

        $this->headers = $headers;

        return substr($response, $size);
    }

    /**
     * Set headers request.
     *
     * @param string $headers
     */
    public function setHeaders(array $headers)
    {
        $this->headers = $headers;
    }

    /**
     * Get headers response.
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }
}