<?php

namespace Kyew;

use Kyew\PubSub\InMemoryPubSub;
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
}
