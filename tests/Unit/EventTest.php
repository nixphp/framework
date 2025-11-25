<?php

namespace Tests\Unit;

use Fixtures\Enums\CustomEvent;
use Fixtures\Events\TestEventListener;
use NixPHP\Core\EventManager;
use Tests\NixPHPTestCase;

class EventTest extends NixPHPTestCase
{

    public function testEventCallable()
    {
        $event = new EventManager();
        $event->listen(CustomEvent::TEST_EVENT, function () { return 'test'; });
        $this->assertSame([0 => 'test'], $event->dispatch(CustomEvent::TEST_EVENT));
    }

    public function testEventClassMethod()
    {
        $event = new EventManager();
        $event->listen(CustomEvent::TEST_EVENT, [TestEventListener::class, 'handle']);
        $this->assertSame([0 => 'test response from class'], $event->dispatch(CustomEvent::TEST_EVENT));
    }

    public function testEventPriorityOrder()
    {
        $event = new EventManager();

        $event->listen(CustomEvent::TEST_EVENT, fn () => 'low priority', priority: -10);
        $event->listen(CustomEvent::TEST_EVENT, fn () => 'default priority'); // 0
        $event->listen(CustomEvent::TEST_EVENT, fn () => 'high priority', priority: 50);

        $responses = $event->dispatch(CustomEvent::TEST_EVENT);

        $this->assertSame(
            ['high priority', 'default priority', 'low priority'],
            $responses
        );
    }

}