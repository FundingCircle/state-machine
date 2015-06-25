<?php


namespace StateMachine\Listener;

use StateMachine\Event\TransitionEvent;
use StateMachine\StateMachine\StateMachineHistoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class HistoryListener implements HistoryListenerInterface
{
    /**
     * {@inheritdoc}
     */
    public function onHistoryChange(
        TransitionEvent $transitionEvent,
        $eventName,
        EventDispatcherInterface $eventDispatcher
    ) {
        $stateMachine = $transitionEvent->getObject()->getStateMachine();
        if ($stateMachine instanceof StateMachineHistoryInterface) {
            $transition = $transitionEvent->getTransition();
            $stateMachine->getHistory()->add($transition);
        }

        return $transition;
    }
}
