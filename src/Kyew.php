<?php

namespace Kyew;

use Predis\Client;
use SuperClosure\Serializer;

class Kyew
{
    public function __construct(Client $redis)
    {
        $this->serializer = new Serializer();
        $this->publisher = $redis;
    }

    public function queue(callable $job)
    {
        $this->publisher->publish('queue', $this->serializer->serialize($job));
    }
}
