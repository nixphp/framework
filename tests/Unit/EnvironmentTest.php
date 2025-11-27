<?php

namespace Tests\Unit;

use Fixtures\Enums\CustomEnvironment;
use Tests\NixPHPTestCase;
use function NixPHP\app;
use function NixPHP\env;

class EnvironmentTest extends NixPHPTestCase
{

    public function testHelperFunction()
    {
        app()->container()->set('env', fn() => CustomEnvironment::TEST);
        $this->assertTrue(env() === CustomEnvironment::TEST);
    }

}