<?php

namespace Kyew;

class Kyew
{
    /**
     * @var EventSubscriber
     */
    private $subscriber;
    /**
     * @var Queue
     */
    private $queue;
    /**
     * @var int
     */
    private $tick = 100000;

    /**
     * Kyew constructor.
     * @param EventSubscriber $subscriber
     * @param Queue $queue
     */
    public function __construct(EventSubscriber $subscriber, Queue $queue)
    {
        $this->subscriber = $subscriber;
        $this->queue = $queue;
    }

    /**
     * @param callable $callback A task to start processing in the background
     * @return Task
     */
    public function async(callable $callback): Task
    {
        $id = TaskIdFactory::new();

        $task = new Task($this->subscriber, $id, $this->tick);

        $this->queue->push($id, $callback);

        return $task;
    }

    /**
     * @param int $microseconds
     *  In microseconds, how long to wait between each event loop wait when Task::await() is
     *  called. Defaults to 100000 (0.1 second)
     */
    public function setAwaitTick(int $microseconds)
    {
        $this->tick = $microseconds;
    }
}
