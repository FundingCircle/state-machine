<?php
namespace StateMachine\StateMachine;

use StateMachine\History\HistoryCollection;
use StateMachine\History\StateChangeInterface;
use StateMachine\Transition\TransitionInterface;

interface StateMachineHistoryInterface
{
    /**
     * @param StateChangeInterface $stateChange
     */
    public function addStateChange(StateChangeInterface $stateChange);

    /**
     * @return HistoryCollection
     */
    public function getHistory();

    /**
     * @return TransitionInterface
     */
    public function getLastTransition();

    /**
     * @return string
     */
    public function getName();

    /**
     * @return array
     */
    public function getMessages();
}
