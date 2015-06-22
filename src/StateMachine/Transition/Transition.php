<?php

namespace StateMachine\Transition;

use StateMachine\Exception\StateMachineException;
use StateMachine\State\StateInterface;

class Transition implements TransitionInterface
{

    private $fromState;

    private $toState;

    private $name;

    public function __construct(StateInterface $fromState = null, StateInterface $toState = null)
    {
        $this->fromState = $fromState;
        $this->toState = $toState;
        $this->name = $fromState->getName().static::EDGE_SYMBOL.$toState->getName();
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
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}
