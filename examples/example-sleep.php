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

// Set a counter which we can refer to, to see how many of the commands have completed
$publisher->set($counterKey = 'job.sleep.' . getmypid(), 0);

// Queue some jobs
foreach (range(1, 4) as $i) {
    var_dump($i);
    // Do this 4 times. Each time sleeps for 5 seconds, so this would normally take
    // 20 seconds to finish
    $kyew->queue(function() use ($publisher, $counterKey) {
        sleep(5);
        $publisher->incr($counterKey);
    });
}

// Wait them to all execute
while ($publisher->get($counterKey) < 4) {
    usleep(500);
}

// Stop the stopwatch
$time_end = microtime(true);
$time = $time_end - $time_start;
echo "Executed 20 seconds worth of sleep() executions in $time seconds\n";
