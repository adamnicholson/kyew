<?php

namespace Kyew;

use Kyew\Exception\TaskNotCompletedException;
use Kyew\Exception\TimeoutException;

class Task
{
    /**
     * @var PubSub
     */
    private $subscriber;
    /**
     * @var string The raw task ID
     */
    private $taskId;
    /**
     * @var string The key used by the event subscriber to identify this task
     */
    private $subscriberKey;
    /**
     * @var bool Whether the task has completed
     */
    private $complete = false;
    /**
     * @var mixed The return value of the task
     */
    private $returnValue;
    /**
     * @var int
     */
    private $tick;

    /**
     * Task constructor.
     * @param PubSub $subscriber
     *
     * @param string $taskId
     *  The unique ID for this tasks
     *
     * @param int $tick
     *  In microseconds, how long to wait between each event loop wait when await() is
     *  called.
     */
    public function __construct(PubSub $subscriber, string $taskId, int $tick)
    {
        $this->subscriber = $subscriber;
        $this->taskId = $taskId;
        $this->subscriberKey = "kyew:task:{$this->taskId}";
        $this->tick = $tick;

        $this->subscriber->on($this->subscriberKey, function ($returnValue) {
            $this->returnValue = $returnValue;
            $this->complete = true;
        });
    }

    /**
     * Block further script execution until isComplete() returns true
     *
     * @param int $timeout Number of seconds to wait before throwing a TimeoutException
     * @return mixed The return value of the completed task
     * @throws TimeoutException
     */
    public function await($timeout = 30)
    {
        $started = time();
        while (true) {

            if (time() >= $started + $timeout) {
                throw new TimeoutException("Task execution has exceeded the {$timeout} second limit");
            }

            if ($this->isComplete()) {
                return $this->getReturnValue();
            }

            usleep($this->tick);
        }
    }

    /**
     * @return bool
     */
    public function isComplete(): bool
    {
        $this->subscriber->recheck($this->subscriberKey);

        return $this->complete;
    }

    /**
     * Get the return value of the completed task
     */
    public function getReturnValue()
    {
        if (!$this->isComplete()) {
            throw new TaskNotCompletedException('Cannot get the return value until the task has completed');
        }

        return $this->returnValue;
    }
}
