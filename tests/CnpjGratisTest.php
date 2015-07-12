<?php

namespace JansenFelipe\CnpjGratis;

use PHPUnit_Framework_TestCase;

class CnpjGratisTest extends PHPUnit_Framework_TestCase {

    private $params;

    public function testGetParams() {

        $this->params = CnpjGratis::getParams();
                
        $this->assertEquals(true, isset($this->params['captchaBase64']));
        $this->assertEquals(true, isset($this->params['cookie']));
    }

}
