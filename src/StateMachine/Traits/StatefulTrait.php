<?php
namespace StateMachine\Traits;

use StateMachine\StateMachine\StateMachineInterface;

trait StatefulTrait
{
    /** @var StateMachineInterface */
    private $stateMachine;

    /**
     * @return StateMachineInterface
     */
    public function getStateMachine()
    {
        return $this->stateMachine;
    }

    /**
     * @param StateMachineInterface $stateMachine
     */
    public function setStateMachine(StateMachineInterface $stateMachine)
    {
        $this->stateMachine = $stateMachine;
    }
}
