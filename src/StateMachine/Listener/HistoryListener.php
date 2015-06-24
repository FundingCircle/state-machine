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
            $transition->setObjectClass(get_class($transitionEvent->getObject()));
            $transition->setIdentifier($transitionEvent->getObject()->getId());
            $transition->setPassed(!$transitionEvent->isTransitionRejected());
            $transition->setFailedCallBack($transitionEvent->getFailedCallback());

            $stateMachine->getHistory()->add($transition);
        }
    }

}
