<?php

namespace StateMachine\History;

use StateMachine\StateMachine\StatefulInterface;
use StateMachine\StateMachine\StateMachineHistoryInterface;

class HistoryManager implements HistoryManagerInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(StatefulInterface $statefulObject, StateMachineHistoryInterface $stateMachine)
    {
        return $stateMachine->getHistory();
    }

    /**
     * {@inheritdoc}
     */
    public function add(StatefulInterface $object, History $history)
    {
        $object->getStateMachine()->getHistory()->add($history);
    }
}
