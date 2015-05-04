<?php

namespace Kyew\Deamon;

use Predis\Client;
use Predis\PubSub\Consumer;

class Worker
{
    /**
     * @var Consumer
     */
    private $pubSubLoop;
    /**
     * @var \Predis\Client
     */
    protected $subscriber;
    protected $publisher;
    /**
     * @var string Channel to listen on
     */
    protected $channel;
    private $shutdownChannel;

    public function __construct(Client $subscriber, Client $publisher, $channel, $shutdownChannel)
    {
        $this->subscriber = $subscriber;
        $this->publisher =$publisher;
        $this->channel = $channel;
        $this->shutdownChannel = $shutdownChannel;
    }

    /**
     * Run the daemon
     */
    public function run()
    {
        $this->pubSubLoop = $this->subscriber->pubSubLoop();

        $this->subscribe($this->pubSubLoop);

        foreach ($this->pubSubLoop as $message) {
            switch ($message->kind) {

                case 'subscribe':
                    echo "Subscribed to {$message->channel}", PHP_EOL;
                    break;

                case 'message':
                    if ($message->channel == 'control_channel') {
                        if ($message->payload == 'quit_loop') {
                            $this->shutdown();
                        } else {
                            throw new \Exception("Received an unrecognized command: {$message->payload}");
                        }
                    } elseif ($message->channel == $this->shutdownChannel) {
                        if ($message->payload == getmypid()) {
                            $this->shutdown();
                        }
                    } {
                        $this->handleMessage($message);
                    }
                    break;
            }
        }

        // Always unset the pubsub consumer instance when you are done! The
        // class destructor will take care of cleanups and prevent protocol
        // desynchronizations between the client and the server.
        unset($this->pubSubLoop);
    }

    /**
     * @param Consumer $pubsub
     */
    private function subscribe(Consumer $pubsub)
    {
        $pubsub->subscribe($this->channel);
        $pubsub->subscribe($this->shutdownChannel);
    }

    /**
     * Shut down
     */
    private function shutdown()
    {
        $this->pubSubLoop->unsubscribe();
        exit;
    }

    /**
     * Handle a message
     *
     * @param $message
     * @return mixed
     */
    private function handleMessage($message)
    {
        // Get the payload if it hasn't been deleted by another worker. Then delete it. del() returns
        // an int 1 if it was successful. If another worker has managed to del() the job it will return
        // 0. This is a lock to ensure jobs can only be processed once.
        if (($payload = $this->publisher->get($message->payload)) && $this->publisher->del($message->payload)) {

            $handler = unserialize($payload);
            call_user_func($handler);

            $this->publisher->publish('console', "Completed {$message->payload}");
        }
    }
}
