<?php

namespace JansenFelipe\CnpjGratis\Contracts;

use JansenFelipe\CnpjGratis\Company;

interface ProviderContract
{
    /**
     * @return Company
     */
    public function getCompany($cnpj);
}