<?php

namespace Tests\Unit;

use Fixtures\Enums\CustomEnvironment;
use NixPHP\Enum\EnvironmentInterface;
use Tests\NixPHPTestCase;
use function NixPHP\app;
use function NixPHP\env;

class EnvironmentTest extends NixPHPTestCase
{

    public function testHelperFunction()
    {
        app()->container()->set(EnvironmentInterface::class, CustomEnvironment::TEST);
        $this->assertTrue(env() === CustomEnvironment::TEST);
    }

}