<?php

require __DIR__ . '/../vendor/autoload.php';
$startAt = microtime(true);

$kyew = new \Kyew\Kyew(new Predis\Client([
    "scheme" => "tcp",
    "host" => "127.0.0.1",
    "port" => 6379
]));

// Queue a job
$kyew->queue(function() {
    // Do something that is really slow
    sleep(15);
});

echo "Finished in  " . (microtime(true) - $startAt) . " seconds" . PHP_EOL;
