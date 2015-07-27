<?php

namespace StateMachine\History;

use StateMachine\StateMachine\StatefulInterface;
use StateMachine\StateMachine\StateMachineHistoryInterface;

interface HistoryManagerInterface
{
    /**
     * Load history for given stateful object
     *
     * @param StatefulInterface $statefulObject
     *
     * @return HistoryCollection
     */
    public function load(StatefulInterface $statefulObject);

    /**
     * Add one more record to the history
     *
     * @param StateMachineHistoryInterface $stateMachine
     * @param History                      $history
     */
    public function add(StateMachineHistoryInterface $stateMachine, History $history);
}
