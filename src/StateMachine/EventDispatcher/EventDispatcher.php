<?php

namespace StateMachine\EventDispatcher;

use StateMachine\Event\TransitionEvent;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcher as BaseEventDispatcher;

class EventDispatcher extends BaseEventDispatcher
{
    /**
     * {@inheritdoc}
     */
    public function dispatch($eventName, Event $event = null)
    {
        if ($event instanceof TransitionEvent) {
            if (null === $event) {
                $event = new Event();
            }

            foreach ($this->getListeners($eventName) as $listener) {
                $return = call_user_func($listener, $event, $eventName);
                if (false === $return) {
                    $event->rejectTransition($listener);

                    return false;
                }
            }

            return true;
        }

        throw new \Exception('Event should be instance of '.TransitionEvent::class);
    }
}
