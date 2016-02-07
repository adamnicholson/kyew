<?php

namespace Kyew\Queue;

use Kyew\EventPublisher;
use Kyew\Queue;

class SynchronousQueue implements Queue
{
    /**
     * @var EventPublisher
     */
    private $publisher;

    /**
     * SynchronousQueue constructor.
     * @param EventPublisher $publisher
     */
    public function __construct(EventPublisher $publisher)
    {
        $this->publisher = $publisher;
    }

    /**
     * @inheritdoc
     */
    public function push($taskId, callable $task)
    {
        $returnValue = call_user_func($task);

        $this->publisher->publish("kyew:task:$taskId", $returnValue);
    }
}
