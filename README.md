# Kyew

[![Build Status](https://travis-ci.org/adamnicholson/kyew.svg?branch=master)](https://travis-ci.org/adamnicholson/kyew)

Kyew is a thin layer on top of your existing *queue* package allowing you to push tasks to the queue and await the task completing.

Some examples where this could be useful include:

- Executing multiple tasks concurrently
- Pushing resource intensive tasks to a more performant server

## Requirements

- PHP7.0+
- A queue package (see [supported queues packages](#supported-queue-packages))

## Example

### Await a single task

```php
$task = $kyew->async(function () {
    // Do some slow I/O operation
    return 'foo';
});
$response = $task->await(); // (string) "foo"
```

### Execute multiple tasks simultaneously

```php
$google = $kyew->async(function () {
    return file_get_contents('http://google.com');
});

$bbc = $kyew->async(function () {
    return file_get_contents('http://bbc.co.uk');
});

// Both closures have already started executing in the background

$google->await(); // (string) HTML source for Google's homepage 
$bbc->await(); // (string) HTML source for BBC's homepage 
```

## How it Works

When tasks are passed to `$task = $kyew->async($task)`, they are immediately handed off to the underlying queue package, along with an additional instruction to then store the task return value back into a persistance layer (eg. a database).

On calling `$task->await()`, we simply sit in a loop until either that value appears in the persistance layer, or until we reach the timeout threshold.

## Installation

Kyew can be installed with Composer

```
composer require adamnicholson/kyew
```

Kyew has two dependencies:

- A queue driver which [implements the `Kyew\Queue` interface](#supported-queue-packages)); used to hand jobs to your queue
- A pub-sub driver which [implements the `Kyew\PubSub` interface](#supported-persistence-layers); used to pass data between processes

The below example uses the `InMemoryPubSub` and `SynchronousQueue` queue - you should use implementations which suit your environment. 

```php
$pubsub = new Kyew\PubSub\InMemoryPubSub;
$kyew = new Kyew\Kyew(
    $pubsub,
    new Kyew\Queue\SynchronousQueue($pubsub)
);

$task = $this->kyew->async(function () {
    return 'Some return value!!!';
});
echo $task->await(); // string 'Some return value!!!'
```

## API

### `Kyew::async(callable $task): Task`
`async` accepts a single callable as its only parameter and will return an instance of `Task`. 

```php
$task = $kyew->async(function () {
    // Do some slow I/O operation
    return 'foo';
});
```
The callable is immediately handed to the queue library to be executed. The `Task` instance will listen to the queue process and be notified when the callable has finished executing. 

### `Task::await(): void`
`await` will block further code execution until the given Task has completed exectuing.

```php
$response = $task->await();
echo $response; // (string) "foo"
```

## Supported Queue Packages

- `IlluminateQueue`: Execute tasks in Laravel's (Illuminate) queue package
- `SynchronousQueue`: Execute tasks synchronously in the current process, mostly only used for testing

## Supported persistence layers

- `DatabasePubSub`: Store task return values in a `PDO` compatible SQL database
- `RedisPubSub`: Store task return values in a redis server
- `InMemoryPubSub`: Store task return values in an in-memory PHP array, mostly only used for testing

## Contributing

We welcome any contributions to Kyew. They can be made via GitHub issues or pull requests.

## License

Kyew is licensed under the MIT License

## Author

Adam Nicholson - adamnicholson10@gmail.com
