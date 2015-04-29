# Kyew

Small queue package to make asynchronously processing tasks in PHP simple.

Just start the deamon

```
php kyew --workers=4
```

Then queue something

```php
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

// The above callback will by queued then executed by a different PHP process,
// so you can continue your with your request without waiting
```

What about if you just want loads of tasks to run asynchronously?

```php
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

// With 4 workers, the above will only take 4 seconds to complete,
// whereas with normal blocking PHP it would take 20 seconds
```
