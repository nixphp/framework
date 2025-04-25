<?php

namespace Tests\Unit;

use PHPico\Core\Event;
use Tests\PHPicoTestCase;

class EventTest extends PHPicoTestCase
{

    public function testEvent()
    {
        $event = new Event();
        $event->listen('test.event', function () { return 'test'; });
        $this->assertSame([0 => 'test'], $event->dispatch('test.event'));
    }

}