<?php

namespace PHPico\Core;

class Event
{

    protected array $listeners = [];

    public function listen(string $event, array|callable $listener): Event
    {
        $this->listeners[$event][] = $listener;
        return $this;
    }

    public function dispatch(string $event, mixed ...$payload): array
    {
        $responses = [];

        if (!empty($this->listeners[$event])) {
            foreach ($this->listeners[$event] as $listener) {
                if (is_array($listener)) {
                    [$class, $handle] = $listener;
                    $responses[] = call_user_func([new $class, $handle], ...$payload);
                } else if (is_callable($listener)) {
                    $responses[] = $listener(...$payload);
                }
            }
        }

        return $responses;
    }

}