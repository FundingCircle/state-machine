<?php
namespace StateMachine\Lib\Traits;

use StateMachine\Lib\StateMachine\StateMachineInterface;

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
    public function setStateMachine($stateMachine)
    {
        $this->stateMachine = $stateMachine;
    }
}
