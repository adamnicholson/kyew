<?php

namespace Kyew\PubSub;

use Kyew\EventPublisher;
use Kyew\EventSubscriber;

class InMemoryPubSub implements EventPublisher, EventSubscriber
{
    private $listeners = [];

    /**
     * @inheritdoc
     */
    public function publish(string $event, $data)
    {
        if (!isset($this->listeners[$event])) {
            return;
        }

        foreach ($this->listeners[$event] as $listener) {
            call_user_func($listener, $data);
        }
    }

    /**
     * @inheritdoc
     */
    public function on(string $event, callable $callback)
    {
        $this->listeners[$event][] = $callback;
    }
}
