<?php


namespace StateMachine\StateMachine;

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
}
