<?php

namespace Kyew\PubSub;

class InMemoryPubSubTest extends \PHPUnit_Framework_TestCase
{
    public function test_published_messages_fire_event_listeners()
    {
        $pubsub = new InMemoryPubSub;

        $value = null;
        $pubsub->on('foo', function ($data) use (&$value) {
            $value = $data;
        });

        $pubsub->publish('foo', 'bar');

        $this->assertEquals('bar', $value);
    }
}
