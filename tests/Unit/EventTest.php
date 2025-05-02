<?php

namespace Tests\Unit;

use NixPHP\Core\Event;
use Tests\NixPHPTestCase;

class EventTest extends NixPHPTestCase
{

    public function testEvent()
    {
        $event = new Event();
        $event->listen('test.event', function () { return 'test'; });
        $this->assertSame([0 => 'test'], $event->dispatch('test.event'));
    }

}