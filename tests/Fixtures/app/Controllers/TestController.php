<?php

namespace Fixtures\Controllers;

use function PHPico\response;

class TestController
{

    public function testResponse()
    {
        return response('test');
    }

}