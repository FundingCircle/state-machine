<?php


namespace StateMachine\Lib\State;

use StateMachine\Lib\StateMachine\StateMachineInterface;

interface StatefulInterface
{
    public function setStateMachine(StateMachineInterface $stateMachine);

    public function getStateMachine();
}
