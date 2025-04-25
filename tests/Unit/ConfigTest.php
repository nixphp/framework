<?php

namespace Tests\Unit;

use PHPico\Core\Config;
use Tests\PHPicoTestCase;
use function PHPico\app;
use function PHPico\env;

class ConfigTest extends PHPIcoTestCase
{

    public function testConfigInternals()
    {
        $config = new Config(['foo' => 'bar']);
        $this->assertEquals('bar', $config->get('foo'));
        $this->assertSame(['foo' => 'bar'], $config->all());
    }

    public function testConfigInternalsWithNamespace()
    {
        $config = new Config(['foo' => ['bar' => 'baz']]);
        $this->assertEquals('baz', $config->get('foo:bar'));
        $this->assertSame(['foo' => ['bar' => 'baz']], $config->all());
    }

    public function testConfigInternalsEnvVariables()
    {
        $_ENV['BAR'] = 'baz';
        $config = new Config(['foo' => 'ENV:BAR']);
        $this->assertSame('baz', $config->get('foo'));
    }

    public function testHelperFunction()
    {
        $config = new Config(['foo' => 'bar']);
        app()->container()->set('config', function () use ($config) {
            return $config;
        });

        $this->assertEquals('bar', \PHPico\config('foo'));
        $this->assertSame(['foo' => 'bar'], \PHPico\config());
    }

}