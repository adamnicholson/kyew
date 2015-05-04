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
     * @return mixed Array of return values from the jobs with matching keys
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
        foreach ($jobs as $i => $job) {
            $this->queue(function() use ($job, $batchId, $i) {
                    $response = $job();
                    $this->publisher->set($batchId . '.' . $i, $response);
                    $this->publisher->incr($batchId);
            });
        }

        // Wait for the jobs to all execute
        while ($this->publisher->get($batchId) < count($jobs)) {
            usleep(500);
        }
        $this->publisher->del($batchId);

        // Fire the callback if passed
        if ($next) {
            $next();
        }

        // Return the responses
        $result = [];
        foreach (array_keys($jobs) as $key) {
            $result[$key] = $this->publisher->get($batchId . '.' . $key);
            $this->publisher->del($batchId . '.' . $key);
        }
        return $result;
    }
}
