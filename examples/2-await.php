<?php

require __DIR__ . '/../vendor/autoload.php';
$startAt = microtime(true);

$kyew = new \Kyew\Kyew(new Predis\Client([
    "scheme" => "tcp",
    "host" => "127.0.0.1",
    "port" => 6379
]));

// Put some jobs into an array. A "Job" is simply a PHP closure
$jobs = [];
for ($i = 0; $i < 4; $i++) {
    $jobs[] = function() {
        // Do some slow thing
        sleep(5);
    };
}

// Execute the jobs and wait for the response
$response = $kyew->await($jobs);
// $response = [0 => "Job #1", 1 => "Job #2", 2 => "Job #3", 3 => "Job #4"]

// A queue worker will automatically be started for each job, so the above
// will only take 4 seconds to complete, whereas with normal blocking PHP
// it would take 20 seconds

echo "Finished in  " . (microtime(true) - $startAt) . " seconds" . PHP_EOL;
