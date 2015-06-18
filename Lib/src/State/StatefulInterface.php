<?php


namespace StateMachine\State;

use StateMachine\StateMachine\StateMachineInterface;

interface StatefulInterface
{
    public function setStateMachine(StateMachineInterface $stateMachine);

    public function getStateMachine();
}
