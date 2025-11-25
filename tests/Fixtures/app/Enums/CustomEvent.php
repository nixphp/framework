<?php

namespace Fixtures\Enums;

use NixPHP\Enum\EventInterface;

enum CustomEvent: string implements EventInterface
{
    case TEST_EVENT = 'test.event';
}