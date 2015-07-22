<?php

namespace StateMachineBundle\StateMachine;

use StateMachine\Accessor\StateAccessor;
use StateMachine\Exception\StateMachineException;
use StateMachine\Listener\HistoryListenerInterface;
use StateMachine\State\StatefulInterface;
use StateMachine\StateMachine\StateMachine;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * This factory is responsible of registering statemachine definition
 * and create statemachines on demand,
 * This is the only place where statemachine is created and booted
 */
class StateMachineFactory
{
    /** @var  string */
    private $transitionClass;

    /** @var  array */
    private $stateMachineDefinitions;

    /**
     * @param null $transitionClass
     */
    public function __construct($transitionClass = null)
    {
        $this->transitionClass = $transitionClass;
    }

    /**
     * Register statemachine definition
     *
     * @param array $definition
     */
    public function register(array $definition)
    {
        $this->stateMachineDefinitions[$definition['class']] = $definition;
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
        $definition = $this->stateMachineDefinitions[$class];

        $stateMachine = new StateMachine(
            $statefulObject,
            new StateAccessor($definition['property']),
            null,
            $this->transitionClass,
            $definition['options']
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
            $addedTransitions = $stateMachine->addTransition($from, $to, $event);

            //adding guards
            foreach ($transition['guards'] as $guard) {
                foreach ($addedTransitions as $addedTransition) {
                    $stateMachine->addGuard(
                        $addedTransition->getName(),
                        [$guard['callback'], $guard['method']]
                    );
                }
            }
            //adding pre-transitions
            foreach ($transition['pre_transitions'] as $guard) {
                foreach ($addedTransitions as $addedTransition) {
                    $stateMachine->addPreTransition(
                        $addedTransition->getName(),
                        [$guard['callback'], $guard['method']]
                    );
                }
            }
            //adding post-transitions
            foreach ($transition['pre_transitions'] as $guard) {
                foreach ($addedTransitions as $addedTransition) {
                    $stateMachine->addPostTransition(
                        $addedTransition->getName(),
                        [$guard['callback'], $guard['method']]
                    );
                }
            }

        }
        //booting the machine here, so it can't be changed somewhere else
        $stateMachine->boot();

        return $stateMachine;
    }
}
