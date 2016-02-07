<?php

namespace Kyew;

interface EventPublisher
{
    /**
     * Publish a message.
     *
     * <code>
     *  $publisher->publish('order-event', ['order-id' => 1, 'event-type' => 'dispatched']);
     * </code>
     *
     * @param string $event
     * @param $data
     * @return mixed
     */
    public function publish($event, $data);
}
