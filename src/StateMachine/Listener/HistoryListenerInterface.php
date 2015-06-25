<?php

namespace StateMachine\Listener;

use StateMachine\Event\TransitionEvent;
use StateMachine\Transition\TransitionInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

interface HistoryListenerInterface
{
    /**
     * Write history for transitions changes
     *
     * @param TransitionEvent          $transitionEvent
     * @param string                   $eventName
     * @param EventDispatcherInterface $eventDispatcher
     *
     * @return TransitionInterface
     */
    public function onHistoryChange(
        TransitionEvent $transitionEvent,
        $eventName,
        EventDispatcherInterface $eventDispatcher
    );
}
