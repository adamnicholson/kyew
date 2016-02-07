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

        $task = new Task($this->subscriber, $id);

        $this->queue->push($id, $callback);

        return $task;
    }
}
