<?php

namespace Kyew;

use Predis\Client;
use SuperClosure\Serializer;

class Kyew
{
    public function __construct(Client $redis)
    {
        $this->serializer = new Serializer();
        $this->publisher = $redis;
    }

    /**
     * Queue a job
     *
     * @param callable $job
     */
    public function queue(callable $job)
    {
        $this->publisher->publish('queue', $this->serializer->serialize($job));
    }

    /**
     * Queue one or more jobs, wait for them to finish, then execute a callback
     *
     * @param array|callable $jobs
     * @param callable|null $next
     */
    public function await($jobs, callable $next = null)
    {
        // Only 1 job was passed; transform it into an array of 1 jobs
        if (is_callable($jobs)) {
            $jobs = [$jobs];
        }

        // Store a batch ID so we can track when all jobs are complete
        $batchId = uniqid('kyew.batch.', true);
        $this->publisher->set($batchId, 0);

        // Queue the jobs
        foreach ($jobs as $job) {
            $this->queue(function() use ($job, $batchId) {
                    $job();
                    $this->publisher->incr($batchId);
            });
        }

        // Wait for the jobs to all execute
        while ($this->publisher->get($batchId) < count($jobs)) {
            usleep(500);
        }

        // Fire the callback if passed
        if ($next) {
            return $next();
        }
    }
}
