<?php

namespace StateMachine\History;

use StateMachine\StateMachine\StatefulInterface;
use StateMachine\StateMachine\StateMachineInterface;

interface HistoryManagerInterface
{
    /**
     * Load history for given stateful object.
     *
     * @param StatefulInterface     $statefulObject
     * @param StateMachineInterface $stateMachine
     *
     * @return HistoryCollection
     */
    public function load(StatefulInterface $statefulObject, StateMachineInterface $stateMachine);

    /**
     * Add one more record to the history.
     *
     * @param StatefulInterface $object
     * @param History           $history
     */
    public function add(StatefulInterface $object, History $history);
}
