# Kyew

> This is a work in progress.

Kyew is a thin layer on top of your existing *queue* package allowing you to push tasks to the queue and await the task completing.

Some examples where this could be useful include:

- Executing multiple tasks asynchronously
- Pushing resource intensive tasks to a more performant server

## Requirements

- PHP7
- A queue package (see [supported queues pacakges](#))
- Redis server

## Example

@todo

## Installation

@todo

## API

### `Kyew::async(callable $task)`
`async` accepts a single callable as its only parameter and will return an instance of `Task`. 

```php
$task = $kyew->async(function () {
    // Do some slow CPU intensive operation
    return 'foo';
});
```
The callable is immediately handed to the queue library to be executed. The `Task` instance will listen to the queue process and be notified when the callable has finished executing. 

### `Task::await()`
`await` will block further code execution until the given Task has completed exectuing.

```php
$task->await();
echo $task->getReturnValue(); // (string) "foo"
```

## Contributing

We welcome any contributions to Kyew. They can be made via GitHub issues or pull requests.

## License

Kyew is licensed under the MIT License

## Author

Adam Nicholson - adamnicholson10@gmail.com
