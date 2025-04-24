<?php

namespace Tests\Unit;

use PHPico\Core\Route;
use PHPico\Exceptions\RouteNotFoundException;
use Tests\PHPicoTestCase;

class RouteTest extends PHPicoTestCase
{

    public function testShouldAddRoute()
    {
        $route = new Route();
        $route->add('GET', '/test', function() { return 'test'; });
        $result = $route->find('/test', 'GET');
        $this->assertTrue(is_callable($result['action']));
    }
    
    public function testShouldReturnNotFoundException()
    {
        $this->expectException(RouteNotFoundException::class);
        $route = new Route();
        $route->add('GET', '/test', function() { return 'test'; });
        $route->find('/other', 'GET');
    }

    public function testShouldIgnoreWrongMethod()
    {
        $this->expectException(RouteNotFoundException::class);
        $route = new Route();
        $route->add('GET', '/test', function() { return 'test'; });
        $route->find('/test', 'POST');
    }

    public function testShouldThrowExceptionForMissingName()
    {
        $this->expectException(\LogicException::class);
        $route = new Route();
        $route->add('GET', '/test', function() { return 'test'; });
        $route->add('GET', '/test2', function() { return 'test'; });
    }

    public function testShouldAllowMultipleRoutes()
    {
        $route = new Route();
        $route->add('GET', '/test', function() { return 'test'; }, 'name');
        $route->add('GET', '/test2', function() { return 'test'; }, 'name2');
        $this->assertIsCallable($route->find('/test', 'GET')['action']);
        $this->assertIsCallable($route->find('/test2', 'GET')['action']);
    }

    public function testShouldReturnRouteByName()
    {
        $route = new Route();
        $route->add('GET', '/test', function() { return 'test'; }, 'testname');
        $this->assertSame('/test', $route->url('testname'));
    }

    public function testShouldReturnRouteByNameWithParams()
    {
        $route = new Route();
        $route->add('GET', '/test/{id}', function($id) { return $id; }, 'testname');
        $this->assertSame('/test/1', $route->url('testname', ['id' => 1]));
    }

    public function testUrlShouldThrowExceptionWhenRouteNotFound()
    {
        $this->expectException(RouteNotFoundException::class);
        $route = new Route();
        $route->add('GET', '/test', function() { return 'test'; }, 'testname');
        $route->url('wrong_testname');
    }

}