<?php

declare(strict_types=1);

namespace NixPHP\Core;

class EventManager
{
    protected array $listeners = [];

    /**
     * Register a listener for a specific event
     *
     * @param string<Event>  $event    Name of the event to listen for
     * @param array|callable $listener Listener callback or array containing class and method
     * @param int            $priority Higher priority = earlier execution (default 0)
     *
     * @return EventManager Returns this EventManager instance for chaining
     */
    public function listen(string $event, array|callable $listener, int $priority = 0): EventManager
    {
        $this->listeners[$event][] = [
            'callback' => $listener,
            'priority' => $priority,
        ];

        return $this;
    }

    /**
     * Dispatch an event to all registered listeners
     *
     * @param string<Event> $event Name of the event to dispatch
     * @param mixed         ...$payload Variable number of arguments to pass to the listeners
     *
     * @return array Array of responses from all listeners
     */
    public function dispatch(string $event, mixed ...$payload): array
    {
        $responses = [];

        if (!empty($this->listeners[$event])) {
            usort(
                $this->listeners[$event],
                fn ($a, $b) => $b['priority'] <=> $a['priority']
            );

            foreach ($this->listeners[$event] as $listener) {
                $callback = $listener['callback'];

                if (is_array($callback)) {
                    [$class, $handle] = $callback;
                    $responses[] = call_user_func([new $class, $handle], ...$payload);
                } elseif (is_callable($callback)) {
                    $responses[] = $callback(...$payload);
                }
            }
        }

        return $responses;
    }
}
