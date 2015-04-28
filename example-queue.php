<?php

require __DIR__ . '/vendor/autoload.php';

$client = new Predis\Client([
    "scheme" => "tcp",
    "host" => "127.0.0.1",
    "port" => 6379
]);

$client->publish('queue', 'Some job');
