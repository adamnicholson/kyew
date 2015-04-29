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
    $jobs[] = function() use ($i) {
        // Do some slow thing
        sleep(5);
        return 'Job #' . $i;
    };
}

// Execute the queue via the workers and wait for the response
$response = $kyew->await($jobs);
// $response = [0 => "Job #1", 1 => "Job #2", 2 => "Job #3", 3 => "Job #4"]

echo "Finished in  " . (microtime(true) - $startAt) . " seconds" . PHP_EOL;
