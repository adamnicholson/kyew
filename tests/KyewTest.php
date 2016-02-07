<?php

namespace Kyew;

use Kyew\Queue\SynchronousQueue;
use PHPUnit_Framework_TestCase;

class KyewTest extends PHPUnit_Framework_Testcase
{
    /**
     * @var Kyew
     */
    private $kyew;

    public function setUp()
    {
        parent::setUp();

        $pubsub = new class implements  EventPublisher, EventSubscriber {
            private $listeners = [];
            public function publish(string $event, $data) {
                if (isset($this->listeners[$event])) {
                    foreach ($this->listeners[$event] as $listener) {
                        call_user_func($listener, $data);
                    }
                }
            }
            public function on(string $event, callable $callback) {
                $this->listeners[$event][] = $callback;
            }
        };

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
}
