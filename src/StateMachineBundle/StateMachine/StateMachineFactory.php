<?php

namespace StateMachineBundle\StateMachine;

use StateMachine\Accessor\StateAccessor;
use StateMachine\Exception\StateMachineException;
use StateMachine\History\HistoryManagerInterface;
use StateMachine\StateMachine\StatefulInterface;
use StateMachine\StateMachine\StateMachine;
use StateMachine\EventDispatcher\EventDispatcher;

/**
 * This factory is responsible of registering statemachine definition
 * and create statemachines on demand,
 * This is the only place where statemachine is created and booted
 */
class StateMachineFactory
{
    /** @var  HistoryManagerInterface */
    private $historyManager;

    /** @var  string */
    private $transitionClass;

    /** @var  array */
    private $stateMachineDefinitions;

    /**
     * @param HistoryManagerInterface $historyManager
     * @param null                    $transitionClass
     */
    public function __construct(HistoryManagerInterface $historyManager, $transitionClass = null)
    {
        $this->historyManager = $historyManager;
        $this->transitionClass = $transitionClass;
    }

    /**
     * Register statemachine definition
     *
     * @param array $definition
     */
    public function register(array $definition)
    {
        $this->stateMachineDefinitions[$definition['object']['class']] = $definition;
    }

    /**
     * Create and boot statemachine for given stateful object
     *
     * @param StatefulInterface $statefulObject
     *
     * @return StateMachine
     * @throws StateMachineException
     */
    public function get(StatefulInterface $statefulObject)
    {
        //@TODO cache booted statemachines
        $class = get_class($statefulObject);
        if (!isset($this->stateMachineDefinitions[$class])) {
            throw new StateMachineException(
                sprintf(
                    "Definition for class :%s is not found, have you forgot to define statemachine in config.yml",
                    $class
                )
            );
        }
        // get definition prepared by the container
        $definition = $this->stateMachineDefinitions[$class];
        //defining the StateMachine
        $eventDispatcher = new EventDispatcher();
        $stateMachine = new StateMachine(
            $statefulObject,
            new StateAccessor($definition['object']['property']),
            $this->transitionClass,
            $definition['options'],
            $definition['history_class'],
            $this->historyManager,
            $eventDispatcher
        );

        //adding states
        foreach ($definition['states'] as $name => $state) {
            $stateMachine->addState($name, $state['type']);
        }

        //adding transitions
        foreach ($definition['transitions'] as $transition) {
            $from = empty($transition['from']) ? null : $transition['from'];
            $to = empty($transition['to']) ? null : $transition['to'];
            $event = empty($transition['event']) ? null : $transition['event'];
            $stateMachine->addTransition($from, $to, $event);
        }

        //adding guards
        foreach ($definition['guards'] as $guard) {
            $stateMachine->addGuard(
                $guard["transition"],
                [$guard['callback'], $guard['method']]
            );
        }
        //adding pre-transitions
        foreach ($definition['pre_transitions'] as $preTransition) {
            $stateMachine->addPreTransition(
                $preTransition["transition"],
                [$preTransition['callback'], $preTransition['method']]
            );

        }
        //adding post-transitions
        foreach ($definition['pre_transitions'] as $postTransition) {
            $stateMachine->addPostTransition(
                $postTransition["transition"],
                [$postTransition['callback'], $postTransition['method']]
            );

        }

        return $stateMachine;
    }
}
