<?php
namespace StateMachine\EventDispatcher;

use StateMachine\Event\TransitionEvent;
use StateMachine\History\StateChange;
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
                if (!$return) {
                    $event->rejectTransition($listener);

                    return false;
                }
            }

            return true;
        } elseif ($event instanceof StateChange) {
            foreach ($this->getListeners($eventName) as $listener) {
                $event = call_user_func($listener, $event, $eventName);
                if ($event->isPropagationStopped()) {
                    break;
                }
            }

            return $event;
        }

        throw new \Exception("Event should be instance of ".TransitionEvent::class);
    }
}
