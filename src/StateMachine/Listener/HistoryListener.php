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
    public function onHistoryChange(TransitionEvent $transitionEvent)
    {
        $stateMachine = $transitionEvent->getObject()->getStateMachine();
        $transition = $transitionEvent->getTransition();
        if ($stateMachine instanceof StateMachineHistoryInterface) {
            $stateMachine->getHistory()->add($transition);
        }

        return $transition;
    }
}
