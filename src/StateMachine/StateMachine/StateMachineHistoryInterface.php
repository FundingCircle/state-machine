<?php
namespace StateMachine\StateMachine;

use StateMachine\History\HistoryCollection;
use StateMachine\Transition\TransitionInterface;

interface StateMachineHistoryInterface
{
    /**
     * @return HistoryCollection
     */
    public function getHistory();

    /**
     * @return TransitionInterface
     */
    public function getLastTransition();
}
