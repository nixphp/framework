<?php

namespace Tests\Unit;

use Nyholm\Psr7\Request;
use Nyholm\Psr7\ServerRequest;
use NixPHP\Exceptions\AbortException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Tests\NixPHPTestCase;
use function NixPHP\abort;
use function NixPHP\app;

use function NixPHP\json;
use function NixPHP\redirect;
use function NixPHP\refresh;
use function NixPHP\request;

class FunctionsTest extends NixPHPTestCase
{

    public function testFunctionRequest()
    {
        app()->container()->set('request', function() { return new ServerRequest('GET', '/test'); });
        $this->assertInstanceOf(ServerRequestInterface::class, request());
    }
    
    public function testFunctionJson()
    {
        $response = json(['name' => 'test']);
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame(json_encode(['name' => 'test'], JSON_PRETTY_PRINT), $response->getBody()->getContents());
    }

    public function testFunctionJsonFail()
    {
        $this->expectException(\RuntimeException::class);
        $brokenData = ['file' => fopen(__FILE__, 'r')];
        $response = json($brokenData);
    }

    public function testFunctionRedirect()
    {
        $response = redirect('/test');
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame(302, $response->getStatusCode());
    }

    public function testRefresh()
    {
        app()->container()->set('request', function() { return new Request('GET', '/test'); });
        $response = refresh();
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('/test', $response->getHeaderLine('Location'));
    }

    public function testAbort()
    {
        $this->expectException(AbortException::class);
        abort();
    }

}