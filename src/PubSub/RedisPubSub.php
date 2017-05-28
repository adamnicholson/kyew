<?php

namespace Kyew\PubSub;

use Kyew\PubSub;
use Predis\Client;

class RedisPubSub implements PubSub
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
        $this->client->set($event, serialize($data));
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

        $value = unserialize($value);

        foreach ($this->listeners[$event] ?? [] as $listener) {
            $listener($value);
        }

        $this->client->del([$event]);
    }
}
