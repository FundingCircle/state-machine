<?php

namespace StateMachine\History;

use StateMachine\StateMachine\StatefulInterface;
use StateMachine\StateMachine\StateMachineHistoryInterface;

interface HistoryManagerInterface
{
    /**
     * Load history for given stateful object.
     *
     * @param StatefulInterface            $statefulObject
     * @param StateMachineHistoryInterface $stateMachine
     *
     * @return HistoryCollection
     */
    public function load(StatefulInterface $statefulObject, StateMachineHistoryInterface $stateMachine);

    /**
     * Add one more record to the history.
     *
     * @param StatefulInterface $object
     * @param History           $history
     */
    public function add(StatefulInterface $object, History $history);
}
