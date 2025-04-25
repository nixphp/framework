<?php

namespace Unit;

use Nyholm\Psr7\Request;
use PHPico\Core\Client;
use PHPico\Core\Config;
use Psr\Http\Message\ResponseInterface;
use Tests\PHPicoTestCase;
use function PHPico\app;
use function PHPico\request;

class ClientTest extends PHPicoTestCase
{

    public function testClientResponse()
    {
        $client = new Client();
        $request = new Request('GET', '/test');
        $response = $client->sendRequest($request, function () {
            return [
                'test', [
                    'HTTP/1.1 200 OK',
                    'Content-Type: text/plain'
                ]
            ];
        });

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testSslIgnore()
    {
        $config = new Config(['client' => ['ssl_verify' => false]]);
        app()->container()->set('config', $config);

        $client = new Client();
        $request = new Request('GET', '/test');
        $response = $client->sendRequest($request, function () {
            return [
                'test', [
                    'HTTP/1.1 200 OK',
                    'Content-Type: text/plain'
                ]
            ];
        });
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame(200, $response->getStatusCode());
    }

}