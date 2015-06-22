<?php

namespace StateMachine\Listener;

use StateMachine\Event\TransitionEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

interface HistoryListenerInterface
{
    /**
     * @param TransitionEvent          $transitionEvent
     * @param string                   $eventName
     * @param EventDispatcherInterface $eventDispatcher
     *
     * @return mixed
     */
    public function onHistoryChange(
        TransitionEvent $transitionEvent,
        $eventName,
        EventDispatcherInterface $eventDispatcher
    );
}
