<?php

namespace Tests\Unit;

use Fixtures\Events\TestEventListener;
use NixPHP\Core\Event;
use Tests\NixPHPTestCase;

class EventTest extends NixPHPTestCase
{

    public function testEventCallable()
    {
        $event = new Event();
        $event->listen('test.event', function () { return 'test'; });
        $this->assertSame([0 => 'test'], $event->dispatch('test.event'));
    }

    public function testEventClassMethod()
    {
        $event = new Event();
        $event->listen('test.event', [TestEventListener::class, 'handle']);
        $this->assertSame([0 => 'test response from class'], $event->dispatch('test.event'));
    }

    public function testEventPriorityOrder()
    {
        $event = new Event();

        $event->listen('test.event', fn () => 'low priority', priority: -10);
        $event->listen('test.event', fn () => 'default priority'); // 0
        $event->listen('test.event', fn () => 'high priority', priority: 50);

        $responses = $event->dispatch('test.event');

        $this->assertSame(
            ['high priority', 'default priority', 'low priority'],
            $responses
        );
    }

}