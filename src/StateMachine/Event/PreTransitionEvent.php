<?php

namespace StateMachine\Event;

class PreTransitionEvent extends TransitionEvent
{
    /** @var  string */
    private $targetState;

    /**
     * @return string
     */
    public function getTargetState()
    {
        return $this->targetState;
    }

    /**
     * @param string $targetState
     */
    public function setTargetState($targetState)
    {
        $this->targetState = $targetState;
    }
}
