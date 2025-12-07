<?php

namespace Fixtures\Controllers;

use Nyholm\Psr7\Response;
use function NixPHP\response;

class TestController
{

    public function testResponse(): Response
    {
        return response('test');
    }

    public function testResponseWithParam($id): Response
    {
        return response("Param: $id");
    }

}