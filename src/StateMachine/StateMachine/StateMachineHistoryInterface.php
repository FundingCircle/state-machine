<?php
namespace StateMachine\StateMachine;

use StateMachine\History\StateChangeInterface;
use StateMachine\Transition\TransitionInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

interface StateMachineHistoryInterface
{
    /**
     * @param StateChangeInterface $stateChange
     */
    public function addStateChange(StateChangeInterface $stateChange);

    /**
     * @return array
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
