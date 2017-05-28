<?php

namespace Kyew;

use Kyew\Exception\TimeoutException;
use Kyew\PubSub\InMemoryPubSub;
use Kyew\Queue\SynchronousQueue;
use PHPUnit_Framework_TestCase;
use Prophecy\Argument;

class KyewTest extends PHPUnit_Framework_Testcase
{
    /**
     * @var Kyew
     */
    private $kyew;

    public function setUp()
    {
        parent::setUp();

        $pubsub = new InMemoryPubSub;
        $this->kyew = new Kyew(
            $pubsub,
            new SynchronousQueue($pubsub)
        );
    }

    public function tearDown()
    {
        parent::tearDown();
        unset($this->kyew);
    }

    public function test_async_returns_task_instance()
    {
        $task = $this->kyew->async(function () {
            return 'Some return value';
        });

        $this->assertInstanceOf(Task::class, $task);
    }

    public function test_async_with_synchronous_queue_blocks_by_default()
    {
        $task = $this->kyew->async(function () {
            return 'Some return value!!!';
        });

        $this->assertTrue($task->isComplete());
        $this->assertEquals('Some return value!!!', $task->getReturnValue());
    }

    public function test_async_await_with_synchronous_queue_returns_expected_return_value()
    {
        $task = $this->kyew->async(function () {
            return 'Fizz buzz!';
        });

        $task->await();
        $this->assertTrue($task->isComplete());
        $this->assertEquals('Fizz buzz!', $task->getReturnValue());
    }

    public function test_async_await_directly_returns_the_return_value()
    {
        $task = $this->kyew->async(function () {
            return 'Fizz buzz!';
        });
        $this->assertEquals('Fizz buzz!', $task->await());
    }

    public function test_example_concurrent_http_requests()
    {
        $tasks = [];
        foreach (['http://google.com', 'http://bbc.co.uk', 'http://reddit.com'] as $url) {
            $tasks[$url] = $this->kyew->async(function () use ($url) {
                return file_get_contents($url);
            });
        }

        $pages = [
            'google' => $tasks['http://google.com']->await(),
            'bbc' => $tasks['http://bbc.co.uk']->await(),
            'yahoo' => $tasks['http://reddit.com']->await(),
        ];

        $this->assertRegExp('/<title>Google<\/title>/', $pages['google']);
        $this->assertRegExp('/<title>reddit/', $pages['yahoo']);
        $this->assertRegExp('/<title>BBC - Home<\/title>/', $pages['bbc']);
    }

    public function test_async_await_throws_timeout_exception_if_subscriber_does_not_trigger_completed_event()
    {
        $subscriber = $this->prophesize(PubSub::class);
        $queue = $this->prophesize(Queue::class);
        $this->kyew = new Kyew(
            $subscriber->reveal(),
            $queue->reveal()
        );

        $queue->push(Argument::any(), Argument::that(function (callable $task) {
            return true;
        }))->shouldBeCalled();

        $task = $this->kyew->async(function () {
            return 'Fizz buzz!';
        });

        $this->expectException(TimeoutException::class);
        $task->await(1);
    }
}
