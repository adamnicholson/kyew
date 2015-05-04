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
    $jobs[] = function() use ($i) {
        // Do some slow thing
        sleep(5);
        return "Job #$i";
    };
}

// Execute the jobs and wait for the response
$responses = $kyew->await($jobs);
// $responses = [0 => "Job #0", 1 => "Job #1", 2 => "Job #2", 3 => "Job #3"]

// A queue worker will automatically be started for each job, so the above
// will only take 4 seconds to complete, whereas with normal blocking PHP
// it would take 20 seconds

echo "Finished in  " . (microtime(true) - $startAt) . " seconds" . PHP_EOL;
