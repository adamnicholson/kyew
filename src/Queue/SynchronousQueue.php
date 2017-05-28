<?php

namespace Kyew\Queue;

use Kyew\PubSub;
use Kyew\Queue;

class SynchronousQueue implements Queue
{
    /**
     * @var PubSub
     */
    private $publisher;

    /**
     * SynchronousQueue constructor.
     * @param PubSub $publisher
     */
    public function __construct(PubSub $publisher)
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
