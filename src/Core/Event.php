<?php

namespace PHPico\Core;

class Event
{

    protected array $listeners = [];

    public function listen(string $event, callable $listener)
    {
        $this->listeners[$event][] = $listener;
        return $this;
    }

    public function dispatch(string $event, mixed ...$payload)
    {
        $responses = [];

        if (!empty($this->listeners[$event])) {
            foreach ($this->listeners[$event] as $listener) {
                $responses[] = $listener(...$payload);
            }
        }

        return $responses;
    }

}