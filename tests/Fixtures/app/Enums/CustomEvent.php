<?php

namespace Fixtures\Enums;

use NixPHP\Core\Event;

class CustomEvent extends Event
{
    const string TEST_EVENT = 'test.event';
}