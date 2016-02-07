<?php

namespace Kyew;

use Illuminate\Container\Container;
use Illuminate\Queue\SyncQueue;
use Kyew\PubSub\InMemoryPubSub;
use Kyew\Queue\IlluminateQueue;
use Kyew\Queue\IlluminateQueueHandler;
use PHPUnit_Framework_TestCase;

class IlluminateQueueTest extends PHPUnit_Framework_Testcase
{
    public function test_queue_with_synchronous_illuminate_queue_driver()
    {
        $pubsub = new InMemoryPubSub();

        $container = new Container;
        $container->singleton(IlluminateQueueHandler::class, function () use ($pubsub) {
            return new IlluminateQueueHandler($pubsub);
        });

        $driver = new SyncQueue();
        $driver->setContainer($container);

        $queue = new IlluminateQueue($driver);
        $queue->push('random-task-id', function () {
            return 'foo!bar!';
        });
    }
}
