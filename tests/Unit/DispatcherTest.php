<?php

namespace Tests\Unit;

use Fixtures\Controllers\TestController;
use Nyholm\Psr7\Response;
use Nyholm\Psr7\ServerRequest;
use NixPHP\Core\Dispatcher;
use NixPHP\Core\Route;
use NixPHP\Exceptions\AbortException;
use NixPHP\Exceptions\DispatcherException;
use NixPHP\Exceptions\RouteNotFoundException;
use Psr\Http\Message\ResponseInterface;
use Tests\NixPHPTestCase;



class DispatcherTest extends NixPHPTestCase
{
    public function testDispatch()
    {
        $request = new ServerRequest('GET', '/test');

        $route = new Route();
        $route->add('GET', '/test', function () {
            return new Response(200, [], 'test');
        });

        $dispatcher = new Dispatcher($route);
        $response = $dispatcher->forward($request);

        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testDispatchWithInvalidResponse()
    {
        $this->expectException(DispatcherException::class);
        $request = new ServerRequest('GET', '/test');

        $route = new Route();
        $route->add('GET', '/test', function () {
            return 'test';
        });

        $dispatcher = new Dispatcher($route);
        $dispatcher->forward($request);
    }

    public function testDispatchWithMissingRoute()
    {
        $this->expectException(RouteNotFoundException::class);
        $request = new ServerRequest('GET', '/test');

        $route = new Route();
        $route->add('GET', '/not-found', function () {
            return 'test';
        });

        $dispatcher = new Dispatcher($route);
        $dispatcher->forward($request);
    }

    public function testDispatchWithClassController()
    {
        $request = new ServerRequest('GET', '/test');

        $route = new Route();
        $route->add('GET', '/test', [TestController::class, 'testResponse']);

        $dispatcher = new Dispatcher($route);
        $response = $dispatcher->forward($request);
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('test', $response->getBody()->getContents());
    }

    public function testDispatchPlaceholderWhenNoRoutesAreConfigured()
    {
        $request = new ServerRequest('GET', '/');
        $route = new Route();

        $dispatcher = new Dispatcher($route);
        $response = $dispatcher->forward($request);
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertStringContainsString('To begin developing, you may add your first route to', $response->getBody()->getContents());
    }

    public function testDispatchNotFoundWhenNoRoutesAreConfiguredAndOtherPathIsCalled()
    {
        $this->expectException(RouteNotFoundException::class);
        $request = new ServerRequest('GET', '/test');
        $route = new Route();

        $dispatcher = new Dispatcher($route);
        $dispatcher->forward($request);
    }

}