<?php

namespace JansenFelipe\CnpjGratis;

use PHPUnit_Framework_TestCase;

class CnpjGratisTest extends PHPUnit_Framework_TestCase {

    public function testGetParams()
    {
        $cnpjGratis = new CnpjGratis();

        $params = $cnpjGratis->params();
                
        $this->assertEquals(true, isset($params->cookie));
        $this->assertEquals(true, isset($params->captchaBase64));
    }

}
