<?php

namespace Kyew;

use Kyew\Exception\TaskNotCompletedException;
use Kyew\Exception\TimeoutException;

class Task
{
    /**
     * @var EventSubscriber
     */
    private $subscriber;
    /**
     * @var string
     */
    private $taskId;
    /**
     * @var bool Whether the task has completed
     */
    private $complete = false;
    /**
     * @var mixed The return value of the task
     */
    private $returnValue;

    /**
     * Task constructor.
     * @param EventSubscriber $subscriber
     * @param string $taskId
     */
    public function __construct(EventSubscriber $subscriber, string $taskId)
    {
        $this->subscriber = $subscriber;
        $this->taskId = $taskId;

        $this->subscriber->on("kyew:task:{$this->taskId}", function ($returnValue) {
            $this->returnValue = $returnValue;
            $this->complete = true;
        });
    }

    /**
     * Block further script execution utnil isComplete() returns true
     * @param int $timeout Number of seconds to wait before throwing a TimeoutException
     * @return mixed The return value of the completed task
     * @throws TimeoutException
     */
    public function await($timeout = 30)
    {
        $started = time();
        while (true) {

            if (time() >= $started + $timeout) {
                // @todo Improve exception message
                throw new TimeoutException;
            }

            if ($this->isComplete()) {
                return $this->getReturnValue();
            }
        }
    }

    /**
     * @return bool
     */
    public function isComplete(): bool
    {
        return $this->complete;
    }

    /**
     * Get the return value of the completed task
     */
    public function getReturnValue()
    {
        if (!$this->isComplete()) {
            // @todo Improve exception message
            throw new TaskNotCompletedException('Cannot get the return value until the task has completed');
        }

        return $this->returnValue;
    }
}
