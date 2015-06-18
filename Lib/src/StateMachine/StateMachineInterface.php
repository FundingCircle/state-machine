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

    /**
     * @param TransitionInterface $transition
     *
     * @return void
     */
    public function addTransition(TransitionInterface $transition);

    /**
     * @return TransitionInterface[]
     */
    public function getAllowedTransitions();

    public function canTransitionTo($state);

    public function transitionTo($state);

    public function trigger($transition);
}
