<?php

namespace Tests\Unit;

use NixPHP\Core\App;
use NixPHP\Core\Container;
use NixPHP\Core\Dispatcher;
use NixPHP\Core\Environment;
use NixPHP\Support\Plugin;
use NixPHP\Support\Stopwatch;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionClass;
use RuntimeException;
use Tests\NixPHPTestCase;

class AppTest extends NixPHPTestCase
{
    public function testHasPluginHonoursVersionConstraint()
    {
        $app = new App(new Container());
        $reflection = new ReflectionClass($app);

        $pluginsProp = $reflection->getProperty('plugins');
        $pluginsProp->setAccessible(true);
        $pluginInstance = new Plugin('nixphp/database');
        $pluginInstance->setVersion('0.1.2');

        $pluginsProp->setValue($app, [
            'nixphp/database' => $pluginInstance,
        ]);

        $this->assertTrue($app->hasPlugin('nixphp/database'));
        try {
            $this->assertTrue($app->hasPlugin('nixphp/database:0.1.2'));
            $this->assertTrue($app->hasPlugin('nixphp/database:>=0.1.2'));
            $this->assertTrue($app->hasPlugin('nixphp/database:>0.1.1'));
            $this->assertTrue($app->hasPlugin('nixphp/database:<=0.1.2'));
            $this->assertFalse($app->hasPlugin('nixphp/database:>0.1.2'));
            $this->assertFalse($app->hasPlugin('nixphp/database:<0.1.2'));
            $this->assertFalse($app->hasPlugin('nixphp/nonexistent:>=1.0.0'));
        } finally {
            Stopwatch::stop('app');
        }
    }

}
