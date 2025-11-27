<?php

namespace Tests\Unit;

use Fixtures\Enums\CustomEnvironment;
use NixPHP\Core\Environment;
use Tests\NixPHPTestCase;
use function NixPHP\app;
use function NixPHP\env;

class EnvironmentTest extends NixPHPTestCase
{

    public function testHelperFunction()
    {
        app()->container()->set(Environment::class, fn() => CustomEnvironment::TEST);
        $this->assertSame(CustomEnvironment::TEST, env());
    }

}