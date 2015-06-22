<?php


namespace StateMachine\Listener;

use StateMachine\Event\Events;
use StateMachine\Event\TransitionEvent;
use StateMachine\History\StateChange;
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
            $postTransitions = $this->getClassNamesFromEvent($eventDispatcher, Events::EVENT_POST_TRANSITION);
            $preTransitions = $this->getClassNamesFromEvent($eventDispatcher, Events::EVENT_PRE_TRANSITION);
            $guards = $this->getClassNamesFromEvent($eventDispatcher, Events::EVENT_ON_GUARD);

            $stateChange = new StateChange();
            $stateChange->setStateMachine($stateMachine->getName());
            $stateChange->setIdentifier($transitionEvent->getObject()->getId());
            $stateChange->setMessages($stateMachine->getMessages());
            $stateChange->setPassed(!$transitionEvent->isTransitionRejected());
            $stateChange->setPostTransitions($postTransitions);
            $stateChange->setPreTransitions($preTransitions);
            $stateChange->setGuards($guards);
            $stateChange->setTransition($transitionEvent->getTransition()->getName());
            $stateChange->setFailedCallBack($transitionEvent->getFailedCallback());

            $stateMachine->addStateChange($stateChange);
        }
    }

    /**
     * @param EventDispatcherInterface $eventDispatcher
     * @param string                   $eventName
     *
     * @return array
     */
    private function getClassNamesFromEvent(EventDispatcherInterface $eventDispatcher, $eventName)
    {
        $classNames = [];
        $listeners = $eventDispatcher->getListeners($eventName);
        foreach ($listeners as $listener) {
            $classNames[] = get_class($listener);
        }

        return $classNames;
    }
}
