<?php

namespace Tests\Unit;

use PHPico\Core\Config;
use Tests\PHPicoTestCase;
use function PHPico\app;
use function PHPico\config;

class ConfigTest extends PHPIcoTestCase
{

    public function testConfigInternals()
    {
        $config = new Config(['foo' => 'bar']);
        $this->assertSame('bar', $config->get('foo'));
        $this->assertSame(['foo' => 'bar'], $config->all());
    }

    public function testConfigInternalsWithNamespace()
    {
        $config = new Config(['foo' => ['bar' => 'baz']]);
        $this->assertSame('baz', $config->get('foo:bar'));
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

        $this->assertSame('bar', config('foo'));
        $this->assertSame(['foo' => 'bar'], config());
    }

    public function testTypeIsFalse()
    {
        $config = new Config(['foo' => false]);
        app()->container()->set('config', function () use ($config) {
            return $config;
        });

        $this->assertSame(['foo' => false], config());
        $this->assertSame(false, config('foo'));
        $this->assertIsBool(config('foo'));
    }

    public function testTypeIsTrue()
    {
        $config = new Config(['foo' => true]);
        app()->container()->set('config', function () use ($config) {
            return $config;
        });

        $this->assertSame(['foo' => true], config());
        $this->assertSame(true, config('foo'));
        $this->assertIsBool(config('foo'));
    }

}