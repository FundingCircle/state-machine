<?php
namespace StateMachine\StateMachine;

use StateMachine\History\HistoryCollection;
use StateMachine\History\StateChange;
use StateMachine\Transition\TransitionInterface;

interface StateMachineHistoryInterface
{
    /**
     * @return HistoryCollection
     */
    public function getHistory();

    /**
     * @return StateChange
     */
    public function getLastStateChange();
}
