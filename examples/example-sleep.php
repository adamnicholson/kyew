<?php

require __DIR__ . '/../vendor/autoload.php';

// Start the stopwatch
$time_start = microtime(true);

$publisher = new Predis\Client([
    "scheme" => "tcp",
    "host" => "127.0.0.1",
    "port" => 6379
]);

$kyew = new \Kyew\Kyew($publisher);

// Queue some jobs
$jobs = [];
foreach (range(1, 4) as $i) {
    $jobs[] = function() use ($publisher) {
        sleep(5);
    };
}

$kyew->await($jobs);

echo "All " . count($jobs) . " jobs executed" . PHP_EOL;

// Stop the stopwatch
$time_end = microtime(true);
$time = $time_end - $time_start;
echo "Executed 20 seconds worth of sleep() executions in $time seconds" . PHP_EOL;
