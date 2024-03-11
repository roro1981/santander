<?php

use Tests\TestCase;
use App\Http\Utils\Util;
use App\Http\Utils\Constants;
use App\Http\Utils\ParamUtil;

class UtilTest extends TestCase
{
    public function testValidateExpirationTime()
    {
        $expiration = time() + 3600;
        $result = Util::validateExpirationTime($expiration);
        $this->assertEquals($expiration, $result);

        $expiration = time() - 3600; 
        $expected = time() + Constants::PARAM_EXPIRATION_TIME;
        $result = Util::validateExpirationTime($expiration);
        $this->assertEquals($expected, $result);

        $expiration = time() + 7200; 
        $result = Util::validateExpirationTime($expiration);
        $this->assertEquals($expiration, $result);

        $expiration = time() - 7200; 
        $expected = time() + Constants::PARAM_EXPIRATION_TIME; 
        $result = Util::validateExpirationTime($expiration);
        $this->assertEquals($expected, $result);
    }
}