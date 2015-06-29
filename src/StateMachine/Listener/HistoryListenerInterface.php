<?php

namespace StateMachine\Listener;

use StateMachine\Event\TransitionEvent;
use StateMachine\Transition\TransitionInterface;

interface HistoryListenerInterface
{
    /**
     * Write history for transitions changes
     *
     * @param TransitionEvent $transitionEvent
     *
     * @return TransitionInterface
     */
    public function onHistoryChange(TransitionEvent $transitionEvent);
}
