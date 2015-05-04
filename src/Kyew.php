<?php

namespace Kyew;

use Predis\Client;
use SuperClosure\Serializer;

class Kyew
{
    /**
     * @var bool
     */
    private $autoStartWorkers;
    private $binDir;
    private $workers = [];

    public function __construct(Client $redis, $autoStartWorkers = true)
    {
        $this->binDir = realpath(__DIR__ . '/../bin');
        $this->serializer = new Serializer();
        $this->publisher = $redis;
        $this->autoStartWorkers = $autoStartWorkers;
    }

    /**
     * Queue a job
     *
     * @param callable $job
     */
    public function queue(callable $job)
    {
        // Put the job in the database
        $jobId = uniqid('kew.job.', true);
        $this->publisher->set($jobId, $this->serializer->serialize($job));

        // Announce that the job is waiting to be processed
        $this->publisher->publish('kyew.job.new', $jobId);
    }

    /**
     * Queue one or more jobs, wait for them to finish, then execute a callback
     *
     * @param array|callable $jobs
     * @return mixed Array of return values from the jobs with matching keys
     */
    public function await($jobs)
    {
        if ($this->autoStartWorkers) {
            $this->startWorkers(count($jobs));
        }

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

        // Return the responses
        $result = [];
        foreach (array_keys($jobs) as $key) {
            $result[$key] = $this->publisher->get($batchId . '.' . $key);
            $this->publisher->del($batchId . '.' . $key);
        }
        return $result;
    }

    /**
     * Start a number of workers
     *
     * @param $num
     */
    private function startWorkers($num)
    {
        foreach (range(1, $num) as $i) {
            $this->startWorker();
        }
    }

    /**
     * Start a worker and save its PID to memory
     */
    private function startWorker()
    {
        $this->workers[] = exec('php ' . $this->binDir . '/worker > /dev/null 2>&1 & echo $!; ', $output);
    }

    /**
     * @param $pid
     */
    private function stopWorker($pid)
    {
        $this->publisher->publish('kyew.worker.shutdown', $pid);
    }

    /**
     * Stop all the workers
     */
    public function __destruct()
    {
        foreach ($this->workers as $worker) {
            $this->stopWorker($worker);
        }
    }
}
