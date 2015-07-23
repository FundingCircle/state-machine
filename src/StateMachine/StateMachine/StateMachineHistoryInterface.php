<?php
namespace StateMachine\StateMachine;

use StateMachine\History\HistoryCollection;
use StateMachine\History\History;
use StateMachine\Transition\TransitionInterface;

interface StateMachineHistoryInterface
{
    /**
     * @return HistoryCollection
     */
    public function getHistory();

    /**
     * @return History
     */
    public function getLastStateChange();
}
