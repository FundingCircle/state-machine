<?php
namespace StateMachine\Lib\StateMachine;

use StateMachine\Lib\State\StatefulInterface;
use StateMachine\Lib\Transition\TransitionInterface;

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
