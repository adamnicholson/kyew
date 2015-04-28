<?php

namespace Kyew\Deamon;

use Predis\Client;
use Predis\PubSub\Consumer;

abstract class Subscriber
{
    /**
     * @var \Predis\Client
     */
    protected $subscriber;
    protected $publisher;
    /**
     * @var string Channel to listen on
     */
    protected $channel;

    public function __construct(Client $subscriber, Client $publisher, $channel)
    {
        $this->subscriber = $subscriber;
        $this->publisher =$publisher;
        $this->channel = $channel;
    }

    /**
     * Run the deamon
     */
    public function run()
    {
        $pubsub = $this->subscriber->pubSubLoop();

        $this->subscribe($pubsub);

        foreach ($pubsub as $message) {
            switch ($message->kind) {

                case 'subscribe':
                    echo "Subscribed to {$message->channel}", PHP_EOL;
                    break;

                case 'message':
                    if ($message->channel == 'control_channel') {
                        if ($message->payload == 'quit_loop') {
                            echo "Aborting pubsub loop...", PHP_EOL;
                            $pubsub->unsubscribe();
                        } else {
                            echo "Received an unrecognized command: {$message->payload}.", PHP_EOL;
                        }
                    } else {
                        $this->handleMessage($message);
                    }
                    break;
            }
        }

        // Always unset the pubsub consumer instance when you are done! The
        // class destructor will take care of cleanups and prevent protocol
        // desynchronizations between the client and the server.
        unset($pubsub);
    }

    /**
     * @param Consumer $pubsub
     */
    protected function subscribe(Consumer $pubsub)
    {
        $pubsub->subscribe($this->channel);
    }

    /**
     * Handle a message
     *
     * @param $message
     * @return mixed
     */
    abstract protected function handleMessage($message);
}
