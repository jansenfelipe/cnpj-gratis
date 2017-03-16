<?php

namespace JansenFelipe\CnpjGratis;

use JansenFelipe\CnpjGratis\Contracts\ProviderContract;
use JansenFelipe\CnpjGratis\Exceptions\CnpjGratisInvalidParameterException;
use JansenFelipe\CnpjGratis\Providers\SiteReceitaProvider;

/**
 * Class to query CNPJ.
 */
class CnpjGratis
{
    /**
     * @var ProviderContract
     */
    private $provider;

    /**
     * Search CEP on all providers.
     *
     * @return array
     */
    public static function params()
    {
        $provider = new SiteReceitaProvider();

        $provider->prepare();

        return [
            'cookie' => $provider->getCookie(),
            'captchaBase64' => $provider->getCaptchaBase64()
        ];
    }

    /**
     * Performs provider CNPJ search.
     *
     * @param string $cnpj CNPJ
     *
     * @return Company
     */
    public function resolve($cnpj)
    {
        if (is_null($this->provider)) {
            throw new CnpjGratisInvalidParameterException('No provider were informed');
        }

        return $this->provider->getCompany($cnpj);
    }

    /**
     * @param ProviderContract $provider
     */
    public function setProvider(ProviderContract $provider)
    {
        $this->provider = $provider;
    }
}
