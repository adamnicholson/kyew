<?php

namespace Kyew\PubSub;

use Kyew\EventPublisher;
use Kyew\EventSubscriber;
use Predis\Client;

class RedisPubSub implements EventPublisher, EventSubscriber
{
    /**
     * @var array
     */
    private $listeners = [];
    /**
     * @var Client
     */
    private $client;

    /**
     * RedisPubSub constructor.
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @inheritdoc
     */
    public function publish($event, $data)
    {
        $this->client->set($event, $data);
    }

    /**
     * @inheritdoc
     */
    public function on($event, callable $callback)
    {
        $this->listeners[$event][] = $callback;
    }

    /**
     * @inheritdoc
     */
    public function recheck($event)
    {
        $value = $this->client->get($event);

        if (!$value) {
            return;
        }

        foreach ($this->listeners[$event] ?? [] as $listener) {
            $listener($value);
        }

        $this->client->del([$event]);
    }
}
