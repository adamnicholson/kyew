<?php

namespace Kyew;

class SlaveJobRunner extends Subscriber
{
    protected function handleMessage($message)
    {
        // Get the payload if it hasn't been deleted by another worker. Then delete it. del() returns
        // an int 1 if it was successful. If another worker has managed to del() the job it will return
        // 0. This is a lock to ensure jobs can only be processed once.
        if ($payload = $this->publisher->get($message->payload) && $this->publisher->del($message->payload)) {
            echo "Processing job {$message->payload} ...";
            sleep(5);
            echo "Done" . PHP_EOL;
            $this->publisher->publish('console', "Completed {$message->payload}");
        }
    }
}
