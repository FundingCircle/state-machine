<?php

namespace StateMachine\History;

use StateMachine\StateMachine\StatefulInterface;

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
     * @param StatefulInterface $object
     * @param History           $history
     */
    public function add(StatefulInterface $object, History $history);
}
