<?php

namespace Kyew\Queue;

use Kyew\EventPublisher;
use SuperClosure\Serializer;

class IlluminateQueueHandler
{
    private $serializer;
    /**
     * @var EventPublisher
     */
    private $publisher;

    /**
     * IlluminateQueueHandler constructor.
     * @param EventPublisher $publisher
     * @param Serializer $serializer
     */
    public function __construct(EventPublisher $publisher, Serializer $serializer)
    {
        $this->publisher = $publisher;
        $this->serializer = $serializer;
    }

    /**
     * @param \Illuminate\Contracts\Queue\Job $job
     * @param array $data
     * @return mixed
     */
    public function fire($job, array $data)
    {
        $id = $data[0];
        $task = $this->serializer->unserialize($data[1]);

        $response = call_user_func($task);

        $job->delete();

        $this->publisher->publish("kyew:task:{$id}", $response);
    }
}
