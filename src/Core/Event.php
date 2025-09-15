<?php
declare(strict_types=1);

namespace NixPHP\Core;

class Event
{

    protected array $listeners = [];

    /**
     * Register a listener for a specific event
     *
     * @param string         $event    Name of the event to listen for
     * @param array|callable $listener Listener callback or array containing class and method
     *
     * @return Event Returns this Event instance for chaining
     */
    public function listen(string $event, array|callable $listener): Event
    {
        $this->listeners[$event][] = $listener;
        return $this;
    }

    /**
     * Dispatch an event to all registered listeners
     *
     * @param string $event      Name of the event to dispatch
     * @param mixed  ...$payload Variable number of arguments to pass to the listeners
     *
     * @return array Array of responses from all listeners
     */
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