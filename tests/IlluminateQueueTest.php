<?php

namespace Kyew;

use Illuminate\Container\Container;
use Illuminate\Queue\SyncQueue;
use Kyew\PubSub\InMemoryPubSub;
use Kyew\Queue\IlluminateQueue;
use Kyew\Queue\IlluminateQueueHandler;
use PHPUnit_Framework_TestCase;
use SuperClosure\Serializer;

class IlluminateQueueTest extends PHPUnit_Framework_Testcase
{
    public function test_queue_with_synchronous_driver_and_in_memory_pubsub_fires_event_with_expected_return_value()
    {
        $pubsub = new InMemoryPubSub;

        $container = new Container;
        $container->singleton(IlluminateQueueHandler::class, function () use ($pubsub) {
            return new IlluminateQueueHandler($pubsub, new Serializer);
        });

        $driver = new SyncQueue();
        $driver->setContainer($container);

        $called = false;
        $pubsub->on('kyew:task:random-task-id', function ($publishedValue) use (&$called) {
            $this->assertEquals('foo!bar!', $publishedValue);
            $called = true;
        });

        $queue = new IlluminateQueue($driver, new Serializer);
        $queue->push('random-task-id', function () {
            return 'foo!bar!';
        });

        $this->assertTrue($called);
    }
}
