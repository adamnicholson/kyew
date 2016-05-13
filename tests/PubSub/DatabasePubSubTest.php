<?php

namespace Kyew\PubSub;

class DatabasePubSubTest extends \PHPUnit_Framework_TestCase
{
    public function test_published_messages_fire_event_listeners_when_rechech_called()
    {
        $pdo = new \PDO('sqlite::memory:');
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        $pdo->exec("CREATE TABLE kyew_pubsub (key VARCHAR, value VARCHAR)");

        $pubsub = new DatabasePubSub($pdo, 'kyew_pubsub');

        $value = null;
        $pubsub->on('foo', function ($data) use (&$value) {
            $value = $data;
        });

        $pubsub->publish('foo', 'bar');
        $pubsub->recheck('foo');

        $this->assertEquals('bar', $value);
    }
}
