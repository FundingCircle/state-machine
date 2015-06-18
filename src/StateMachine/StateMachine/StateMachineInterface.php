<?php
namespace StateMachine\StateMachine;

use StateMachine\State\StatefulInterface;
use StateMachine\Transition\TransitionInterface;

interface StateMachineInterface
{
    /**
     * @param StatefulInterface $object
     *
     * @return void
     */
    public function setObject(StatefulInterface $object);

    /**
     * @return StatefulInterface
     */
    public function getObject();

    /**
     * @return string
     */
    public function getCurrentState();


    public function addTransition($from, $to);

    public function addState($name, $type);

    /**
     * @return TransitionInterface[]
     */
    public function getAllowedTransitions();

    public function canTransitionTo($state);

    /**
     * @param string $state
     *
     * @return bool
     */
    public function transitionTo($state);

    public function trigger($transition);
}
