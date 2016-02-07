<?php

namespace Kyew;

interface EventSubscriber
{
    /**
     * Listen for an event, and trigger a callback when fired.
     *
     * <code>
     *  $subscriber->on('foo-event', function ($data) {
     *      echo "foo-event was triggered with data " . json_encode($data);
     *  });
     * </code>
     *
     * @param string $event
     * @param callable $callback Callback to run on receiving the message. The published data will be passed as the
     *                           first argument to the callback.
     * @return mixed
     */
    public function on($event, callable $callback);

    /**
     * Re-check to see if any events have been fired that have not already been noticed
     *
     * This can be used if the event may have been fired prior to listening for the event with on().
     *
     * It is also useful for EventSubscriber implementations which cannot fully implement the Pub/Sub
     * pattern, meaning you must manually tell the subscriber to re-check if the event has been
     * fired yet or not.
     *
     * Eg. with the PdoPubSub implementation, EventPublisher::publish() stores the event in a table,
     * and recheck() will check if that event key exists in the table, and if so fire the on() listeners.
     *
     * @param $event
     * @return mixed
     */
    public function recheck($event);
}
