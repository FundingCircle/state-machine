<?php
namespace StateMachine\StateMachine;

interface StateMachineHistoryInterface
{
    public function getHistory();

    public function getLastTransition();
}
