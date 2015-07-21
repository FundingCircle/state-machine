<?php


namespace StateMachine\State;

use StateMachine\StateMachine\StateMachineInterface;

interface StatefulInterface
{
    /**
     * @return int
     */
    public function getId();

    /**
     * @param StateMachineInterface $stateMachine
     */
    public function setStateMachine(StateMachineInterface $stateMachine);

    /**
     * @return StateMachineInterface
     */
    public function getStateMachine();

    /**
     * @return string
     */
    public function getState();

    /**
     * @param string $state
     */
    public function setState($state);
}
