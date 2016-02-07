<?php

namespace Kyew\Queue;

use Kyew\Queue;
use SuperClosure\Serializer;

class IlluminateQueue implements Queue
{
    /**
     * @var \Illuminate\Contracts\Queue\Queue
     */
    private $illuminateQueue;
    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * SynchronousQueue constructor.
     * @param \Illuminate\Contracts\Queue\Queue $illuminateQueue
     * @param Serializer $serializer
     */
    public function __construct(\Illuminate\Contracts\Queue\Queue $illuminateQueue, Serializer $serializer = null)
    {
        $this->illuminateQueue = $illuminateQueue;
        $this->serializer = $serializer ?: new Serializer;
    }

    /**
     * @inheritdoc
     */
    public function push($taskId, callable $task)
    {
        $this->illuminateQueue->push(IlluminateQueueHandler::class, [
            $taskId,
            $this->serializer->serialize($task),
        ]);
    }
}
