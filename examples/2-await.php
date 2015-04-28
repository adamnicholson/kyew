<?php

require __DIR__ . '/../vendor/autoload.php';
$startAt = microtime(true);

$kyew = new \Kyew\Kyew(new Predis\Client([
    "scheme" => "tcp",
    "host" => "127.0.0.1",
    "port" => 6379
]));

// Put some jobs into an array
$jobs = [];
foreach (range(1, 4) as $i) {
    $jobs[] = function() {
        // Do some slow thing
        sleep(5);
    };
}

// Execute the queue via the workers and wait for the response
$kyew->await($jobs);

echo "Finished in  " . (microtime(true) - $startAt) . " seconds" . PHP_EOL;
