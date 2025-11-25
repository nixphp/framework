<?php

namespace Fixtures\Enums;

use NixPHP\Enum\EnvironmentInterface;

enum CustomEnvironment: string implements EnvironmentInterface
{
    case TEST = 'test';
}