<?php

namespace Kyew;

interface Queue
{
    /**
     * Push a job onto the Queue stack.
     *
     * @param string $taskId
     * @param callable $task
     */
    public function push($taskId, callable $task);
}
