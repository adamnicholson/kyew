<?php

namespace Kyew\Deamon;

use Kyew\Deamon\Subscriber;
use Predis\PubSub\Consumer;

class Master extends Subscriber
{
    private $consoleChannel = 'console';

    protected function handleMessage($message)
    {
        switch ($message->channel) {
            case $this->channel:
                $this->handleJob($message);
                break;
            case $this->consoleChannel:
                $this->handleConsoleMessage($message);
        }

    }

    private function handleJob($message)
    {
        // Put the job in the database
        $jobId = uniqid('job-', true);
        $this->publisher->set($jobId, $message->payload);

        // Announce that the job is waiting to be processed
        $this->publisher->publish('queue-job-waiting', $jobId);
    }

    private function handleConsoleMessage($message)
    {
        echo "Console: " . $message->payload . PHP_EOL;
    }

    protected function subscribe(Consumer $pubsub)
    {
        $pubsub->subscribe($this->channel);
        $pubsub->subscribe($this->consoleChannel);
    }
}
