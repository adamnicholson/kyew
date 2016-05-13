<?php

namespace Kyew\PubSub;

use Predis\Client;
use Predis\Connection\ConnectionException;

class RedisPubSubTest extends \PHPUnit_Framework_TestCase
{
    public function test_published_messages_fire_event_listeners_when_rechech_called()
    {
        $redis = new Client;
        $pubsub = new RedisPubSub($redis);
        try {
            $redis->connect();
        } catch (ConnectionException $e) {
            $this->markTestSkipped("Test skipped because no Redis server is running on default ports");
        }

        $value = null;
        $pubsub->on('foo', function ($data) use (&$value) {
            $value = $data;
        });

        $pubsub->publish('foo', 'bar');
        $pubsub->recheck('foo');

        $this->assertEquals('bar', $value);
    }
}
