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

    public function testEveryPluginIsRegisteredBeforeAnyOfThemBoots()
    {
        $app = new App(new Container());

        $bootstrap = tempnam(sys_get_temp_dir(), 'plugin_');
        file_put_contents(
            $bootstrap,
            '<?php $GLOBALS["seenDuringBoot"] = count(\NixPHP\app()->getPlugins());'
        );

        $first = new Plugin('test/first');
        $first->setBootstrapFile($bootstrap);

        $reflection = new ReflectionClass($app);
        $pluginsProp = $reflection->getProperty('plugins');
        $pluginsProp->setValue($app, [
            'test/first'  => $first,
            'test/second' => new Plugin('test/second'),
            'test/third'  => new Plugin('test/third'),
        ]);

        $bootPlugins = $reflection->getMethod('bootPlugins');

        try {
            $bootPlugins->invoke($app);

            // The very first bootstrap must already see all three plugins.
            // Otherwise config(), which caches itself on first access, would
            // freeze a partial view of the application.
            $this->assertSame(3, $GLOBALS['seenDuringBoot']);
            $this->assertTrue($first->isBooted());
        } finally {
            unset($GLOBALS['seenDuringBoot']);
            unlink($bootstrap);
            Stopwatch::stop('app');
        }
    }

}
