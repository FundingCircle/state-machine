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
    public function dispatch($eventName, Event $event = null, &$messages = [])
    {
        if ($event instanceof TransitionEvent) {
            if (null === $event) {
                $event = new Event();
            }

            foreach ($this->getListeners($eventName) as $listener) {
                $return = call_user_func($listener, $event, $eventName);
                $this->bindMessages($event, $messages);
                if (false === $return) {
                    $event->rejectTransition($listener);

                    return false;
                }
            }

            return true;
        }

        throw new \Exception('Event should be instance of '.TransitionEvent::class);
    }

    /**
     * @param TransitionEvent $event
     * @param array           $messages
     */
    private function bindMessages(TransitionEvent $event, &$messages)
    {
        $messages = array_merge($messages, $event->getMessages());
        $event->clearMessages();
    }
}
