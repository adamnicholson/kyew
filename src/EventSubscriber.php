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
}
