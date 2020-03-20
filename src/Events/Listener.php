<?php

namespace Api\Events;

use League\Event\AbstractListener;

use League\Event\EventInterface;

class Listener extends AbstractListener
{
    public function handle(EventInterface $event)
    {
        $action = $event->getPayload()->getAction();

        if (method_exists($this, $action)) {
            $this->{$action}($event);
        }
    }
}
