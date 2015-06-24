<?php

namespace StateMachineBundle\StateMachine;

use StateMachine\Accessor\StateAccessor;
use StateMachine\Accessor\StateAccessorInterface;
use StateMachine\Exception\StateMachineException;
use StateMachine\Listener\HistoryListenerInterface;
use StateMachine\State\StatefulInterface;
use StateMachine\StateMachine\StateMachine;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class StateMachineFactory
{
    /** @var  EventDispatcherInterface */
    private $eventDispatcher;

    /** @var  HistoryListenerInterface */
    private $historyListener;

    /** @var  string */
    private $transitionClass;

    /** @var  array */
    private $stateMachineDefinitions;

    /**
     * @param EventDispatcherInterface $eventDispatcher
     * @param HistoryListenerInterface $historyListener
     * @param string                   $transitionClass
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        HistoryListenerInterface $historyListener,
        $transitionClass
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->historyListener = $historyListener;
        $this->transitionClass = $transitionClass;
    }


    public function register(array $definition)
    {
        $this->stateMachineDefinitions[$definition['class']] = $definition;
    }

    public function get(StatefulInterface $statefulObject)
    {
        $class = get_class($statefulObject);
        if (!isset($this->stateMachineDefinitions[$class])) {
            throw new StateMachineException(
                sprintf(
                    "Definition for class :%s is not found, have you forgot to define statemachine in config.yml",
                    $class
                )
            );
        }
        $definition = $this->stateMachineDefinitions[$class];

        $stateMachine = new StateMachine(
            $statefulObject,
            $this->eventDispatcher,
            new StateAccessor($definition['property']),
            $this->historyListener,
            $this->transitionClass
        );
        //adding states
        foreach ($definition['states'] as $name => $state) {
            $stateMachine->addState($name, $state['type']);
        }

        //adding transitions
        foreach ($definition['transitions'] as $transition) {
            $from = empty($transition['from']) ? null : $transition['from'];
            $to = empty($transition['to']) ? null : $transition['to'];
            $addedTransitions = $stateMachine->addTransition($from, $to);

            foreach ($transition['guards'] as $guard) {
                foreach ($addedTransitions as $addedTransition) {
                    $stateMachine->addGuard($addedTransition->getName(), [$guard['callback'], $guard['method']]);
                }
            }
            foreach ($transition['pre_transitions'] as $guard) {
                foreach ($addedTransitions as $addedTransition) {
                    $stateMachine->addGuard($addedTransition->getName(), [$guard['callback'], $guard['method']]);
                }
            }
            foreach ($transition['pre_transitions'] as $guard) {
                foreach ($addedTransitions as $addedTransition) {
                    $stateMachine->addGuard($addedTransition->getName(), [$guard['callback'], $guard['method']]);
                }
            }

        }
        dump($definition);
        die;

        $stateMachine->boot();

        return $stateMachine;
    }
}
