<?php

require __DIR__ . '/../vendor/autoload.php';

// Start the stopwatch
$time_start = microtime(true);

$publisher = new Predis\Client([
    "scheme" => "tcp",
    "host" => "127.0.0.1",
    "port" => 6379
]);

$queue = function(\Kyew\Job $job) use ($publisher) {
    $publisher->publish('queue', serialize($job));
};

// Set a counter which we can refer to, to see how many of the commands have completed
$publisher->set('SleepJobsRan', 0);

$queue(new \Kyew\Examples\SleepJob());
$queue(new \Kyew\Examples\SleepJob());
$queue(new \Kyew\Examples\SleepJob());
$queue(new \Kyew\Examples\SleepJob());

// Wait them to all execute
while ($publisher->get('SleepJobsRan') < 4) {
    usleep(500);
}

// Stop the stopwatch
$time_end = microtime(true);
$time = $time_end - $time_start;
echo "Executed 20 seconds worth of sleep() executions in $time seconds\n";
