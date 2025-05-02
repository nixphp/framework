<?php

namespace Fixtures\Controllers;

use function NixPHP\response;

class TestController
{

    public function testResponse()
    {
        return response('test');
    }

}