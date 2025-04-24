<?php

namespace Tests\Unit;

use PHPico\Core\Route;
use Tests\PHPicoTestCase;

class RouterTest extends PHPicoTestCase
{

    public function testShouldAddRoute()
    {
        $route = new Route();
        $route->add('GET', '/test', function() { return 'test'; });
        $result = $route->find('/test', 'GET');
        $this->assertTrue(is_callable($result['action']));
    }

}