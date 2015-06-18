<?php

namespace StateMachine\Lib\Transition;

use StateMachine\Lib\Exception\StateMachineException;
use StateMachine\Lib\State\StateInterface;

class Transition implements TransitionInterface
{
    private $fromState;

    private $toState;

    private $guards;

    private $name;

    public function __construct(StateInterface $fromState = null, StateInterface $toState = null)
    {
        if ($fromState == null && $toState == null) {
            throw new StateMachineException('At least "from" or "to" need to be defined');
        }
        $this->fromState = $fromState;
        $this->toState = $toState;
        $this->name = $fromState->getName().'_'.$toState->getName();
    }

    /**
     * @return StateInterface
     */
    public function getFromState()
    {
        return $this->fromState;
    }

    /**
     * @return StateInterface
     */
    public function getToState()
    {
        return $this->toState;
    }


    /**
     * @return \Closure[]
     **/
    public function getGuards()
    {
        return $this->guards;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}
