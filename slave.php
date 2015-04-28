<?php

require __DIR__ . '/vendor/autoload.php';

$runner = new \Kyew\Deamon\Slave(
    new Predis\Client([
        "scheme" => "tcp",
        "host" => "127.0.0.1",
        "port" => 6379,
        "read_write_timeout" => -1
    ]),
    new Predis\Client([
        "scheme" => "tcp",
        "host" => "127.0.0.1",
        "port" => 6379,
        "read_write_timeout" => -1
    ]),
    'queue-job-waiting'
);

$runner->run();
