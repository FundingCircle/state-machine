<?php
namespace StateMachine\Lib\StateMachine;

interface StateMachineHistoryInterface
{
    public function getHistory();

    public function getLastTransition();
}
