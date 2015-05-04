# Kyew

Small queue package to make asynchronously processing tasks in PHP simple.

Tasks are put into a Redis queue, which are then executed by individual PHP processes. The queue workers can either be manually created using the daemon, or can be automatically started when required.

Requirements

- Redis server

## Example

### Asynchronous tasks
```php
$redis = new Predis\Client([
    "scheme" => "tcp",
    "host" => "127.0.0.1",
    "port" => 6379
]);

$kyew = new \Kyew\Kyew($redis);

// Put some jobs into an array. A "Job" is simply a PHP closure
$jobs = [];
for ($i = 0; $i < 4; $i++) {
    $jobs[] = function() {
        // Do some slow thing
        sleep(5);
    };
}

// Execute the jobs and wait for the response
$kyew->await($jobs);

// A queue worker will automatically be started for each job, so the above 
// will only take 4 seconds to complete, whereas with normal blocking PHP 
// it would take 20 seconds
```

### The Daemon
Use the deamon if you'd prefer to control the workers manually, rather than letting Kyew automatically spawn a worker for each job.

Start the daemon with `5` workers:

```
vendor/bin/kyew 5
```

Then disable automatically starting workers when you instantiate Kyew using the second constructor argument:

```php
$redis = new Predis\Client([
    "scheme" => "tcp",
    "host" => "127.0.0.1",
    "port" => 6379
]);

$kyew = new \Kyew\Kyew($redis, false);
```

## Contributing

We welcome any contributions to Kyew. They can be made via GitHub issues or pull requests.

## License

Kyew is licensed under the MIT License

## Author

Adam Nicholson - adamnicholson10@gmail.com
