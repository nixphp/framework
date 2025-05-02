<?php

namespace Tests\Unit;

use NixPHP\Core\Environment;
use Tests\NixPHPTestCase;
use function NixPHP\app;
use function NixPHP\env;

class EnvironmentTest extends NixPHPTestCase
{

    public function testEnvironmentLocal()
    {
        $env = new Environment(Environment::LOCAL);
        $this->assertTrue($env->isLocal());
    }

    public function testEnvironmentStaging()
    {
        $env = new Environment(Environment::STAGING);
        $this->assertTrue($env->isStaging());
    }

    public function testEnvironmentProduction()
    {
        $env = new Environment(Environment::PRODUCTION);
        $this->assertTrue($env->isProduction());
    }

    public function testEnvironmentTesting()
    {
        $env = new Environment(Environment::TESTING);
        $this->assertTrue($env->isTesting());
    }

    public function testHelperFunction()
    {
        $env = new Environment(Environment::TESTING);
        app()->container()->set('environment', $env);
        $this->assertTrue(env()->isTesting());
    }

    public function testHelperFunctionWithArgument()
    {
        $env = new Environment(Environment::TESTING);
        app()->container()->set('environment', $env);
        $this->assertSame(Environment::TESTING, env('APP_ENV'));
    }

}