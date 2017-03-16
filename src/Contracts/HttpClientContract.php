<?php

namespace JansenFelipe\CnpjGratis\Contracts;

interface HttpClientContract
{
    /**
     * Send GET request.
     *
     * @return string
     */
    public function get($uri);

    /**
     * Send POST request.
     *
     * @return string
     */
    public function post($uri, $data = []);

    /**
     * Set headers request.
     *
     * @param array $headers
     */
    public function setHeaders(array $headers);

    /**
     * Get headers response.
     *
     * @return array
     */
    public function getHeaders();
}