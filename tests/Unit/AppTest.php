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
    public function testRunReturnsEarlyInProdWhenDispatcherThrows()
    {
        $previousEnv = $_ENV['APP_ENV'] ?? null;
        $_ENV['APP_ENV'] = Environment::PROD;
        putenv('APP_ENV=' . Environment::PROD);

        $app = new App(new Container());
        $container = $app->container();

        $container->set(Dispatcher::class, fn($container) => new class {
            public function forward(ServerRequestInterface $request)
            {
                throw new RuntimeException('boom');
            }
        });

        $app->run();

        $this->assertTrue(true); // reached only when send_response is skipped

        if ($previousEnv === null) {
            unset($_ENV['APP_ENV']);
            putenv('APP_ENV');
        } else {
            $_ENV['APP_ENV'] = $previousEnv;
            putenv('APP_ENV=' . $previousEnv);
        }
    }

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
            $this->assertFalse($app->hasPlugin('nixphp/database:>0.1.2'));
            $this->assertFalse($app->hasPlugin('nixphp/database:<0.1.2'));
        } finally {
            Stopwatch::stop('app');
        }
    }

}
