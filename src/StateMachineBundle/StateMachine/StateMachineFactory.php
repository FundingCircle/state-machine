<?php

namespace StateMachineBundle\StateMachine;

use Doctrine\Common\Persistence\Proxy;
use StateMachine\Accessor\StateAccessor;
use StateMachine\Exception\StateMachineException;
use StateMachine\History\HistoryManagerInterface;
use StateMachine\StateMachine\StatefulInterface;
use StateMachine\StateMachine\StateMachine;
use StateMachine\EventDispatcher\EventDispatcher;

/**
 * This factory is responsible of registering statemachine definition
 * and create statemachines on demand,
 * This is the only place where statemachine is created and booted.
 */
class StateMachineFactory
{
    /** @var  HistoryManagerInterface */
    private $historyManager;

    /** @var  string */
    private $transitionClass;

    /** @var  array */
    private $stateMachineDefinitions;

    /** @var  array */
    private $stateFullClasses;

    /**
     * @param HistoryManagerInterface $historyManager
     * @param null                    $transitionClass
     */
    public function __construct(HistoryManagerInterface $historyManager, $transitionClass = null)
    {
        $this->historyManager = $historyManager;
        $this->transitionClass = $transitionClass;
        $this->stateFullClasses = [];
    }

    /**
     * Register statemachine definition.
     *
     * @param array $definition
     */
    public function register(array $definition)
    {
        $this->stateMachineDefinitions[$definition['object']['class']] = $definition;
        $this->stateFullClasses[] = $definition['object']['class'];
    }

    /**
     * Get all state machines defintions.
     *
     * @return array
     */
    public function getDefinitions()
    {
        return $this->stateMachineDefinitions;
    }

    /**
     * Get one definition by id.
     *
     * @param $id
     *
     * @return mixed
     *
     * @throws StateMachineException
     */
    public function getDefinition($id)
    {
        foreach ($this->stateMachineDefinitions as $definition) {
            if ($definition['id'] == $id) {
                return $definition;
            }
        }

        throw new StateMachineException(sprintf("can't find definition %s", $id));
    }

    /**
     * Create and boot statemachine for given stateful object.
     *
     * @param StatefulInterface $statefulObject
     *
     * @return StateMachine
     *
     * @throws StateMachineException
     */
    public function get(StatefulInterface $statefulObject)
    {
        //@TODO cache booted statemachines
        $class = $this->getClass($statefulObject);
        if (!isset($this->stateMachineDefinitions[$class])) {
            throw new StateMachineException(
                sprintf(
                    'Definition for class :%s is not found, have you forgot to define statemachine in config.yml',
                    get_class($statefulObject)
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
            if (!isset($preTransition['callback'])) {
                $preTransition['callback'] = $class;
            }
            $stateMachine->addGuard(
                [$guard['callback'], $guard['method']],
                $guard['from'],
                $guard['to']
            );
        }
        //adding pre-transitions
        foreach ($definition['pre_transitions'] as $preTransition) {
            if (!isset($preTransition['callback'])) {
                $preTransition['callback'] = $class;
            }
            $stateMachine->addPreTransition(
                [$preTransition['callback'], $preTransition['method']],
                $preTransition['from'],
                $preTransition['to']
            );
        }
        //adding post-transitions
        foreach ($definition['pre_transitions'] as $postTransition) {
            if (!isset($preTransition['callback'])) {
                $preTransition['callback'] = $class;
            }
            $stateMachine->addPostTransition(
                [$postTransition['callback'], $postTransition['method']],
                $postTransition['from'],
                $postTransition['to']
            );
        }

        return $stateMachine;
    }

    /**
     * Get the class of an Doctrine entity.
     *
     * @param StatefulInterface $statefulObject
     *
     * @return string
     */
    private function getClass(StatefulInterface $statefulObject)
    {
        //if proxy class get the original class
        $class = ($statefulObject instanceof Proxy)
            ? get_parent_class($statefulObject)
            : get_class($statefulObject);

        //if class is found in the registered list
        if (isset($this->stateMachineDefinitions[$class])) {
            return $class;
        } else { //incase of a child class
            foreach ($this->stateFullClasses as $stateFullClass) {
                if (is_subclass_of($class, $stateFullClass)) {
                    return $stateFullClass;
                }
            }
        }
    }
}
