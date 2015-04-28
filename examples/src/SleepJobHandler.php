<?php

namespace Kyew\Examples;

use Predis\Client;

class SleepJobHandler
{
    public function handle(SleepJob $job)
    {
        sleep(5);

        $publisher = new Client([
            "scheme" => "tcp",
            "host" => "127.0.0.1",
            "port" => 6379
        ]);

        $publisher->incr('SleepJobsRan');
    }
}
