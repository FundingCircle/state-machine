<?php

namespace StateMachine\StateMachine;

use StateMachine\History\HistoryCollection;
use StateMachine\History\History;

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

    /**
     * @return string
     */
    public function getHistoryClass();

    /**
     * @return StatefulInterface
     */
    public function getObject();
}
